<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
  protected $fillable = ['key', 'value'];

  public static function get($key, $default = null)
  {
    return Cache::rememberForever("setting.{$key}", function () use ($key, $default) {
      return static::query()->where('key', $key)->value('value') ?? $default;
    });
  }

  public static function set($key, $value)
  {
    Cache::forget("setting.{$key}");
    return static::updateOrCreate(['key' => $key], ['value' => $value]);
  }

  public static function clearCache(): void
  {
    Cache::flush();
  }
}
