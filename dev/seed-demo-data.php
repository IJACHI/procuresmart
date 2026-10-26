<?php
/************************************************************************
 * Seed Demo Data for ProcureSmart / EspoCRM
 ************************************************************************/

include __DIR__ . '/../bootstrap.php';

use Espo\Core\Application;
use Espo\ORM\EntityManager;

$app = new Application();
$container = $app->getContainer();
$em = $container->getByClass(EntityManager::class);

echo "Seeding demo procurement data...\n";

// Helper function to create or update an entity
function createEntity($em, $entityType, $data) {
    $entity = $em->getEntity($entityType);
    foreach ($data as $field => $val) {
        $entity->set($field, $val);
    }
    $em->saveEntity($entity);
    return $entity;
}

try {
    // 1. Seed Accounts (Suppliers & Vendors)
    $suppliers = [
        [
            'name' => 'Apex Industrial Supplies Ltd',
            'type' => 'Vendor',
            'industry' => 'Manufacturing',
            'website' => 'https://apexsupplies.example.com',
            'emailAddress' => 'sales@apexsupplies.example.com',
            'phoneNumber' => '+1-555-019-2831',
            'billingAddressStreet' => '100 Industrial Parkway',
            'billingAddressCity' => 'Chicago',
            'billingAddressState' => 'IL',
            'billingAddressPostalCode' => '60601',
            'billingAddressCountry' => 'USA',
            'description' => 'Preferred tier-1 supplier for heavy industrial machinery, safety equipment, and raw manufacturing components.'
        ],
        [
            'name' => 'Nexus Cloud & IT Infrastructure',
            'type' => 'Vendor',
            'industry' => 'Technology',
            'website' => 'https://nexuscloud.example.com',
            'emailAddress' => 'contracts@nexuscloud.example.com',
            'phoneNumber' => '+1-555-014-9921',
            'billingAddressStreet' => '450 Silicon Way',
            'billingAddressCity' => 'San Jose',
            'billingAddressState' => 'CA',
            'billingAddressPostalCode' => '95110',
            'billingAddressCountry' => 'USA',
            'description' => 'Enterprise vendor supplying cloud hosting, servers, IT workstations, and licensed SaaS services.'
        ],
        [
            'name' => 'Global Logistics & Freight Solutions',
            'type' => 'Partner',
            'industry' => 'Transportation',
            'website' => 'https://globallogistics.example.com',
            'emailAddress' => 'operations@globallogistics.example.com',
            'phoneNumber' => '+1-555-017-8833',
            'billingAddressStreet' => '820 Harbour Boulevard',
            'billingAddressCity' => 'Newark',
            'billingAddressState' => 'NJ',
            'billingAddressPostalCode' => '07101',
            'billingAddressCountry' => 'USA',
            'description' => 'Logistics and freight partner managing port imports, customs brokerage, and cross-border transport.'
        ],
        [
            'name' => 'BioHealth Pharmaceuticals Procurement',
            'type' => 'Customer',
            'industry' => 'Healthcare',
            'website' => 'https://biohealth.example.com',
            'emailAddress' => 'procurement@biohealth.example.com',
            'phoneNumber' => '+1-555-012-4455',
            'billingAddressStreet' => '300 Research Boulevard',
            'billingAddressCity' => 'Boston',
            'billingAddressState' => 'MA',
            'billingAddressPostalCode' => '02115',
            'billingAddressCountry' => 'USA',
            'description' => 'Healthcare institution purchasing laboratory supplies, specialized pharmaceuticals, and diagnostic PPE.'
        ]
    ];

    $createdAccounts = [];
    foreach ($suppliers as $data) {
        $createdAccounts[] = createEntity($em, 'Account', $data);
    }

    // 2. Seed Contacts
    $contacts = [
        [
            'firstName' => 'Sarah',
            'lastName' => 'Jenkins',
            'title' => 'Chief Procurement Officer',
            'emailAddress' => 's.jenkins@apexsupplies.example.com',
            'phoneNumber' => '+1-555-019-2832',
            'accountId' => $createdAccounts[0]->getId(),
            'description' => 'Primary decision-maker for commercial terms and annual supplier contract renewals.'
        ],
        [
            'firstName' => 'Marcus',
            'lastName' => 'Vance',
            'title' => 'Director of Vendor Relations',
            'emailAddress' => 'm.vance@nexuscloud.example.com',
            'phoneNumber' => '+1-555-014-9925',
            'accountId' => $createdAccounts[1]->getId(),
            'description' => 'Key contact for IT hardware bulk bidding and enterprise license negotiations.'
        ],
        [
            'firstName' => 'Elena',
            'lastName' => 'Rostova',
            'title' => 'Supply Chain Operations Lead',
            'emailAddress' => 'e.rostova@globallogistics.example.com',
            'phoneNumber' => '+1-555-017-8837',
            'accountId' => $createdAccounts[2]->getId(),
            'description' => 'Coordinates fulfillment timelines, shipping manifests, and customs compliance.'
        ]
    ];

    foreach ($contacts as $data) {
        createEntity($em, 'Contact', $data);
    }

    // 3. Seed Opportunities / RFQs
    $opportunities = [
        [
            'name' => 'Q4 Industrial Safety Gear Bulk Purchase',
            'stage' => 'Proposal/Price Quote',
            'amount' => 145000.00,
            'amountCurrency' => 'USD',
            'closeDate' => date('Y-m-d', strtotime('+30 days')),
            'probability' => 75,
            'accountId' => $createdAccounts[0]->getId(),
            'description' => 'Bulk RFQ covering protective gear, respirators, and workwear for 3 manufacturing facilities.'
        ],
        [
            'name' => 'Data Center Server Hardware Upgrade',
            'stage' => 'Negotiation/Review',
            'amount' => 280000.00,
            'amountCurrency' => 'USD',
            'closeDate' => date('Y-m-d', strtotime('+15 days')),
            'probability' => 85,
            'accountId' => $createdAccounts[1]->getId(),
            'description' => 'Hardware procurement tender for 40 rack units, NVMe storage arrays, and network switches.'
        ],
        [
            'name' => 'Annual Overland Logistics Master Agreement',
            'stage' => 'Closed Won',
            'amount' => 520000.00,
            'amountCurrency' => 'USD',
            'closeDate' => date('Y-m-d', strtotime('-5 days')),
            'probability' => 100,
            'accountId' => $createdAccounts[2]->getId(),
            'description' => 'Master service contract awarded for nation-wide freight distribution and warehousing.'
        ]
    ];

    foreach ($opportunities as $data) {
        createEntity($em, 'Opportunity', $data);
    }

    echo "Demo procurement data seeded successfully!\n";
} catch (\Throwable $e) {
    echo "Warning during demo seeding: " . $e->getMessage() . "\n";
}
