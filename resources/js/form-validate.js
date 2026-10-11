/* ============================================================
   Client-side validation for the two lead forms (contact page +
   quote modal).

   Both forms POST to /contact and are validated by StoreQuoteRequest.
   This module is the browser half of those same rules, and nothing more:
   a rule here that the server does not enforce would reject a lead the
   server would have accepted, which is the expensive failure mode. Keep
   the two in step — app/Http/Requests/StoreQuoteRequest.php.

   `novalidate` stays on both forms on purpose. The native bubbles fire
   one field at a time, cannot be styled, and vanish on the next click;
   these errors sit under the field, stay there, and are wired to the
   input through aria-describedby so a screen reader hears them.
   ============================================================ */

/** Server: 'email' => ['required', 'email:rfc', 'max:255']. */
const EMAIL_RE = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;

/** Characters a human types into a phone field. Letters are not among them. */
const PHONE_ALLOWED_RE = /^[0-9+\-\s().]+$/;

export const MESSAGES = {
    name: 'Please add your name.',
    email: 'Please enter a valid email address.',
    phone: 'Use digits only — for example +91 98765 43210.',
    message: 'Tell us a little about the project.',
    tooLong: 'This is longer than we can store — please shorten it.',
};

/**
 * The one rule table. `required` is checked against the TRIMMED value: a
 * name of three spaces is not a name, and it was passing both forms.
 */
const RULES = {
    name: { required: true, max: 255, message: MESSAGES.name },
    company: { required: false, max: 255, message: MESSAGES.tooLong },
    email: { required: true, max: 255, pattern: EMAIL_RE, message: MESSAGES.email },
    phone: { required: false, max: 64, pattern: PHONE_ALLOWED_RE, minDigits: 7, message: MESSAGES.phone },
    message: { required: true, max: 5000, message: MESSAGES.message },
    location: { required: false, max: 64, message: MESSAGES.tooLong },
};

/**
 * Validate one named value.
 *
 * @param {string} name  Field name as the server sees it.
 * @param {string} value Raw input value.
 * @returns {string|null} Error message, or null when the value is acceptable.
 */
export function fieldError(name, value) {
    const rule = RULES[name];
    if (!rule) return null;

    const trimmed = String(value ?? '').trim();

    if (trimmed === '') return rule.required ? rule.message : null;
    if (trimmed.length > rule.max) return MESSAGES.tooLong;
    if (rule.pattern && !rule.pattern.test(trimmed)) return rule.message;
    // A phone of "+" or "()" passes the character test and is still not a number.
    if (rule.minDigits && trimmed.replace(/\D/g, '').length < rule.minDigits) return rule.message;

    return null;
}

/**
 * Validate a map of name => value.
 *
 * @param {Record<string, string>} values
 * @returns {Record<string, string>} name => message, empty when all valid.
 */
export function validateValues(values) {
    const errors = {};
    Object.keys(values).forEach((name) => {
        const err = fieldError(name, values[name]);
        if (err) errors[name] = err;
    });

    return errors;
}

/** The `<small class="err">` belonging to an input, created on first need. */
function errorNodeFor(input) {
    const id = `${input.id || input.name}-err`;
    let node = document.getElementById(id);
    if (!node) {
        node = document.createElement('small');
        node.className = 'err';
        node.id = id;
        // Announced without moving focus: the field keeps it while the message
        // appears beside the field the person is still in.
        node.setAttribute('role', 'alert');
        input.insertAdjacentElement('afterend', node);
    }

    return node;
}

export function showFieldError(input, message) {
    const node = errorNodeFor(input);
    node.textContent = message;
    node.hidden = false;
    input.setAttribute('aria-invalid', 'true');
    input.setAttribute('aria-describedby', node.id);
}

export function clearFieldError(input) {
    const id = `${input.id || input.name}-err`;
    const node = document.getElementById(id);
    // Server-rendered messages carry the same id. Emptying rather than removing
    // keeps one node per field whichever half of the stack wrote it.
    if (node) { node.textContent = ''; node.hidden = true; }
    input.removeAttribute('aria-invalid');
    if (input.getAttribute('aria-describedby') === id) input.removeAttribute('aria-describedby');
}

/**
 * Validate every known field of a form or container and paint the results.
 *
 * @param {Element} scope Form, or any element holding the inputs.
 * @param {{focus?: boolean}} [opts] focus: move focus to the first invalid field.
 * @returns {boolean} true when the scope is valid.
 */
export function validateScope(scope, opts = {}) {
    const inputs = [...scope.querySelectorAll('input[name], textarea[name], select[name]')]
        .filter((el) => RULES[el.name] && el.type !== 'hidden');

    let firstInvalid = null;
    inputs.forEach((el) => {
        const err = fieldError(el.name, el.value);
        if (err) {
            showFieldError(el, err);
            if (!firstInvalid) firstInvalid = el;
        } else {
            clearFieldError(el);
        }
    });

    if (firstInvalid && opts.focus !== false) firstInvalid.focus();

    return firstInvalid === null;
}

/**
 * Wire live clearing: once a field is fixed its message goes away on blur
 * (and on input, if it was already flagged) rather than waiting for the next
 * submit. Only ever removes a message — it never flags a field the person has
 * not finished typing into.
 */
export function wireLiveValidation(scope) {
    const inputs = [...scope.querySelectorAll('input[name], textarea[name], select[name]')]
        .filter((el) => RULES[el.name] && el.type !== 'hidden');

    inputs.forEach((el) => {
        el.addEventListener('input', () => {
            if (el.getAttribute('aria-invalid') === 'true' && !fieldError(el.name, el.value)) clearFieldError(el);
        });
        el.addEventListener('blur', () => {
            // Blurring an untouched optional field must not accuse it of anything.
            if (el.value.trim() === '' && el.getAttribute('aria-invalid') !== 'true') return;
            const err = fieldError(el.name, el.value);
            if (err) showFieldError(el, err); else clearFieldError(el);
        });
    });
}
