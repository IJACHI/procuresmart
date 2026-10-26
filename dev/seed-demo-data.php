<?php
/************************************************************************
 * Seed Comprehensive Demo Data for NRS-Procuresmart / EspoCRM
 * 
 * Demonstrates all major procurement CRM features:
 * - Teams (Tenders Board, Procurement Committee, Compliance)
 * - Accounts (Vendors, Suppliers, Partners, Federal Agencies)
 * - Contacts (Procurement Directors, Vendor Reps, Compliance Officers)
 * - Leads (Prospective Vendors, Prequalification Submissions)
 * - Opportunities (Public Tenders, RFQs, Works, Consumables)
 * - Tasks (Compliance Checks, Technical Evaluations, Site Audits)
 * - Meetings (Public Bid Openings, Pre-bid Conferences, Board Sessions)
 * - Calls (Vendor Inquiries, Price Clarifications, Delivery Updates)
 * - Cases (Vendor Issues, Portal Support, Specification Inquiries)
 * - Knowledge Base (Categories & Comprehensive Procurement Guides)
 * - Campaigns & Target Lists (Vendor Registration Drives)
 * - Activity Stream Notes (Committee Notes & Audit Trails)
 ************************************************************************/

include __DIR__ . '/../bootstrap.php';

use Espo\Core\Application;
use Espo\ORM\EntityManager;

$app = new Application();
$container = $app->getContainer();
$em = $container->getByClass(EntityManager::class);

echo "=========================================================\n";
echo "Starting NRS-Procuresmart Comprehensive Demo Data Seeding\n";
echo "=========================================================\n";

// Helper function to create an entity safely
function seedEntity($em, $entityType, $data) {
    try {
        $entity = $em->getEntity($entityType);
        foreach ($data as $field => $val) {
            $entity->set($field, $val);
        }
        $em->saveEntity($entity);
        return $entity;
    } catch (\Throwable $e) {
        echo "Note on {$entityType} [{$data['name']}]: " . $e->getMessage() . "\n";
        return null;
    }
}

// -------------------------------------------------------------
// 0. Locate Admin User
// -------------------------------------------------------------
$adminUser = $em->getRepository('User')->where(['userName' => 'admin'])->findOne();
$adminId = $adminUser ? $adminUser->getId() : null;
echo "Admin user ID: " . ($adminId ?? 'none') . "\n";

// -------------------------------------------------------------
// 1. Seed Teams
// -------------------------------------------------------------
echo "1. Seeding Procurement Teams...\n";
$teamsData = [
    [
        'name' => 'NRS Tenders Board',
        'rolesIds' => [],
        'description' => 'Standing executive committee responsible for reviewing, evaluating, and awarding federal procurement contracts.'
    ],
    [
        'name' => 'Procurement Planning Committee',
        'rolesIds' => [],
        'description' => 'Unit responsible for annual procurement planning, needs assessment, and technical specification drafting.'
    ],
    [
        'name' => 'Vendor Compliance & Audit Unit',
        'rolesIds' => [],
        'description' => 'Unit in charge of statutory verification (CAC, Tax Clearance Certificates, PenCom, ITF, and BPP registration).'
    ]
];

$createdTeams = [];
foreach ($teamsData as $data) {
    $existing = $em->getRepository('Team')->where(['name' => $data['name']])->findOne();
    if ($existing) {
        $createdTeams[] = $existing;
    } else {
        $team = seedEntity($em, 'Team', $data);
        if ($team) {
            $createdTeams[] = $team;
        }
    }
}

// Assign admin to teams
if ($adminUser && !empty($createdTeams)) {
    try {
        $teamIds = array_map(fn($t) => $t->getId(), $createdTeams);
        $adminUser->set('teamsIds', $teamIds);
        $em->saveEntity($adminUser);
    } catch (\Throwable $e) {}
}

