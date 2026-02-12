<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\DashboardService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        private DashboardService $dashboardService
    ) {}

    /**
     * Display the hiring dashboard.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        if (! $user instanceof User) {
            abort(401);
        }

        return Inertia::render('Dashboard', [
            'stats' => $this->dashboardService->getHiringStats($user),
            'recent_activity' => $this->dashboardService->getRecentActivity($user),
            'top_talent' => $this->dashboardService->getTopTalent($user),
        ]);
    }
}
