<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $user = Auth::user();

        $latestVehicles = Vehicle::with(['prefecture:id,name', 'city:id,name'])
            ->latest()
            ->take(8)
            ->get();

        $userVehicles = $user
            ? Vehicle::with(['prefecture:id,name', 'city:id,name'])
                ->where('user_id', $user->id)
                ->latest()
                ->take(4)
                ->get()
            : collect();

        return view('home.index', [
            'latestVehicles' => $latestVehicles,
            'userVehicles' => $userVehicles,
            'needsProfile' => $user && $user->profile && ! $user->profile->onboarding_completed,
        ]);
    }
}