// -------------------------------------------------------------
// 2. Seed Accounts (Vendors, Suppliers & Regulatory Partners)
// -------------------------------------------------------------
echo "2. Seeding Accounts (Suppliers, Vendors & Federal Partners)...\n";
$accountsData = [
    [
        'name' => 'Abuja Data Systems Ltd',
        'type' => 'Vendor',
        'industry' => 'Technology',
        'website' => 'https://abujadatasystems.example.ng',
        'emailAddress' => 'tenders@abujadatasystems.example.ng',
        'phoneNumber' => '+234-9-291-8821',
        'billingAddressStreet' => 'Plot 412 Constitution Avenue, Central Business District',
        'billingAddressCity' => 'Abuja',
        'billingAddressState' => 'FCT',
        'billingAddressPostalCode' => '900211',
        'billingAddressCountry' => 'Nigeria',
        'assignedUserId' => $adminId,
        'description' => 'Tier-1 certified OEM hardware vendor, enterprise data center integrator, and network security contractor for federal agencies.'
    ],
    [
        'name' => 'Greenfield Facilities & Logistics Services',
        'type' => 'Vendor',
        'industry' => 'Services',
        'website' => 'https://greenfieldservices.example.ng',
        'emailAddress' => 'contracts@greenfieldservices.example.ng',
        'phoneNumber' => '+234-1-492-3310',
        'billingAddressStreet' => '14 Commercial Boulevard, Victoria Island',
        'billingAddressCity' => 'Lagos',
        'billingAddressState' => 'Lagos',
        'billingAddressPostalCode' => '101241',
        'billingAddressCountry' => 'Nigeria',
        'assignedUserId' => $adminId,
        'description' => 'Leading facility management contractor managing heavy diesel generators, HVAC cooling systems, and electrical infrastructure.'
    ],
    [
        'name' => 'Zenith Security & Surveillance Ltd',
        'type' => 'Vendor',
        'industry' => 'Security',
        'website' => 'https://zenithsecurity.example.ng',
        'emailAddress' => 'bids@zenithsecurity.example.ng',
        'phoneNumber' => '+234-84-391-445',
        'billingAddressStreet' => '88 Trans-Amadi Industrial Layout',
        'billingAddressCity' => 'Port Harcourt',
        'billingAddressState' => 'Rivers',
        'billingAddressPostalCode' => '500101',
        'billingAddressCountry' => 'Nigeria',
        'assignedUserId' => $adminId,
        'description' => 'Specialized security engineering firm providing biometric turnstiles, thermal imaging, automated perimeter defense, and IP CCTV surveillance.'
    ],
    [
        'name' => 'Sterling Office & Stationery Mart',
        'type' => 'Vendor',
        'industry' => 'Retail',
        'website' => 'https://sterlingoffice.example.ng',
        'emailAddress' => 'supply@sterlingoffice.example.ng',
        'phoneNumber' => '+234-9-523-9912',
        'billingAddressStreet' => '22 Gana Street, Maitama',
        'billingAddressCity' => 'Abuja',
        'billingAddressState' => 'FCT',
        'billingAddressPostalCode' => '900271',
        'billingAddressCountry' => 'Nigeria',
        'assignedUserId' => $adminId,
        'description' => 'Approved national vendor supplying ergonomic executive chairs, modular workstations, secure shredders, and high-volume stationery.'
    ],
    [
        'name' => 'Trans-Sahara Logistics & Transport Ltd',
        'type' => 'Vendor',
        'industry' => 'Transportation',
        'website' => 'https://transsaharalogistics.example.ng',
        'emailAddress' => 'fleet@transsahara.example.ng',
        'phoneNumber' => '+234-64-633-820',
        'billingAddressStreet' => '10 Bompai Road',
        'billingAddressCity' => 'Kano',
        'billingAddressState' => 'Kano',
        'billingAddressPostalCode' => '700213',
        'billingAddressCountry' => 'Nigeria',
        'assignedUserId' => $adminId,
        'description' => 'Nationwide haulage, warehousing, and secure document freight carrier covering all 36 state branch offices.'
    ],
    [
        'name' => 'Alpha Health & Medical Supplies',
        'type' => 'Vendor',
        'industry' => 'Healthcare',
        'website' => 'https://alphahealthsupplies.example.ng',
        'emailAddress' => 'orders@alphahealth.example.ng',
        'phoneNumber' => '+234-2-810-4491',
        'billingAddressStreet' => '5 Queen Elizabeth Road, Mokola',
        'billingAddressCity' => 'Ibadan',
        'billingAddressState' => 'Oyo',
        'billingAddressPostalCode' => '200221',
        'billingAddressCountry' => 'Nigeria',
        'assignedUserId' => $adminId,
        'description' => 'Certified medical equipment distributor supplying workplace health clinics, automated external defibrillators (AED), and emergency kits.'
    ],
    [
        'name' => 'Bureau of Public Procurement (BPP) Desk',
        'type' => 'Partner',
        'industry' => 'Government',
        'website' => 'https://bpp.gov.ng',
        'emailAddress' => 'compliance@bpp.gov.ng',
        'phoneNumber' => '+234-9-461-9000',
        'billingAddressStreet' => '11 Suleiman Barau Crescent, Presidential Villa',
        'billingAddressCity' => 'Abuja',
        'billingAddressState' => 'FCT',
        'billingAddressPostalCode' => '900211',
        'billingAddressCountry' => 'Nigeria',
        'assignedUserId' => $adminId,
        'description' => 'Federal regulatory body overseeing procurement compliance, Certificate of No Objection, and National Database of Contractors.'
    ]
];

$accounts = [];
foreach ($accountsData as $data) {
    $existing = $em->getRepository('Account')->where(['name' => $data['name']])->findOne();
    if ($existing) {
        $accounts[] = $existing;
    } else {
        $acc = seedEntity($em, 'Account', $data);
        if ($acc) {
            $accounts[] = $acc;
        }
    }
}

