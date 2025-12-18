<?php

namespace App\Helpers;

use App\Enums\FileType;
use Illuminate\Http\UploadedFile;

class FileTypeHelper
{
    /**
     * Get the file type based on the file's MIME type or extension.
     *
     * @param UploadedFile $file
     * @return FileType
     */
    public static function getFileType(UploadedFile $file): FileType
    {
        // Get the MIME type of the file
        $mimeType = $file->getMimeType();

        // Check the MIME type and return the corresponding FileType enum
        switch (true) {
            case str_contains($mimeType, 'image'):
                return FileType::IMAGE;

            case str_contains($mimeType, 'audio'):
                return FileType::AUDIO;

            case str_contains($mimeType, 'video'):
                return FileType::VIDEO;

            case str_contains($mimeType, 'pdf') || str_contains($mimeType, 'msword') || str_contains($mimeType, 'text'):
                return FileType::DOCUMENT;

            default:
                return FileType::OTHER;
        }
    }
}
