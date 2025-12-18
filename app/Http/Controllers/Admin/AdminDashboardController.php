<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\User;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    /**
     * Fetch reports with pagination.
     */
    public function index(Request $request)
    {
        return response()->json([
            [
                'name' => 'reports',
                'stat' => Report::all()->count(),  // Get count of reports
            ],
            [
                'name' => 'moderators',
                'stat' => User::role(UserRole::MODERATOR)->count(),  // Get count of moderators
            ],
            [
                'name' => 'users',
                'stat' => User::role(UserRole::USER)->count(),  // Get count of users
            ]
        ]);
    }
}
