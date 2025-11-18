<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SemiMonthlyPayrollService;
use Illuminate\Support\Carbon;

class GenerateSemiMonthlyPayslips extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payslips:generate-bi-monthly 
                            {--cutoff=auto : Cutoff number (1, 2, or auto)}
                            {--date= : Date for generation (Y-m-d format, defaults to today)}
                            {--send-email : Send email notifications to employees}
                            {--force : Bypass date validation (generate on any day)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate bi-monthly payslips (15th and end of month)';

    protected SemiMonthlyPayrollService $payrollService;

    /**
     * Create a new command instance.
     */
    public function __construct(SemiMonthlyPayrollService $payrollService)
    {
        parent::__construct();
        $this->payrollService = $payrollService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== Bi-Monthly Payslip Generator ===');
        $this->newLine();
        
        // Parse date option
        $date = $this->option('date') 
            ? Carbon::parse($this->option('date')) 
            : Carbon::now();
        
        $this->info('Generation Date: ' . $date->format('F d, Y'));
        
        // Determine cutoff
        $cutoffOption = $this->option('cutoff');
        
        if ($cutoffOption === 'auto') {
            $cutoff = SemiMonthlyPayrollService::getTodayCutoff();
            
            if (!$cutoff) {
                $this->error('Today is not a scheduled payout day (15th or last day of month).');
                $this->info('Use --cutoff=1 or --cutoff=2 to force generation.');
                return Command::FAILURE;
            }
            
            $this->info('Auto-detected cutoff: ' . $cutoff);
        } else {
            $cutoff = (int) $cutoffOption;
            
            if (!in_array($cutoff, [1, 2])) {
                $this->error('Invalid cutoff number. Must be 1, 2, or auto.');
                return Command::FAILURE;
            }
            
            $this->warn('Forcing cutoff: ' . $cutoff);
        }
        
        $sendEmail = $this->option('send-email');
        $force = $this->option('force');
        
        $this->newLine();
        $this->info('Cutoff: ' . ($cutoff === 1 ? '1st (1-15)' : '2nd (16-end of month)'));
        $this->info('Send Emails: ' . ($sendEmail ? 'Yes' : 'No'));
        if ($force) {
            $this->warn('⚠️  Date validation bypassed (--force flag)');
        }
        $this->newLine();
        
        // Show progress bar
        $this->info('Generating payslips...');
        $bar = $this->output->createProgressBar();
        $bar->start();
        
        // Generate payslips
        try {
            $results = $this->payrollService->generateBiMonthlyPayslips(
                $cutoff,
                $date,
                $sendEmail,
                $force // Pass bypass flag
            );
        } catch (\Exception $e) {
            $bar->finish();
            $this->newLine(2);
            $this->error('Generation failed: ' . $e->getMessage());
            $this->newLine();
            $this->info('Hint: Use --force to bypass date validation.');
            return Command::FAILURE;
        }
        
        $bar->finish();
        $this->newLine(2);
        
        // Display results
        $this->info('=== Generation Complete ===');
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Employees', $results['total']],
                ['Successfully Generated', $results['success']],
                ['Skipped/Failed', $results['skipped']],
            ]
        );
        
        // Show detailed results if needed
        if ($this->option('verbose')) {
            $this->newLine();
            $this->info('Detailed Results:');
            $this->table(
                ['Employee', 'Status', 'Amount/Reason'],
                collect($results['details'])->map(function ($item) {
                    return [
                        $item['employee'],
                        $item['status'],
                        $item['status'] === 'success' 
                            ? '₱' . number_format($item['amount'], 2)
                            : ($item['reason'] ?? 'N/A')
                    ];
                })
            );
        }
        
        return Command::SUCCESS;
    }
}
