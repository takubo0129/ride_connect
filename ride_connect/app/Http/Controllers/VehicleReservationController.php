<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class VehicleReservationController extends Controller
{
    private const PERIOD_DAY_MAP = [
        '1day' => 1,
        '2day' => 2,
        '3day' => 3,
        '4day' => 4,
        '5day' => 5,
        '6day' => 6,
        '1week' => 7,
        '2week' => 14,
        '3week' => 21,
        '4week' => 28,
        '1month' => 30,
    ];

    public function store(Request $request, Vehicle $vehicle)
    {
        if ($vehicle->user_id === Auth::id()) {
            return back()->withErrors(['start_date' => '自身の車両に対しては予約申請できません。']);
        }

        $availablePeriods = $vehicle->sharing_periods ?? [];
        if (empty($availablePeriods)) {
            return back()->withErrors(['sharing_period' => 'この車両では現在予約可能な期間が設定されていません。']);
        }

        $availablePeriods = array_values(array_filter($availablePeriods, fn ($period) => isset(self::PERIOD_DAY_MAP[$period])));

        if (empty($availablePeriods)) {
            return back()->withErrors(['sharing_period' => 'この車両では現在予約可能な期間が設定されていません。']);
        }

        $validated = $request->validate([
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'sharing_period' => ['required', Rule::in($availablePeriods)],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $startDate = Carbon::parse($validated['start_date'])->startOfDay();
        $periodKey = $validated['sharing_period'];
        $days = self::PERIOD_DAY_MAP[$periodKey] ?? null;

        if (! $days) {
            return back()->withErrors(['sharing_period' => '選択したシェアリング期間は利用できません。']);
        }

        $endDate = $startDate->copy()->addDays($days)->subDay();

        $hasConflict = Reservation::where('vehicle_id', $vehicle->id)
            ->whereIn('status', Reservation::blockingStatuses())
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate->toDateString(), $endDate->toDateString()])
                    ->orWhereBetween('end_date', [$startDate->toDateString(), $endDate->toDateString()])
                    ->orWhere(function ($inner) use ($startDate, $endDate) {
                        $inner->where('start_date', '<=', $startDate->toDateString())
                            ->where('end_date', '>=', $endDate->toDateString());
                    });
            })
            ->exists();

        if ($hasConflict) {
            return back()->withErrors(['start_date' => '指定した期間には既に予約が入っています。別の日程をお試しください。'])->withInput();
        }

        Reservation::create([
            'vehicle_id' => $vehicle->id,
            'renter_id' => Auth::id(),
            'owner_id' => $vehicle->user_id,
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'status' => 'pending',
            'notes' => $validated['notes'] ?? null,
            'sharing_period' => $periodKey,
        ]);

        return redirect()->route('vehicles.show', $vehicle)->with('reservation_success', '予約申請を送信しました。オーナーからの返答をお待ちください。');
    }
}
