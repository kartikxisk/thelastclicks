<?php

namespace App\Invoicing;

/**
 * GST state codes — the first two digits of every GSTIN, and the input to the
 * intra-state vs inter-state decision.
 *
 * Two legacy codes are deliberately absent: 25 (Daman and Diu, merged into 26
 * in 2020) and 28 (undivided Andhra Pradesh, split into 37 and 36). Neither can
 * appear in a GSTIN issued today, so offering them only lets someone pick a
 * state that will never match the counterparty's registration.
 */
final class StateCodes
{
    public const CODES = [
        '01' => 'Jammu and Kashmir',
        '02' => 'Himachal Pradesh',
        '03' => 'Punjab',
        '04' => 'Chandigarh',
        '05' => 'Uttarakhand',
        '06' => 'Haryana',
        '07' => 'Delhi',
        '08' => 'Rajasthan',
        '09' => 'Uttar Pradesh',
        '10' => 'Bihar',
        '11' => 'Sikkim',
        '12' => 'Arunachal Pradesh',
        '13' => 'Nagaland',
        '14' => 'Manipur',
        '15' => 'Mizoram',
        '16' => 'Tripura',
        '17' => 'Meghalaya',
        '18' => 'Assam',
        '19' => 'West Bengal',
        '20' => 'Jharkhand',
        '21' => 'Odisha',
        '22' => 'Chhattisgarh',
        '23' => 'Madhya Pradesh',
        '24' => 'Gujarat',
        '26' => 'Dadra and Nagar Haveli and Daman and Diu',
        '27' => 'Maharashtra',
        '29' => 'Karnataka',
        '30' => 'Goa',
        '31' => 'Lakshadweep',
        '32' => 'Kerala',
        '33' => 'Tamil Nadu',
        '34' => 'Puducherry',
        '35' => 'Andaman and Nicobar Islands',
        '36' => 'Telangana',
        '37' => 'Andhra Pradesh',
        '38' => 'Ladakh',
        '97' => 'Other Territory',
    ];

    public static function name(?string $code): ?string
    {
        return $code === null ? null : (self::CODES[$code] ?? null);
    }

    public static function exists(?string $code): bool
    {
        return $code !== null && array_key_exists($code, self::CODES);
    }

    /**
     * Select options. The code is part of the label because it is what a client
     * reads off their own GSTIN when checking we billed them correctly.
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];

        foreach (self::CODES as $code => $name) {
            $options[$code] = $code.' — '.$name;
        }

        return $options;
    }
}
