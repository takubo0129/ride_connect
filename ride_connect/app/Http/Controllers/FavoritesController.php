<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use App\Models\Vehicle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class FavoritesController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();

        $favorites = Favorite::with(['vehicle.prefecture:id,name', 'vehicle.city:id,name'])
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        return view('favorites.index', [
            'favorites' => $favorites,
        ]);
    }

    public function toggle(Request $request, Vehicle $vehicle): JsonResponse|RedirectResponse
    {
        $user = Auth::user();

        $existing = Favorite::where('user_id', $user->id)
            ->where('vehicle_id', $vehicle->id)
            ->first();

        if ($existing) {
            $existing->delete();
            $favorited = false;
        } else {
            Favorite::create([
                'user_id' => $user->id,
                'vehicle_id' => $vehicle->id,
            ]);
            $favorited = true;
        }

        if ($request->expectsJson()) {
            return response()->json([
                'favorited' => $favorited,
                'favorites_count' => $vehicle->favorites()->count(),
            ]);
        }

        return back();
    }
}
