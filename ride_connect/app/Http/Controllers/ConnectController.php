<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ConnectController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $reservationsQuery = Reservation::with([
            'vehicle.prefecture',
            'vehicle.city',
            'renter.profile',
            'owner.profile',
            'messages.sender.profile',
        ])->latest('updated_at');

        $pendingRequests = (clone $reservationsQuery)
            ->where('owner_id', $user->id)
            ->where('status', Reservation::STATUS_PENDING)
            ->orderByDesc('updated_at')
            ->get();

        $ownerActive = (clone $reservationsQuery)
            ->where('owner_id', $user->id)
            ->whereIn('status', [
                Reservation::STATUS_APPROVED,
                Reservation::STATUS_PRE_CHECK_COMPLETED,
                Reservation::STATUS_ACTIVE,
                Reservation::STATUS_ENDING_REQUESTED,
                Reservation::STATUS_RETURN_CONFIRMED,
                Reservation::STATUS_POST_CHECK_COMPLETED,
            ])
            ->orderByDesc('updated_at')
            ->get();

        $renterActive = (clone $reservationsQuery)
            ->where('renter_id', $user->id)
            ->whereIn('status', [
                Reservation::STATUS_APPROVED,
                Reservation::STATUS_PRE_CHECK_COMPLETED,
                Reservation::STATUS_ACTIVE,
                Reservation::STATUS_ENDING_REQUESTED,
                Reservation::STATUS_RETURN_CONFIRMED,
                Reservation::STATUS_POST_CHECK_COMPLETED,
            ])
            ->orderByDesc('updated_at')
            ->get();

        $history = (clone $reservationsQuery)
            ->where(function ($query) use ($user) {
                $query->where('owner_id', $user->id)
                    ->orWhere('renter_id', $user->id);
            })
            ->whereIn('status', [Reservation::STATUS_COMPLETED, Reservation::STATUS_REJECTED, Reservation::STATUS_CANCELED])
            ->orderByDesc('updated_at')
            ->limit(10)
            ->get();

        $preCheckItems = config('reservations.pre_check_items', []);
        $postCheckItems = config('reservations.post_check_items', []);
        $qaLinks = config('reservations.qa_links', []);

        return view('connect.index', [
            'pendingRequests' => $pendingRequests,
            'ownerActive' => $ownerActive,
            'renterActive' => $renterActive,
            'history' => $history,
            'preCheckItems' => $preCheckItems,
            'postCheckItems' => $postCheckItems,
            'qaLinks' => $qaLinks,
            'currentUser' => $user,
        ]);
    }
}
