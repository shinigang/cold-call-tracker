<?php

namespace App\Services;

use App\Models\Call;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{

    public function getAnalytics()
    {
        $stats = [];
        $analyticsType = request()->input('stats_type') ?? 'all';
        $durationDays = request()->input('duration') ?? 0;

        if ($analyticsType == 'calls' || $analyticsType == 'all') {
            $totalCalls = 0;
            $prevTotalCalls = 0;
            $successfulCalls = 0;
            $prevSuccessfulCalls = 0;
            $followUpCalls = 0;
            $prevFollowUpCalls = 0;
            $otherCategoryCalls = 0;
            $prevOtherCategoryCalls = 0;

            if ($durationDays == 0) {
                $totalCalls = Call::count();
                $successfulCalls = Call::where('status', 'Set Appointment Date')->count();
                $followUpCalls = Call::where('status', 'Call Again on Date')->count();
                $otherCategoryCalls = Call::whereNotIn('status', ['Set Appointment Date', 'Call Again on Date'])->count();
            } else {
                $totalCalls = Call::whereDate('created_at', '>=', now()->subDays($durationDays))->count();
                $prevTotalCalls = Call::whereDate('created_at', '<', now()->subDays($durationDays))->count();
                $successfulCalls = Call::where('status', 'Set Appointment Date')->whereDate('created_at', '>=', now()->subDays($durationDays))->count();
                $prevSuccessfulCalls = Call::where('status', 'Set Appointment Date')->whereDate('created_at', '<', now()->subDays($durationDays))->count();
                $followUpCalls = Call::where('status', 'Call Again on Date')->whereDate('created_at', '>=', now()->subDays($durationDays))->count();
                $prevFollowUpCalls = Call::where('status', 'Call Again on Date')->whereDate('created_at', '<', now()->subDays($durationDays))->count();
                $otherCategoryCalls = Call::whereNotIn('status', ['Set Appointment Date', 'Call Again on Date'])->whereDate('created_at', '>=', now()->subDays($durationDays))->count();
                $prevOtherCategoryCalls = Call::whereNotIn('status', ['Set Appointment Date', 'Call Again on Date'])->whereDate('created_at', '<', now()->subDays($durationDays))->count();
            }

            $stats = [
                'calls' => [
                    'totalCalls' => $totalCalls,
                    'prevTotalCalls' => $prevTotalCalls,
                    'successfulCalls' => $successfulCalls,
                    'prevSuccessfulCalls' => $prevSuccessfulCalls,
                    'followUpCalls' => $followUpCalls,
                    'prevFollowUpCalls' => $prevFollowUpCalls,
                    'otherCategoryCalls' => $otherCategoryCalls,
                    'prevOtherCategoryCalls' => $prevOtherCategoryCalls,
                ]
            ];
        }

        if ($analyticsType == 'activeCallers' || $analyticsType == 'all') {
            $stats = [
                ...$stats,
                'activeCallers' => User::where('metadata->logged_in', true)->whereDate('metadata->last_login_at', DB::raw('CURDATE()'))->get(),
            ];
        }

        if ($analyticsType == 'consultants' || $analyticsType == 'all') {
            $topConsultants = [];
            if ($durationDays == 0) {
                $topConsultants = User::query()->addSelect([
                    'total_appointments' => Call::query()->select(DB::raw('count(*)'))->where('status', 'Set Appointment Date')->whereColumn('consultant_id', 'users.id')->take(1)
                ])->having('total_appointments', '>', '0')->orderByRaw('total_appointments DESC')->limit(8)->get();
            } else {
                $topConsultants = User::query()->addSelect([
                    'total_appointments' => Call::query()->select(DB::raw('count(*)'))->where('status', 'Set Appointment Date')->whereDate('called_at', '>=', now()->subDays($durationDays))->whereColumn('consultant_id', 'users.id')->take(1),
                    'prev_total_appointments' => Call::query()->select(DB::raw('count(*)'))->where('status', 'Set Appointment Date')->whereDate('called_at', '<', now()->subDays($durationDays))->whereColumn('consultant_id', 'users.id')->take(1)
                ])->having('total_appointments', '>', '0')->orderByRaw('total_appointments DESC')->limit(8)->get();
            }

            $stats = [
                ...$stats,
                'consultants' => [
                    'topConsultants' => $topConsultants
                ]
            ];
        }

        return $stats;
    }
}
