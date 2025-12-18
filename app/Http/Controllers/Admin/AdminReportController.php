<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BanType;
use App\Enums\ConversationType;
use App\Enums\MessageType;
use App\Enums\NotificationType;
use App\Helpers\Pusher;
use App\Http\Requests\Admin\Report\UpdateReportStatusRequest;
use App\Http\Resources\FileResource;
use App\Models\Notification;
use App\Models\Report;
use App\Enums\ReportStatus;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AdminReportController extends Controller
{
    public function __construct(private readonly NotificationService $notificationService)
    {
    }

    /**
     * Fetch reports with pagination.
     */
    public function index(Request $request)
    {
        $reports = Report::with(['user', 'reportedUser', 'files']) // Load associated user data
        ->latest('id')                // Order by latest reports
        ->paginate($request->per_page ?? 10);            // Paginate results

        return response()->json($reports);
    }

    /**
     * Show a single report with user and files.
     */
    public function show($id)
    {
        // Fetch the report with related user and files
        $report = Report::with(['user', 'files'])->findOrFail($id);

        return response()->json([
            'message' => 'Report fetched successfully!',
            'data' => $report,
        ]);
    }

    /**
     * Update the status of a report.
     */
    public function updateStatus(UpdateReportStatusRequest $request, int $id): JsonResponse
    {
        //ACCEPTED
        // 1. jesli status jest accepted to wymaga zablokowania uzytkownika stale albo tymczasowo
        // 2. dodanie notyfikacji do bazy 'wiadomosc', status unread i przypisanie to do obecnego usera
        // 3. trigger eventu pushera z przekazaniem danych tej notyfikacji, ze zablokowany i na ile

        //REJECTED
        // 2. dodanie notyfikacji do bazy 'wiadomosc', status unread i przypisanie to do obecnego usera
        // 3. trigger eventu pushera z przekazaniem danych tej notyfikacji, ze odrzucone

        $message = '';

        $validated = $request->validated();

        $report = Report::findOrFail($id);

        if ($validated ['status'] == ReportStatus::ACCEPTED->value) {
            $report->reportedUser->banUser($validated ['ban_type'], $validated ['banned_until']);
            $message = 'The report was accepted. User ' . $report->reportedUser->first_name . ' ' . $report->reportedUser->last . 'has been banned!';
        }

        if ($validated ['status'] == ReportStatus::REJECTED->value) {
            $message = 'The report was rejected. User ' . $report->reportedUser->first_name . ' ' . $report->reportedUser->last . 'has not been banned!';
        }

        $report->update([
            'status' => $validated['status']
        ]);

        $this->notificationService->addNotification(
            $report->user,
            $message
        );

        return response()->json([
            'message' => 'Report status updated successfully!',
            'data' => $report,
        ]);
    }

    /**
     * Delete a report and its related files (if any).
     */
    public function destroy($id)
    {
        $report = Report::findOrFail($id);

        // Detach related files before deleting the report
        $report->files()->detach();

        // Delete the report
        $report->delete();

        return response()->json([
            'message' => 'Report deleted successfully!',
        ], 204);
    }
}
