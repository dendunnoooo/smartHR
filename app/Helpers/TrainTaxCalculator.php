<?php

namespace App\Helpers;

class TrainTaxCalculator
{
    /**
     * Calculate monthly withholding tax based on TRAIN Law (RA 10963)
     * 
     * @param float $monthlySalary Monthly gross salary
     * @return float Monthly withholding tax
     */
    public static function calculateMonthlyTax(float $monthlySalary): float
    {
        // Convert monthly to annual
        $annualSalary = $monthlySalary * 12;
        
        // Calculate annual tax based on TRAIN brackets
        $annualTax = self::calculateAnnualTax($annualSalary);
        
        // Convert back to monthly
        return round($annualTax / 12, 2);
    }
    
    /**
     * Calculate annual tax based on TRAIN Law brackets
     * 
     * @param float $annualIncome Annual taxable income
     * @return float Annual tax amount
     */
    public static function calculateAnnualTax(float $annualIncome): float
    {
        if ($annualIncome <= 250000) {
            // 0 – 250,000: 0%
            return 0;
        } elseif ($annualIncome <= 400000) {
            // 250,001 – 400,000: 15% of excess over 250k
            return ($annualIncome - 250000) * 0.15;
        } elseif ($annualIncome <= 800000) {
            // 400,001 – 800,000: 22,500 + 20% of excess over 400k
            return 22500 + (($annualIncome - 400000) * 0.20);
        } elseif ($annualIncome <= 2000000) {
            // 800,001 – 2,000,000: 102,500 + 25% of excess over 800k
            return 102500 + (($annualIncome - 800000) * 0.25);
        } elseif ($annualIncome <= 8000000) {
            // 2,000,001 – 8,000,000: 402,500 + 30% of excess over 2M
            return 402500 + (($annualIncome - 2000000) * 0.30);
        } else {
            // Above 8,000,000: 2,202,500 + 35% of excess over 8M
            return 2202500 + (($annualIncome - 8000000) * 0.35);
        }
    }
    
    /**
     * Get the tax bracket information for a given monthly salary
     * 
     * @param float $monthlySalary Monthly gross salary
     * @return array Tax bracket details
     */
    public static function getTaxBracketInfo(float $monthlySalary): array
    {
        $annualSalary = $monthlySalary * 12;
        $annualTax = self::calculateAnnualTax($annualSalary);
        $monthlyTax = round($annualTax / 12, 2);
        
        $bracket = '';
        $rate = '';
        
        if ($annualSalary <= 250000) {
            $bracket = '₱0 - ₱250,000';
            $rate = '0%';
        } elseif ($annualSalary <= 400000) {
            $bracket = '₱250,001 - ₱400,000';
            $rate = '15% of excess over ₱250,000';
        } elseif ($annualSalary <= 800000) {
            $bracket = '₱400,001 - ₱800,000';
            $rate = '₱22,500 + 20% of excess over ₱400,000';
        } elseif ($annualSalary <= 2000000) {
            $bracket = '₱800,001 - ₱2,000,000';
            $rate = '₱102,500 + 25% of excess over ₱800,000';
        } elseif ($annualSalary <= 8000000) {
            $bracket = '₱2,000,001 - ₱8,000,000';
            $rate = '₱402,500 + 30% of excess over ₱2,000,000';
        } else {
            $bracket = 'Above ₱8,000,000';
            $rate = '₱2,202,500 + 35% of excess over ₱8,000,000';
        }
        
        return [
            'monthly_salary' => $monthlySalary,
            'annual_salary' => $annualSalary,
            'annual_tax' => $annualTax,
            'monthly_tax' => $monthlyTax,
            'bracket' => $bracket,
            'rate' => $rate,
        ];
    }
}
