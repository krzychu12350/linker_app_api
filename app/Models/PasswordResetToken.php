<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PasswordResetToken extends Model
{
    use HasFactory;

    // Define the table name if not following Laravel's convention
    protected $table = 'password_reset_tokens';

    // Disable timestamps as we don't need created_at and updated_at
    public $timestamps = false;

    // Set email as the primary key
    protected $primaryKey = 'email';

    // Define the fillable attributes for mass assignment
    protected $fillable = ['email', 'token', 'created_at'];

    /**
     * Scope to check token expiration
     *
     * @return bool
     */
    public function isExpired(): bool
    {
        // Check if the token is older than 60 minutes (for example)
        return $this->created_at < now()->subMinutes(60);
    }
}
