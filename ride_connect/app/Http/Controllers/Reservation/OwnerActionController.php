<?php

namespace App\Http\Controllers\Reservation;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OwnerActionController extends Controller
{
    public function approve(Request $request, Reservation $reservation): RedirectResponse
    {
        $this->ensureOwner($reservation, $request->user()->id);

        if ($reservation->status !== Reservation::STATUS_PENDING) {
            return back()->with('error', 'この予約はすでに対応済みです。');
        }

        $reservation->update([
            'status' => Reservation::STATUS_APPROVED,
            'approved_at' => now(),
            'chat_enabled_at' => now(),
            'reject_reason' => null,
        ]);

        return back()->with('success', '予約申請を承認しました。事前チェックを進めてください。');
    }

    public function reject(Request $request, Reservation $reservation): RedirectResponse
    {
        $this->ensureOwner($reservation, $request->user()->id);

        if ($reservation->status !== Reservation::STATUS_PENDING) {
            return back()->with('error', 'この予約はすでに対応済みです。');
        }

        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $reservation->update([
            'status' => Reservation::STATUS_REJECTED,
            'rejected_at' => now(),
            'reject_reason' => $validated['reason'] ?? null,
        ]);

        return back()->with('success', '予約申請を拒否しました。');
    }

    public function completePreCheck(Request $request, Reservation $reservation): RedirectResponse
    {
        $this->ensureOwner($reservation, $request->user()->id);

        if (! in_array($reservation->status, [Reservation::STATUS_APPROVED, Reservation::STATUS_PRE_CHECK_COMPLETED], true)) {
            return back()->with('error', 'この取引は事前チェックを実施できない状態です。');
        }

        $items = config('reservations.pre_check_items', []);
        $validKeys = collect($items)->pluck('key')->all();

        $validated = $request->validate([
            'checks' => ['required', 'array'],
            'checks.*' => ['string', 'in:' . implode(',', $validKeys)],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        if (count(array_unique($validated['checks'])) !== count($validKeys)) {
            return back()->withInput()->with('error', 'すべてのチェック項目を確認してください。');
        }

        $reservation->update([
            'status' => Reservation::STATUS_PRE_CHECK_COMPLETED,
            'pre_check_completed_at' => now(),
            'pre_check_items' => [
                'items' => array_values($validated['checks']),
                'note' => $validated['note'] ?? null,
            ],
        ]);

        return back()->with('success', '乗車前チェックを完了しました。借主がレンタル開始できます。');
    }

    public function confirmReturn(Request $request, Reservation $reservation): RedirectResponse
    {
        $this->ensureOwner($reservation, $request->user()->id);

        if ($reservation->status !== Reservation::STATUS_ENDING_REQUESTED) {
            return back()->with('error', 'この取引は返却確認を行えません。');
        }

        $validated = $request->validate([
            'decision' => ['required', 'in:confirmed,waiting'],
        ]);

        if ($validated['decision'] === 'confirmed') {
            $reservation->update([
                'status' => Reservation::STATUS_RETURN_CONFIRMED,
                'owner_return_status' => 'confirmed',
                'return_confirmed_at' => now(),
            ]);

            return back()->with('success', '返却を確認しました。返却後チェックを行ってください。');
        }

        $reservation->update([
            'owner_return_status' => 'waiting',
        ]);

        return back()->with('info', '返却待機中として記録しました。改めて返却が完了した際に評価をお願いします。');
    }

    public function completePostCheck(Request $request, Reservation $reservation): RedirectResponse
    {
        $this->ensureOwner($reservation, $request->user()->id);

        if ($reservation->status !== Reservation::STATUS_RETURN_CONFIRMED) {
            return back()->with('error', '返却後チェックを実施できる状態ではありません。');
        }

        $items = config('reservations.post_check_items', []);
        $validKeys = collect($items)->pluck('key')->all();

        $validated = $request->validate([
            'checks' => ['required', 'array'],
            'checks.*' => ['string', 'in:' . implode(',', $validKeys)],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        if (count(array_unique($validated['checks'])) !== count($validKeys)) {
            return back()->withInput()->with('error', 'すべてのチェック項目を確認してください。');
        }

        $reservation->update([
            'status' => Reservation::STATUS_POST_CHECK_COMPLETED,
            'post_check_completed_at' => now(),
            'post_check_items' => [
                'items' => array_values($validated['checks']),
                'note' => $validated['note'] ?? null,
            ],
        ]);

        return back()->with('success', '返却後チェックを完了しました。「取引完了」ボタンで締めてください。');
    }

    public function completeDeal(Request $request, Reservation $reservation): RedirectResponse
    {
        $this->ensureOwner($reservation, $request->user()->id);

        if ($reservation->status !== Reservation::STATUS_POST_CHECK_COMPLETED) {
            return back()->with('error', '取引完了の操作ができない状態です。');
        }

        $reservation->update([
            'status' => Reservation::STATUS_COMPLETED,
            'completed_at' => now(),
        ]);

        return back()->with('success', '取引を完了しました。ご利用ありがとうございました。');
    }

    private function ensureOwner(Reservation $reservation, int $userId): void
    {
        if (! $reservation->isOwner($userId)) {
            abort(403);
        }
    }
}