// -------------------------------------------------------------
// 3. Seed Contacts (Key Decision-Makers & Vendor Liaisons)
// -------------------------------------------------------------
echo "3. Seeding Contacts (Vendor Representatives & Officials)...\n";
$contactsData = [
    [
        'firstName' => 'Babatunde',
        'lastName' => 'Adeyemi',
        'salutationName' => 'Engr.',
        'title' => 'Managing Director / Lead Systems Engineer',
        'emailAddress' => 'b.adeyemi@abujadatasystems.example.ng',
        'phoneNumber' => '+234-803-123-4567',
        'accountIndex' => 0,
        'assignedUserId' => $adminId,
        'description' => 'Primary liaison for datacenter hardware tenders and Cisco/Fortinet technical compliance.'
    ],
    [
        'firstName' => 'Amina',
        'lastName' => 'Bello',
        'salutationName' => 'Hajia',
        'title' => 'Director of Corporate Bidding',
        'emailAddress' => 'a.bello@greenfieldservices.example.ng',
        'phoneNumber' => '+234-802-987-6543',
        'accountIndex' => 1,
        'assignedUserId' => $adminId,
        'description' => 'Lead contact for annual facilities maintenance SLA negotiations and generator overhaul schedules.'
    ],
    [
        'firstName' => 'Chinedu',
        'lastName' => 'Okonkwo',
        'salutationName' => 'Dr.',
        'title' => 'Head of Technical Compliance',
        'emailAddress' => 'c.okonkwo@zenithsecurity.example.ng',
        'phoneNumber' => '+234-805-443-2211',
        'accountIndex' => 2,
        'assignedUserId' => $adminId,
        'description' => 'Directs access control installations, security certifications, and biometric hardware warranties.'
    ],
    [
        'firstName' => 'Ibrahim',
        'lastName' => 'Musa',
        'salutationName' => 'Mallam',
        'title' => 'Senior Corporate Account Executive',
        'emailAddress' => 'i.musa@sterlingoffice.example.ng',
        'phoneNumber' => '+234-807-665-9988',
        'accountIndex' => 3,
        'assignedUserId' => $adminId,
        'description' => 'Coordinates executive furniture bulk deliveries, warehouse logistics, and assembly warranty.'
    ],
    [
        'firstName' => 'Garba',
        'lastName' => 'Shehu',
        'salutationName' => 'Alhaji',
        'title' => 'Director of Fleet & Dispatch Logistics',
        'emailAddress' => 'g.shehu@transsahara.example.ng',
        'phoneNumber' => '+234-809-332-1100',
        'accountIndex' => 4,
        'assignedUserId' => $adminId,
        'description' => 'Oversees national transit manifests, waybills, and secure bonded courier deliveries.'
    ],
    [
        'firstName' => 'Ngozi',
        'lastName' => 'Eze',
        'salutationName' => 'Dr.',
        'title' => 'Medical Supplies Procurement Manager',
        'emailAddress' => 'n.eze@alphahealth.example.ng',
        'phoneNumber' => '+234-803-555-8899',
        'accountIndex' => 5,
        'assignedUserId' => $adminId,
        'description' => 'Clinical supplies coordinator for federal staff medical centers and workplace first aid gear.'
    ],
    [
        'firstName' => 'Folashade',
        'lastName' => 'Adeleke',
        'salutationName' => 'Mrs.',
        'title' => 'Federal Procurement Liaison Inspector',
        'emailAddress' => 'f.adeleke@bpp.gov.ng',
        'phoneNumber' => '+234-806-221-4477',
        'accountIndex' => 6,
        'assignedUserId' => $adminId,
        'description' => 'BPP Desk officer monitoring compliance with Public Procurement Act guidelines.'
    ]
];

$contacts = [];
foreach ($contactsData as $data) {
    $acc = isset($data['accountIndex']) && isset($accounts[$data['accountIndex']]) ? $accounts[$data['accountIndex']] : null;
    unset($data['accountIndex']);
    if ($acc) {
        $data['accountId'] = $acc->getId();
    }
    
    $existing = $em->getRepository('Contact')->where(['emailAddress' => $data['emailAddress']])->findOne();
    if ($existing) {
        $contacts[] = $existing;
    } else {
        $cnt = seedEntity($em, 'Contact', $data);
        if ($cnt) {
            $contacts[] = $cnt;
        }
    }
}

// -------------------------------------------------------------
// 4. Seed Leads (Prospective Vendors & Prequalification Submissions)
// -------------------------------------------------------------
echo "4. Seeding Leads (Prospective Vendors & Expression of Interest)...\n";
$leadsData = [
    [
        'firstName' => 'Solomon',
        'lastName' => 'Danjuma',
        'salutationName' => 'Engr.',
        'accountName' => 'Solomon & Brothers Electrical Engineering Ltd',
        'title' => 'Chief Executive Officer',
        'status' => 'New',
        'source' => 'Web Site',
        'emailAddress' => 'info@solomonbrothers.example.ng',
        'phoneNumber' => '+234-803-771-0022',
        'assignedUserId' => $adminId,
        'description' => 'Submitted online expression of interest (EOI) for upcoming solar mini-grid tender for zonal offices.'
    ],
    [
        'firstName' => 'Mustapha',
        'lastName' => 'Aliyu',
        'salutationName' => 'Alhaji',
        'accountName' => 'Northern Satellite & Fiber Telecoms',
        'title' => 'Managing Director',
        'status' => 'Assigned',
        'source' => 'Campaign',
        'emailAddress' => 'm.aliyu@northernsat.example.ng',
        'phoneNumber' => '+234-802-334-9911',
        'assignedUserId' => $adminId,
        'description' => 'Expressed interest in providing backup satellite connectivity (VSAT) to remote tax audit field units.'
    ],
    [
        'firstName' => 'Emeka',
        'lastName' => 'Nnaji',
        'salutationName' => 'Mr.',
        'accountName' => 'Apex Biometric Solutions Africa Ltd',
        'title' => 'Business Development Lead',
        'status' => 'In Process',
        'source' => 'Self Generated',
        'emailAddress' => 'e.nnaji@apexbiometric.example.ng',
        'phoneNumber' => '+234-805-992-3344',
        'assignedUserId' => $adminId,
        'description' => 'Submitted pre-qualification documents for contactless facial recognition time-and-attendance system.'
    ],
    [
        'firstName' => 'Kudirat',
        'lastName' => 'Olatunji',
        'salutationName' => 'Mrs.',
        'accountName' => 'Crown Guard Patrol & Surveillance Services',
        'title' => 'Head of Corporate Sales',
        'status' => 'Recycled',
        'source' => 'Conference',
        'emailAddress' => 'k.olatunji@crownguard.example.ng',
        'phoneNumber' => '+234-807-112-8833',
        'assignedUserId' => $adminId,
        'description' => 'Vendor pre-qualification deferred pending renewal of NSCDC private security company operational license.'
    ]
];

