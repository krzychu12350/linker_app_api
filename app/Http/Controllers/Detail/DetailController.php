<?php

namespace App\Http\Controllers\Detail;

use App\Http\Controllers\Controller;
use App\Services\UserInterestService;

class DetailController extends Controller
{
    public function __construct(private readonly UserInterestService $userInterestService)
    {
    }


    /**
     * Get all user details options (with or without subgroups).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $userDetails = $this->userInterestService->getAllUserDetails();

        return response()->json($userDetails);
    }
}
