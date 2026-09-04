<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Google reCAPTCHA v2
    |--------------------------------------------------------------------------
    */
    'recaptcha_site_key'   => env('RECAPTCHA_SITE_KEY', '6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI'),
    'recaptcha_secret_key' => env('RECAPTCHA_SECRET_KEY', '6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe'),

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting (Login)
    |--------------------------------------------------------------------------
    */
    'rate_limit_max'    => 5,
    'rate_limit_window' => 900, // 15 minutes in seconds

    /*
    |--------------------------------------------------------------------------
    | Reporting Tables (all 24 table keys used across the system)
    |--------------------------------------------------------------------------
    */
    'all_tables' => [
        'T1', 'T2a', 'T2b', 'T3', 'T4', 'T5', 'T6', 'T7a', 'T7b',
        'T8a', 'T8b', 'T9', 'T10', 'T11', 'T12', 'T13', 'T14',
        'T15', 'T16', 'T17', 'T18', 'T19', 'T20a', 'T20b',
    ],

    'table_labels' => [
        'T1'   => 'Table 1 — Summary of AIHRs',
        'T2a'  => 'Table 2a — RSRDH Summary',
        'T2A'  => 'Table 2a — RSRDH Summary',
        'T2b'  => 'Table 2b — RSRDH Participants',
        'T2B'  => 'Table 2b — RSRDH Participants',
        'T3'   => 'Table 3 — Ongoing Projects',
        'T4'   => 'Table 4 — Completed Projects',
        'T5'   => 'Table 5 — Donations & Grants',
        'T6'   => 'Table 6 — Strategic R&D Plan',
        'T7a'  => 'Table 7a — ICT Resources',
        'T7A'  => 'Table 7a — ICT Resources',
        'T7b'  => 'Table 7b — ICT Issues',
        'T7B'  => 'Table 7b — ICT Issues',
        'T8a'  => 'Table 8a — R&D Personnel',
        'T8A'  => 'Table 8a — R&D Personnel',
        'T8b'  => 'Table 8b — Collaborative R&D Programs',
        'T8B'  => 'Table 8b — Collaborative R&D Programs',
        'T9'   => 'Table 9 — Tech Transfer',
        'T10'  => 'Table 10 — Publications',
        'T11'  => 'Table 11 — Awards',
        'T12'  => 'Table 12 — Training Needs',
        'T13'  => 'Table 13 — Trainings Attended',
        'T14'  => 'Table 14 — Capability Dev.',
        'T15'  => 'Table 15 — Governance',
        'T16'  => 'Table 16 — Ethics',
        'T17'  => 'Table 17 — Policy Recommendations',
        'T18'  => 'Table 18 — Policy Issuances',
        'T19'  => 'Table 19 — Policy Research',
        'T20a' => 'Table 20a — Budget Summary',
        'T20A' => 'Table 20a — Budget Summary',
        'T20b' => 'Table 20b — Budget Details',
        'T20B' => 'Table 20b — Budget Details',
    ],

    /*
    |--------------------------------------------------------------------------
    | Dashboard Sections (CMI)
    |--------------------------------------------------------------------------
    */
    'sections' => [
        'R&D Mgt. & Coord.'       => ['T1', 'T2a', 'T2b', 'T3', 'T4', 'T5', 'T6', 'T7a', 'T7b'],
        'Strategic R&D'           => ['T8a', 'T8b', 'T9'],
        'Results Utilization'     => ['T10', 'T11', 'T12', 'T13'],
        'Capability & Governance' => ['T14', 'T15', 'T16', 'T17', 'T18', 'T19'],
        'Policy Analysis'         => ['T20a', 'T20b'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Institutions (22 CVAARRD Members)
    |--------------------------------------------------------------------------
    */
    'institutions' => [
        'Isabela State University - Echague'    => ['short' => 'ISU-E',       'type' => 'State University', 'logo' => 1],
        'Isabela State University - Cabagan'    => ['short' => 'ISU-C',       'type' => 'State University', 'logo' => 1],
        'Batanes State College'                 => ['short' => 'BSC',         'type' => 'State College',    'logo' => 3],
        'Cagayan State University'              => ['short' => 'CSU',         'type' => 'State University', 'logo' => 4],
        'Nueva Vizcaya State University'        => ['short' => 'NVSU',        'type' => 'State University', 'logo' => 5],
        'Quirino State University'              => ['short' => 'QSU',         'type' => 'State University', 'logo' => 6],
        'University of La Salette'              => ['short' => 'ULS',         'type' => 'Private HEI',      'logo' => 7],
        'DA - Agricultural Training Institute Region II' => ['short' => 'DA-ATI',  'type' => "Gov't Agency", 'logo' => 8],
        'DA - Regional Field Office 2'          => ['short' => 'DA-RFO2',     'type' => "Gov't Agency",    'logo' => 9],
        'Bureau of Fisheries & Aquatic Resources - R2' => ['short' => 'BFAR-R2', 'type' => "Gov't Agency", 'logo' => 10],
        'Department of Environment and Natural Resources - Region II' => ['short' => 'DENR-R2', 'type' => "Gov't Agency", 'logo' => 11],
        'Department of Science and Technology - Region II' => ['short' => 'DOST-R2', 'type' => "Gov't Agency", 'logo' => 12],
        'Department of Trade and Industry - Region II' => ['short' => 'DTI-R2', 'type' => "Gov't Agency", 'logo' => 13],
        'Department of Economy, Planning and Development - Region II' => ['short' => 'DEPD-R2', 'type' => "Gov't Agency", 'logo' => 14],
        'National Tobacco Administration'       => ['short' => 'NTA',         'type' => "Gov't Agency",    'logo' => 15],
        'DA - Philippine Rice Research Institute - Isabela' => ['short' => 'PhilRice', 'type' => 'Research Institute', 'logo' => 16],
        'Philippine Council for Agriculture, Aquatic and Natural Resources Research and Development' => ['short' => 'PCAARRD', 'type' => "Gov't Agency", 'logo' => 17],
        'DA - Bureau of Agricultural Research'  => ['short' => 'DA-BAR',      'type' => "Gov't Agency",    'logo' => 18],
        'Watershed & Water Resources Research Development and Extension Center' => ['short' => 'WWRRDEC', 'type' => 'Research Center', 'logo' => 19],
        'Mabuwaya Foundation Inc.'              => ['short' => 'MFI',          'type' => 'NGO/Foundation',  'logo' => 20],
        'Government City of Santiago'           => ['short' => 'LGU-Santiago', 'type' => 'LGU',            'logo' => 21],
        'Commission on Higher Education - Regional Office 2' => ['short' => 'CHED-RO2', 'type' => "Gov't Agency", 'logo' => 22],
    ],

    /*
    |--------------------------------------------------------------------------
    | File Uploads
    |--------------------------------------------------------------------------
    */
    'upload_max_size_mb'  => 10,
    'image_max_size_mb'   => 5,
    'allowed_mime_types'  => ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf'],
    'allowed_image_types' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],

    /*
    |--------------------------------------------------------------------------
    | OTP Settings
    |--------------------------------------------------------------------------
    */
    'otp_expires_minutes' => 10,

];
