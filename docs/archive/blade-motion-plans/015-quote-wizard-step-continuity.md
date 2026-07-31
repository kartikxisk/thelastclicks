# 015 — Make the quote wizard's steps move in the direction the user is going

- **Status**: TODO
- **Commit**: 8d8716b
- **Severity**: LOW (missed opportunity — additive)
- **Category**: Missed opportunities (AUDIT §8), Interruptibility (AUDIT §4)
- **Estimated scope**: 2 files (`resources/css/pages.css`, `resources/js/chrome.js`), ~30 lines

## Problem

```css
/* resources/css/pages.css:1840-1842 — current */
.quote__panel-step { display: none; animation: quoteIn 0.45s var(--ease-3); }
.quote__panel-step.is-on { display: block; }
@keyframes quoteIn { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }
```

```js
/* resources/js/chrome.js:358-374 — current */
      function render() {
        stepEls.forEach((s,i) => s.classList.toggle('is-on', i+1 <= step));
        panels.forEach(p => p.classList.toggle('is-on', +p.dataset.panel === step));
        if (fill) fill.style.width = ((step-1)/total)*100 + '%';
        if (backBtn) backBtn.style.visibility = step > 1 && step < 4 ? 'visible' : 'hidden';
        if (step === 4) {
          if (nav) nav.style.display = 'none';
          if (fill) fill.style.width = '100%';
          /* … */
```

Every step enters identically — rising 12px from below — whether the user pressed
**Continue** or **Back**. Going backwards looks exactly like going forwards, so
the motion carries no information about direction. It is the only cue the wizard
could give about where the user is in a four-step form, and it is spent saying
nothing.

Two smaller issues in the same code:

- **`visibility: hidden` on the Back button** (`resources/js/chrome.js:362`)
  makes it vanish and reappear with no transition as the user crosses step 1.
- **Step 4 hides the whole nav footer** with `nav.style.display = 'none'`
  (`:364`), which drops the panel's height in one frame at the exact moment the
  success state is trying to feel like a reward. AUDIT §8's third case —
  *"Rare, high-emotion moments (first-run, success, celebration) rendered with
  none of the delight budget they're allowed"* — this is the one moment in the
  whole form that has earned some delight, and it gets a layout jump.

`quoteIn` being a keyframe rather than a transition is worth noting but is **not**
a finding here: each step is a separate element toggling `display`, so there is
nothing to retarget and a restart is correct. Do not "fix" it into a transition.

## Target

**A. Direction-aware entrance.** A `data-dir` attribute on the wizard root
selects which keyframe runs.

```css
/* target — resources/css/pages.css:1840-1842 */
.quote__panel-step { display: none; }
.quote__panel-step.is-on { display: block; animation: quoteInFwd 0.3s var(--ease-out); }
.quote[data-dir="back"] .quote__panel-step.is-on { animation-name: quoteInBack; }

@keyframes quoteInFwd  { from { opacity: 0; transform: translateX(16px); } to { opacity: 1; transform: none; } }
@keyframes quoteInBack { from { opacity: 0; transform: translateX(-16px); } to { opacity: 1; transform: none; } }

@media (prefers-reduced-motion: reduce) {
  .quote__panel-step.is-on,
  .quote[data-dir="back"] .quote__panel-step.is-on { animation: none; }
}
```

The animation moves to the `.is-on` selector so it only runs on the step actually
being shown — currently it is declared on every `.quote__panel-step` including
the `display: none` ones, which is harmless but misleading.

Horizontal rather than vertical: a wizard is a left-to-right sequence, and 16px
of horizontal travel says "forward" or "back" in a way 12px of rise cannot.
0.45s → 0.3s brings it inside AUDIT §2's UI ceiling.

**B. Back button fades instead of blinking.**

```css
/* target — resources/css/pages.css, add near the .quote__back rule at :1918-1923 */
.quote__back {
  opacity: 0;
  pointer-events: none;
  transition: opacity 0.2s var(--ease-out), color 0.2s var(--ease-in-out);
}
.quote__back.is-shown { opacity: 1; pointer-events: auto; }
```

Read the existing `.quote__back` rule at `resources/css/pages.css:1918-1923`
first and merge these declarations into it rather than adding a second rule;
it already has a `transition: color 0.3s var(--ease);` line to replace.

**C. Step 4 keeps its footer height.** Instead of removing the nav from layout,
fade it out in place so the panel does not collapse.

```css
/* target — resources/css/pages.css, add after the .quote__nav rule */
.quote__nav {
  transition: opacity 0.2s var(--ease-out);
}
.quote__nav.is-hidden { opacity: 0; pointer-events: none; }
```

**D. The JS that drives all three.**

```js
/* target — resources/js/chrome.js:358-374, replacing render() */
      function render(dir) {
        quote.dataset.dir = dir === 'back' ? 'back' : 'fwd';
        stepEls.forEach((s,i) => s.classList.toggle('is-on', i+1 <= step));
        panels.forEach(p => p.classList.toggle('is-on', +p.dataset.panel === step));
        if (fill) fill.style.transform = `scaleX(${(step - 1) / total})`;
        if (backBtn) backBtn.classList.toggle('is-shown', step > 1 && step < 4);
        if (step === 4) {
          if (nav) nav.classList.add('is-hidden');
          if (fill) fill.style.transform = 'scaleX(1)';
          const nameOut = quote.querySelector('[data-out="name"]');
          const emailOut = quote.querySelector('[data-out="email"]');
          if (nameOut && data.name) nameOut.textContent = data.name;
          if (emailOut && data.email) emailOut.textContent = data.email;
        } else {
          if (nav) nav.classList.remove('is-hidden');
          if (nextLabel) nextLabel.textContent = step === 3 ? 'Send brief' : 'Continue →';
        }
      }
```

