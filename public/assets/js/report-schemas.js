/* Shared built-in columns for forms, imports, and submission editing. */
(function () {
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
      categories: ['GO', 'NGO', 'Private Sector', 'LGU'],
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
        { key: 'duration', header: 'Duration', aliases: ['duration', 'period', 'timeline'], sample: '' },
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

  window.REPORT_SCHEMAS = TABLE_SCHEMAS;
})();
