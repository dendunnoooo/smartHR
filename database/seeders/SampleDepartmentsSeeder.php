<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SampleDepartmentsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = [
            [
                'name' => 'Human Resources',
                'location' => 'Building A, 2nd Floor',
                'description' => 'Manages employee relations, recruitment, training, and development. Handles payroll, benefits, and compliance with labor laws.'
            ],
            [
                'name' => 'Information Technology',
                'location' => 'Building B, 3rd Floor',
                'description' => 'Responsible for managing company technology infrastructure, software development, cybersecurity, and technical support.'
            ],
            [
                'name' => 'Finance & Accounting',
                'location' => 'Building A, 5th Floor',
                'description' => 'Handles financial planning, budgeting, accounting operations, auditing, and financial reporting.'
            ],
            [
                'name' => 'Sales & Marketing',
                'location' => 'Building C, 1st Floor',
                'description' => 'Drives business growth through sales strategies, marketing campaigns, customer relationship management, and brand development.'
            ],
            [
                'name' => 'Operations',
                'location' => 'Building B, Ground Floor',
                'description' => 'Oversees daily business operations, process optimization, quality control, and operational efficiency.'
            ],
            [
                'name' => 'Customer Support',
                'location' => 'Building C, 2nd Floor',
                'description' => 'Provides customer service, handles inquiries, resolves issues, and ensures customer satisfaction.'
            ],
            [
                'name' => 'Research & Development',
                'location' => 'Building B, 4th Floor',
                'description' => 'Focuses on innovation, product development, research initiatives, and continuous improvement of offerings.'
            ],
            [
                'name' => 'Legal & Compliance',
                'location' => 'Building A, 6th Floor',
                'description' => 'Manages legal matters, contract review, regulatory compliance, and corporate governance.'
            ],
            [
                'name' => 'Administration',
                'location' => 'Building A, 1st Floor',
                'description' => 'Handles general administrative tasks, office management, facilities coordination, and support services.'
            ],
            [
                'name' => 'Quality Assurance',
                'location' => 'Building B, 2nd Floor',
                'description' => 'Ensures product and service quality through testing, quality control processes, and standards compliance.'
            ]
        ];

        foreach ($departments as $department) {
            Department::create($department);
        }

        $this->command->info('Created ' . count($departments) . ' sample departments.');
    }
}
