<?php

namespace App\Strategies\PhotoStorageStrategy;

use Cloudinary\Transformation\ImageTransformation;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class CloudinaryStorageStrategy implements PhotoStorageStrategy
{
    protected $cloudinary;

    public function __construct()
    {
        $this->cloudinary = new Cloudinary([
            'cloud' => [
                'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
                'api_key' => env('CLOUDINARY_API_KEY'),
                'api_secret' => env('CLOUDINARY_API_SECRET')
            ]
        ]);
    }

    /**
     * Store the photo in Cloudinary and return the file URL.
     *
     * @param \Illuminate\Http\UploadedFile $photo
     * @return string
     */
    public function store($photo): string
    {
        // Upload with transformation
        $data = cloudinary()->upload($photo->getRealPath(), [
            'folder' => 'profile_photos',
//            'transformation' => [
//                'width' => 400,
//                'height' => 400,
//                'crop' => 'fill'
//            ]
        ]);
       // dd('dwdwdw', $data->getPublicId());
//        return $data->getPublicId();
        return $data->getSecurePath();


//        dd($data);
//        dd($uploadedFileUrl);
//        return $uploadedFileUrl;
//        $imageName = time() . '_' . pathinfo($photo->getClientOriginalName(), PATHINFO_FILENAME);;
//
//        cloudinary()->upload($photo->getRealPath())->getSecurePath();
//        $result = $request->file('image')->storeOnCloudinaryAs('profile_photos', $imageName);
//
////        $uploadResult = $this->cloudinary->uploadApi()->upload($photo->getRealPath(), [
////            'folder' => 'profile_photos'
////        ]);
//
//        return $uploadResult['secure_url'];
    }

    public function remove($path): bool
    {
        try {
            //cloudinary()->destroy($path);
            $data = cloudinary()->destroy($path);
            return true;
        } catch (\Exception $exception) {
            return false;
        }
    }

    public function get($path): string
    {
        return cloudinary()->getUrl($path);
    }
}
