<?php
use App\Models\Setting;

if (! function_exists('setting')) {
  function setting($key, $default = null)
  {
    return Setting::get($key, $default);
  }
}
if (! function_exists('userImage')) {
  function userImage($model, $collection = 'profile', $conversion = '')
  {
    $media = $model->getFirstMedia($collection);

    if ($media) {
      $path = $media->getPath($conversion ?:'');

      // check if file exists in server
      if (file_exists($path)) {
        return $media->getUrl($conversion ?:'');
      }
    }

    // fallback if not found or deleted
    return 'https://ui-avatars.com/api/?name=' . urlencode($model->name);
  }
}
if (!function_exists('getSettingImage')) {
  function getSettingImage($key = 'branding.logo_url', $collection = 'icon', $conversion = 'thumb', $defaultUrl = 'https://placehold.co/400')
  {
    // Use a static variable to store settings to prevent duplicate queries
    static $settings = [];

    // Check if the setting is already retrieved in this request
    if (!array_key_exists($key, $settings)) {
      $settings[$key] = \App\Models\Setting::where('key', $key)->first();
    }

    // Return the image URL or the default placeholder
    return $settings[$key]?->getFirstMediaUrl($collection, $conversion) ?? \setting('placeHolder', $defaultUrl);
  }
}
