/**
 * cmi-excel.js — Excel Import & Template Download for CMI Fill Up Tables
 * Works offline using SheetJS (xlsx.full.min.js).
 * Supports all 24 built-in tables (T1 through T20b) and dynamic format templates.
 */

(function () {
  'use strict';

  // Table Schema Definitions
  const TABLE_SCHEMAS = {
    T1: {
      no: 'T1',
      title: 'Table 1. Summary of Agency In-House Reviews (AIHRs)',
      categories: ['Pre In-House', "AIHR's"],
      columns: [
        { key: 'category', header: 'Category', aliases: ['category', 'type', 'cat'], sample: 'Pre In-House' },
        { key: 'date', header: 'Date (YYYY-MM-DD)', aliases: ['date', 'review date'], sample: '2026-03-15' },
        { key: 'agency', header: 'Agency', aliases: ['agency', 'agency name', 'institution'], sample: 'DA-RFO 02' },
        { key: 'new_', header: 'New Projects', aliases: ['new', 'new_', 'new projects'], sample: 2 },
        { key: 'ongoing', header: 'Ongoing Projects', aliases: ['ongoing', 'ongoing projects'], sample: 5 },
        { key: 'completed', header: 'Completed Projects', aliases: ['completed', 'completed projects'], sample: 3 },
        { key: 'terminated', header: 'Terminated Projects', aliases: ['terminated', 'terminated projects'], sample: 0 },
      ],
      sampleRows: [
        { category: 'Pre In-House', date: '2026-03-15', agency: 'DA-RFO 02', new_: 2, ongoing: 5, completed: 3, terminated: 0 },
        { category: "AIHR's", date: '2026-05-20', agency: 'Isabela State University', new_: 1, ongoing: 4, completed: 2, terminated: 1 },
      ]
    },

    T2a: {
      no: 'T2a',
      title: 'Table 2a. Summary of Regional Symposium on R&D Highlights',
      categories: ['Research', 'Development'],
      columns: [
        { key: 'category', header: 'Category (Research/Development)', aliases: ['category', 'cat', 'type'], sample: 'Research' },
        { key: 'title', header: 'Title', aliases: ['title', 'project title', 'research title'], sample: 'Optimization of Citrus Production in Region II' },
        { key: 'agency', header: 'Implementing Agency(ies)', aliases: ['agency', 'implementing agency', 'institution'], sample: 'Nueva Vizcaya State University' },
        { key: 'researcher', header: 'Researcher(s)', aliases: ['researcher', 'researchers', 'author', 'lead researcher'], sample: 'Dr. Juan Dela Cruz' },
        { key: 'recommendations', header: 'Major Recommendations', aliases: ['recommendations', 'major recommendations', 'recommendation'], sample: 'Establish nursery certification protocols' },
        { key: 'winners', header: 'Winners', aliases: ['winners', 'winner', 'award', 'place'], sample: '1st Place' },
      ],
      sampleRows: [
        { category: 'Research', title: 'Optimization of Citrus Production in Region II', agency: 'Nueva Vizcaya State University', researcher: 'Dr. Juan Dela Cruz', recommendations: 'Establish nursery certification protocols', winners: '1st Place' },
        { category: 'Development', title: 'Commercialization of Goat Milk Soap', agency: 'Isabela State University', researcher: 'Prof. Maria Santos', recommendations: 'Scale up production to regional cooperatives', winners: '' },
      ]
    },

    T2b: {
      no: 'T2b',
      title: 'Table 2b. Number of Participants in the RSRDH',
      categories: ['Consortium Member-Institutions (CMIs)', 'Non-Consortium Member-Institutions'],
      columns: [
        { key: 'category', header: 'Category', aliases: ['category', 'cat', 'group'], sample: 'Consortium Member-Institutions (CMIs)' },
        { key: 'agency', header: 'Agency / Association', aliases: ['agency', 'association', 'institution'], sample: 'Isabela State University' },
        { key: 'count', header: 'No. of Participants', aliases: ['count', 'participants', 'no of participants', 'number of participants'], sample: 24 },
        { key: 'remarks', header: 'Remarks', aliases: ['remarks', 'notes', 'comment'], sample: 'Faculty, students, and researchers' },
      ],
      sampleRows: [
        { category: 'Consortium Member-Institutions (CMIs)', agency: 'Isabela State University', count: 24, remarks: 'Faculty and researchers' },
        { category: 'Non-Consortium Member-Institutions', agency: 'Regional Agribusiness Association', count: 10, remarks: 'Private industry observers' },
      ]
    },

    T3: {
      no: 'T3',
      title: 'Table 3. List of Projects and Activities Monitored and Evaluated',
      columns: [
        { key: 'project', header: 'Projects and Activities', aliases: ['project', 'title', 'program', 'project title', 'projects and activities', 'program / project title'], sample: 'Integrated Pest Management for Corn' },
        { key: 'status', header: 'Ongoing or Completed', aliases: ['status', 'project status', 'ongoing or completed'], sample: 'Ongoing' },
        { key: 'duration', header: 'Duration', aliases: ['duration', 'period', 'timeline'], sample: 'Jan 2026 - Dec 2026' },
        { key: 'fund', header: 'Source of Fund', aliases: ['fund', 'funding', 'funding agency', 'source', 'source of fund'], sample: 'DOST-PCAARRD' },
      ],
      sampleRows: []
    },

    T4: {
      no: 'T4',
      title: 'Table 4. Resources Shared',
      columns: [
        { key: 'donor', header: 'Donor / Source', aliases: ['donor', 'source', 'donor source'], sample: 'DA-BAR' },
        { key: 'activity', header: 'Activity / Project', aliases: ['activity', 'project', 'activity project'], sample: 'Consortium-led R&D and Technology Transfer' },
        { key: 'amount', header: 'Amount Shared', aliases: ['amount', 'amount shared', 'value'], sample: 150000 },
        { key: 'remarks', header: 'Remarks', aliases: ['remarks', 'notes'], sample: 'In-kind facility and logistical support' },
      ],
      sampleRows: [
        { donor: 'DA-BAR', activity: 'Consortium-led R&D and Technology Transfer', amount: 150000, remarks: 'In-kind facility and logistical support' }
      ]
    },

    T5: {
      no: 'T5',
      title: 'Table 5. Resources Generated by the Consortium',
      columns: [
        { key: 'donor', header: 'Donor / Source', aliases: ['donor', 'source', 'donor source'], sample: 'DOST Region II' },
        { key: 'activity', header: 'Activity / Project', aliases: ['activity', 'project', 'activity project'], sample: 'Regional Agri-Aqua Innovation Summit' },
        { key: 'amount', header: 'Amount Generated', aliases: ['amount', 'amount generated', 'value'], sample: 250000 },
        { key: 'remarks', header: 'Remarks', aliases: ['remarks', 'notes'], sample: 'Grant funds secured for regional activities' },
      ],
      sampleRows: [
        { donor: 'DOST Region II', activity: 'Regional Agri-Aqua Innovation Summit', amount: 250000, remarks: 'Grant funds secured for regional activities' }
      ]
    },

    T6: {
      no: 'T6',
      title: 'Table 6. Linkages Forged and Maintained',
      categories: [
        'Developed / New - Local',
        'Developed / New - Regional',
        'Developed / New - National',
        'Developed / New - International',
        'Maintained / Sustained - Local',
        'Maintained / Sustained - Regional',
        'Maintained / Sustained - National',
        'Maintained / Sustained - International',
      ],
      columns: [
        { key: 'scope', header: 'Category / Scope', aliases: ['scope', 'category', 'category / scope', 'scope / category', 'scope / level', 'scope / type', 'level', 'type'], sample: 'Developed / New - Local' },
        { key: 'agency', header: 'Agency / Institution', aliases: ['agency', 'institution', 'partner', 'agency / institution'], sample: 'Cagayan State University' },
        { key: 'address', header: 'Address', aliases: ['address', 'location'], sample: 'Tuguegarao City, Cagayan' },
        { key: 'year', header: 'Year', aliases: ['year', 'date'], sample: '2026' },
        { key: 'nature', header: 'Nature of Assistance / Linkages / Project', aliases: ['nature', 'assistance', 'project', 'nature of assistance'], sample: 'Joint research on native pig breeding and feed formulation' },
      ],
      sampleRows: []
    },

    T7a: {
      no: 'T7a',
      title: 'Table 7a. List of Databases Developed/ Enhanced and Maintained',
      categories: ['Developed / Enhanced', 'Maintained'],
      columns: [
        { key: 'category', header: 'Category', aliases: ['category', 'cat', 'type_cat'], sample: 'Developed / Enhanced' },
        { key: 'type', header: 'Type of Database', aliases: ['type', 'database type', 'name', 'database'], sample: 'Regional Citrus Germplasm Database' },
        { key: 'date', header: 'Date Created (YYYY-MM-DD)', aliases: ['date', 'date created', 'created'], sample: '2026-02-15' },
        { key: 'purpose', header: 'Purpose / Use', aliases: ['purpose', 'use', 'description'], sample: 'Cataloging local citrus accessions and disease status' },
      ],
      sampleRows: []
    },

    T7b: {
      no: 'T7b',
      title: 'Table 7b. List of Information Systems Developed/ Enhanced and Maintained',
      categories: ['Developed / Enhanced', 'Maintained'],
      columns: [
        { key: 'category', header: 'Category', aliases: ['category', 'cat', 'type_cat'], sample: 'Developed / Enhanced' },
        { key: 'type', header: 'Type of Information System', aliases: ['type', 'system', 'system type', 'name'], sample: 'Web-Based Commodity Price Monitoring System' },
        { key: 'date', header: 'Date Created (YYYY-MM-DD)', aliases: ['date', 'date created'], sample: '2026-03-01' },
        { key: 'purpose', header: 'Purpose / Use', aliases: ['purpose', 'use', 'description'], sample: 'Real-time retail price monitoring of high-value crops' },
      ],
      sampleRows: []
    },

    T8a: {
      no: 'T8a',
      title: 'Table 8a. List of R&D Programs/ Projects Packaged, Approved, and Implemented',
      categories: ['Proposals Packaged', 'Projects Approved and Implemented'],
      columns: [
        { key: 'category', header: 'Category', aliases: ['category', 'cat', 'status'], sample: 'Projects Approved and Implemented' },
        { key: 'title', header: 'Program / Project Title', aliases: ['title', 'program', 'project', 'project title'], sample: 'Corn Mechanization in Isabela' },
        { key: 'researcher', header: 'Researcher', aliases: ['researcher', 'lead researcher', 'author'], sample: 'Engr. Pedro Santos' },
        { key: 'agency', header: 'Implementing Agency', aliases: ['agency', 'implementing agency', 'institution'], sample: 'Isabela State University' },
        { key: 'duration', header: 'Duration (Start and End dates)', aliases: ['duration', 'dates', 'period'], sample: 'Jan 2026 - Dec 2027' },
        { key: 'funds', header: 'Source of Funds / Budget', aliases: ['funds', 'budget', 'source of funds', 'source'], sample: 'DOST-PCAARRD / PHP 1,500,000' },
        { key: 'commodity', header: 'Regional Priority Commodities Addressed', aliases: ['commodity', 'commodities', 'priority'], sample: 'Corn' },
      ],
      sampleRows: []
    },

    T8b: {
      no: 'T8b',
      title: 'Table 8b. Collaborative R&D Programs/ Projects Implemented',
      columns: [
        { key: 'program', header: 'Program Title', aliases: ['program', 'program title'], sample: 'Regional Fisheries R&D Program' },
        { key: 'project', header: 'Project Title', aliases: ['project', 'project title', 'title'], sample: 'Tilapia Fingerling Quality Enhancement' },
        { key: 'agency', header: 'Implementing Agency / Institution', aliases: ['agency', 'implementing agency', 'institution'], sample: 'BFAR Regional Office 02' },
        { key: 'duration', header: 'Duration', aliases: ['duration', 'period'], sample: 'Jan 2026 - Dec 2026' },
        { key: 'budget', header: 'Budget', aliases: ['budget', 'amount'], sample: 800000 },
        { key: 'source', header: 'Source(s) of Fund', aliases: ['source', 'funds', 'fund source'], sample: 'DA-BFAR' },
        { key: 'role', header: 'Role of Consortium', aliases: ['role', 'consortium role'], sample: 'Technical review, monitoring, and linkage coordination' },
      ],
      sampleRows: []
    },

    T9: {
      no: 'T9',
      title: 'Table 9. List of Technologies/ Information Generated from R&D',
      columns: [
        { key: 'title', header: 'Title of Technology / Brief Description', aliases: ['title', 'technology', 'description'], sample: 'Bio-organic Liquid Fertilizer Formulation' },
        { key: 'source', header: 'Project / Program Source', aliases: ['source', 'program', 'project source'], sample: 'Organic Soil Health Improvement Project' },
        { key: 'agency', header: 'Agency', aliases: ['agency', 'institution'], sample: 'Nueva Vizcaya State University' },
        { key: 'researcher', header: 'Researcher(s)', aliases: ['researcher', 'researchers', 'author'], sample: 'Dr. Maria Ramos' },
        { key: 'impact', header: 'Potential Impact or Contribution', aliases: ['impact', 'contribution', 'potential impact'], sample: 'Reduces commercial fertilizer expenditure by 30% for vegetable growers' },
      ],
      sampleRows: []
    },

    T10: {
      no: 'T10',
      title: 'Table 10. List of Technology Transfer Program/ Projects Packaged, Approved, and Implemented',
      categories: ['Proposals Packaged', 'Projects Approved and Implemented'],
      columns: [
        { key: 'category', header: 'Category', aliases: ['category', 'cat', 'status_group'], sample: 'Projects Approved and Implemented' },
        { key: 'title', header: 'Program / Project Title', aliases: ['title', 'program', 'project'], sample: 'Upgrading of Dairy Goat Production' },
        { key: 'agency', header: 'Agency Proponent', aliases: ['agency', 'proponent', 'institution'], sample: 'DA-RFO 02' },
        { key: 'priority', header: 'Regional Priority Addressed', aliases: ['priority', 'regional priority'], sample: 'Small Ruminants' },
        { key: 'status', header: 'Status (Approved / For Revision / Disapproved)', aliases: ['status', 'approval status'], sample: 'Approved' },
        { key: 'duration', header: 'Approved Duration', aliases: ['duration', 'approved duration'], sample: '1 Year (2026)' },
        { key: 'budget', header: 'Approved Budget', aliases: ['budget', 'approved budget'], sample: 500000 },
        { key: 'fund', header: 'Funding Source', aliases: ['fund', 'funding source', 'source'], sample: 'DA-BAR' },
      ],
      sampleRows: []
    },

    T11: {
      no: 'T11',
      title: 'Table 11. List of Technologies Extended/Deployed through Various Modalities',
      columns: [
        { key: 'tech', header: 'Name of Technology', aliases: ['tech', 'technology', 'name'], sample: 'Mushroom Spawn Production & Substrate Preparation' },
        { key: 'project', header: 'Project Title', aliases: ['project', 'title', 'project title'], sample: 'Community-Based Mushroom Enterprise' },
        { key: 'agency', header: 'Implementing Agency', aliases: ['agency', 'implementing agency', 'institution'], sample: 'Quirino State University' },
        { key: 'stcbf', header: 'STCBF', aliases: ['stcbf', 'stabs'], sample: '✓' },
        { key: 'stc4id', header: 'STC4iD', aliases: ['stc4id', 'stc4_id'], sample: '' },
        { key: 'safe', header: 'SAFE', aliases: ['safe'], sample: '' },
        { key: 'food_value_chain', header: 'Food Value Chain', aliases: ['food_value_chain', 'food value chain', 'fvc'], sample: '' },
        { key: 'others', header: 'Others (Specify / Details)', aliases: ['others', 'other', 'modality_other', 'details', 'remarks', 'other_extension_initiatives'], sample: 'Conducted farmer field schools in 3 municipalities' },
      ],
      sampleRows: [
        { tech: 'Mushroom Spawn Production & Substrate Preparation', project: 'Community-Based Mushroom Enterprise', agency: 'Quirino State University', stcbf: '✓', stc4id: '', safe: '', food_value_chain: '', others: 'Conducted farmer field schools in 3 municipalities' }
      ]
    },

    T12: {
      no: 'T12',
      title: 'Table 12. List of Technologies Commercialized or Pre-Commercialization Initiatives',
      columns: [
        { key: 'tech', header: 'Name of Technology', aliases: ['tech', 'technology', 'name'], sample: 'Bottled Citrus Calamansi Concentrate' },
        { key: 'owner', header: 'Technology Owner', aliases: ['owner', 'technology owner', 'agency'], sample: 'NVSU Citrus Research Center' },
        { key: 'precomm', header: 'Pre-Commercialization Activity Undertaken', aliases: ['precomm', 'pre_comm', 'pre commercialization', 'pre-commercialization', 'activity', 'pre commercialization activity undertaken'], sample: 'Shelf-life testing and FDA-LTO application' },
        { key: 'licensor', header: 'Commercialized - Licensor', aliases: ['licensor'], sample: 'Malabing Valley Multi-Purpose Coop' },
        { key: 'startup', header: 'Commercialized - Start-Up', aliases: ['startup', 'start-up'], sample: '' },
        { key: 'spinoff', header: 'Commercialized - Spin-Off', aliases: ['spinoff', 'spin-off'], sample: '' },
      ],
      sampleRows: [
        { tech: 'Bottled Citrus Calamansi Concentrate', owner: 'NVSU Citrus Research Center', precomm: 'Shelf-life testing and FDA-LTO application', licensor: 'Malabing Valley Multi-Purpose Coop', startup: '', spinoff: '' }
      ]
    },

    T13: {
      no: 'T13',
      title: 'Table 13. List of Technology Promotion Approaches',
      categories: [
        'Regional FIESTA',
        'Print (press releases, publications, newspapers, etc.)',
        'Radio/TV broadcast (features, plugs, podcasts, vlogs, etc.)',
        'ICT-based IEC materials (e-publications, web pages, videos)',
        'IEC-related trainings',
        'Other Technology Promotion (campaigns, caravans, etc.)'
      ],
      columns: [
        { key: 'category', header: 'Category', aliases: ['category', 'cat', 'approach type'], sample: 'Regional FIESTA' },
        { key: 'approach', header: 'Information, Education, and Communication (IEC) Approaches', aliases: ['approach', 'approaches', 'activity', 'title', 'information, education, and communication (iec) and integrated marketing communication (imc) approaches', 'iec/imc approaches', 'iec approaches'], sample: 'Citrus Nutrient Management Flyers (1,000 copies)' },
        { key: 'remarks', header: 'Remarks', aliases: ['remarks', 'notes', 'comment'], sample: 'Distributed to farmer participants during regional field day' },
      ],
      sampleRows: []
    },

    T14: {
      no: 'T14',
      title: 'Table 14. Non-degree Trainings Conducted/ Facilitated',
      columns: [
        { key: 'title', header: 'Title of Activity', aliases: ['title', 'activity', 'training'], sample: 'Training on Good Agricultural Practices (GAP) for Citrus' },
        { key: 'venue', header: 'Date / Venue', aliases: ['venue', 'date_venue', 'date', 'venue and date'], sample: 'April 15-16, 2026 / Bayombong, Nueva Vizcaya' },
        { key: 'participants', header: 'Number of Participants', aliases: ['participants', 'count', 'no of participants'], sample: 45 },
        { key: 'expenditures', header: 'Expenditures', aliases: ['expenditures', 'expenditure', 'cost', 'amount', 'expense'], sample: 65000 },
        { key: 'funds', header: 'Source of Funds', aliases: ['funds', 'fundsource', 'source', 'fund source'], sample: 'Consortium HRD Fund' },
      ],
      sampleRows: []
    },

    T15: {
      no: 'T15',
      title: 'Table 15. Equipment/ Facilities Funded',
      categories: [
        'Endorsed',
        'Approved',
        'Facilities Upgraded',
        'Facilities Established',
        'Equipment Purchased'
      ],
      columns: [
        { key: 'category', header: 'Category', aliases: ['category', 'cat', 'type', 'status'], sample: 'Endorsed' },
        { key: 'item', header: 'Item Description', aliases: ['item', 'item description', 'equipment', 'description'], sample: 'Refrigerated Micro-Centrifuge with Rotor' },
        { key: 'location', header: 'Location / Agency', aliases: ['location', 'agency', 'institution'], sample: 'CSU Carig Biotechnology Laboratory' },
        { key: 'expenditures', header: 'Expenditures', aliases: ['expenditures', 'cost', 'amount', 'expense'], sample: 380000 },
        { key: 'source', header: 'Source(s) of Funds', aliases: ['source', 'funds', 'fund source', 'source(s) of funds'], sample: 'DOST-PCAARRD Institutional Grant' },
      ],
      sampleRows: []
    },

    T16: {
      no: 'T16',
      title: 'Table 16. Awards Received',
      categories: ['Local', 'Regional', 'National', 'International'],
      columns: [
        { key: 'category', header: 'Category', aliases: ['category', 'cat', 'award category'], sample: 'National' },
        { key: 'award', header: 'Title of Award', aliases: ['award', 'title', 'award title'], sample: 'Best R&D Paper Award (Agriculture Category)' },
        { key: 'recipient', header: 'Recipient / Agency', aliases: ['recipient', 'agency', 'awardee'], sample: 'Dr. Juan Dela Cruz / ISU' },
        { key: 'sponsor', header: 'Sponsor', aliases: ['sponsor', 'sponsoring agency'], sample: 'DOST-PCAARRD' },
        { key: 'event', header: 'Event / Activity', aliases: ['event', 'activity', 'conference'], sample: 'National Agriculture Research Symposium' },
        { key: 'venue', header: 'Place of Award', aliases: ['venue', 'place', 'location'], sample: 'Manila, Philippines' },
        { key: 'date', header: 'Date of Award', aliases: ['date', 'date awarded', 'award date'], sample: 'November 15, 2026' },
      ],
      sampleRows: []
    },

    T17: {
      no: 'T17',
      title: 'Table 17. Schedule, Venue, Host Agencies of Regular Meetings',
      columns: [
        { key: 'type', header: 'Type of Meeting / Activity', aliases: ['type', 'activity', 'meeting', 'type of meeting', 'type of meeting / activity'], sample: '1st Quarter Regional R&D Council (RRC) Meeting' },
        { key: 'venue', header: 'Venue and Date', aliases: ['venue', 'venue_date', 'date and venue', 'venue date', 'venue and date', 'date', 'place'], sample: 'CSU Andrews Campus / March 25, 2026' },
        { key: 'host', header: 'Host Agency', aliases: ['host', 'host agency', 'agency'], sample: 'Cagayan State University' },
      ],
      sampleRows: [
        { type: '1st Quarter Regional R&D Council (RRC) Meeting', venue: 'CSU Andrews Campus / March 25, 2026', host: 'Cagayan State University' }
      ]
    },

    T18: {
      no: 'T18',
      title: 'Table 18. List of CMI Contributions',
      columns: [
        { key: 'cmi', header: 'Name of CMI', aliases: ['cmi', 'agency', 'institution', 'name of cmi'], sample: 'Isabela State University' },
        { key: 'amount', header: 'Amount of Contribution', aliases: ['amount', 'contribution', 'amount of contribution'], sample: 'PHP 250,000 (Cash & In-Kind)' },
      ],
      sampleRows: [
        { cmi: 'Isabela State University', amount: 'PHP 250,000 (Cash & In-Kind)' }
      ]
    },

    T19: {
      no: 'T19',
      title: 'Table 19. List of New Initiatives on Governance',
      columns: [
        { key: 'initiative', header: 'New Initiatives', aliases: ['initiative', 'new initiatives', 'title', 'description'], sample: 'Implementation of Online Research Proposal Management Portal' },
        { key: 'date', header: 'Date Conducted / Implemented', aliases: ['date', 'date conducted', 'date implemented'], sample: '2026-05-15' },
      ],
      sampleRows: [
        { initiative: 'Implementation of Online Research Proposal Management Portal', date: '2026-05-15' }
      ]
    },

    // ── Table 20a: Agency, Author, Description, Findings ──
    T20a: {
      no: 'T20a',
      title: 'Table 20a. Policy Researches Conducted',
      columns: [
        { key: 'project', header: 'Policy Analysis / Advocacy Project', aliases: ['project', 'policy analysis', 'advocacy project', 'title'], sample: 'Policy Study on Water Resource Allocation in Magat River Basin' },
        { key: 'agency', header: 'Agency', aliases: ['agency', 'institution'], sample: 'Nueva Vizcaya State University' },
        { key: 'author', header: 'Author', aliases: ['author', 'researcher', 'lead author'], sample: 'Dr. Aris Mercado' },
        { key: 'description', header: 'Description of the Project', aliases: ['description', 'project description', 'details'], sample: 'Assessment of irrigation distribution efficiency among smallholder rice growers' },
        { key: 'findings', header: 'Findings', aliases: ['findings', 'results', 'major findings'], sample: 'Identified a 25% water conveyance loss requiring canal lining investments' },
      ],
      sampleRows: [
        { project: 'Policy Study on Water Resource Allocation in Magat River Basin', agency: 'Nueva Vizcaya State University', author: 'Dr. Aris Mercado', description: 'Assessment of irrigation distribution efficiency among smallholder rice growers', findings: 'Identified a 25% water conveyance loss requiring canal lining investments' }
      ]
    },

    // ── Table 20b: Agency, Description ──
    T20b: {
      no: 'T20b',
      title: 'Table 20b. Policies Formulated, Advocated, Implemented, and Institutionalized',
      categories: ['Policy Formulated', 'Policy Advocated', 'Policy Implemented or Institutionalized'],
      columns: [
        { key: 'category', header: 'Category', aliases: ['category', 'cat', 'policy category', 'policy group'], sample: 'Policy Formulated' },
        { key: 'policy', header: 'List of Policies', aliases: ['policy', 'title', 'list of policies', 'policy title', 'ordinance', 'resolution'], sample: 'Ordinance No. 2025-917' },
        { key: 'agency', header: 'Agency', aliases: ['agency', 'institution', 'implementing agency'], sample: 'LGU-San Mateo' },
        { key: 'description', header: 'Description', aliases: ['description', 'policy description'], sample: 'Regional Ordinance on Citrus Planting Material Certification' },
      ],
      sampleRows: []
    }
  };

  // Provide uppercase & canonical aliases
  for (const k of Object.keys(TABLE_SCHEMAS)) {
    TABLE_SCHEMAS[k.toUpperCase()] = TABLE_SCHEMAS[k];
    TABLE_SCHEMAS[k.toLowerCase()] = TABLE_SCHEMAS[k];
  }

  /* ─────────────────────────────────────────
     CORE UTILITIES
  ───────────────────────────────────────── */
  function normalizeKey(str) {
    if (!str) return '';
    return String(str).toLowerCase().replace(/[^a-z0-9]/g, '');
  }

  function getActiveTableNo() {
    const raw = window._cmiActiveTable || 'T1';
    const s = String(raw).trim();
    const m = s.match(/^(T\d+)([a-zA-Z])?$/i);
    if (m) {
      return m[1].toUpperCase() + (m[2] ? m[2].toLowerCase() : '');
    }
    return s;
  }

  function resolveSchema(tableNo) {
    const tNo = tableNo || getActiveTableNo();
    const canon = String(tNo).trim();
    if (TABLE_SCHEMAS[canon]) return TABLE_SCHEMAS[canon];
    if (TABLE_SCHEMAS[canon.toUpperCase()]) return TABLE_SCHEMAS[canon.toUpperCase()];
    if (TABLE_SCHEMAS[canon.toLowerCase()]) return TABLE_SCHEMAS[canon.toLowerCase()];

    // Fallback: Resolve dynamic format template
    let tDef = null;
    if (window.CMI && typeof window.CMI === 'object') {
      const defs = window._templateDefs || {};
      tDef = defs[canon] || defs[canon.toUpperCase()] || defs[canon.toLowerCase()];
    }

    if (tDef) {
      let parsedCols = tDef.columns_json;
      if (typeof parsedCols === 'string') {
        try { parsedCols = JSON.parse(parsedCols); } catch (e) { parsedCols = null; }
      }
      let cols = [];
      let categories = [];
      if (parsedCols) {
        if (Array.isArray(parsedCols)) {
          cols = parsedCols;
        } else if (typeof parsedCols === 'object') {
          if (Array.isArray(parsedCols.titles) && parsedCols.titles.length > 0) {
            categories = parsedCols.titles.map(t => String(t.title || '').trim()).filter(Boolean);
            if (Array.isArray(parsedCols.titles[0]?.columns)) cols = parsedCols.titles[0].columns;
          }
          if (Array.isArray(parsedCols.categories)) categories = parsedCols.categories;
          if (Array.isArray(parsedCols.columns) && cols.length === 0) cols = parsedCols.columns;
        }
      }
      if (!cols || cols.length === 0) {
        cols = [
          { name: 'Title / Description', type: 'text' },
          { name: 'Agency / Institution', type: 'text' },
          { name: 'Amount / Value', type: 'number' },
          { name: 'Remarks', type: 'text' }
        ];
      }

      const schemaCols = [];
      if (categories.length > 0) {
        schemaCols.push({ key: 'category', header: 'Category', aliases: ['category', 'cat'], sample: categories[0] });
      }
      cols.forEach((c, idx) => {
        const name = typeof c === 'string' ? c : (c.name || c.label || `Column ${idx + 1}`);
        const colKey = typeof c === 'string' ? `col_${idx}` : (c.key || c.name || `col_${idx}`);
        schemaCols.push({
          key: colKey,
          header: name,
          aliases: [name, colKey, normalizeKey(name)],
          sample: (c.type === 'number' || c.type === 'amount') ? 100 : (c.type === 'date' ? '2026-01-01' : `Sample ${name}`)
        });
      });

      return {
        no: canon,
        title: tDef.title || `Table ${canon}`,
        categories: categories,
        columns: schemaCols,
        sampleRows: []
      };
    }

    // Default fallback from DOM headers
    const ths = document.querySelectorAll('#fillBody table thead th:not(:empty)');
    const domCols = [];
    ths.forEach((th, i) => {
      const txt = th.textContent.trim();
      if (txt && txt !== '#' && !th.querySelector('svg')) {
        domCols.push({
          key: `col_${i}`,
          header: txt,
          aliases: [txt, normalizeKey(txt)],
          sample: `Sample ${txt}`
        });
      }
    });

    return {
      no: canon,
      title: document.querySelector('#fillBody .t-title')?.textContent?.trim() || `Table ${canon}`,
      columns: domCols.length ? domCols : [{ key: 'item', header: 'Description', aliases: ['item', 'description'], sample: 'Sample Item' }],
      sampleRows: []
    };
  }

  /* ─────────────────────────────────────────
     TABLE LOCK CHECK
  ───────────────────────────────────────── */
  function isTableLocked(tableNo) {
    const targetNo = tableNo || getActiveTableNo();
    if (window.CMI && typeof window.CMI.isLocked === 'function') {
      try {
        if (window.CMI.isLocked(targetNo)) return true;
      } catch (e) {}
    }
    const fillBody = document.getElementById('fillBody');
    if (fillBody) {
      if (fillBody.querySelector('#cmi-submitted-banner, #cmi-locked-cmi-banner')) return true;
      const inputs = fillBody.querySelectorAll('table input, table select, table textarea');
      if (inputs.length > 0 && Array.from(inputs).every(i => i.disabled)) return true;
    }
    return false;
  }

  /* ─────────────────────────────────────────
     DOWNLOAD EXCEL TEMPLATE
  ───────────────────────────────────────── */
  function downloadTemplate(specificTableNo) {
    if (typeof XLSX === 'undefined') {
      showMsg('Excel library is still loading. Please try again in a moment.', 'error');
      return;
    }

    const tableNo = specificTableNo || getActiveTableNo();
    if (isTableLocked(tableNo)) {
      showMsg(`Table ${tableNo} is locked. Template download is disabled.`, 'error');
      return;
    }

    const schema = resolveSchema(tableNo);

    const headers = schema.columns.map(c => c.header);
    // Provide clean template with header titles
    const data = [headers];

    // For categorized tables (e.g. T6, T7a, T7b, T8a, T10, T13, T15, T20b, T1, T2a, T2b, T16):
    // Pre-populate clean starter rows with the category title in Column A,
    // so users immediately see where to enter data for each category/scope!
    if (schema.categories && schema.categories.length > 0) {
      schema.categories.forEach(cat => {
        const row = [cat];
        for (let i = 1; i < schema.columns.length; i++) {
          row.push('');
        }
        data.push(row);
      });
    }

    const ws = XLSX.utils.aoa_to_sheet(data);

    // Auto-fit column widths
    const colWidths = schema.columns.map((c, i) => {
      const headerLen = (c.header || '').length;
      return { wch: Math.max(headerLen + 6, 22) };
    });
    ws['!cols'] = colWidths;

    const wb = XLSX.utils.book_new();
    const safeSheetName = `${tableNo}_Template`.slice(0, 31).replace(/[\\/?*[\]]/g, '_');
    XLSX.utils.book_append_sheet(wb, ws, safeSheetName);

    const cleanTitle = (schema.title || `Table_${tableNo}`).replace(/[^a-zA-Z0-9_-]/g, '_').replace(/_+/g, '_');
    const filename = `SecReCo_${cleanTitle}_Template.xlsx`;

    XLSX.writeFile(wb, filename);

    if (typeof toast === 'function') {
      toast(`📥 Template for Table ${tableNo} downloaded successfully! Fill it out and click "Import Excel".`);
    } else {
      alert(`Template for Table ${tableNo} downloaded!`);
    }
  }

  /* ─────────────────────────────────────────
     IMPORT EXCEL LOGIC
  ───────────────────────────────────────── */
  let _fileInputEl = null;

  function getFileInput() {
    if (!_fileInputEl) {
      let el = document.getElementById('cmi-excel-global-file-input');
      if (!el) {
        el = document.createElement('input');
        el.type = 'file';
        el.id = 'cmi-excel-global-file-input';
        el.accept = '.xlsx, .xls, .csv';
        el.style.display = 'none';
        el.addEventListener('change', handleFileSelect);
        document.body.appendChild(el);
      }
      _fileInputEl = el;
    }
    return _fileInputEl;
  }

  function triggerImport(tableNo) {
    const active = tableNo || getActiveTableNo();
    if (isTableLocked(active)) {
      showMsg(`Table ${active} is locked. Excel import is disabled.`, 'error');
      return;
    }
    window._cmiExcelTargetTable = active;
    const input = getFileInput();
    input.value = ''; // Reset so same file can be chosen again
    input.click();
  }

  /* ─────────────────────────────────────────
     IMPORT LOADING MODAL (2s MINIMUM DISPLAY)
  ───────────────────────────────────────── */
  function ensureModalStyles() {
    if (document.getElementById('cmi-excel-modal-styles')) return;
    const style = document.createElement('style');
    style.id = 'cmi-excel-modal-styles';
    style.textContent = `
      @keyframes cmiSpin {
        100% { transform: rotate(360deg); }
      }
      @keyframes cmiBarIndeterminate {
        0% { left: -40%; width: 40%; }
        50% { left: 30%; width: 50%; }
        100% { left: 100%; width: 40%; }
      }
      @keyframes cmiModalPop {
        0% { transform: scale(0.92); opacity: 0; }
        100% { transform: scale(1); opacity: 1; }
      }
      .cmi-excel-loading-overlay {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.6);
        backdrop-filter: blur(4px);
        -webkit-backdrop-filter: blur(4px);
        z-index: 999999;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: opacity 0.2s ease;
      }
      .cmi-excel-loading-card {
        background: #ffffff;
        border-radius: 16px;
        box-shadow: 0 20px 45px -10px rgba(0, 0, 0, 0.28), 0 0 0 1px rgba(0, 0, 0, 0.06);
        padding: 30px 36px;
        width: 90%;
        max-width: 360px;
        text-align: center;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 14px;
        animation: cmiModalPop 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        box-sizing: border-box;
      }
    `;
    document.head.appendChild(style);
  }

  function showImportModal(tableNo) {
    ensureModalStyles();
    let modal = document.getElementById('cmi-excel-import-modal');
    if (!modal) {
      modal = document.createElement('div');
      modal.id = 'cmi-excel-import-modal';
      modal.className = 'cmi-excel-loading-overlay';
      document.body.appendChild(modal);
    }

    const tDisplay = tableNo ? `Table ${tableNo}` : 'the table';

    modal.innerHTML = `
      <div class="cmi-excel-loading-card">
        <div style="width:58px;height:58px;border-radius:50%;background:#e6f4ea;display:flex;align-items:center;justify-content:center;position:relative;">
          <svg viewBox="0 0 50 50" style="width:54px;height:54px;animation:cmiSpin 1s linear infinite;position:absolute;">
            <circle cx="25" cy="25" r="21" fill="none" stroke="#2e7d32" stroke-width="3.5" stroke-linecap="round" stroke-dasharray="85" stroke-dashoffset="65"></circle>
          </svg>
          <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="#1b5e20" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="z-index:2;">
            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
            <polyline points="17 8 12 3 7 8"></polyline>
            <line x1="12" y1="3" x2="12" y2="15"></line>
          </svg>
        </div>
        <div>
          <h3 style="margin:0 0 6px 0;font-size:18px;font-weight:700;color:#1e293b;letter-spacing:-0.2px;font-family:'Inter',system-ui,sans-serif;">Importing...</h3>
          <p style="margin:0;font-size:13px;color:#64748b;line-height:1.45;font-family:'Inter',system-ui,sans-serif;">Please wait while data is being imported into ${tDisplay}...</p>
        </div>
        <div style="width:100%;height:4px;background:#f1f5f9;border-radius:2px;overflow:hidden;position:relative;margin-top:4px;">
          <div style="height:100%;background:linear-gradient(90deg, #1b5e20, #4caf50);width:40%;border-radius:2px;position:absolute;animation:cmiBarIndeterminate 1.4s infinite ease-in-out;"></div>
        </div>
      </div>
    `;

    modal.style.display = 'flex';
  }

  function hideImportModal() {
    const modal = document.getElementById('cmi-excel-import-modal');
    if (modal) {
      modal.style.display = 'none';
      modal.remove();
    }
  }

  function handleFileSelect(e) {
    const file = e.target.files?.[0];
    if (!file) return;

    const tableNo = window._cmiExcelTargetTable || getActiveTableNo();
    if (isTableLocked(tableNo)) {
      showMsg(`Table ${tableNo} is locked. Excel import is disabled.`, 'error');
      return;
    }

    const startTime = Date.now();
    showImportModal(tableNo);

    // Guaranteed minimum display duration of 2 seconds (2000ms)
    const finishWithDelay = (callback) => {
      const elapsed = Date.now() - startTime;
      const minDuration = 2000;
      const remaining = Math.max(0, minDuration - elapsed);
      setTimeout(() => {
        hideImportModal();
        if (typeof callback === 'function') {
          callback();
        }
      }, remaining);
    };

    const reader = new FileReader();

    reader.onload = function (evt) {
      try {
        const buffer = evt.target.result;
        const wb = XLSX.read(buffer, {
          type: 'array',
          cellDates: true,
          dateNF: 'yyyy-mm-dd'
        });

        if (!wb.SheetNames || wb.SheetNames.length === 0) {
          finishWithDelay(() => showMsg('The selected Excel file contains no worksheets.', 'error'));
          return;
        }

        const sheetName = wb.SheetNames[0];
        const sheet = wb.Sheets[sheetName];
        const rawJson = XLSX.utils.sheet_to_json(sheet, {
          raw: false,
          defval: '',
          dateNF: 'yyyy-mm-dd'
        });

        if (!rawJson || rawJson.length === 0) {
          finishWithDelay(() => showMsg('The selected Excel worksheet contains no data rows.', 'error'));
          return;
        }

        const schema = resolveSchema(tableNo);
        const mappedRows = mapExcelRowsToSchema(rawJson, schema);

        if (mappedRows.length === 0) {
          finishWithDelay(() => showMsg('No valid rows could be imported. Please verify that data was filled in.', 'error'));
          return;
        }

        finishWithDelay(() => {
          applyImportedRows(tableNo, mappedRows, schema);
        });

      } catch (err) {
        console.error('Excel Import Error:', err);
        finishWithDelay(() => showMsg('Failed to read Excel file: ' + (err.message || 'Invalid format'), 'error'));
      }
    };

    reader.onerror = function () {
      finishWithDelay(() => showMsg('Error reading the selected file.', 'error'));
    };

    reader.readAsArrayBuffer(file);
  }

  /**
   * Intelligently maps Excel rows to table field keys
   */
  function mapExcelRowsToSchema(rawRows, schema) {
    if (!rawRows || rawRows.length === 0) return [];

    const colMap = {}; // normalized header string -> schema column object
    schema.columns.forEach(col => {
      colMap[normalizeKey(col.header)] = col;
      colMap[normalizeKey(col.key)] = col;
      if (Array.isArray(col.aliases)) {
        col.aliases.forEach(a => {
          colMap[normalizeKey(a)] = col;
        });
      }
    });

    const parsedRows = [];

    rawRows.forEach((rawRow, rowIdx) => {
      const rowObj = {};
      let hasData = false;

      // Map through all raw keys in the Excel row
      Object.keys(rawRow).forEach(rawHeader => {
        let val = rawRow[rawHeader];
        if (val !== undefined && val !== null) {
          if (val instanceof Date) {
            val = val.toISOString().split('T')[0];
          } else {
            val = String(val).trim();
          }
        } else {
          val = '';
        }

        const normH = normalizeKey(rawHeader);
        const matchedCol = colMap[normH];

        if (matchedCol) {
          rowObj[matchedCol.key] = val;
          if (matchedCol.key === 'new_') rowObj['new'] = val;
          // Mark hasData only if actual data field has content (not just category/scope)
          if (val !== '' && matchedCol.key !== 'category' && matchedCol.key !== 'scope') {
            hasData = true;
          }
        }
      });

      // Positional fallback if some keys missing
      if (!hasData) {
        const rawValues = Object.values(rawRow);
        schema.columns.forEach((c, idx) => {
          if (rawValues[idx] !== undefined && String(rawValues[idx]).trim() !== '') {
            let v = String(rawValues[idx]).trim();
            rowObj[c.key] = v;
            if (c.key === 'new_') rowObj['new'] = v;
            if (c.key !== 'category' && c.key !== 'scope') {
              hasData = true;
            }
          }
        });
      }

      // Fill in default category if needed
      if (schema.categories && schema.categories.length > 0 && !rowObj.category && !rowObj.scope) {
        rowObj.category = schema.categories[0];
      }

      // If Table 6, ensure scope is populated from scope or category
      if (String(schema.no || '').toUpperCase() === 'T6') {
        rowObj.scope = rowObj.scope || rowObj.category || '';
      }

      const sNoUpper = String(schema.no || '').toUpperCase().trim();
      const isTruthy = (v) => {
        if (typeof v === 'boolean') return v;
        if (typeof v === 'number') return v > 0;
        if (!v) return false;
        const str = String(v).toLowerCase().trim();
        return ['approved', 'yes', 'true', '1', '✓', 'x', 'checked', 'y'].includes(str);
      };

      // T10 Checklist / Status normalization
      if (sNoUpper === 'T10') {
        const s = String(rowObj.status || '').toLowerCase().trim();
        if (isTruthy(rowObj.approved) || s === 'approved' || s === 'app' || s === 'yes' || s === '✓') {
          rowObj.approved = true;
          rowObj.for_revision = false;
          rowObj.disapproved = false;
          rowObj.status = 'Approved';
          hasData = true;
        } else if (isTruthy(rowObj.for_revision) || s.includes('revis')) {
          rowObj.for_revision = true;
          rowObj.approved = false;
          rowObj.disapproved = false;
          rowObj.status = 'For Revision';
          hasData = true;
        } else if (isTruthy(rowObj.disapproved) || s.includes('disapprov')) {
          rowObj.disapproved = true;
          rowObj.approved = false;
          rowObj.for_revision = false;
          rowObj.status = 'Disapproved';
          hasData = true;
        }
      }

      // T11 Modality checklist normalization
      if (sNoUpper === 'T11') {
        rowObj.modalities = rowObj.modalities || {};
        ['stcbf', 'stc4id', 'safe', 'food_value_chain'].forEach(k => {
          if (rowObj[k] !== undefined && isTruthy(rowObj[k])) {
            rowObj.modalities[k] = true;
            rowObj[k] = true;
            hasData = true;
          }
        });
        if (rowObj.others) {
          rowObj.modalities.others = String(rowObj.others).trim();
          hasData = true;
        }
        if (rowObj.modality) {
          const mStr = String(rowObj.modality).toLowerCase().trim();
          if (mStr.includes('stcbf') || mStr.includes('stabs')) { rowObj.modalities.stcbf = true; rowObj.stcbf = true; }
          if (mStr.includes('stc4id')) { rowObj.modalities.stc4id = true; rowObj.stc4id = true; }
          if (mStr.includes('safe')) { rowObj.modalities.safe = true; rowObj.safe = true; }
          if (mStr.includes('food') || mStr.includes('value')) { rowObj.modalities.food_value_chain = true; rowObj.food_value_chain = true; }
          if (mStr.includes('other')) {
            const oth = rowObj.modality_other || rowObj.others || rowObj.modality;
            rowObj.modalities.others = oth;
            rowObj.others = oth;
          }
          hasData = true;
        }
      }

      // T12 Pre-commercialization alias normalization
      if (sNoUpper === 'T12') {
        const preVal = rowObj.precomm || rowObj.pre_comm || rowObj.activity || '';
        if (preVal) {
          rowObj.precomm = preVal;
          rowObj.pre_comm = preVal;
          hasData = true;
        }
      }

      // T17 field normalization (fallback for older template aliases)
      if (sNoUpper === 'T17') {
        if (!rowObj.type && (rowObj.activity || rowObj.meeting)) {
          rowObj.type = rowObj.activity || rowObj.meeting;
        }
        if (!rowObj.venue && (rowObj.venue_date || rowObj['date and venue'] || rowObj['venue and date'])) {
          rowObj.venue = rowObj.venue_date || rowObj['date and venue'] || rowObj['venue and date'];
        }
        if (rowObj.type || rowObj.venue || rowObj.host) {
          hasData = true;
        }
      }

      if (hasData) {
        parsedRows.push(rowObj);
      }
    });

    return parsedRows;
  }

  /**
   * Applies the parsed rows to the currently active table UI
   */
  function applyImportedRows(tableNo, rows, schema) {
    const sNo = String(tableNo).trim();
    const canon = sNo.toUpperCase();
    const cleanNo = sNo.replace(/^T/i, '');

    // Check if table module has custom setRows / loadRows
    const mod = getTableModule(tableNo);
    if (mod && typeof mod.setRows === 'function') {
      mod.setRows(rows);
      showImportSuccess(tableNo, rows.length);
      return;
    }

    // Dynamic table handler
    const dynWrap = document.getElementById(`dyn_${tableNo}_rows`);
    if (dynWrap) {
      applyToDynamicTable(tableNo, rows, schema);
      showImportSuccess(tableNo, rows.length);
      return;
    }

    // Built-in DOM fallback insertion
    const applied = applyToBuiltInDOM(tableNo, rows, schema);
    if (applied) {
      showImportSuccess(tableNo, rows.length);
    } else {
      showMsg(`Could not automatically populate rows into Table ${tableNo}.`, 'error');
    }
  }

  function applyToDynamicTable(tableNo, rows, schema) {
    const tbody = document.getElementById(`dyn_${tableNo}_rows`);
    if (!tbody) return;

    // Remove existing empty rows
    tbody.querySelectorAll('tr.dyn-data-row').forEach(tr => {
      const vals = [...tr.querySelectorAll('.dyn-col-val')].map(i => i.value.trim());
      if (vals.every(v => v === '')) tr.remove();
    });

    const hasCats = Array.isArray(schema.categories) && schema.categories.length > 0;

    rows.forEach(r => {
      const cat = r.category || (hasCats ? schema.categories[0] : null);
      if (hasCats && cat) {
        const catBtn = document.querySelector(`.dyn-add-cat-btn[data-cat="${cat}"]`);
        if (catBtn) catBtn.click();
        else {
          const firstBtn = document.querySelector(`.dyn-add-cat-btn`);
          if (firstBtn) firstBtn.click();
        }
      } else {
        const addBtn = document.getElementById(`dyn_${tableNo}_add_btn`);
        if (addBtn) addBtn.click();
      }

      // Populate last added row
      const addedRow = tbody.querySelector('tr.dyn-data-row:last-child');
      if (addedRow) {
        addedRow.querySelectorAll('.dyn-col-val').forEach(input => {
          const key = input.getAttribute('data-key');
          if (key && r[key] !== undefined) {
            input.value = r[key];
            input.title = r[key];
          }
        });
      }
    });

    // Renumber
    if (window.CMI && typeof window.CMI.renumberDynRows === 'function') {
      window.CMI.renumberDynRows(tableNo);
    }
  }

  function applyToBuiltInDOM(tableNo, rows, schema) {
    const sNo = String(tableNo).trim();
    const cleanNo = sNo.replace(/^T/i, '').toLowerCase();
    const tbodyId = `t${cleanNo}_rows`;
    const tbody = document.getElementById(tbodyId) || document.getElementById(`${sNo.toLowerCase()}_rows`);
    if (!tbody) return false;

    const mod = getTableModule(tableNo);

    // If addRow method exists on table module
    if (mod && typeof mod.addRow === 'function') {
      // Clear non-header empty rows
      tbody.querySelectorAll('tr:not([class*="cat"]):not([class*="hdr"])').forEach(tr => {
        const inputs = [...tr.querySelectorAll('input:not([type="hidden"]), textarea')];
        if (inputs.every(inp => inp.value.trim() === '')) tr.remove();
      });

      rows.forEach(r => {
        try {
          if (schema.categories && schema.categories.length > 0) {
            mod.addRow(r.category || schema.categories[0], r, true);
          } else {
            mod.addRow(r, true);
          }
        } catch (e) {
          // Fallback call signature without data
          mod.addRow(r.category || undefined);
        }

        // Fill inputs of latest added row
        const newRow = tbody.querySelector('tr:last-child');
        if (newRow) {
          fillRowInputs(newRow, r, schema);
        }
      });

      // Recalc or renumber if available
      if (typeof mod._recalcAll === 'function') mod._recalcAll();
      if (typeof mod._renumber === 'function') mod._renumber();
      return true;
    }

    return false;
  }

  function fillRowInputs(tr, rowData, schema) {
    schema.columns.forEach(c => {
      let val = rowData[c.key] ?? rowData[c.header] ?? '';
      if (val === '' && c.aliases) {
        for (const a of c.aliases) {
          if (rowData[a] !== undefined && rowData[a] !== '') {
            val = rowData[a];
            break;
          }
        }
      }
      if (val === '') return;

      const escapedKey = c.key.replace(/[^a-zA-Z0-9_-]/g, '');
      const cleanKey = escapedKey.replace(/_/g, '');
      const input = tr.querySelector(`input[class*="${escapedKey}"], textarea[class*="${escapedKey}"], select[class*="${escapedKey}"], input[class*="${cleanKey}"], textarea[class*="${cleanKey}"], select[class*="${cleanKey}"]`);
      if (input) {
        input.value = val;
        input.title = val;
        input.dispatchEvent(new Event('input', { bubbles: true }));
        input.dispatchEvent(new Event('change', { bubbles: true }));
      }
    });
  }

  function getTableModule(no) {
    if (!no) return null;
    const str = String(no).trim();
    const clean = str.replace(/^T/i, '');
    const candidates = [str, 'T' + clean, str.toUpperCase(), str.toLowerCase(), 'T' + clean.toLowerCase(), 'T' + clean.toUpperCase()];
    for (const c of candidates) {
      if (window[c] && (typeof window[c].addRow === 'function' || typeof window[c].save === 'function')) {
        return window[c];
      }
    }
    return null;
  }

  function showImportSuccess(tableNo, count) {
    const msg = `✅ Successfully imported ${count} row(s) from Excel into Table ${tableNo}! Remember to click "Save" or "Save Draft".`;
    if (typeof toast === 'function') {
      toast(msg);
    } else {
      alert(msg);
    }

    // Touch status badge to In Progress / Draft
    const badge = document.getElementById(`${String(tableNo).toLowerCase()}_status_badge`) ||
                  document.getElementById(`dyn_${tableNo}_status_badge`);
    if (badge) {
      badge.textContent = 'In Progress';
      badge.style.display = 'inline-block';
      badge.style.background = '#fff4e5';
      badge.style.color = '#b06b00';
    }
  }

  function showMsg(text, type = 'info') {
    if (typeof toast === 'function') {
      toast(text);
    } else {
      alert(text);
    }
  }

  /* ─────────────────────────────────────────
     TOOLBAR INJECTION & RENDER
  ───────────────────────────────────────── */
  function renderToolbar(tableNo) {
    const targetNo = tableNo || getActiveTableNo();
    const tHdr = document.querySelector('#fillBody .t-hdr');
    if (!tHdr) return;

    // Check if toolbar buttons already exist
    let actions = tHdr.querySelector('.table-excel-actions');
    if (!actions) {
      actions = document.createElement('div');
      actions.className = 'table-excel-actions';
      tHdr.appendChild(actions);
    }

    const locked = isTableLocked(targetNo);
    const disAttr = locked ? 'disabled' : '';
    const styleAttr = locked ? 'opacity:0.45;cursor:not-allowed;pointer-events:none;' : '';
    const dlTitle = locked ? `Table ${targetNo} is locked: Template download is disabled` : `Download Excel template for Table ${targetNo}`;
    const impTitle = locked ? `Table ${targetNo} is locked: Excel import is disabled` : `Import data from Excel spreadsheet into Table ${targetNo}`;
    const dlClick = locked ? 'return false;' : `CMIExcel.downloadTemplate('${targetNo}')`;
    const impClick = locked ? 'return false;' : `CMIExcel.triggerImport('${targetNo}')`;

    actions.innerHTML = `
      <button type="button" class="btn-table-excel btn-download-template" ${disAttr} onclick="${dlClick}" title="${dlTitle}" style="${styleAttr}">
        <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
          <polyline points="7 10 12 15 17 10"/>
          <line x1="12" y1="15" x2="12" y2="3"/>
        </svg>
        Download Template
      </button>
      <button type="button" class="btn-table-excel btn-import-excel" ${disAttr} onclick="${impClick}" title="${impTitle}" style="${styleAttr}">
        <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
          <polyline points="17 8 12 3 7 8"/>
          <line x1="12" y1="3" x2="12" y2="15"/>
        </svg>
        Import Excel
      </button>
    `;

    if (!locked) {
      setupDragAndDrop();
    }
  }

  /* ── Drag & Drop support for Excel files ── */
  function setupDragAndDrop() {
    const tblWrap = document.querySelector('#fillBody .tbl-wrap');
    if (!tblWrap || tblWrap._hasExcelDrop) return;
    tblWrap._hasExcelDrop = true;

    ['dragenter', 'dragover'].forEach(name => {
      tblWrap.addEventListener(name, (e) => {
        const targetNo = getActiveTableNo();
        if (isTableLocked(targetNo)) return;
        e.preventDefault();
        e.stopPropagation();
        tblWrap.classList.add('excel-drag-over');
      });
    });

    ['dragleave', 'drop'].forEach(name => {
      tblWrap.addEventListener(name, (e) => {
        e.preventDefault();
        e.stopPropagation();
        tblWrap.classList.remove('excel-drag-over');
      });
    });

    tblWrap.addEventListener('drop', (e) => {
      const targetNo = getActiveTableNo();
      if (isTableLocked(targetNo)) {
        showMsg(`Table ${targetNo} is locked. Excel import is disabled.`, 'error');
        return;
      }
      const dt = e.dataTransfer;
      const file = dt?.files?.[0];
      if (file && (file.name.endsWith('.xlsx') || file.name.endsWith('.xls') || file.name.endsWith('.csv'))) {
        const input = getFileInput();
        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(file);
        input.files = dataTransfer.files;
        handleFileSelect({ target: input });
      }
    });
  }

  /* ─────────────────────────────────────────
     LIVE TEXT LENGTH & INFINITE INPUT HANDLING
  ───────────────────────────────────────── */
  function setupInputLengthHelper() {
    document.addEventListener('input', function (e) {
      if (e.target.matches('.fill-body table input, .fill-body table textarea')) {
        const val = e.target.value || '';
        e.target.title = val ? `Length: ${val.length} characters\n${val}` : '';
      }
    }, true);

    document.addEventListener('focusin', function (e) {
      if (e.target.matches('.fill-body table input, .fill-body table textarea')) {
        const val = e.target.value || '';
        e.target.title = val ? `Length: ${val.length} characters\n${val}` : '';
      }
    }, true);
  }

  // Initialize helper on load
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', setupInputLengthHelper);
  } else {
    setupInputLengthHelper();
  }

  /* ─────────────────────────────────────────
     PUBLIC INTERFACE
  ───────────────────────────────────────── */
  window.CMIExcel = {
    downloadTemplate,
    triggerImport,
    handleFileSelect,
    renderToolbar,
    updateLockState: renderToolbar,
    isLocked: isTableLocked,
    resolveSchema,
    TABLE_SCHEMAS
  };

  // Re-check lock states when window loads or custom event triggers
  window.addEventListener('cmi:tableLoaded', () => renderToolbar());
  window.addEventListener('cmi:statusChanged', () => renderToolbar());
})();