The `fill.style.transform` lines assume plan `005` has converted
`.quote__bar-fill` from `width` to `scaleX`. **Run plan 005 first.** If it has
not run, keep the two `fill.style.width` lines exactly as they are and change
only the direction/back-button/nav parts.

Every `render()` call site must pass a direction:

```js
/* resources/js/chrome.js:398  — current: if (step < 4) { step++; render(); } */
/* target:                       if (step < 4) { step++; render('fwd'); }      */

/* resources/js/chrome.js:430-431 — current: step++; render();                 */
/* target:                          step++; render('fwd');                     */

/* resources/js/chrome.js:452 — current:
   if (backBtn) backBtn.addEventListener('click', () => { if (step > 1) { step--; render(); } }); */
/* target:
   if (backBtn) backBtn.addEventListener('click', () => { if (step > 1) { step--; render('back'); } }); */

/* resources/js/chrome.js:453 — current: render();                             */
/* target:                       render('fwd');   // initial paint             */
```

## Repo conventions to follow

- `--ease-out` and `--ease-in-out` come from plan `004`. **Run plan 004 first.**
- State classes are `is-*`; data attributes on a container to switch a variant
  are not yet used in this repo, so `data-dir` on `.quote` is new — keep it to
  this one container and document it with the comment shown.
- The wizard's whole state machine lives inside `if (quote) { … }` at
  `resources/js/chrome.js:346-454`. Everything stays there.
- Chip and panel classes are toggled with `classList.toggle(name, condition)` —
  match that form rather than if/else.

## Steps

1. Confirm plan `005` has run: `grep -n "fill.style.width" resources/js/chrome.js`
   should return nothing. If it still matches, either run plan 005 first or use
   the fallback noted in section D and say so in your report.
2. `resources/css/pages.css:1840-1842` — replace with the target step rules and
   both keyframes, and add the reduced-motion block.
3. `resources/css/pages.css:1918-1923` — merge the `.quote__back` opacity /
   pointer-events / transition declarations into the existing rule, and add
   `.quote__back.is-shown`.
4. `resources/css/pages.css` — find the `.quote__nav` rule and add the
   `transition: opacity 0.2s var(--ease-out);` plus the `.quote__nav.is-hidden`
   rule.
5. `resources/js/chrome.js:358-374` — replace `render()` with the target.
6. `resources/js/chrome.js` — update all four `render()` call sites (lines 398,
   431, 452, 453) to pass a direction.
7. Delete the now-unused `backBtn.style.visibility` handling — it is replaced by
   the class toggle.
8. Run `npm run build` and confirm it exits 0.

## Boundaries

- Do NOT convert `quoteIn` from a keyframe to a transition. Each step is a
  separate `display: none` element; a keyframe is correct here.
- Do NOT change the wizard's logic: step numbering, validation
  (`resources/js/chrome.js:394`), the fetch, the honeypot field, or the success
  handling. Presentation only.
- Do NOT animate the step-indicator pips in the aside
  (`.quote__step`, `resources/js/chrome.js:359`) — they change colour, which is
  enough.
- Do NOT add a slide-out for the departing step. Because the steps are
  `display: none`/`block`, the old panel is gone before the new one animates;
  cross-fading them would need both in the DOM simultaneously and a position
  change, which is more restructuring than this earns.
- Do NOT touch `.quote__panel` (`resources/css/pages.css:1731-1745`) — plan `010`
  owns the modal entrance.
- Do NOT add a celebration animation to the success step. It was considered and
  is deliberately out of scope here; keeping the footer's height is the fix.
- If `resources/js/chrome.js:361` does not read
  `        if (fill) fill.style.width = ((step-1)/total)*100 + '%';` **and** plan
  005 has not run, the file has drifted since commit 8d8716b — STOP and report.

## Verification

- **Mechanical**: `npm run build` exits 0.
  `grep -n "backBtn.style.visibility" resources/js/chrome.js` returns no matches.
  `grep -c "render(" resources/js/chrome.js` — every call must have an argument;
  check each hit by eye.
- **Feel check**: open the quote modal and
  - Click Continue. The new step must slide in **from the right**.
  - Click Back. The previous step must slide in **from the left**. Alternate
    Continue / Back several times and confirm the direction always matches the
    button pressed. If both directions look the same, `data-dir` is not being
    written or the CSS specificity is wrong.
  - Watch the Back button as you move from step 1 to step 2: it must **fade** in,
    not blink into existence. Going back to step 1, it fades out.
  - Complete the form (or force `step = 4` in the console and call
    `render('fwd')`). The footer nav must **fade out in place** — the panel's
    height must not change. Compare against the current build, where the panel
    visibly shortens.
  - Confirm the faded-out nav is not clickable — try to click where the Continue
    button was.
  - Spam Continue rapidly on step 1. Each step restarts its entrance cleanly;
    nothing is left half-transparent.
  - Confirm the progress bar still advances one quarter per step and reaches full
    on the success step.
  - Tab through the form on step 1 — the hidden Back button must not receive
    focus (`pointer-events: none` does not block Tab; verify the button is either
    `disabled` or genuinely unreachable. If it is reachable, add
    `backBtn.disabled = !(step > 1 && step < 4);` to `render()` and note it).
  - Toggle `prefers-reduced-motion: reduce`: steps must swap instantly with no
    horizontal travel, and the Back button and nav must still fade (opacity is
    permitted under plan 008).
- **Done when**: forward and backward navigation are visually distinguishable,
  the Back button never blinks, the panel height is stable through step 4, and
  the hidden Back button is not keyboard-reachable.
