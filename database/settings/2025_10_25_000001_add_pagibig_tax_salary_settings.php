<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        // Add Pag-IBIG and Withholding Tax defaults so older installs won't throw MissingSettings
        try {
            $this->migrator->add('general_salary.enable_tax', false);
        } catch (\Spatie\LaravelSettings\Exceptions\SettingAlreadyExists $e) {
            // already present, ignore
        }
        try {
            $this->migrator->add('general_salary.emp_pagibig_percentage', 0);
        } catch (\Spatie\LaravelSettings\Exceptions\SettingAlreadyExists $e) {
            // already present, ignore
        }
        try {
            $this->migrator->add('general_salary.company_pagibig_percentage', 0);
        } catch (\Spatie\LaravelSettings\Exceptions\SettingAlreadyExists $e) {
            // already present, ignore
        }
    }
};
