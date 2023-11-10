<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

use Carbon\Carbon;

class CallStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (DB::table('call_status_groups')->count() == 0) {
            DB::table('call_status_groups')->insert([
                [
                    'group' => __('Raw'),
                    'color' => '#b1b3b8',
                    'system_default' => true,
                    'created_at' => Carbon::now(),
                ],
                [
                    'group' => __('Cancellation (before interview)'),
                    'color' => '#f89898',
                    'system_default' => true,
                    'created_at' => Carbon::now(),
                ],
                [
                    'group' => __('Cancellation (after interview)'),
                    'color' => '#eebe77',
                    'system_default' => true,
                    'created_at' => Carbon::now(),
                ],
                [
                    'group' => __('Interested'),
                    'color' => '#95d475',
                    'system_default' => true,
                    'created_at' => Carbon::now(),
                ]
            ]);
        }

        if (DB::table('call_statuses')->count() == 0) {
            DB::table('call_statuses')->insert([
                [
                    'status_group_id' => 1,
                    'status' => __('Unprocessed'),
                    'system_default' => true,
                    'created_at' => Carbon::now(),
                ],
                [
                    'status_group_id' => 1,
                    'status' => __('Not reached'),
                    'system_default' => true,
                    'created_at' => Carbon::now(),
                ],
                [
                    'status_group_id' => 1,
                    'status' => __('Already a customer'),
                    'system_default' => true,
                    'created_at' => Carbon::now(),
                ],
                [
                    'status_group_id' => 2,
                    'status' => __('Wrong number'),
                    'system_default' => true,
                    'created_at' => Carbon::now(),
                ],
                [
                    'status_group_id' => 3,
                    'status' => __('Has no interest'),
                    'system_default' => true,
                    'created_at' => Carbon::now(),
                ],
                [
                    'status_group_id' => 3,
                    'status' => __('Website under construction'),
                    'system_default' => true,
                    'created_at' => Carbon::now(),
                ],
                [
                    'status_group_id' => 3,
                    'status' => __('Company non-existent'),
                    'system_default' => true,
                    'created_at' => Carbon::now(),
                ],
                [
                    'status_group_id' => 3,
                    'status' => __('Duplicate entry'),
                    'system_default' => true,
                    'created_at' => Carbon::now(),
                ],
                [
                    'status_group_id' => 3,
                    'status' => __("Doesn't want to be called anymore"),
                    'system_default' => true,
                    'created_at' => Carbon::now(),
                ],
                [
                    'status_group_id' => 4,
                    'status' => __('Call again on Date'),
                    'system_default' => true,
                    'created_at' => Carbon::now(),
                ],
                [
                    'status_group_id' => 4,
                    'status' => __('Set Appointment Date'),
                    'system_default' => true,
                    'created_at' => Carbon::now(),
                ]
            ]);
        }
    }
}
