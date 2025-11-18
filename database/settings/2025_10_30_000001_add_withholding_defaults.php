<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        // Add Withholding Tax defaults so older installs won't throw MissingSettings
        $this->migrator->add('general_salary.emp_withholding_percentage', 0);
        $this->migrator->add('general_salary.company_withholding_percentage', 0);
    }
};
