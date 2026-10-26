<?php
/************************************************************************
 * NRS-Procuresmart Demo Procurement Dataset Seeder
 * Comprehensive seed covering all core CRM and procurement modules
 ************************************************************************/

include __DIR__ . '/../bootstrap.php';

use Espo\Core\Application;
use Espo\ORM\EntityManager;

$app = new Application();
$container = $app->getContainer();
$em = $container->getByClass(EntityManager::class);

echo "Seeding comprehensive NRS-Procuresmart demo data across all modules...\n";

// Helper function to create or update an entity cleanly
function getOrCreateEntity($em, $entityType, $where, $data) {
    $existing = null;
    if (!empty($where)) {
        try {
            $existing = $em->getRDBRepository($entityType)->where($where)->findOne();
        } catch (\Throwable $e) {}
    }
    $entity = $existing ?: $em->getEntity($entityType);
    foreach ($data as $field => $val) {
        $entity->set($field, $val);
    }
    try {
        $em->saveEntity($entity);
    } catch (\Throwable $e) {
        echo "Notice saving {$entityType}: " . $e->getMessage() . "\n";
    }
    return $entity;
}

try {
    // -------------------------------------------------------------
    // 0. Locate / Update Admin User Profile
    // -------------------------------------------------------------
    $adminUser = null;
    $adminId = null;
    try {
        $adminUser = $em->getRDBRepository('User')->where(['userName' => 'admin'])->findOne();
        if ($adminUser) {
            $adminUser->set('firstName', 'Procurement');
            $adminUser->set('lastName', 'Administrator');
            $adminUser->set('title', 'Chief Procurement Director');
            $adminUser->set('emailAddress', 'admin@nrs.gov.ng');
            $em->saveEntity($adminUser);
            $adminId = $adminUser->getId();
        }
    } catch (\Throwable $e) {}

    // -------------------------------------------------------------
    // 1. Teams (Procurement Directorates & Committees)
    // -------------------------------------------------------------
    $teamsData = [
        ['name' => 'Tenders & Bidding Directorate', 'description' => 'Oversees tender publications, RFP documentations, and bid opening procedures.'],
        ['name' => 'Vendor Compliance & Due Diligence', 'description' => 'Manages vendor registration, tax clearance verification, and CAC compliance audits.'],
        ['name' => 'Supply Chain & Logistics', 'description' => 'Monitors shipments, overland freight, and warehouse fulfillment.'],
        ['name' => 'Audit & Financial Verification', 'description' => 'Conducts financial reviews, bank guarantee validations, and final invoice clearances.']
    ];

    $createdTeams = [];
    foreach ($teamsData as $tData) {
        $createdTeams[] = getOrCreateEntity($em, 'Team', ['name' => $tData['name']], $tData);
    }

    // -------------------------------------------------------------
    // 2. Accounts (Vendors, Suppliers & Regulatory Partners)
    // -------------------------------------------------------------
    $suppliers = [
        [
            'name' => 'Apex Industrial Equipment Ltd',
            'type' => 'Vendor',
            'industry' => 'Manufacturing',
            'website' => 'https://apexindustrial.example.com',
            'emailAddress' => 'procurement@apexindustrial.example.com',
            'phoneNumber' => '+234-1-809-2001',
            'billingAddressStreet' => '12 Trans-Amadi Industrial Layout',
            'billingAddressCity' => 'Port Harcourt',
            'billingAddressState' => 'Rivers',
            'billingAddressPostalCode' => '500101',
            'billingAddressCountry' => 'Nigeria',
            'description' => 'Grade-A accredited vendor for heavy industrial power generators, specialized plant machinery, and factory safety apparel.'
        ],
        [
            'name' => 'Nexus Cyber & Cloud Technologies',
            'type' => 'Vendor',
            'industry' => 'Technology',
            'website' => 'https://nexuscloud.example.com',
            'emailAddress' => 'tenders@nexuscloud.example.com',
            'phoneNumber' => '+234-1-809-2002',
            'billingAddressStreet' => '7 Adeola Odeku Street, Victoria Island',
            'billingAddressCity' => 'Lagos',
            'billingAddressState' => 'Lagos',
            'billingAddressPostalCode' => '101241',
            'billingAddressCountry' => 'Nigeria',
            'description' => 'Enterprise vendor providing sovereign cloud infrastructure, data center server racks, and AI firewall cybersecurity.'
        ],
        [
            'name' => 'Global Trans-Freight Logistics',
            'type' => 'Partner',
            'industry' => 'Transportation',
            'website' => 'https://transfreight.example.com',
            'emailAddress' => 'operations@transfreight.example.com',
            'phoneNumber' => '+234-1-809-2003',
            'billingAddressStreet' => '44 Wharf Road, Apapa Maritime Hub',
            'billingAddressCity' => 'Lagos',
            'billingAddressState' => 'Lagos',
            'billingAddressPostalCode' => '102272',
            'billingAddressCountry' => 'Nigeria',
            'description' => 'Strategic logistics partner for nationwide container haulage, customs clearance, and cold-chain transport.'
        ],
        [
            'name' => 'Zenith Security & Surveillance Systems',
            'type' => 'Vendor',
            'industry' => 'Services',
            'website' => 'https://zenithsecurity.example.com',
            'emailAddress' => 'contracts@zenithsecurity.example.com',
            'phoneNumber' => '+234-1-809-2004',
            'billingAddressStreet' => '18 Constitution Avenue, Central Business District',
            'billingAddressCity' => 'Abuja',
            'billingAddressState' => 'FCT',
            'billingAddressPostalCode' => '900211',
            'billingAddressCountry' => 'Nigeria',
            'description' => 'Vendor specializing in integrated biometric access control, CCTV command centers, and perimeter fencing.'
        ],
        [
            'name' => 'Standard Corporate Stationery & Supplies',
            'type' => 'Vendor',
            'industry' => 'Retail',
            'website' => 'https://standardcorporate.example.com',
            'emailAddress' => 'sales@standardcorporate.example.com',
            'phoneNumber' => '+234-1-809-2005',
            'billingAddressStreet' => '25 Commercial Avenue, Yaba',
            'billingAddressCity' => 'Lagos',
            'billingAddressState' => 'Lagos',
            'billingAddressPostalCode' => '101212',
            'billingAddressCountry' => 'Nigeria',
            'description' => 'Long-standing supplier for corporate stationery, secure security printing, and ergonomic office furniture.'
        ],
        [
            'name' => 'BioHealth Pharmaceuticals & Lab Diagnostics',
            'type' => 'Supplier',
            'industry' => 'Healthcare',
            'website' => 'https://biohealthdiagnostics.example.com',
            'emailAddress' => 'supply@biohealthdiagnostics.example.com',
            'phoneNumber' => '+234-1-809-2006',
            'billingAddressStreet' => '15 Medical Boulevard, Utako',
            'billingAddressCity' => 'Abuja',
            'billingAddressState' => 'FCT',
            'billingAddressPostalCode' => '900108',
            'billingAddressCountry' => 'Nigeria',
            'description' => 'Health commodities provider for PPE respirators, diagnostic reagents, and laboratory testing equipment.'
        ],
        [
            'name' => 'Federal Bureau of Public Procurement',
            'type' => 'Customer',
            'industry' => 'Government',
            'website' => 'https://bpp.gov.ng',
            'emailAddress' => 'compliance@bpp.gov.ng',
            'phoneNumber' => '+234-9-234-5678',
            'billingAddressStreet' => '11 Suleiman Barau Crescent, Presidential Villa',
            'billingAddressCity' => 'Abuja',
            'billingAddressState' => 'FCT',
            'billingAddressPostalCode' => '900241',
            'billingAddressCountry' => 'Nigeria',
            'description' => 'National procurement regulatory oversight commission ensuring statutory transparency and compliance.'
        ],
        [
            'name' => 'National Clean Energy & Solar Consortium',
            'type' => 'Vendor',
            'industry' => 'Energy',
            'website' => 'https://cleanenergyconsortium.example.com',
            'emailAddress' => 'projects@cleanenergyconsortium.example.com',
            'phoneNumber' => '+234-1-809-2007',
            'billingAddressStreet' => '88 Airport Road, Ikeja',
            'billingAddressCity' => 'Lagos',
            'billingAddressState' => 'Lagos',
            'billingAddressPostalCode' => '100281',
            'billingAddressCountry' => 'Nigeria',
            'description' => 'Turnkey solar micro-grid contractors and backup industrial lithium battery storage installers.'
        ]
    ];

    $createdAccounts = [];
    foreach ($suppliers as $data) {
        if ($adminId) {
            $data['assignedUserId'] = $adminId;
        }
        $createdAccounts[] = getOrCreateEntity($em, 'Account', ['name' => $data['name']], $data);
    }

    // -------------------------------------------------------------
    // 3. Contacts (Supplier Representatives & Procurement Leads)
    // -------------------------------------------------------------
    $contacts = [
        [
            'firstName' => 'Sarah',
            'lastName' => 'Jenkins',
            'title' => 'Chief Commercial Officer',
            'emailAddress' => 's.jenkins@apexindustrial.example.com',
            'phoneNumber' => '+234-802-555-0101',
            'accountId' => $createdAccounts[0]->getId(),
            'description' => 'Primary decision maker for heavy equipment delivery schedules and warranty commitments.'
        ],
        [
            'firstName' => 'Marcus',
            'lastName' => 'Vance',
            'title' => 'Enterprise Solutions Director',
            'emailAddress' => 'm.vance@nexuscloud.example.com',
            'phoneNumber' => '+234-802-555-0102',
            'accountId' => $createdAccounts[1]->getId(),
            'description' => 'Technical lead for server rack hardware configurations and SLA management.'
        ],
        [
            'firstName' => 'Elena',
            'lastName' => 'Rostova',
            'title' => 'Head of Global Logistics',
            'emailAddress' => 'e.rostova@transfreight.example.com',
            'phoneNumber' => '+234-802-555-0103',
            'accountId' => $createdAccounts[2]->getId(),
            'description' => 'Coordinates port clearance, customs declarations, and dispatch timelines.'
        ],
        [
            'firstName' => 'Tariq',
            'lastName' => 'Al-Mansoor',
            'title' => 'Managing Director',
            'emailAddress' => 't.mansoor@zenithsecurity.example.com',
            'phoneNumber' => '+234-802-555-0104',
            'accountId' => $createdAccounts[3]->getId(),
            'description' => 'Senior signatory for physical security infrastructure contracts.'
        ],
        [
            'firstName' => 'Amina',
            'lastName' => 'Bello',
            'title' => 'Senior Contracts Manager',
            'emailAddress' => 'a.bello@standardcorporate.example.com',
            'phoneNumber' => '+234-802-555-0105',
            'accountId' => $createdAccounts[4]->getId(),
            'description' => 'Key liaison for bulk paper, stationery, and corporate branding materials.'
        ],
        [
            'firstName' => 'Dr. Kenneth',
            'lastName' => 'Cole',
            'title' => 'Director of Diagnostic Supplies',
            'emailAddress' => 'k.cole@biohealthdiagnostics.example.com',
            'phoneNumber' => '+234-802-555-0106',
            'accountId' => $createdAccounts[5]->getId(),
            'description' => 'Supervises laboratory cold-chain compliance and medical equipment certifications.'
        ],
        [
            'firstName' => 'Engr. Babatunde',
            'lastName' => 'Adeleke',
            'title' => 'Chief Technical Officer',
            'emailAddress' => 'b.adeleke@cleanenergyconsortium.example.com',
            'phoneNumber' => '+234-802-555-0107',
            'accountId' => $createdAccounts[7]->getId(),
            'description' => 'Lead solar engineer coordinating regional sub-station power backup retrofits.'
        ]
    ];

    $createdContacts = [];
    foreach ($contacts as $data) {
        if ($adminId) {
            $data['assignedUserId'] = $adminId;
        }
        $createdContacts[] = getOrCreateEntity($em, 'Contact', ['emailAddress' => $data['emailAddress']], $data);
    }

    // -------------------------------------------------------------
    // 4. Leads (Prospective Vendor Registrations & Prequalifications)
    // -------------------------------------------------------------
    $leads = [
        [
            'firstName' => 'Olumide',
            'lastName' => 'Bakare',
            'accountName' => 'SolarEdge Engineering Systems',
            'title' => 'Business Development Director',
            'emailAddress' => 'o.bakare@solaredge-eng.example.com',
            'phoneNumber' => '+234-803-111-2233',
            'status' => 'New',
            'source' => 'Web Site',
            'industry' => 'Energy',
            'opportunityAmount' => 175000.00,
            'description' => 'Submitted online expression of interest for the 2026 Solar Mini-Grid Procurement tender.'
        ],
        [
            'firstName' => 'Chiamaka',
            'lastName' => 'Okoro',
            'accountName' => 'EcoClean Environmental Services Ltd',
            'title' => 'General Manager',
            'emailAddress' => 'c.okoro@ecoclean.example.com',
            'phoneNumber' => '+234-803-222-3344',
            'status' => 'Assigned',
            'source' => 'Campaign',
            'industry' => 'Services',
            'opportunityAmount' => 65000.00,
            'description' => 'Vendor applied for annual headquarters janitorial and waste management tender.'
        ],
        [
            'firstName' => 'Ibrahim',
            'lastName' => 'Danjuma',
            'accountName' => 'SafeGuard Fleet Telematics',
            'title' => 'VP Enterprise Sales',
            'emailAddress' => 'i.danjuma@safeguardfleet.example.com',
            'phoneNumber' => '+234-803-333-4455',
            'status' => 'In Process',
            'source' => 'Partner',
            'industry' => 'Technology',
            'opportunityAmount' => 120000.00,
            'description' => 'Submitting technical compliance dossier for vehicle GPS tracking and asset monitoring.'
        ],
        [
            'firstName' => 'Grace',
            'lastName' => 'Etim',
            'accountName' => 'Pinnacle Civil Engineering Works',
            'title' => 'Managing Partner',
            'emailAddress' => 'g.etim@pinnaclecivil.example.com',
            'phoneNumber' => '+234-803-444-5566',
            'status' => 'Converted',
            'source' => 'Public Relations',
            'industry' => 'Construction',
            'opportunityAmount' => 380000.00,
            'description' => 'Successfully passed preliminary screening; converted to approved vendor pool.'
        ],
        [
            'firstName' => 'Victor',
            'lastName' => 'Okafor',
            'accountName' => 'Metro Security Printing Press',
            'title' => 'Sales Director',
            'emailAddress' => 'v.okafor@metrosecurityprint.example.com',
            'phoneNumber' => '+234-803-555-6677',
            'status' => 'Recycled',
            'source' => 'Email',
            'industry' => 'Manufacturing',
            'opportunityAmount' => 45000.00,
            'description' => 'Tax clearance certificate expired; returned to pipeline pending document renewal.'
        ]
    ];

    foreach ($leads as $data) {
        if ($adminId) {
            $data['assignedUserId'] = $adminId;
        }
        getOrCreateEntity($em, 'Lead', ['emailAddress' => $data['emailAddress']], $data);
    }

    // -------------------------------------------------------------
    // 5. Opportunities / Procurement Tenders (Sales Funnel & Kanban)
    // -------------------------------------------------------------
    $opportunities = [
        [
            'name' => '2026 Heavy Duty Power Generators Tender',
            'stage' => 'Prospecting',
            'amount' => 185000.00,
            'amountCurrency' => 'USD',
            'closeDate' => date('Y-m-d', strtotime('+45 days')),
            'probability' => 15,
            'accountId' => $createdAccounts[0]->getId(),
            'description' => 'Procurement of three 500kVA low-emission diesel generators with automatic transfer switches.'
        ],
        [
            'name' => 'National IT Cloud Infrastructure & Enterprise Firewall RFP',
            'stage' => 'Qualification',
            'amount' => 450000.00,
            'amountCurrency' => 'USD',
            'closeDate' => date('Y-m-d', strtotime('+30 days')),
            'probability' => 35,
            'accountId' => $createdAccounts[1]->getId(),
            'description' => 'Multi-region sovereign cloud deployment, high-throughput firewalls, and 24/7 SIEM monitoring.'
        ],
        [
            'name' => 'Annual Protective PPE & Industrial Safety Bulk Tender',
            'stage' => 'Proposal/Price Quote',
            'amount' => 135000.00,
            'amountCurrency' => 'USD',
            'closeDate' => date('Y-m-d', strtotime('+20 days')),
            'probability' => 65,
            'accountId' => $createdAccounts[0]->getId(),
            'description' => 'Bulk supply of high-grade flame-retardant coveralls, steel-toe boots, and respirators.'
        ],
        [
            'name' => 'Enterprise ERP Software & Database Licensing Renewal',
            'stage' => 'Negotiation/Review',
            'amount' => 320000.00,
            'amountCurrency' => 'USD',
            'closeDate' => date('Y-m-d', strtotime('+10 days')),
            'probability' => 85,
            'accountId' => $createdAccounts[1]->getId(),
            'description' => 'Annual enterprise license renewals, premier database maintenance, and developer support SLAs.'
        ],
        [
            'name' => 'Fleet Haulage & Overland Freight Framework Agreement',
            'stage' => 'Closed Won',
            'amount' => 580000.00,
            'amountCurrency' => 'USD',
            'closeDate' => date('Y-m-d', strtotime('-5 days')),
            'probability' => 100,
            'accountId' => $createdAccounts[2]->getId(),
            'description' => 'Contract formally awarded for nationwide multi-modal freight haulage and regional distribution.'
        ],
        [
            'name' => 'Biometric Access Control & Physical Security Installation',
            'stage' => 'Closed Won',
            'amount' => 210000.00,
            'amountCurrency' => 'USD',
            'closeDate' => date('Y-m-d', strtotime('-15 days')),
            'probability' => 100,
            'accountId' => $createdAccounts[3]->getId(),
            'description' => 'Completed installation of turnstiles, biometric scanners, and centralized control station.'
        ],
        [
            'name' => 'Legacy Document Archival & Scanning Tender (Batch 4)',
            'stage' => 'Closed Lost',
            'amount' => 95000.00,
            'amountCurrency' => 'USD',
            'closeDate' => date('Y-m-d', strtotime('-25 days')),
            'probability' => 0,
            'accountId' => $createdAccounts[4]->getId(),
            'description' => 'Bids exceeded statutory budget ceiling; tender re-scoped for subsequent fiscal quarter.'
        ]
    ];

    $createdOpportunities = [];
    foreach ($opportunities as $data) {
        if ($adminId) {
            $data['assignedUserId'] = $adminId;
        }
        $createdOpportunities[] = getOrCreateEntity($em, 'Opportunity', ['name' => $data['name']], $data);
    }

    // -------------------------------------------------------------
    // 6. Cases (Vendor Disputes, Inquiries & Quality Tickets)
    // -------------------------------------------------------------
    $cases = [
        [
            'name' => 'Urgent: Defective Industrial Respirators - Batch Recall Request',
            'priority' => 'Urgent',
            'status' => 'Assigned',
            'type' => 'Problem',
            'accountId' => $createdAccounts[0]->getId(),
            'contactId' => $createdContacts[0]->getId(),
            'description' => 'Quality inspection revealed defective exhalation valves in Lot #904. Immediate replacement demanded.'
        ],
        [
            'name' => 'Port Clearance Delay on 40ft Container Freight #4092',
            'priority' => 'High',
            'status' => 'Pending',
            'type' => 'Incident',
            'accountId' => $createdAccounts[2]->getId(),
            'contactId' => $createdContacts[2]->getId(),
            'description' => 'Customs documentation audit delayed release at Apapa Port. Demurrage waiver requested.'
        ],
        [
            'name' => 'Technical Specification Clarification: Server Rack Bidding',
            'priority' => 'Normal',
            'status' => 'New',
            'type' => 'Question',
            'accountId' => $createdAccounts[1]->getId(),
            'contactId' => $createdContacts[1]->getId(),
            'description' => 'Vendor requested confirmation on whether 42U or 48U rack enclosures are required for Section B.'
        ],
        [
            'name' => '2026 Tax Clearance Certificate Renewal Protocol',
            'priority' => 'Low',
            'status' => 'Closed',
            'type' => 'Question',
            'accountId' => $createdAccounts[4]->getId(),
            'contactId' => $createdContacts[4]->getId(),
            'description' => 'Vendor confirmed updated TCC uploaded and successfully validated with tax authorities.'
        ]
    ];

    foreach ($cases as $data) {
        if ($adminId) {
            $data['assignedUserId'] = $adminId;
        }
        getOrCreateEntity($em, 'Case', ['name' => $data['name']], $data);
    }

    // -------------------------------------------------------------
    // 7. Tasks (Action Items & Milestones)
    // -------------------------------------------------------------
    $tasks = [
        [
            'name' => 'Evaluate Technical Bids for National IT Cloud Tender',
            'priority' => 'Urgent',
            'status' => 'Started',
            'dateStart' => date('Y-m-d 08:00:00'),
            'dateEnd' => date('Y-m-d 17:00:00', strtotime('+2 days')),
            'parentType' => 'Opportunity',
            'parentId' => $createdOpportunities[1]->getId(),
            'description' => 'Complete technical evaluation scoring sheet based on ISO 27001 and FedRAMP compliance.'
        ],
        [
            'name' => 'Conduct Physical Factory Inspection at Apex Industrial',
            'priority' => 'High',
            'status' => 'Not Started',
            'dateStart' => date('Y-m-d 09:00:00', strtotime('+4 days')),
            'dateEnd' => date('Y-m-d 16:00:00', strtotime('+5 days')),
            'parentType' => 'Account',
            'parentId' => $createdAccounts[0]->getId(),
            'description' => 'Verify factory manufacturing tooling, worker safety protocols, and backup generator inventory.'
        ],
        [
            'name' => 'Verify CAC Registration & Tax Clearance for SolarEdge Ltd',
            'priority' => 'Normal',
            'status' => 'Not Started',
            'dateStart' => date('Y-m-d 08:30:00'),
            'dateEnd' => date('Y-m-d 17:00:00', strtotime('+1 day')),
            'parentType' => 'Account',
            'parentId' => $createdAccounts[7]->getId(),
            'description' => 'Cross-reference corporate affairs commission filings and electronic tax compliance status.'
        ],
        [
            'name' => 'Sign & Execute Overland Freight Master Service Agreement',
            'priority' => 'Normal',
            'status' => 'Completed',
            'dateStart' => date('Y-m-d 10:00:00', strtotime('-3 days')),
            'dateEnd' => date('Y-m-d 12:00:00', strtotime('-1 day')),
            'parentType' => 'Opportunity',
            'parentId' => $createdOpportunities[4]->getId(),
            'description' => 'Contracts reviewed by Legal Department and counter-signed by Managing Director.'
        ],
        [
            'name' => 'Prepare Quarterly Procurement Expenditure & Audit Report',
            'priority' => 'Low',
            'status' => 'Deferred',
            'dateStart' => date('Y-m-d 08:00:00', strtotime('+10 days')),
            'dateEnd' => date('Y-m-d 18:00:00', strtotime('+14 days')),
            'description' => 'Aggregate all Q1 procurement commitments, purchase orders, and cost variance analytics.'
        ]
    ];

    foreach ($tasks as $data) {
        if ($adminId) {
            $data['assignedUserId'] = $adminId;
        }
        getOrCreateEntity($em, 'Task', ['name' => $data['name']], $data);
    }

    // -------------------------------------------------------------
    // 8. Meetings (Calendar Events & Bid Opening Sessions)
    // -------------------------------------------------------------
    $meetings = [
        [
            'name' => 'Pre-Bid Clarification Conference: Cloud Infrastructure RFP',
            'status' => 'Planned',
            'dateStart' => date('Y-m-d 10:00:00', strtotime('+3 days')),
            'dateEnd' => date('Y-m-d 12:00:00', strtotime('+3 days')),
            'parentType' => 'Opportunity',
            'parentId' => $createdOpportunities[1]->getId(),
            'description' => 'Virtual pre-bid conference addressing bidder questions regarding cloud architecture.'
        ],
        [
            'name' => 'Quarterly Vendor Performance & SLA Review: Global Freight',
            'status' => 'Planned',
            'dateStart' => date('Y-m-d 14:00:00', strtotime('+7 days')),
            'dateEnd' => date('Y-m-d 15:30:00', strtotime('+7 days')),
            'parentType' => 'Account',
            'parentId' => $createdAccounts[2]->getId(),
            'description' => 'Review on-time delivery metrics, port detention times, and Q2 haulage capacity.'
        ],
        [
            'name' => 'Tender Evaluation Committee Official Bid Opening',
            'status' => 'Held',
            'dateStart' => date('Y-m-d 11:00:00', strtotime('-2 days')),
            'dateEnd' => date('Y-m-d 13:00:00', strtotime('-2 days')),
            'parentType' => 'Opportunity',
            'parentId' => $createdOpportunities[2]->getId(),
            'description' => 'Public opening of sealed financial proposals in accordance with public procurement statutory guidelines.'
        ]
    ];

    foreach ($meetings as $data) {
        if ($adminId) {
            $data['assignedUserId'] = $adminId;
        }
        getOrCreateEntity($em, 'Meeting', ['name' => $data['name']], $data);
    }

    // -------------------------------------------------------------
    // 9. Calls (Telephonic Logs & Inquiries)
    // -------------------------------------------------------------
    $calls = [
        [
            'name' => 'Supplier Delivery Milestone Check-in with Sarah Jenkins',
            'status' => 'Held',
            'direction' => 'Outbound',
            'dateStart' => date('Y-m-d 09:15:00', strtotime('-1 day')),
            'dateEnd' => date('Y-m-d 09:30:00', strtotime('-1 day')),
            'parentType' => 'Account',
            'parentId' => $createdAccounts[0]->getId(),
            'description' => 'Confirmed manufacturing timeline for the first batch of generators is on schedule for next month.'
        ],
        [
            'name' => 'Bidding Terms Clarification with Marcus Vance',
            'status' => 'Planned',
            'direction' => 'Inbound',
            'dateStart' => date('Y-m-d 15:00:00', strtotime('+1 day')),
            'dateEnd' => date('Y-m-d 15:30:00', strtotime('+1 day')),
            'parentType' => 'Account',
            'parentId' => $createdAccounts[1]->getId(),
            'description' => 'Clarify payment milestone clauses and local content compliance requirements.'
        ],
        [
            'name' => 'Urgent Logistics Escalation: Port Clearance Hold',
            'status' => 'Held',
            'direction' => 'Inbound',
            'dateStart' => date('Y-m-d 16:00:00', strtotime('-2 days')),
            'dateEnd' => date('Y-m-d 16:25:00', strtotime('-2 days')),
            'parentType' => 'Account',
            'parentId' => $createdAccounts[2]->getId(),
            'description' => 'Discussed terminal handling charge exemptions and expedited scanning protocol.'
        ]
    ];

    foreach ($calls as $data) {
        if ($adminId) {
            $data['assignedUserId'] = $adminId;
        }
        getOrCreateEntity($em, 'Call', ['name' => $data['name']], $data);
    }

    // -------------------------------------------------------------
    // 10. Documents (Procurement Library & Compliance Manuals)
    // -------------------------------------------------------------
    $documents = [
        [
            'name' => 'NRS National Procurement & Compliance Manual 2026',
            'status' => 'Active',
            'type' => 'Policy',
            'publishDate' => date('Y-01-05'),
            'description' => 'Comprehensive manual defining public procurement statutory thresholds, tendering rules, and ethics code.'
        ],
        [
            'name' => 'Standard Bidding Document (SBD) Template - Works & Goods',
            'status' => 'Active',
            'type' => 'Contract',
            'publishDate' => date('Y-01-15'),
            'description' => 'Official standard template for all competitive procurement bids, RFPs, and framework agreements.'
        ],
        [
            'name' => 'Supplier Code of Conduct & Anti-Corruption Charter',
            'status' => 'Active',
            'type' => 'Policy',
            'publishDate' => date('Y-02-01'),
            'description' => 'Zero-tolerance charter on bribery, conflicts of interest, and anti-competitive practices.'
        ],
        [
            'name' => 'Annual Vendor Performance Assessment & Spend Review 2025',
            'status' => 'Active',
            'type' => 'Other',
            'publishDate' => date('Y-03-01'),
            'description' => 'Audited performance analytics covering supplier delivery lead times and contract compliance.'
        ]
    ];

    foreach ($documents as $data) {
        if ($adminId) {
            $data['assignedUserId'] = $adminId;
        }
        getOrCreateEntity($em, 'Document', ['name' => $data['name']], $data);
    }

    // -------------------------------------------------------------
    // 11. Knowledge Base (Categories & Articles)
    // -------------------------------------------------------------
    $kbCategories = [
        ['name' => 'Vendor Onboarding & Accreditation', 'description' => 'Guides and documentation on how vendors register and obtain approved status.'],
        ['name' => 'Tender Procedures & Evaluation Guidelines', 'description' => 'How procurement evaluation committees assess technical and commercial proposals.'],
        ['name' => 'Invoicing & Milestone Payment Protocols', 'description' => 'Standard procedures for submitting invoices and securing disbursement approvals.']
    ];

    $createdKBCats = [];
    foreach ($kbCategories as $cData) {
        $createdKBCats[] = getOrCreateEntity($em, 'KnowledgeBaseCategory', ['name' => $cData['name']], $cData);
    }

    $kbArticles = [
        [
            'name' => 'Step-by-Step Guide to Vendor Pre-Qualification and Registration',
            'status' => 'Published',
            'body' => '<p>All prospective vendors wishing to participate in public procurement exercises must complete the five-step registration process:</p><ol><li>Submit Corporate Affairs Commission (CAC) Registration Certificate.</li><li>Provide verified 3-year Tax Clearance Certificates (TCC).</li><li>Submit Industrial Training Fund (ITF) and PENCOM Compliance Certificates.</li><li>Submit Bank Reference Letter with verified credit rating.</li><li>Sign and adhere to the Supplier Anti-Corruption Charter.</li></ol>',
            'cat' => $createdKBCats[0]
        ],
        [
            'name' => 'Two-Stage Bidding Evaluation Matrix and Scoring Methodology',
            'status' => 'Published',
            'body' => '<p>Procurement bids are evaluated using a strict transparent two-stage evaluation methodology:</p><ul><li><strong>Stage 1: Technical Qualification (70% Minimum Pass Threshold)</strong>: Evaluates experience, personnel qualifications, methodology, and equipment specifications.</li><li><strong>Stage 2: Financial Proposal (Lowest Responsive Bidder)</strong>: Only bids passing the technical threshold have financial proposals opened and scored.</li></ul>',
            'cat' => $createdKBCats[1]
        ],
        [
            'name' => 'Guidelines for Bank Guarantees and Performance Bonds',
            'status' => 'Published',
            'body' => '<p>Contracts exceeding statutory threshold levels require an unconditional, irrevocable Bank Guarantee or Performance Bond issued by an accredited financial institution:</p><ul><li>Advance payment guarantees must cover 100% of the advance amount.</li><li>Performance bonds must represent at least 10% of the total awarded contract sum.</li></ul>',
            'cat' => $createdKBCats[1]
        ],
        [
            'name' => 'Milestone Deliverable Approvals and Invoice Processing Workflow',
            'status' => 'Published',
            'body' => '<p>To ensure prompt payment processing, vendors must adhere to the following workflow:</p><ol><li>Submit delivery notes signed by an authorized facility inspection officer.</li><li>Provide Job Completion Certificate (JCC) counter-signed by Project Lead.</li><li>Submit commercial invoice matching Purchase Order (PO) line items.</li><li>Payments are disbursed via electronic treasury transfer within 21 working days.</li></ol>',
            'cat' => $createdKBCats[2]
        ]
    ];

    foreach ($kbArticles as $aData) {
        $cat = $aData['cat'];
        unset($aData['cat']);
        if ($adminId) {
            $aData['assignedUserId'] = $adminId;
        }
        $article = getOrCreateEntity($em, 'KnowledgeBaseArticle', ['name' => $aData['name']], $aData);
        if ($cat) {
            try {
                $em->getRDBRepository('KnowledgeBaseArticle')->getRelation($article, 'categories')->relate($cat);
            } catch (\Throwable $t) {}
        }
    }

    // -------------------------------------------------------------
    // 12. Target Lists & Campaigns
    // -------------------------------------------------------------
    $targetList = getOrCreateEntity($em, 'TargetList', ['name' => 'Accredited Tier-1 Government Suppliers'], [
        'name' => 'Accredited Tier-1 Government Suppliers',
        'description' => 'Target list containing pre-qualified enterprise suppliers across technology, civil works, and heavy equipment.'
    ]);

    $campaign = getOrCreateEntity($em, 'Campaign', ['name' => '2026 Annual Vendor Recertification & Sustainability Drive'], [
        'name' => '2026 Annual Vendor Recertification & Sustainability Drive',
        'status' => 'Active',
        'type' => 'Email',
        'budget' => 25000.00,
        'description' => 'National procurement communication campaign reminding approved vendors to update tax compliance records and environmental certifications.'
    ]);

    // -------------------------------------------------------------
    // 13. Activity Feeds & Stream Notes (Audit Trail)
    // -------------------------------------------------------------
    $notes = [
        [
            'parentType' => 'Account',
            'parentId' => $createdAccounts[0]->getId(),
            'post' => 'Vendor submitted updated ISO 9001:2015 Quality Management certification and 2025 Tax Clearance Certificate. Verified valid by Compliance Team.'
        ],
        [
            'parentType' => 'Account',
            'parentId' => $createdAccounts[1]->getId(),
            'post' => 'Nexus Cyber completed Tier-IV data center security audit. All encryption standards and GDPR/NDPR compliance approved.'
        ],
        [
            'parentType' => 'Opportunity',
            'parentId' => $createdOpportunities[1]->getId(),
            'post' => 'Tenders Evaluation Committee completed Stage 1 technical scoring. Overall technical score: 94.5/100 (Pass).'
        ],
        [
            'parentType' => 'Opportunity',
            'parentId' => $createdOpportunities[4]->getId(),
            'post' => 'Formal contract signed and sealed. Advance mobilization bond deposited and verified with Central Bank.'
        ]
    ];

    foreach ($notes as $nData) {
        try {
            $note = $em->getEntity('Note');
            $note->set('type', 'Post');
            $note->set('parentType', $nData['parentType']);
            $note->set('parentId', $nData['parentId']);
            $note->set('post', $nData['post']);
            if ($adminId) {
                $note->set('createdById', $adminId);
            }
            $em->saveEntity($note);
        } catch (\Throwable $e) {}
    }

    echo "Comprehensive NRS-Procuresmart demo data seeded successfully!\n";
} catch (\Throwable $e) {
    echo "Warning during demo seeding: " . $e->getMessage() . "\n";
}
