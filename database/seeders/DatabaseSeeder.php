<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\SystemSetting;
use App\Models\FormatTemplate;
use App\Models\Notification;
use App\Models\ReportTable;
use App\Models\Submission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // ── 1. USERS ─────────────────────────────────────────────────
        $ptaUser = User::firstOrCreate(
            ['email' => 'pta@gmail.com'],
            [
                'first_name'  => 'PTA',
                'last_name'   => 'Admin',
                'password'    => Hash::make('password'),
                'role'        => 'pta',
                'designation' => 'Project Technical Assistant II',
                'status'      => 'active',
            ]
        );
    

        // ── 3. FORMAT TEMPLATES ───────────────────────────────────────
        $templates = [
            ['table_no' => 'T1',  'title' => 'R&D Projects Funded / Implemented',     'section' => 'R&D Mgt. & Coord.',   'sort_order' => 1, 'is_required' => true,  'columns_json' => ['Project Title','Leader','Budget','Funding Agency','Duration','Status']],
            ['table_no' => 'T2a', 'title' => 'Technologies Generated & Commercialized','section' => 'Results Utilization', 'sort_order' => 2, 'is_required' => true,  'columns_json' => ['Technology Name','Inventor','Adopter','Location','Adoption Date','Status']],
            ['table_no' => 'T2b', 'title' => 'Technologies Transferred to End-Users',  'section' => 'Results Utilization', 'sort_order' => 3, 'is_required' => false, 'columns_json' => ['Technology','Recipient','Date Transferred','Location','Terms']],
            ['table_no' => 'T3',  'title' => 'Publications in Refereed Journals',      'section' => 'Strategic R&D',        'sort_order' => 4, 'is_required' => true,  'columns_json' => ['Article Title','Authors','Journal Name','Volume/Issue','DOI','Year']],
            ['table_no' => 'T4',  'title' => 'Intellectual Property Applications',     'section' => 'Policy Analysis',     'sort_order' => 5, 'is_required' => false, 'columns_json' => ['IP Title','Inventors','Type','Filing Date','Status']],
            ['table_no' => 'T5',  'title' => 'Research Awards & Recognition',          'section' => 'Capability & Gov.',   'sort_order' => 6, 'is_required' => false, 'columns_json' => ['Award Title','Recipient','Awarding Body','Date','Scope']],
        ];

        foreach ($templates as $tmpl) {
            FormatTemplate::updateOrCreate(
                ['year' => (int)date('Y'), 'table_no' => $tmpl['table_no']],
                array_merge($tmpl, [
                    'year'       => (int)date('Y'),
                    'status'     => 'active',
                    'created_by' => $ptaUser->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }

    }
}
