<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Payslip;

class ClearPayslips extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payslips:clear {--force : Skip confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete all payslip entries from database (irreversible).';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (! $this->option('force')) {
            if (! $this->confirm('Are you sure you want to DELETE ALL payslips? This action cannot be undone.')) {
                $this->info('Aborted. No payslips were deleted.');
                return 1;
            }
        }

        try {
            DB::beginTransaction();
            // disable foreign key checks for MySQL/SQLite compatibility when truncating
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            DB::table((new Payslip())->getTable())->truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            DB::commit();

            $this->info('All payslips have been deleted.');
            return 0;
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error('Failed to delete payslips: ' . $e->getMessage());
            return 1;
        }
    }
}
