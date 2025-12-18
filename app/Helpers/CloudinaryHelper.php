<?php

namespace App\Helpers;

class CloudinaryHelper
{
    public static function getFileUrl(string $publicId):string
    {
        return "https://res.cloudinary.com/dm4zof0l0/video/upload/v1734207746/" . $publicId;
    }
}