foreach ($leadsData as $data) {
    $existing = $em->getRepository('Lead')->where(['emailAddress' => $data['emailAddress']])->findOne();
    if (!$existing) {
        seedEntity($em, 'Lead', $data);
    }
}

// -------------------------------------------------------------
// 5. Seed Opportunities (Public Tenders, RFQs & Awarded Contracts)
// -------------------------------------------------------------
echo "5. Seeding Opportunities (Procurement Tenders & RFQs)...\n";
$oppsData = [
    [
        'name' => 'Procurement of Enterprise Core Network Switches & Next-Gen Firewalls',
        'stage' => 'Proposal/Price Quote',
        'amount' => 85000000.00,
        'amountCurrency' => 'NGN',
        'closeDate' => date('Y-m-d', strtotime('+25 days')),
        'probability' => 70,
        'accountIndex' => 0,
        'assignedUserId' => $adminId,
        'description' => 'Public tender for 12x 48-port 10GbE PoE core switches and dual redundant perimeter firewalls for headquarters.'
    ],
    [
        'name' => 'Annual Facility Power Backup & Generator Overhaul Contract',
        'stage' => 'Negotiation/Review',
        'amount' => 120000000.00,
        'amountCurrency' => 'NGN',
        'closeDate' => date('Y-m-d', strtotime('+12 days')),
        'probability' => 85,
        'accountIndex' => 1,
        'assignedUserId' => $adminId,
        'description' => 'Master maintenance agreement for 3x 1500kVA Cummins generator sets, automated transfer switches, and quarterly servicing.'
    ],
    [
        'name' => 'Supply & Installation of 500 Executive Ergonomic Workstations',
        'stage' => 'Closed Won',
        'amount' => 42500000.00,
        'amountCurrency' => 'NGN',
        'closeDate' => date('Y-m-d', strtotime('-4 days')),
        'probability' => 100,
        'accountIndex' => 3,
        'assignedUserId' => $adminId,
        'description' => 'Awarded tender for 500 high-grade ergonomic mesh task chairs, dual-monitor desks, and mobile pedestal filing cabinets.'
    ],
    [
        'name' => 'Upgrade of Integrated Biometric Access Control & IP Surveillance System',
        'stage' => 'Needs Analysis',
        'amount' => 68000000.00,
        'amountCurrency' => 'NGN',
        'closeDate' => date('Y-m-d', strtotime('+45 days')),
        'probability' => 40,
        'accountIndex' => 2,
        'assignedUserId' => $adminId,
        'description' => 'Replacement of magnetic stripe card readers with multi-factor biometric turnstiles and 4K AI-enabled security cameras.'
    ],
    [
        'name' => 'Provision of Cloud Backup & Offsite Disaster Recovery Infrastructure',
        'stage' => 'Qualification',
        'amount' => 95000000.00,
        'amountCurrency' => 'NGN',
        'closeDate' => date('Y-m-d', strtotime('+60 days')),
        'probability' => 30,
        'accountIndex' => 0,
        'assignedUserId' => $adminId,
        'description' => 'Tender for Tier-3 local datacenter colocation space, 1Gbps dedicated fiber cross-connect, and automated offsite snapshots.'
    ],
    [
        'name' => 'Interstate Secure Document Courier & Logistics Framework Agreement',
        'stage' => 'Closed Won',
        'amount' => 38000000.00,
        'amountCurrency' => 'NGN',
        'closeDate' => date('Y-m-d', strtotime('-15 days')),
        'probability' => 100,
        'accountIndex' => 4,
        'assignedUserId' => $adminId,
        'description' => 'Contract awarded for trackable courier transit of audit records and confidential revenue dockets across Nigeria.'
    ],
    [
        'name' => 'Emergency Procurement of First Aid Kits & Clinic Diagnostic Equipment',
        'stage' => 'Proposal/Price Quote',
        'amount' => 18500000.00,
        'amountCurrency' => 'NGN',
        'closeDate' => date('Y-m-d', strtotime('+18 days')),
        'probability' => 75,
        'accountIndex' => 5,
        'assignedUserId' => $adminId,
        'description' => 'Restocking of workplace clinic automated external defibrillators, oxygen cylinders, stretchers, and emergency PPE packs.'
    ]
];

$opportunities = [];
foreach ($oppsData as $data) {
    $acc = isset($data['accountIndex']) && isset($accounts[$data['accountIndex']]) ? $accounts[$data['accountIndex']] : null;
    unset($data['accountIndex']);
    if ($acc) {
        $data['accountId'] = $acc->getId();
    }
    
    $existing = $em->getRepository('Opportunity')->where(['name' => $data['name']])->findOne();
    if ($existing) {
        $opportunities[] = $existing;
    } else {
        $opp = seedEntity($em, 'Opportunity', $data);
        if ($opp) {
            $opportunities[] = $opp;
        }
    }
}

