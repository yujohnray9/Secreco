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
        $cmiUser = User::updateOrCreate(
            ['email' => 'ejaquino@gmail.com'],
            [
                'first_name'  => 'EJ',
                'last_name'   => 'Aquino',
                'password'    => Hash::make('@Password123'),
                'role'        => 'cmi',
                'institution' => 'Isabela State University - Echague',
                'designation' => 'CMI Representative',
                'status'      => 'active',
            ]
        );

        // ── 3. FORMAT TEMPLATES (All 24 Standard CMI Tables) ─────────
        $templates = [
            ['table_no' => 'T1',   'title' => 'Summary of Agency In-House Reviews (AIHRs) conducted by consortium member-agencies.', 'section' => 'R&D Mgt. & Coord.', 'sort_order' => 1,  'is_required' => true],
            ['table_no' => 'T2a',  'title' => 'Summary of Regional Sectoral R&D Highlights (RSRDH) presented during the AIHR.',     'section' => 'R&D Mgt. & Coord.', 'sort_order' => 2,  'is_required' => true],
            ['table_no' => 'T2b',  'title' => 'Number of Participants during the Regional Sectoral R&D Highlights (RSRDH).',         'section' => 'R&D Mgt. & Coord.', 'sort_order' => 3,  'is_required' => false],
            ['table_no' => 'T3',   'title' => 'Projects Monitored/ Evaluated.',                                                      'section' => 'R&D Mgt. & Coord.', 'sort_order' => 4,  'is_required' => true],
            ['table_no' => 'T4',   'title' => 'Resources Shared with Other Member-Agencies.',                                        'section' => 'R&D Mgt. & Coord.', 'sort_order' => 5,  'is_required' => false],
            ['table_no' => 'T5',   'title' => 'Resources Generated.',                                                                'section' => 'R&D Mgt. & Coord.', 'sort_order' => 6,  'is_required' => false],
            ['table_no' => 'T6',   'title' => 'Linkages Established/ Maintained.',                                                   'section' => 'R&D Mgt. & Coord.', 'sort_order' => 7,  'is_required' => false],
            ['table_no' => 'T7a',  'title' => 'Databases Established/ Maintained/ Upgraded.',                                        'section' => 'R&D Mgt. & Coord.', 'sort_order' => 8,  'is_required' => false],
            ['table_no' => 'T7b',  'title' => 'Information Systems (IS) / Software Developed/ Maintained/ Upgraded.',               'section' => 'R&D Mgt. & Coord.', 'sort_order' => 9,  'is_required' => false],
            ['table_no' => 'T8a',  'title' => 'R&D Programs/ Projects/ Activities Funded/ Implemented.',                             'section' => 'Strategic R&D',     'sort_order' => 10, 'is_required' => true],
            ['table_no' => 'T8b',  'title' => 'Collaborative R&D Programs/ Projects Implemented.',                                   'section' => 'Strategic R&D',     'sort_order' => 11, 'is_required' => false],
            ['table_no' => 'T9',   'title' => 'Technologies Generated from R&D Projects.',                                          'section' => 'Strategic R&D',     'sort_order' => 12, 'is_required' => false],
            ['table_no' => 'T10',  'title' => 'Technology Transfer (TT) Programs/ Projects Funded/ Implemented.',                    'section' => 'Results Utilization','sort_order' => 13, 'is_required' => false],
            ['table_no' => 'T11',  'title' => 'Technologies Extended/ Adopted.',                                                     'section' => 'Results Utilization','sort_order' => 14, 'is_required' => false],
            ['table_no' => 'T12',  'title' => 'Commercialized Technologies.',                                                        'section' => 'Results Utilization','sort_order' => 15, 'is_required' => false],
            ['table_no' => 'T13',  'title' => 'List of Technology Promotion Approaches.',                                            'section' => 'Results Utilization','sort_order' => 16, 'is_required' => false],
            ['table_no' => 'T14',  'title' => 'Non-degree Trainings Conducted/ Attended.',                                           'section' => 'Capability & Gov.', 'sort_order' => 17, 'is_required' => false],
            ['table_no' => 'T15',  'title' => 'Equipment/ Facilities Funded.',                                                       'section' => 'Capability & Gov.', 'sort_order' => 18, 'is_required' => false],
            ['table_no' => 'T16',  'title' => 'Awards/ Recognitions Received.',                                                      'section' => 'Capability & Gov.', 'sort_order' => 19, 'is_required' => false],
            ['table_no' => 'T17',  'title' => 'Regular Meetings Conducted.',                                                         'section' => 'Capability & Gov.', 'sort_order' => 20, 'is_required' => false],
            ['table_no' => 'T18',  'title' => 'CMI Contributions to Consortium.',                                                    'section' => 'Capability & Gov.', 'sort_order' => 21, 'is_required' => false],
            ['table_no' => 'T19',  'title' => 'New Governance/ Policy Initiatives.',                                                 'section' => 'Capability & Gov.', 'sort_order' => 22, 'is_required' => false],
            ['table_no' => 'T20a', 'title' => 'Policy Researches Conducted.',                                                        'section' => 'Policy Analysis',   'sort_order' => 23, 'is_required' => false],
            ['table_no' => 'T20b', 'title' => 'Policies Formulated/ Advocated.',                                                     'section' => 'Policy Analysis',   'sort_order' => 24, 'is_required' => false],
        ];

        foreach ($templates as $tmpl) {
            FormatTemplate::updateOrCreate(
                ['year' => (int)date('Y'), 'table_no' => $tmpl['table_no']],
                array_merge($tmpl, [
                    'year'         => (int)date('Y'),
                    'status'       => 'active',
                    'columns_json' => ['Item Description', 'Agency', 'Remarks'],
                    'created_by'   => $ptaUser->id,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ])
            );
        }

    }
}
