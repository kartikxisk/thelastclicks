<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;

class SiteSetting extends Model
{
    protected $primaryKey = 'key';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['key', 'value_json'];

    protected $casts = ['value_json' => 'array'];

    public static function get(string $key, mixed $default = null): mixed
    {
        try {
            $row = static::find($key);
        } catch (QueryException) {
            return $default;
        }
        if (! $row) {
            return $default;
        }
        $v = $row->value_json;

        return is_array($v) && array_key_exists('v', $v) ? $v['v'] : $v;
    }

    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value_json' => ['v' => $value]]);
    }
}