// -------------------------------------------------------------
// 6. Seed Tasks (Actionable Procurement Milestones & Audits)
// -------------------------------------------------------------
echo "6. Seeding Tasks (Compliance Audits & Evaluation Milestones)...\n";
$tasksData = [
    [
        'name' => 'Verify Tax Clearance Certificate (TCC) & PenCom compliance for Greenfield Facilities',
        'priority' => 'High',
        'status' => 'Completed',
        'dateEnd' => date('Y-m-d H:i:s', strtotime('-2 days')),
        'assignedUserId' => $adminId,
        'parentType' => 'Account',
        'accountIndex' => 1,
        'description' => 'Cross-check e-TCC barcode on NRS tax portal and verify PenCom compliance certificate expiration date.'
    ],
    [
        'name' => 'Convene Technical Evaluation Committee for Enterprise Core Network Switches RFP',
        'priority' => 'Urgent',
        'status' => 'Started',
        'dateEnd' => date('Y-m-d H:i:s', strtotime('+3 days')),
        'assignedUserId' => $adminId,
        'parentType' => 'Opportunity',
        'oppIndex' => 0,
        'description' => 'Distribute technical bid submissions to committee members. Score compliance against 70% benchmark.'
    ],
    [
        'name' => 'Issue Formal Letter of Award (LOA) for Executive Ergonomic Workstations',
        'priority' => 'Normal',
        'status' => 'Completed',
        'dateEnd' => date('Y-m-d H:i:s', strtotime('-1 day')),
        'assignedUserId' => $adminId,
        'parentType' => 'Opportunity',
        'oppIndex' => 2,
        'description' => 'Prepare award letter approved by Tenders Board; instruct vendor to submit 10% Bank Performance Guarantee.'
    ],
    [
        'name' => 'Conduct physical site inspection for Data Center Power Backup Installation',
        'priority' => 'High',
        'status' => 'Not Started',
        'dateEnd' => date('Y-m-d H:i:s', strtotime('+7 days')),
        'assignedUserId' => $adminId,
        'parentType' => 'Opportunity',
        'oppIndex' => 1,
        'description' => 'Joint engineering team inspection of transformer substation, fuel storage tanks, and earthing pit resistance.'
    ],
    [
        'name' => 'Review Advance Payment Guarantee (APG) submission from Trans-Sahara Logistics',
        'priority' => 'Urgent',
        'status' => 'Started',
        'dateEnd' => date('Y-m-d H:i:s', strtotime('+2 days')),
        'assignedUserId' => $adminId,
        'parentType' => 'Opportunity',
        'oppIndex' => 5,
        'description' => 'Forward bank guarantee to Legal Directorate to verify financial solvency and confirmation with issuing bank.'
    ],
    [
        'name' => 'Audit and certify warehouse delivery of Lot 1 office consumables',
        'priority' => 'Normal',
        'status' => 'Not Started',
        'dateEnd' => date('Y-m-d H:i:s', strtotime('+10 days')),
        'assignedUserId' => $adminId,
        'parentType' => 'Account',
        'accountIndex' => 3,
        'description' => 'Store inspection officer to verify delivery docket against contract specifications before signing invoice.'
    ]
];

foreach ($tasksData as $data) {
    if (isset($data['accountIndex']) && isset($accounts[$data['accountIndex']])) {
        $data['parentId'] = $accounts[$data['accountIndex']]->getId();
    } elseif (isset($data['oppIndex']) && isset($opportunities[$data['oppIndex']])) {
        $data['parentId'] = $opportunities[$data['oppIndex']]->getId();
    }
    unset($data['accountIndex'], $data['oppIndex']);
    
    $existing = $em->getRepository('Task')->where(['name' => $data['name']])->findOne();
    if (!$existing) {
        seedEntity($em, 'Task', $data);
    }
}

// -------------------------------------------------------------
// 7. Seed Meetings (Tender Openings & Committee Sessions)
// -------------------------------------------------------------
echo "7. Seeding Meetings (Public Bid Openings & Committee Conferences)...\n";
$meetingsData = [
    [
        'name' => 'Public Bid Opening Ceremony: ICT Infrastructure Tender (Lots 1-4)',
        'status' => 'Held',
        'dateStart' => date('Y-m-d H:i:s', strtotime('-1 day 10:00:00')),
        'dateEnd' => date('Y-m-d H:i:s', strtotime('-1 day 12:30:00')),
        'assignedUserId' => $adminId,
        'parentType' => 'Opportunity',
        'oppIndex' => 0,
        'description' => 'Held in Main Conference Hall. Bids opened in the presence of Civil Society Observers, BPP Desk, and bidder representatives.'
    ],
    [
        'name' => 'Pre-Bid Clarification Conference with Prequalified Security Contractors',
        'status' => 'Held',
        'dateStart' => date('Y-m-d H:i:s', strtotime('-4 days 14:00:00')),
        'dateEnd' => date('Y-m-d H:i:s', strtotime('-4 days 15:30:00')),
        'assignedUserId' => $adminId,
        'parentType' => 'Opportunity',
        'oppIndex' => 3,
        'description' => 'Addressed questions regarding CCTV integration with existing perimeter optical sensors and API access for control room.'
    ],
    [
        'name' => 'NRS Tenders Board Final Adjudication & Award Session',
        'status' => 'Planned',
        'dateStart' => date('Y-m-d H:i:s', strtotime('+4 days 10:00:00')),
        'dateEnd' => date('Y-m-d H:i:s', strtotime('+4 days 13:00:00')),
        'assignedUserId' => $adminId,
        'parentType' => 'Opportunity',
        'oppIndex' => 1,
        'description' => 'Executive review of Technical Evaluation Committee recommendations for the Annual Power Backup & Facilities contract.'
    ],
    [
        'name' => 'Quarterly Vendor Performance & SLA Review Meeting (Q4)',
        'status' => 'Planned',
        'dateStart' => date('Y-m-d H:i:s', strtotime('+14 days 11:00:00')),
        'dateEnd' => date('Y-m-d H:i:s', strtotime('+14 days 12:30:00')),
        'assignedUserId' => $adminId,
        'parentType' => 'Account',
        'accountIndex' => 1,
        'description' => 'Formal quarterly supplier audit assessing fuel delivery turnaround time, uptime records, and preventive maintenance logs.'
    ]
];

