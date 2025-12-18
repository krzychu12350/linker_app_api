<?php

namespace App\Models;

use App\Enums\ReportStatus;
use App\Enums\ReportType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    // Define the fillable fields for mass assignment protection
    protected $fillable = [
        'description',
        'type',
        'status',
        'reported_user_id', // The reported user
    ];

    // Casting 'type' to the ReportType enum
    protected $casts = [
        'type' => ReportType::class,
        'status' => ReportStatus::class,
    ];

    /**
     * Define the relationship between Report and User
     * A report belongs to a user.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the files associated with a report.
     */
    public function files()
    {
        return $this->belongsToMany(File::class, 'file_report', 'report_id', 'file_id')
            ->withTimestamps();
    }

    /**
     * Define the relationship between Report and the reported User
     * A report belongs to a reported user.
     */
    public function reportedUser(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_user_id');
    }
}
