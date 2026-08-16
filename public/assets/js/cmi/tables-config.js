/**
 * tables-config.js — CMI Fill Up Report
 * Data-driven definitions for all 24 tables of the
 * Annual Accomplishment Report Format.
 *
 * Each table entry:
 *   no:        table number/id (used as key in DB, e.g. "T1")
 *   title:     full table title shown above the form
 *   section:   which sidebar section it belongs to
 *   note:      optional footnote shown below the table
 *   groups:    optional array of {label, span} for a merged header row
 *   columns:   array of {key, label, type, width, readonly, default, options}
 *              type: 'text' | 'number' | 'date' | 'textarea' | 'select'
 *   fixedRows: optional array of row presets — each preset is an object
 *              {label, key, group} that creates a non-removable first
 *              cell with that label and locks rowKey for that row.
 *              If omitted, the table starts with 1 blank addable row.
 *   addableRow: true by default; set false if all rows are fixed.
 */

window.CMI_TABLES = (function () {

  const SECTIONS = [
    {
      label: 'R&D Mgt. & Coord.',
      tables: ['T1', 'T2A', 'T2B', 'T3', 'T4', 'T5', 'T6', 'T7A', 'T7B'],
    },
    {
      label: 'Strategic R&D',
      tables: ['T8A', 'T8B', 'T9'],
    },
    {
      label: 'Results Utilization',
      tables: ['T10', 'T11', 'T12', 'T13'],
    },
    {
      label: 'Capability & Gov.',
      tables: ['T14', 'T15', 'T16', 'T17', 'T18', 'T19'],
    },
    {
      label: 'Policy Analysis',
      tables: ['T20A', 'T20B'],
    },
  ];

  const TABLES = {

    /* ───────────────────────── T1 ───────────────────────── */
    T1: {
      title: 'Table 1. Summary of Agency In-House Reviews (AIHRs)',
      subtitle: 'conducted by consortium member-agencies',
      note: 'Note: The Regional Consortium may prepare other tables for ease in data presentation.',
      groups: [
        { label: 'Date', span: 1 },
        { label: 'Agency', span: 1 },
        { label: 'Number of Projects Presented', span: 4 },
        { label: 'Total Projects Reviewed', span: 1 },
      ],
      columns: [
        { key: 'date',       label: 'Date',       type: 'date',  width: 110, group: true },
        { key: 'agency',     label: 'Agency',     type: 'text',  width: 150, group: true },
        { key: 'new_',       label: 'New',        type: 'number', sub: true },
        { key: 'ongoing',    label: 'Ongoing',    type: 'number', sub: true },
        { key: 'completed',  label: 'Completed',  type: 'number', sub: true },
        { key: 'terminated', label: 'Terminated', type: 'number', sub: true },
        { key: 'total',      label: 'Total Projects Reviewed', type: 'computed', formula: ['new_','ongoing','completed','terminated'], group: true },
      ],
    },

    /* ───────────────────────── T2A ───────────────────────── */
    T2A: {
      title: 'Table 2a. Summary of Regional Symposium on R&D Highlights',
      meta: [
        { key: 'date',  label: 'Date' },
        { key: 'venue', label: 'Venue' },
      ],
      columns: [
        { key: 'title',   label: 'Title',                  type: 'text', width: 220 },
        { key: 'agency',  label: 'Implementing Agency(ies)', type: 'text', width: 160 },
        { key: 'researchers', label: 'Researcher(s)',       type: 'text', width: 160 },
        { key: 'recommendations', label: 'Major Recommendations', type: 'textarea', width: 220 },
        { key: 'winners', label: 'Winners',                 type: 'text', width: 120 },
      ],
      fixedRows: [
        { group: 'Research Category', rows: 3 },
        { group: 'Development Category', rows: 3 },
      ],
    },

    /* ───────────────────────── T2B ───────────────────────── */
    T2B: {
      title: 'Table 2b. Number of Participants in the RSRDH',
      note: 'Note: Participants may be researchers, farmers, policy makers, or extension workers',
      columns: [
        { key: 'participants', label: 'Participants',         type: 'text', width: 160, readonly: true },
        { key: 'agency',       label: 'Agency / Association',  type: 'text', width: 200 },
        { key: 'count',        label: 'No. of Participants',   type: 'number' },
        { key: 'remarks',      label: 'Remarks',               type: 'textarea', width: 200 },
      ],
      fixedRows: [
        { label: 'GO' },
        { label: 'NGO' },
        { label: 'SUC/Academe' },
        { label: 'Private Sector' },
      ],
    },

    /* ───────────────────────── T3 ───────────────────────── */
    T3: {
      title: 'Table 3. List of Projects and Activities monitored and evaluated',
      columns: [
        { key: 'project',  label: 'Projects and Activities', type: 'text', width: 240 },
        { key: 'status',   label: 'Ongoing or Completed', type: 'select', options: ['Ongoing', 'Completed'] },
        { key: 'duration', label: 'Duration', type: 'text', width: 140 },
        { key: 'fund',     label: 'Source of Fund', type: 'text', width: 160 },
      ],
    },

    /* ───────────────────────── T4 ───────────────────────── */
    T4: {
      title: 'Table 4. Resources Shared',
      note: '*Activity/Project can be inputted as: Implementation of Consortium-led R&D and Technology Transfer-related programs/activities; HRD activities; Improvement of consortium\u2019s or member-institutions\u2019 facilities; Planning/consultation activities; AIHRs/Sectoral Reviews; RSRDH; Regional Fairs/Exhibits (e.g., FIESTA, etc.); others (specify)',
      columns: [
        { key: 'donor',    label: 'Donor / Source',  type: 'text', width: 180 },
        { key: 'activity', label: 'Activity/ Project*', type: 'text', width: 220 },
        { key: 'amount',   label: 'Amount shared',   type: 'number', step: '0.01' },
        { key: 'remarks',  label: 'Remarks',         type: 'textarea', width: 180 },
      ],
    },

    /* ───────────────────────── T5 ───────────────────────── */
    T5: {
      title: 'Table 5. Resources Generated by the Consortium',
      note: '*Activity/Project can be inputted as: Implementation of Consortium-led R&D and Technology Transfer-related programs/activities; HRD activities; Improvement of consortium\u2019s or member-institutions\u2019 facilities; Planning/consultation activities; AIHRs/Sectoral Reviews; RSRDH; Regional Fairs/Exhibits (e.g., FIESTA, etc.); others (specify)',
      columns: [
        { key: 'donor',    label: 'Donor/ Source',     type: 'text', width: 180 },
        { key: 'activity', label: 'Activity / Project*', type: 'text', width: 220 },
        { key: 'amount',   label: 'Amount generated',  type: 'number', step: '0.01' },
        { key: 'remarks',  label: 'Remarks',           type: 'textarea', width: 180 },
      ],
    },

    /* ───────────────────────── T6 ───────────────────────── */
    T6: {
      title: 'Table 6. Linkages Forged and Maintained',
      columns: [
        { key: 'agency',  label: 'Agency/ Institution', type: 'text', width: 200 },
        { key: 'address', label: 'Address', type: 'text', width: 200 },
        { key: 'year',    label: 'Year', type: 'text', width: 90 },
        { key: 'nature',  label: 'Nature of Assistance / Linkages / Project', type: 'textarea', width: 240 },
        { key: 'scope',   label: 'Scope', type: 'select', options: ['Local', 'National', 'International'] },
      ],
      fixedRows: [
        { group: 'Developed/New' },
        { group: 'Maintained' },
      ],
    },

    /* ───────────────────────── T7A ───────────────────────── */
    T7A: {
      title: 'Table 7a. List of Databases Developed/ Enhanced and Maintained',
      columns: [
        { key: 'type',    label: 'Type of Database', type: 'text', width: 220 },
        { key: 'date',    label: 'Date Created', type: 'date' },
        { key: 'purpose', label: 'Purpose/Use', type: 'textarea', width: 240 },
      ],
      fixedRows: [
        { label: 'Developed/Enhanced' },
        { label: 'Maintained' },
      ],
    },

    /* ───────────────────────── T7B ───────────────────────── */
    T7B: {
      title: 'Table 7b. List of Information Systems Developed/ Enhanced and Maintained',
      columns: [
        { key: 'type',    label: 'Type of Information System', type: 'text', width: 220 },
        { key: 'date',    label: 'Date Created', type: 'date' },
        { key: 'purpose', label: 'Purpose/Use', type: 'textarea', width: 240 },
      ],
      fixedRows: [
        { label: 'Developed/Enhanced' },
        { label: 'Maintained' },
      ],
    },

    /* ───────────────────────── T8A ───────────────────────── */
    T8A: {
      title: 'Table 8a. List of R&D Programs/ Projects Packaged, Approved, and Implemented',
      note: '*Indicate start and end dates',
      columns: [
        { key: 'title',    label: 'Program / Project Title', type: 'text', width: 220 },
        { key: 'researcher', label: 'Researcher', type: 'text', width: 150 },
        { key: 'agency',   label: 'Implementing Agency', type: 'text', width: 150 },
        { key: 'duration', label: 'Duration*', type: 'text', width: 130 },
        { key: 'budget',   label: 'Source of Funds / Budget', type: 'text', width: 160 },
        { key: 'priority', label: 'Regional Priority Commodities Addressed', type: 'textarea', width: 220 },
      ],
      fixedRows: [
        { group: 'Proposals Packaged', rows: 3 },
        { group: 'Proposals Approved', rows: 3 },
        { group: 'Proposals Implemented', rows: 3 },
      ],
    },

    /* ───────────────────────── T8B ───────────────────────── */
    T8B: {
      title: 'Table 8b. Collaborative R&D Programs/ Projects implemented by the Consortium and member-agencies in support of regional priorities',
      note: '*Indicate start and end dates',
      columns: [
        { key: 'program',  label: 'Program Title', type: 'text', width: 180 },
        { key: 'project',  label: 'Project Title', type: 'text', width: 180 },
        { key: 'agency',   label: 'Implementing Agency/ Institution', type: 'text', width: 180 },
        { key: 'duration', label: 'Duration*', type: 'text', width: 120 },
        { key: 'budget',   label: 'Budget', type: 'number', step: '0.01' },
        { key: 'fundsource', label: 'Source(s) of Fund', type: 'text', width: 150 },
        { key: 'role',     label: 'Role of Consortium', type: 'textarea', width: 200 },
      ],
    },

    /* ───────────────────────── T9 ───────────────────────── */
    T9: {
      title: 'Table 9. List of Technologies/ Information Generated from R&D',
      columns: [
        { key: 'title',    label: 'Title of Technology/ Brief Description', type: 'textarea', width: 240 },
        { key: 'source',   label: 'Project/ Program Source', type: 'text', width: 180 },
        { key: 'agency',   label: 'Agency', type: 'text', width: 140 },
        { key: 'researcher', label: 'Researcher(s)', type: 'text', width: 150 },
        { key: 'impact',   label: 'Potential impact or contribution', type: 'textarea', width: 220 },
      ],
    },

    /* ───────────────────────── T10 ───────────────────────── */
    T10: {
      title: 'Table 10. List of Technology Transfer Program/ Projects Packaged, Approved, and Implemented',
      columns: [
        { key: 'program',  label: 'Program Title', type: 'text', width: 170 },
        { key: 'project',  label: 'Project Title', type: 'text', width: 170 },
        { key: 'agency',   label: 'Implementing Agency/ Institution', type: 'text', width: 170 },
        { key: 'duration', label: 'Duration*', type: 'text', width: 110 },
        { key: 'budget',   label: 'Budget', type: 'number', step: '0.01' },
        { key: 'fundsource', label: 'Source(s) of Fund', type: 'text', width: 140 },
        { key: 'role',     label: 'Role of Consortium', type: 'textarea', width: 200 },
      ],
    },

    /* ───────────────────────── T11 ───────────────────────── */
    T11: {
      title: 'Table 11. List of Technologies Extended/Deployed through Various Technology Transfer Extension Modalities',
      columns: [
        { key: 'title',   label: 'Title of Technology/ Brief Description', type: 'textarea', width: 240 },
        { key: 'source',  label: 'Project/ Program Source', type: 'text', width: 180 },
        { key: 'agency',  label: 'Agency', type: 'text', width: 140 },
        { key: 'researcher', label: 'Researcher(s)', type: 'text', width: 150 },
        { key: 'impact',  label: 'Potential impact or contribution', type: 'textarea', width: 220 },
      ],
    },

    /* ───────────────────────── T12 ───────────────────────── */
    T12: {
      title: 'Table 12. List of Technologies Commercialized or Pre-Commercialization Initiatives',
      groups: [
        { label: 'Name of Technology', span: 1 },
        { label: 'Technology Owner', span: 1 },
        { label: 'Status', span: 4 },
      ],
      columns: [
        { key: 'tech',      label: 'Name of Technology', type: 'text', width: 180, group: true },
        { key: 'owner',     label: 'Technology Owner', type: 'text', width: 140, group: true },
        { key: 'precomm',   label: 'Pre-Commercialization Activity Undertaken', type: 'textarea', width: 200, sub: true },
        { key: 'commercialized', label: 'Commercialized (Y/N)', type: 'select', options: ['Yes','No'], sub: true },
        { key: 'adoptors',  label: 'Name of Person/Firm Adoptors', type: 'textarea', width: 200, sub: true },
        { key: 'remarks',   label: 'Remarks', type: 'textarea', width: 160, sub: true },
      ],
    },

    /* ───────────────────────── T13 ───────────────────────── */
    T13: {
      title: 'Table 13. List of Technology Promotion Approaches',
      groups: [
        { label: 'Name of Technology', span: 1 },
        { label: 'Project Title', span: 1 },
        { label: 'Implementing Agency', span: 1 },
        { label: 'Technology Transfer Modality', span: 5 },
      ],
      columns: [
        { key: 'tech',    label: 'Name of Technology', type: 'text', width: 160, group: true },
        { key: 'project', label: 'Project Title', type: 'text', width: 160, group: true },
        { key: 'agency',  label: 'Implementing Agency', type: 'text', width: 160, group: true },
        { key: 'stcbf',   label: 'STCBF', type: 'select', options: ['Yes','No'], sub: true },
        { key: 'stc4id',  label: 'STC4iD', type: 'select', options: ['Yes','No'], sub: true },
        { key: 'safe',    label: 'SAFE', type: 'select', options: ['Yes','No'], sub: true },
        { key: 'fvc',     label: 'Food Value Chain', type: 'select', options: ['Yes','No'], sub: true },
        { key: 'other',   label: 'Other extension initiatives', type: 'text', width: 160, sub: true },
      ],
    },

    /* ───────────────────────── T14 ───────────────────────── */
    T14: {
      title: 'Table 14. Non-degree Trainings Conducted/ Facilitated',
      columns: [
        { key: 'title',    label: 'Title of Activity', type: 'text', width: 220 },
        { key: 'venue',    label: 'Date/ Venue', type: 'text', width: 180 },
        { key: 'participants', label: 'Number of Participants', type: 'number' },
        { key: 'expenditures', label: 'Expenditures', type: 'number', step: '0.01' },
        { key: 'fundsource', label: 'Source of funds', type: 'text', width: 160 },
      ],
    },

    /* ───────────────────────── T15 ───────────────────────── */
    T15: {
      title: 'Table 15. Equipment/ Facilities Funded',
      columns: [
        { key: 'equipment', label: 'Equipment/ Facilities Established/ Upgraded/ Approved', type: 'textarea', width: 240 },
        { key: 'location',  label: 'Location/Agency', type: 'text', width: 160 },
        { key: 'expenditures', label: 'Expenditures', type: 'number', step: '0.01' },
        { key: 'fundsource', label: 'Source(s) of funds', type: 'text', width: 160 },
      ],
      fixedRows: [
        { label: 'Endorsed' },
        { label: 'Approved' },
      ],
    },

    /* ───────────────────────── T16 ───────────────────────── */
    T16: {
      title: 'Table 16. Awards Received',
      columns: [
        { key: 'title',    label: 'Title of Award', type: 'text', width: 180 },
        { key: 'recipient', label: 'Recipient/ Agency', type: 'text', width: 160 },
        { key: 'sponsor',  label: 'Sponsor', type: 'text', width: 140 },
        { key: 'activity', label: 'Event/Activity', type: 'text', width: 160 },
        { key: 'venue',    label: 'Venue (Place of Award)', type: 'text', width: 160 },
        { key: 'date',     label: 'Date Awarded', type: 'date' },
      ],
      fixedRows: [
        { label: 'Local' },
        { label: 'Regional' },
        { label: 'National' },
      ],
    },

    /* ───────────────────────── T17 ───────────────────────── */
    T17: {
      title: 'Table 17. Schedule, Venue, Host Agencies of Regular Meetings',
      columns: [
        { key: 'type',  label: 'Type of Meeting/Activity', type: 'text', width: 220 },
        { key: 'venue', label: 'Venue and Date', type: 'text', width: 200 },
        { key: 'host',  label: 'Host Agency', type: 'text', width: 180 },
      ],
    },

    /* ───────────────────────── T18 ───────────────────────── */
    T18: {
      title: 'Table 18. List of CMI Contributions',
      note: '*Indicate whether the contribution is in kind or in the form of services rendered.',
      columns: [
        { key: 'cmi',    label: 'Name of CMI', type: 'text', width: 220 },
        { key: 'amount', label: 'Amount of contribution*', type: 'textarea', width: 260 },
      ],
    },

    /* ───────────────────────── T19 ───────────────────────── */
    T19: {
      title: 'Table 19. List of New Initiatives on Governance',
      columns: [
        { key: 'initiative', label: 'New Initiatives', type: 'textarea', width: 320 },
        { key: 'date',       label: 'Date Conducted/Implemented', type: 'date' },
      ],
    },

    /* ───────────────────────── T20A ───────────────────────── */
    T20A: {
      title: 'Table 20a. Policy Researches Conducted',
      columns: [
        { key: 'project',     label: 'Policy Analysis/ Advocacy Project', type: 'textarea', width: 220 },
        { key: 'agency',      label: 'Agency', type: 'text', width: 140 },
        { key: 'author',      label: 'Author', type: 'text', width: 140 },
        { key: 'description', label: 'Description of the Project', type: 'textarea', width: 220 },
        { key: 'findings',    label: 'Findings', type: 'textarea', width: 220 },
      ],
    },

    /* ───────────────────────── T20B ───────────────────────── */
    T20B: {
      title: 'Table 20b. Policies Formulated, Advocated, Implemented, and Institutionalized',
      columns: [
        { key: 'policy',      label: 'List of policies', type: 'text', width: 220 },
        { key: 'agency',      label: 'Agency', type: 'text', width: 160 },
        { key: 'description', label: 'Description', type: 'textarea', width: 260 },
      ],
      fixedRows: [
        { label: 'Policy formulated' },
        { label: 'Policy advocated' },
        { label: 'Policy implemented' },
        { label: 'Policy institutionalized' },
      ],
    },

  };

  // Flat ordered list with section assignment, used for the sidebar
  function buildNavData() {
    return SECTIONS.map(s => ({
      label: s.label,
      tables: s.tables.map(no => ({ no, title: TABLES[no].title.split('.')[0] + '. ' + (TABLES[no].title.split('. ')[1] || '').split(',')[0] }))
    }));
  }

  return {
    SECTIONS,
    TABLES,
    buildNavData,
    getTable: (no) => TABLES[no],
    allTableNos: () => Object.keys(TABLES),
  };

})();
