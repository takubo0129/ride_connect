<?php

namespace App\Http\Controllers\Reservation;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RenterActionController extends Controller
{
    public function startRide(Request $request, Reservation $reservation): RedirectResponse
    {
        $this->ensureRenter($reservation, $request->user()->id);

        if ($reservation->status !== Reservation::STATUS_PRE_CHECK_COMPLETED) {
            return back()->with('error', 'レンタル開始を行える状態ではありません。');
        }

        $reservation->update([
            'status' => Reservation::STATUS_ACTIVE,
            'ride_started_at' => now(),
        ]);

        return back()->with('success', 'レンタルを開始しました。安全運転でお楽しみください。');
    }

    public function requestEnd(Request $request, Reservation $reservation): RedirectResponse
    {
        $this->ensureRenter($reservation, $request->user()->id);

        if ($reservation->status !== Reservation::STATUS_ACTIVE) {
            return back()->with('error', 'レンタル終了を申請できる状態ではありません。');
        }

        $reservation->update([
            'status' => Reservation::STATUS_ENDING_REQUESTED,
            'ride_ended_at' => now(),
            'owner_return_status' => 'pending',
        ]);

        return back()->with('success', '返却手続きを申請しました。オーナーの確認をお待ちください。');
    }

    private function ensureRenter(Reservation $reservation, int $userId): void
    {
        if (! $reservation->isRenter($userId)) {
            abort(403);
        }
    }
}
