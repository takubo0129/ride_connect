<?php

namespace App\Http\Controllers\Reservation;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\ReservationMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function store(Request $request, Reservation $reservation): RedirectResponse
    {
        $user = $request->user();

        if (! $reservation->isOwner($user->id) && ! $reservation->isRenter($user->id)) {
            abort(403);
        }

        if (! $reservation->isChatEnabled()) {
            return back()->with('error', 'この取引ではまだメッセージを送信できません。');
        }

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        ReservationMessage::create([
            'reservation_id' => $reservation->id,
            'sender_id' => $user->id,
            'body' => $validated['body'],
        ]);

        return back()->with('success', 'メッセージを送信しました。');
    }
}