foreach ($meetingsData as $data) {
    if (isset($data['oppIndex']) && isset($opportunities[$data['oppIndex']])) {
        $data['parentId'] = $opportunities[$data['oppIndex']]->getId();
    } elseif (isset($data['accountIndex']) && isset($accounts[$data['accountIndex']])) {
        $data['parentId'] = $accounts[$data['accountIndex']]->getId();
    }
    unset($data['oppIndex'], $data['accountIndex']);
    
    $existing = $em->getRepository('Meeting')->where(['name' => $data['name']])->findOne();
    if (!$existing) {
        seedEntity($em, 'Meeting', $data);
    }
}

// -------------------------------------------------------------
// 8. Seed Calls (Supplier Inquiries & Follow-up Communications)
// -------------------------------------------------------------
echo "8. Seeding Calls (Supplier Clarifications & Check-ins)...\n";
$callsData = [
    [
        'name' => 'Clarification on Bill of Quantities (BOQ) with Engr. Babatunde',
        'status' => 'Held',
        'dateStart' => date('Y-m-d H:i:s', strtotime('-1 day 14:15:00')),
        'dateEnd' => date('Y-m-d H:i:s', strtotime('-1 day 14:35:00')),
        'assignedUserId' => $adminId,
        'parentType' => 'Account',
        'accountIndex' => 0,
        'contactIndex' => 0,
        'description' => 'Clarified power supply redundancy specifications for Cisco Catalyst switches. Vendor confirmed dual hot-swappable AC supplies.'
    ],
    [
        'name' => 'Factory Inspection Schedule Alignment with Hajia Amina Bello',
        'status' => 'Held',
        'dateStart' => date('Y-m-d H:i:s', strtotime('-3 days 11:00:00')),
        'dateEnd' => date('Y-m-d H:i:s', strtotime('-3 days 11:20:00')),
        'assignedUserId' => $adminId,
        'parentType' => 'Account',
        'accountIndex' => 1,
        'contactIndex' => 1,
        'description' => 'Agreed on dates for engineering inspection team visit to Lagos central generator refurbishment facility.'
    ],
    [
        'name' => 'Biometric Turnstile Warranty & SLA Confirmation with Dr. Chinedu',
        'status' => 'Planned',
        'dateStart' => date('Y-m-d H:i:s', strtotime('+1 day 09:30:00')),
        'dateEnd' => date('Y-m-d H:i:s', strtotime('+1 day 10:00:00')),
        'assignedUserId' => $adminId,
        'parentType' => 'Account',
        'accountIndex' => 2,
        'contactIndex' => 2,
        'description' => 'Reviewing 36-month on-site warranty coverage and 4-hour critical incident response time commitment.'
    ]
];

foreach ($callsData as $data) {
    if (isset($data['accountIndex']) && isset($accounts[$data['accountIndex']])) {
        $data['parentId'] = $accounts[$data['accountIndex']]->getId();
    }
    if (isset($data['contactIndex']) && isset($contacts[$data['contactIndex']])) {
        $data['contactId'] = $contacts[$data['contactIndex']]->getId();
    }
    unset($data['accountIndex'], $data['contactIndex']);
    
    $existing = $em->getRepository('Call')->where(['name' => $data['name']])->findOne();
    if (!$existing) {
        seedEntity($em, 'Call', $data);
    }
}

