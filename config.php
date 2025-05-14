<?php
$host = 'localhost';
$dbname = 'conference_registration';
$username = 'root';
$password = '';

// error catching
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Could not connect to the database: " . $e->getMessage());
}

// Philippine geographical data (simplified)
$regions = [
    'National Capital Region (NCR)' => [
        'provinces' => ['Metro Manila'],
        'districts' => ['1st', '2nd', '3rd', '4th'],
        'cities' => [
            'Manila' => [
                'barangays' => [
                    'Binondo', 'Ermita', 'Intramuros', 'Malate', 'Paco',
                    'Pandacan', 'Port Area', 'Quiapo', 'San Andres',
                    'San Miguel', 'Sampaloc', 'Santa Ana', 'Santa Cruz',
                    'Santo Domingo', 'Tondo'
                ]
            ],
            'Quezon City' => [],
            'Caloocan' => [],
            'Las Piñas' => [],
            'Makati' => [],
            'Malabon' => [],
            'Mandaluyong' => [],
            'Marikina' => [],
            'Muntinlupa' => [],
            'Navotas' => [],
            'Parañaque' => [],
            'Pasay' => [],
            'Pasig' => [],
            'San Juan' => [],
            'Taguig' => [],
            'Valenzuela' => []
        ],
        'barangays' => ['Various'] // Simplified for demo
    ],
    'Cordillera Administrative Region (CAR)' => [
        'provinces' => ['Abra', 'Apayao', 'Benguet', 'Ifugao', 'Kalinga', 'Mountain Province'],
        'districts' => ['CAR'],
        'cities' => ['Baguio', 'Tabuk'],
        'barangays' => ['Various']
    ],
    'Ilocos Region (Region I)' => [
        'provinces' => ['Ilocos Norte', 'Ilocos Sur', 'La Union', 'Pangasinan'],
        'districts' => ['1st District', '2nd District', '3rd District', '4th District'],
        'cities' => ['Laoag', 'Vigan', 'San Fernando', 'Dagupan', 'Alaminos', 'San Carlos', 'Urdaneta'],
        'barangays' => ['Various']
    ],
    'Cagayan Valley (Region II)' => [
        'provinces' => ['Batanes', 'Cagayan', 'Isabela', 'Nueva Vizcaya', 'Quirino'],
        'districts' => ['1st District', '2nd District', '3rd District', '4th District'],
        'cities' => ['Tuguegarao', 'Ilagan', 'Santiago', 'Cauayan'],
        'barangays' => ['Various']
    ],
    'Central Luzon (Region III)' => [
        'provinces' => ['Aurora', 'Bataan', 'Bulacan', 'Nueva Ecija', 'Pampanga', 'Tarlac', 'Zambales'],
        'districts' => ['1st District', '2nd District', '3rd District', '4th District'],
        'cities' => ['Angeles', 'San Fernando', 'Malolos', 'Meycauayan', 'San Jose del Monte', 'Cabanatuan', 'Gapan', 'Mabalacat', 'San Jose', 'Tarlac', 'Olongapo'],
        'barangays' => ['Various']
    ]
];
?>
