<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Validate and update the given user's availability.
     *
     * @param  array<string, string>  $input
     */
    public function updateAvailability(): void
    {
        $user = request()->user();
        $input = request()->all();
        // dd($input['days_of_week']);
        Validator::make($input, [
            'days_of_week' => ['required'],
            'shift_start' => ['required', 'string'],
            'shift_end' => ['required', 'string'],
            'meeting_duration' => ['required', 'numeric'],
        ])->validateWithBag('updateAvailability');

        $days_of_week = [
            'Sunday' => false,
            'Monday' => false,
            'Tuesday' => false,
            'Wednesday' => false,
            'Thursday' => false,
            'Friday' => false,
            'Saturday' => false
        ];
        foreach ($input['days_of_week'] as $dow) {
            $days_of_week[$dow] = true;
        }

        $availability = $user->availability;
        $availability['days_of_week'] = $days_of_week;
        $availability['shift_start'] = $input['shift_start'];
        $availability['shift_end'] = $input['shift_end'];
        $availability['meeting_duration'] = $input['meeting_duration'];
        $user->availability = $availability;
        $user->save();
    }
}