// -------------------------------------------------------------
// 9. Seed Cases (Vendor Inquiries & Support Tickets)
// -------------------------------------------------------------
echo "9. Seeding Cases (Vendor Inquiries & Support Requests)...\n";
$casesData = [
    [
        'name' => 'Inquiry regarding e-Procurement portal document upload limits during RFP submission',
        'status' => 'Closed',
        'priority' => 'High',
        'type' => 'Question',
        'accountIndex' => 0,
        'contactIndex' => 0,
        'assignedUserId' => $adminId,
        'description' => 'Vendor reported 25MB file upload limit reached while uploading audited accounts. Clarified that multi-volume ZIP files up to 50MB are supported.'
    ],
    [
        'name' => 'Discrepancy in Bill of Quantities specification for Lot 3 Fiber Optic SFP modules',
        'status' => 'Assigned',
        'priority' => 'Urgent',
        'type' => 'Problem',
        'accountIndex' => 0,
        'contactIndex' => 0,
        'assignedUserId' => $adminId,
        'description' => 'Lot 3 schedule listed single-mode transceivers while rack patch panels are multi-mode OM4. Referred to IT engineering team for official addendum.'
    ],
    [
        'name' => 'Request for 5-day extension of technical bid submission deadline for Facility Tender',
        'status' => 'Pending',
        'priority' => 'Normal',
        'type' => 'Question',
        'accountIndex' => 1,
        'contactIndex' => 1,
        'assignedUserId' => $adminId,
        'description' => 'Vendor requested extension citing global shipping lead time delays for manufacturer authorization certificates. Awaiting Tenders Board decision.'
    ],
    [
        'name' => 'Warranty replacement claim for damaged server rack mounting brackets',
        'status' => 'Closed',
        'priority' => 'Normal',
        'type' => 'Incident',
        'accountIndex' => 3,
        'contactIndex' => 3,
        'assignedUserId' => $adminId,
        'description' => 'Three 4U rail kits delivered with transit bend damage. Vendor provided brand new replacements within 48 hours under warranty.'
    ]
];

foreach ($casesData as $data) {
    if (isset($data['accountIndex']) && isset($accounts[$data['accountIndex']])) {
        $data['accountId'] = $accounts[$data['accountIndex']]->getId();
    }
    if (isset($data['contactIndex']) && isset($contacts[$data['contactIndex']])) {
        $data['contactId'] = $contacts[$data['contactIndex']]->getId();
    }
    unset($data['accountIndex'], $data['contactIndex']);
    
    $existing = $em->getRepository('Case')->where(['name' => $data['name']])->findOne();
    if (!$existing) {
        seedEntity($em, 'Case', $data);
    }
}

// -------------------------------------------------------------
// 10. Seed Knowledge Base Categories & Articles
// -------------------------------------------------------------
echo "10. Seeding Knowledge Base (Procurement Rules & Vendor Guides)...\n";
$kbCategoriesData = [
    [
        'name' => 'Vendor Registration & Prequalification',
        'description' => 'Mandatory requirements and registration process for companies seeking to do business with the Nigeria Revenue Service.'
    ],
    [
        'name' => 'Tender Guidelines & Bidding Rules',
        'description' => 'Rules governing Public Bidding, Technical Evaluations, Financial Thresholds, and Bid Security.'
    ],
    [
        'name' => 'Contract Execution & Payment Procedures',
        'description' => 'Guidelines on Advance Payment Guarantees, Milestone Certifications, and Tax Withholding.'
    ]
];

$kbCategories = [];
foreach ($kbCategoriesData as $data) {
    $existing = $em->getRepository('KnowledgeBaseCategory')->where(['name' => $data['name']])->findOne();
    if ($existing) {
        $kbCategories[] = $existing;
    } else {
        $cat = seedEntity($em, 'KnowledgeBaseCategory', $data);
        if ($cat) {
            $kbCategories[] = $cat;
        }
    }
}

$kbArticlesData = [
    [
        'name' => 'Step-by-Step Guide to Registering on the NRS ProcureSmart Supplier Portal',
        'status' => 'Published',
        'categoryIndex' => 0,
        'assignedUserId' => $adminId,
        'body' => '<p>All prospective contractors, suppliers, and consultants wishing to participate in NRS procurement must be registered on the National Database of Federal Contractors administered by the Bureau of Public Procurement (BPP).</p><h3>Required Statutory Documents:</h3><ol><li><strong>Certificate of Incorporation:</strong> Issued by the Corporate Affairs Commission (CAC) including Form CAC 1.1.</li><li><strong>Tax Clearance Certificate (TCC):</strong> Valid 3-year TCC with verifiable Tax Identification Number (TIN).</li><li><strong>PENCOM Compliance Certificate:</strong> Current clearance issued by the National Pension Commission.</li><li><strong>Industrial Training Fund (ITF) Certificate:</strong> Compliance with the ITF Act.</li><li><strong>BPP National Database Registration:</strong> Interim Registration Report (IRR) with active contractor status.</li></ol>'
    ],
    [
        'name' => 'Technical Tender Scoring Criteria: Benchmarks and Pass Marks',
        'status' => 'Published',
        'categoryIndex' => 1,
        'assignedUserId' => $adminId,
        'body' => '<p>Procurement bids submitted to the NRS are evaluated in two strict, transparent stages in compliance with the Public Procurement Act 2007:</p><h3>1. Technical Evaluation (Pass Mark: 70%)</h3><ul><li><strong>Specific Experience (30 points):</strong> Evidence of at least 3 similar contracts successfully executed in the last 5 years.</li><li><strong>Key Personnel Qualifications (25 points):</strong> CVs and professional registration (COREN, CPN, ICAN, etc.).</li><li><strong>Methodology & Work Plan (25 points):</strong> Proposed technical approach, SLA response times, and quality assurance.</li><li><strong>Financial Capability (20 points):</strong> Audited accounts for 3 years and bank credit support letter.</li></ul><h3>2. Financial Bid Opening</h3><p>Only bids achieving 70% and above in the technical evaluation shall have their financial proposals opened publicly.</p>'
    ],
    [
        'name' => 'Procurement Ethics, Anti-Corruption Code, and Whistleblowing Policy',
        'status' => 'Published',
        'categoryIndex' => 2,
        'assignedUserId' => $adminId,
        'body' => '<p>The Nigeria Revenue Service upholds zero tolerance for corruption, bid-rigging, conflict of interest, and inducements across all procurement proceedings.</p><p>Any vendor attempting to influence procurement officials or submit forged certificates shall face immediate disqualification, blacklisting from federal contracting, and referral to the Economic and Financial Crimes Commission (EFCC) or ICPC.</p><p>Confidential whistleblowing desk: <code>procurement-integrity@nrs.gov.ng</code>.</p>'
    ]
];

