<?php

namespace App\Strategies\PhotoStorageStrategy;

use Illuminate\Support\Facades\Storage;

class LocalStorageStrategy implements PhotoStorageStrategy
{
    public function get($path): string
    {
      return env('APP_URL')  . parse_url($path, PHP_URL_PATH);
    }

    public function store($photo): string
    {
        $path = $photo->store('profile_photos', 'public');
        return Storage::url($path);
    }

    public function remove($path): bool
    {
        // Check if the $path includes 'storage/' and remove it if it does
        $path = str_replace('storage/', '', $path);
        // Delete the file using the cleaned-up path
        return Storage::disk('public')->delete($path);
    }


}
