<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreQuoteRequest extends FormRequest
{
    /**
     * Budget bands offered in the quote form. The stored value is the label
     * itself, which is why this is a flat list rather than a value => label
     * map — changing a label here rewrites what is already saved on existing
     * quotes, so treat these strings as data, not copy.
     *
     * One list for the /contact form, the quote popup (chrome.js reads it from a
     * meta tag) and the admin select. The popup used to hardcode its own bands,
     * so the same lead pipeline held two incompatible scales and a budget filter
     * in the admin could not compare them.
     *
     * @var list<string>
     */
    public const BUDGET_RANGES = [
        '< ₹1L',
        '₹1L – ₹3L',
        '₹3L – ₹7L',
        '₹7L – ₹15L',
        '₹15L+',
    ];

    /**
     * Bands the /contact form offered before the scales were unified. Still on
     * existing quotes, so the admin select must keep them as options — a Select
     * whose stored value is not an option renders blank and saves it away.
     *
     * @var list<string>
     */
    public const LEGACY_BUDGET_RANGES = [
        'Under ₹5L',
        '₹5L – ₹15L',
        '₹15L – ₹50L',
        '₹50L+',
    ];

    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'company' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email:rfc', 'max:255'],
            'phone' => ['nullable', 'string', 'max:64'],
            'project_type' => ['nullable', 'string', 'max:64'],
            'budget' => ['nullable', 'string', 'max:64'],
            'timeline' => ['nullable', 'string', 'max:64'],
            'message' => ['required', 'string', 'max:5000'],
            'source_page' => ['nullable', 'string', 'max:255'],
        ];
    }
}
