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
        
        $cmiUsers = [
            ['email' => 'cmi@gmail.com',   'first_name' => 'Maria',    'last_name' => 'Santos',     'institution' => 'Isabela State University - Echague', 'designation' => 'Research Coordinator'],
            ['email' => 'cmi2@gmail.com',  'first_name' => 'Juan',     'last_name' => 'Dela Cruz',  'institution' => 'Cagayan State University',           'designation' => 'CMI Representative'],
            ['email' => 'cmi3@gmail.com',  'first_name' => 'Ana',      'last_name' => 'Reyes',      'institution' => 'Nueva Vizcaya State University',     'designation' => 'Science Research Analyst'],
            ['email' => 'cmi4@gmail.com',  'first_name' => 'Pedro',    'last_name' => 'Bautista',   'institution' => 'Quirino State University',           'designation' => 'Research Staff'],
            ['email' => 'cmi5@gmail.com',  'first_name' => 'Carla',    'last_name' => 'Mendoza',    'institution' => 'Batanes State College',              'designation' => 'Faculty Researcher'],
            ['email' => 'cmi6@gmail.com',  'first_name' => 'Roberto',  'last_name' => 'Garcia',     'institution' => 'NVSU - Bayombong',                   'designation' => 'Administrative Officer'],
        ];

        $createdCmiUsers = [];
        foreach ($cmiUsers as $u) {
            $createdCmiUsers[] = User::firstOrCreate(
                ['email' => $u['email']],
                array_merge($u, [
                    'password' => Hash::make('password'),
                    'role'     => 'cmi',
                    'status'   => 'active',
                ])
            );
        }

        User::firstOrCreate(
            ['email' => 'viewer@secreco.ph'],
            [
                'first_name'  => 'Elena',
                'last_name'   => 'Reyes',
                'password'    => Hash::make('password'),
                'role'        => 'viewer',
                'designation' => 'Monitoring Officer',
                'status'      => 'active',
            ]
        );

        // Pending users for approval testing
        $pendingUsers = [
            ['email' => 'pending@secreco.ph',  'first_name' => 'Roberto',  'last_name' => 'Gomez',    'institution' => 'Nueva Vizcaya State University'],
            ['email' => 'pending2@secreco.ph', 'first_name' => 'Maricris', 'last_name' => 'Tolentino', 'institution' => 'Isabela State University - Santiago'],
        ];
        foreach ($pendingUsers as $p) {
            User::firstOrCreate(
                ['email' => $p['email']],
                array_merge($p, [
                    'password'    => Hash::make('password'),
                    'role'        => 'cmi',
                    'designation' => 'Project Staff',
                    'status'      => 'pending',
                ])
            );
        }

        // ── 2. SYSTEM SETTINGS ────────────────────────────────────────
        $settingsData = [
            ['key' => 'current_cy',          'value' => (string)date('Y')],
            ['key' => 'active_year',         'value' => (string)date('Y')],
            ['key' => 'submission_deadline', 'value' => date('Y') . '-12-31'],
            ['key' => 'system_maintenance',  'value' => 'false'],
            ['key' => 'email_reminders',     'value' => '1'],
            ['key' => 'deadline_reminder_days', 'value' => '7,1'],
            ['key' => 'late_submission_policy', 'value' => 'allowed_with_approval'],
            ['key' => 'consortium_name',     'value' => 'CVAARRD Consortium Office'],
        ];
        foreach ($settingsData as $s) {
            SystemSetting::updateOrCreate(
                ['key' => $s['key']],
                ['value' => $s['value'], 'updated_by' => $ptaUser->id, 'updated_at' => now()]
            );
        }

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

        // ── 4. REPORT TABLES ──────────────────────────────────────────
        $reportData = [
            // cmi@secreco.ph — ISU Echague — fully done
            [$createdCmiUsers[0]->id, 'T1',  'done',  [['Project Title'=>'Smart Agriculture Monitoring System','Leader'=>'Maria Santos','Budget'=>'500,000','Funding Agency'=>'DOST','Duration'=>'12 Months','Status'=>'Ongoing']]],
            [$createdCmiUsers[0]->id, 'T2a', 'done',  [['Technology Name'=>'Organic Fertilizer Kit','Inventor'=>'Santos et al.','Adopter'=>'CVAARRD Farmers','Location'=>'Cauayan City','Adoption Date'=>'2024-03-15','Status'=>'Adopted']]],
            [$createdCmiUsers[0]->id, 'T3',  'done',  [['Article Title'=>'Rice Yield Optimization Methods','Authors'=>'M. Santos, J. Cruz','Journal Name'=>'SEARCA Journal','Volume/Issue'=>'Vol. 12 No. 2','DOI'=>'10.1234/sj.2024.001','Year'=>'2024']]],
            // cmi2@secreco.ph — CSU — partial (draft)
            [$createdCmiUsers[1]->id, 'T1',  'draft', [['Project Title'=>'Coastal Fisheries Assessment','Leader'=>'Juan Dela Cruz','Budget'=>'350,000','Funding Agency'=>'PCAARRD','Duration'=>'6 Months','Status'=>'Completed']]],
            [$createdCmiUsers[1]->id, 'T2a', 'not-started', []],
            // cmi3@secreco.ph — NVSU — in progress
            [$createdCmiUsers[2]->id, 'T1',  'done',  [['Project Title'=>'Highland Vegetable Production','Leader'=>'Ana Reyes','Budget'=>'280,000','Funding Agency'=>'BAR','Duration'=>'18 Months','Status'=>'Ongoing']]],
            [$createdCmiUsers[2]->id, 'T3',  'draft', [['Article Title'=>'Potato Virus Resistance Study','Authors'=>'A. Reyes','Journal Name'=>'Phil J Agri','Volume/Issue'=>'Vol. 8 No. 1','DOI'=>'10.5678/pja.2024','Year'=>'2024']]],
            // cmi4@secreco.ph — QSU — not started
            [$createdCmiUsers[3]->id, 'T1',  'not-started', []],
        ];

        foreach ($reportData as [$userId, $tableNo, $status, $rows]) {
            ReportTable::updateOrCreate(
                ['user_id' => $userId, 'reporting_year' => (int)date('Y'), 'table_no' => $tableNo],
                [
                    'meta_json'  => ['completed' => $status === 'done'],
                    'rows_json'  => $rows,
                    'status'     => $status,
                    'updated_at' => now()->subDays(rand(1,14)),
                ]
            );
        }

        // ── 5. SUBMISSIONS ────────────────────────────────────────────
        $submissionData = [
            [$createdCmiUsers[0]->id, 'Isabela State University - Echague', 'completed', now()->subDays(2)],
            [$createdCmiUsers[1]->id, 'Cagayan State University',           'pending',   now()->subDays(7)],
            [$createdCmiUsers[2]->id, 'Nueva Vizcaya State University',     'pending',   now()->subDays(5)],
        ];

        foreach ($submissionData as [$userId, $institution, $status, $submittedAt]) {
            Submission::updateOrCreate(
                ['user_id' => $userId, 'reporting_year' => (int)date('Y')],
                [
                    'institution'  => $institution,
                    'region'       => 'Region II',
                    'status'       => $status,
                    'submitted_at' => $submittedAt,
                    'year'         => (int)date('Y'),
                    'tables_filled'=> $status === 'completed' ? 3 : 1,
                    'form_data'    => ['submitted_tables' => $status === 'completed' ? ['T1','T2a','T3'] : ['T1']],
                ]
            );
        }

        // ── 6. NOTIFICATIONS ─────────────────────────────────────────
        $notifData = [
            ['user_id'=>$ptaUser->id, 'role'=>'pta', 'type'=>'submission',  'message'=>'Isabela State University submitted their CY ' . date('Y') . ' Accomplishment Report.',    'action_url'=>'/dashboard/pta/submissions', 'action_label'=>'View Submission', 'is_read'=>false, 'created_at'=>now()->subHours(5)],
            ['user_id'=>$ptaUser->id, 'role'=>'pta', 'type'=>'registration','message'=>'New user Roberto Gomez (NVSU) registered and is awaiting account approval.',               'action_url'=>'/dashboard/pta/users',       'action_label'=>'Manage Users',    'is_read'=>false, 'created_at'=>now()->subHours(12)],
            ['user_id'=>$ptaUser->id, 'role'=>'pta', 'type'=>'submission',  'message'=>'Cagayan State University submitted Table T1 for review.',                                  'action_url'=>'/dashboard/pta/submissions', 'action_label'=>'View Submission', 'is_read'=>true,  'created_at'=>now()->subDays(1)],
            ['user_id'=>$ptaUser->id, 'role'=>'pta', 'type'=>'registration','message'=>'New user Maricris Tolentino (ISU-Santiago) registered and is awaiting account approval.',  'action_url'=>'/dashboard/pta/users',       'action_label'=>'Manage Users',    'is_read'=>true,  'created_at'=>now()->subDays(2)],
            ['user_id'=>$createdCmiUsers[0]->id, 'role'=>'cmi', 'type'=>'system',     'message'=>'Welcome to CVAARRD SecReCo System. Annual reporting is now open.',               'action_url'=>'/dashboard/cmi/fillup',      'action_label'=>'Fill Up Report',  'is_read'=>true,  'created_at'=>now()->subDays(1)],
            ['user_id'=>$createdCmiUsers[0]->id, 'role'=>'cmi', 'type'=>'correction', 'message'=>'PTA has accepted your Table T1 submission. Great work!',                         'action_url'=>'/dashboard/cmi/submissions', 'action_label'=>'View Status',     'is_read'=>false, 'created_at'=>now()->subHours(3)],
        ];

        foreach ($notifData as $n) {
            Notification::create(array_merge($n, ['icon'=>'📄','color'=>'green']));
        }
    }
}
