<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SalarySettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now()->toDateTimeString();

        // Insert or update each setting payload using the same structure Spatie expects
        $settings = [
            ['group' => 'general_salary', 'name' => 'enable_tax', 'payload' => json_encode(false)],
            ['group' => 'general_salary', 'name' => 'emp_pagibig_percentage', 'payload' => json_encode(0)],
            ['group' => 'general_salary', 'name' => 'company_pagibig_percentage', 'payload' => json_encode(0)],
        ];

        foreach ($settings as $setting) {
            DB::table('settings')->updateOrInsert([
                'group' => $setting['group'],
                'name' => $setting['name'],
            ], [
                'payload' => $setting['payload'],
                'locked' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
