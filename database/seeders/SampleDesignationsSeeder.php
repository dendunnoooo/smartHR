<?php

namespace Database\Seeders;

use App\Models\Designation;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SampleDesignationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $designations = [
            [
                'name' => 'Chief Executive Officer (CEO)',
                'description' => 'Top executive responsible for overall company strategy, operations, and major corporate decisions.'
            ],
            [
                'name' => 'Chief Technology Officer (CTO)',
                'description' => 'Leads technology strategy, innovation, and oversees all technology-related operations and development.'
            ],
            [
                'name' => 'Chief Financial Officer (CFO)',
                'description' => 'Manages financial planning, risk management, record-keeping, and financial reporting.'
            ],
            [
                'name' => 'Senior Software Engineer',
                'description' => 'Experienced developer responsible for designing, developing, and maintaining complex software systems.'
            ],
            [
                'name' => 'Software Engineer',
                'description' => 'Develops and maintains software applications, writes code, and collaborates with development teams.'
            ],
            [
                'name' => 'Junior Software Engineer',
                'description' => 'Entry-level developer learning and contributing to software development projects under supervision.'
            ],
            [
                'name' => 'HR Manager',
                'description' => 'Oversees human resources operations including recruitment, employee relations, and HR policies.'
            ],
            [
                'name' => 'HR Specialist',
                'description' => 'Handles specific HR functions such as recruitment, onboarding, training, or employee benefits.'
            ],
            [
                'name' => 'Accountant',
                'description' => 'Manages financial records, prepares financial statements, and ensures accuracy of financial data.'
            ],
            [
                'name' => 'Senior Accountant',
                'description' => 'Experienced accountant handling complex financial tasks, auditing, and supervising junior accountants.'
            ],
            [
                'name' => 'Sales Manager',
                'description' => 'Leads sales team, develops sales strategies, and manages client relationships to achieve revenue targets.'
            ],
            [
                'name' => 'Sales Executive',
                'description' => 'Actively engages with clients, generates leads, closes deals, and maintains customer relationships.'
            ],
            [
                'name' => 'Marketing Manager',
                'description' => 'Plans and executes marketing campaigns, manages brand strategy, and oversees marketing team.'
            ],
            [
                'name' => 'Marketing Specialist',
                'description' => 'Implements marketing strategies, creates content, manages social media, and analyzes campaign performance.'
            ],
            [
                'name' => 'Project Manager',
                'description' => 'Plans, executes, and closes projects while managing resources, timelines, and stakeholder communication.'
            ],
            [
                'name' => 'Business Analyst',
                'description' => 'Analyzes business processes, identifies improvements, and bridges communication between business and technical teams.'
            ],
            [
                'name' => 'Quality Assurance Engineer',
                'description' => 'Tests software applications, identifies bugs, ensures quality standards, and validates product functionality.'
            ],
            [
                'name' => 'DevOps Engineer',
                'description' => 'Manages infrastructure, automates deployment processes, and ensures system reliability and performance.'
            ],
            [
                'name' => 'Data Analyst',
                'description' => 'Collects, processes, and analyzes data to provide insights and support data-driven decision making.'
            ],
            [
                'name' => 'Customer Support Representative',
                'description' => 'Assists customers with inquiries, resolves issues, and provides product or service information.'
            ],
            [
                'name' => 'Administrative Assistant',
                'description' => 'Provides administrative support including scheduling, correspondence, and office management tasks.'
            ],
            [
                'name' => 'Legal Counsel',
                'description' => 'Provides legal advice, reviews contracts, handles legal matters, and ensures regulatory compliance.'
            ]
        ];

        foreach ($designations as $designation) {
            Designation::create($designation);
        }

        $this->command->info('Created ' . count($designations) . ' sample designations.');
    }
}
