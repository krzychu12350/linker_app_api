<?php

namespace App\Http\Controllers\User\Photo;

use App\Enums\FileType;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\Photo\UpdateUserPhotoRequest;
use App\Models\File;
use App\Models\User;
use App\Strategies\PhotoStorageStrategy\PhotoStorageStrategy;
use Illuminate\Http\JsonResponse;

class UserProfilePhotoController extends Controller
{
    protected PhotoStorageStrategy $storageStrategy;

    public function __construct(PhotoStorageStrategy $storageStrategy)
    {
        $this->storageStrategy = $storageStrategy;
    }

    /**
     * Get all profile photos for the authenticated user.
     */
    public function index(): JsonResponse
    {
        $user = auth()->user(); // Get the authenticated user

        // Retrieve all the files related to the user
        $files = $user->files()->where('type', FileType::IMAGE)->get();

        // Transform file URLs to include the APP_URL with the storage path
        $photos = $files->map(function ($file) {
            return [
                'id' => $file->id,
                'url' => $file->url, // Full URL to the profile photo
            ];
        });

        return response()->json([
            'photos' => $photos,
        ], 200);
    }

    /**
     * Update or add multiple profile photos for a user.
     */
    public function update(UpdateUserPhotoRequest $request): JsonResponse
    {
        $user = $request->user();

        // Validate has already been done by UpdateUserPhotosRequest

        $uploadedFiles = [];

        // Access the 'photos' array from the request (which contains the uploaded files)
        foreach ($request->file('photos') as $photo) {
            // Store each file and get its URL
//            $path = $photo->store('profile_photos', 'public'); // Store the photo in the 'profile_photos' directory within 'public'
//            $url = Storage::url($path); // Get the URL of the stored file
            $url = $this->storageStrategy->store($photo);
           // dd( $url );
            // Create a new file record in the `files` table
            $file = File::create([
                'url' => $url,
                'type' => FileType::IMAGE,
            ]);

//            $file->url = $this->storageStrategy->get($file->url);

            // Attach the file to the user via the pivot table
            $user->files()->attach($file->id);

            // Add the file details to the uploadedFiles array
            $uploadedFiles[] = $file;
        }

        // Return a successful response with the uploaded files
        return response()->json([
            'message' => 'Profile photos updated successfully.',
            'files' => $uploadedFiles,
        ], 200);
    }

    /**
     * Remove a specific profile photo for the authenticated user.
     *
     * @param int $id
     * @param User $user
     * @return JsonResponse
     */
    public function destroy(User $user, int $id): JsonResponse
    {
       $user = auth()->user(); // Get the authenticated user
      //  dd($user);
        // Find the file by ID
        $file = $user->files()->where('files.id', $id)->first();

        // If the file does not exist, return an error
        if (!$file) {
            return response()->json(['message' => 'Photo not found.'], 404);
        }

        // Delete the file record from the `files` table
       // Storage::disk('public')->delete(parse_url($file->url, PHP_URL_PATH)); // Delete the photo from storage
        $this->storageStrategy->remove($file->url);
        $file->delete(); // Remove the file record from the `files` table

        // Remove the attachment in the pivot table (user_files)
        $user->files()->detach($file->id);

        return response()->json([], 204);
    }
}
