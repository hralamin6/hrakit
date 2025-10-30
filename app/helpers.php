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
