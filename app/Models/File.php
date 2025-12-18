<?php

namespace App\Models;

use App\Enums\FileExtension;
use App\Enums\FileType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    use HasFactory;

    // Define the table associated with the model (optional, if follows convention)
    protected $table = 'files';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

    // Specify the fillable attributes for mass assignment
    protected $fillable = [
        'url', // The URL of the image
        'type',
        'extension',
    ];


    protected $casts = [
        'type' => FileType::class,
        'extension' => FileExtension::class,
    ];

    /**
     * Get the users associated with this image.
     * Many-to-many relationship between File and User
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'image_user', 'image_id', 'user_id');
    }

    /**
     * Get the messages that the file is attached to.
     */
    public function messages()
    {
        return $this->belongsToMany(Message::class, 'file_message', 'file_id', 'message_id')
            ->withTimestamps();
    }
}
