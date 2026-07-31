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
     * Consumed by the Blade quote form and by the /api/v1/pages/contact
     * endpoint, so both offer the same options.
     *
     * @var list<string>
     */
    public const BUDGET_RANGES = [
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
