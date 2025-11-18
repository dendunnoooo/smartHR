<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LeaveType;

class LeaveTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            ['name' => 'Annual Leave', 'code' => 'AN', 'accrual_rate_per_month' => 1.5, 'requires_approval' => true, 'max_days' => 15],
            ['name' => 'Sick Leave', 'code' => 'SL', 'accrual_rate_per_month' => 0.0, 'requires_approval' => true, 'max_days' => 15],
            ['name' => 'Maternity Leave', 'code' => 'ML', 'accrual_rate_per_month' => 0.0, 'requires_approval' => true, 'max_days' => 110],
            ['name' => 'Paternity Leave', 'code' => 'PL', 'accrual_rate_per_month' => 0.0, 'requires_approval' => true, 'max_days' => 7],
            ['name' => 'Unpaid Leave', 'code' => 'UL', 'accrual_rate_per_month' => 0.0, 'requires_approval' => false, 'max_days' => null],
        ];

        foreach ($types as $t) {
            LeaveType::updateOrCreate(
                ['code' => $t['code']],
                $t
            );
        }
    }
}