foreach ($kbArticlesData as $data) {
    $cat = isset($data['categoryIndex']) && isset($kbCategories[$data['categoryIndex']]) ? $kbCategories[$data['categoryIndex']] : null;
    unset($data['categoryIndex']);
    if ($cat) {
        $data['categoriesIds'] = [$cat->getId()];
    }
    
    $existing = $em->getRepository('KnowledgeBaseArticle')->where(['name' => $data['name']])->findOne();
    if (!$existing) {
        seedEntity($em, 'KnowledgeBaseArticle', $data);
    }
}

// -------------------------------------------------------------
// 11. Seed Campaigns & Target Lists (Supplier Outreach)
// -------------------------------------------------------------
echo "11. Seeding Campaigns & Target Lists (National Supplier Drives)...\n";
$targetListsData = [
    [
        'name' => 'Federal Approved ICT & Networking Vendors',
        'type' => 'Email',
        'description' => 'Target list of certified hardware OEMs and network integrators eligible for ICT tenders.'
    ],
    [
        'name' => 'Category A Building, Power & Facility Contractors',
        'type' => 'Email',
        'description' => 'Prequalified facilities engineering contractors for zonal head office maintenance.'
    ]
];

$targetLists = [];
foreach ($targetListsData as $data) {
    $existing = $em->getRepository('TargetList')->where(['name' => $data['name']])->findOne();
    if ($existing) {
        $targetLists[] = $existing;
    } else {
        $tl = seedEntity($em, 'TargetList', $data);
        if ($tl) {
            $targetLists[] = $tl;
        }
    }
}

$campaignsData = [
    [
        'name' => 'FY2026 NRS Annual National Vendor Pre-Qualification Drive',
        'status' => 'Active',
        'type' => 'Email',
        'startDate' => date('Y-m-d', strtotime('-10 days')),
        'endDate' => date('Y-m-d', strtotime('+50 days')),
        'assignedUserId' => $adminId,
        'description' => 'Public notice inviting interested reputable contractors and service providers to renew or register pre-qualification credentials.'
    ]
];

foreach ($campaignsData as $data) {
    $existing = $em->getRepository('Campaign')->where(['name' => $data['name']])->findOne();
    if (!$existing) {
        seedEntity($em, 'Campaign', $data);
    }
}

// -------------------------------------------------------------
// 12. Seed Activity Stream Notes (Committee Deliberations & Updates)
// -------------------------------------------------------------
echo "12. Seeding Stream Notes (Internal Audit Trails & Committee Logs)...\n";
$notesData = [
    [
        'parentType' => 'Account',
        'accountIndex' => 0,
        'post' => "Technical Evaluation Committee completed preliminary screening. All OEM authorization letters from Cisco and Fortinet verified authentic with regional headquarters."
    ],
    [
        'parentType' => 'Opportunity',
        'oppIndex' => 0,
        'post' => "Public financial bid opening completed in presence of observers. Abuja Data Systems proposal came in 4.2% below our engineer's internal benchmark. Tender recommended for award."
    ],
    [
        'parentType' => 'Opportunity',
        'oppIndex' => 1,
        'post' => "Legal Directorate reviewed Cummins OEM partner agreement. 24/7 emergency response SLA clause accepted by vendor without revision."
    ],
    [
        'parentType' => 'Account',
        'accountIndex' => 1,
        'post' => "Due diligence team completed physical inspection of Lagos central maintenance facility. Spare parts warehouse capacity confirmed adequate."
    ],
    [
        'parentType' => 'Opportunity',
        'oppIndex' => 2,
        'post' => "Formal Letter of Award (LOA) executed by Executive Chairman. Bank Performance Guarantee received from Zenith Bank Plc and filed with Finance."
    ]
];

foreach ($notesData as $data) {
    if ($data['parentType'] === 'Account' && isset($accounts[$data['accountIndex']])) {
        $data['parentId'] = $accounts[$data['accountIndex']]->getId();
    } elseif ($data['parentType'] === 'Opportunity' && isset($opportunities[$data['oppIndex']])) {
        $data['parentId'] = $opportunities[$data['oppIndex']]->getId();
    }
    unset($data['accountIndex'], $data['oppIndex']);
    
    if (!empty($data['parentId'])) {
        $data['type'] = 'Post';
        seedEntity($em, 'Note', $data);
    }
}

echo "=========================================================\n";
echo "NRS-Procuresmart Demo Data Seeded Successfully!\n";
echo "Accounts: " . count($accounts) . "\n";
echo "Contacts: " . count($contacts) . "\n";
echo "Opportunities: " . count($opportunities) . "\n";
echo "=========================================================\n";
