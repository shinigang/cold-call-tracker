<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Middleware;
use Tightenco\Ziggy\Ziggy;
use App\Models\CallStatus;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            // 'csrf_token' => csrf_token(),
            'flash' => [
                'message' => fn () => $request->session()->get('message')
            ],
            'ziggy' => fn () => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
            'authUserCurrentTeam.role' => fn () => $request->user() ? $request->user()->teamRole($request->user()->currentTeam) : null,
            'callStatuses' => fn () => Cache::remember('call_status_list', 60 * 60, function () {
                return CallStatus::with('group')->get();
            }),
            'consultants' => fn () => ($request->user() ? Cache::remember('team_consultants_list', 60 * 60, function () {
                return request()->user()->currentTeam->users->where('role', '!=', 'caller')->all();
            }) : [])
        ];
    }
}
