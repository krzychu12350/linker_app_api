<?php

namespace App\Http\Controllers\User\Report;

use App\Enums\ReportStatus;
use App\Helpers\FileTypeHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\Report\StoreReportRequest;
use App\Models\File;
use App\Models\Report;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function __construct(private readonly NotificationService $notificationService)
    {
    }

    /**
     * Display a listing of the user's reports with their associated files.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Fetch reports with related files using Eager Loading
        $reports = $user->reports()
            ->with('files', 'reportedUser') // Eager load associated files
            ->latest()      // Order by the most recent reports
            ->paginate($request->per_page ?? 10); // Paginate the results (10 per page)

        return response()->json([
            'message' => 'Reports fetched successfully!',
            'data' => $reports,
        ]);
    }

    /**
     * Store a new report created by the user.
     */
    public function store(StoreReportRequest $request, User $user)
    {
        $user = Auth::user();

        // Create a new report
        $report = $user->reports()->create([
            'description' => $request->description,
            'type' => $request->type,
            'status' => ReportStatus::WAITING->value, // Default status is 'WAITING'
            'reported_user_id' => $request->reported_user_id,
        ]);

        // Handle file upload if it exists
        if ($request->hasFile('files')) {
            $fileIds = [];

            // Loop through all uploaded files
            foreach ($request->file('files') as $file) {
                $fileType = FileTypeHelper::getFileType($file); // Determine the file type

                // Upload file to Cloudinary
                $uploadedFile = cloudinary()->upload($file->getRealPath(), [
                    'resource_type' => 'auto',
                    'folder' => 'reports',
                ]);

                // Create a new File record in the database
                $fileRecord = File::create([
                    'url' => $uploadedFile->getSecurePath(),
                    'type' => $fileType,
                ]);

                $fileIds[] = $fileRecord->id;
            }

            // Associate the uploaded files with the created report
            $report->files()->attach($fileIds);
        }

        $users = User::whereRoles(['moderator', 'admin'])->get();

        foreach ($users as $user) {
            $this->notificationService->addNotification(
                $user,
                'User ' . $user->first_name . ' ' . $user->last_name . ' added a new report.'
            );
        }

        return response()->json([
            'message' => 'Report submitted successfully!',
            'data' => $report,
        ], 201);
    }
}
