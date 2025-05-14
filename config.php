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

// Philippine geographical data (with added barangays)
$regions = [
    'National Capital Region (NCR)' => [
        'provinces' => ['Metro Manila'],
        'districts' => ['1st', '2nd', '3rd', '4th'],
        'cities' => [
            'Manila' => [
                'barangays' => ['Binondo', 'Ermita', 'Intramuros', 'Malate', 'Paco', 'Pandacan', 'Port Area', 'Quiapo', 'San Andres', 'San Miguel']
            ],
            'Quezon City' => [
                'barangays' => ['Bagong Pag-asa', 'Batasan Hills', 'Central', 'Diliman', 'Fairview', 'Holy Spirit', 'Libis', 'Loyola Heights', 'New Manila', 'Novaliches']
            ],
            'Caloocan' => [
                'barangays' => ['Bagong Barrio', 'Balingasa', 'Bamboo', 'Camarin', 'Deparo', 'Grace Park', 'Longos', 'San Jose', 'San Roque', 'Sangandaan']
            ],
            'Las Piñas' => [
                'barangays' => ['Almanza', 'Bacoor', 'CAA', 'Daniel Fajardo', 'Golden Acres', 'Pamplona', 'Pilar', 'Talon', 'San Isidro', 'San Antonio']
            ],
            'Makati' => [
                'barangays' => ['Bel-Air', 'Carmona', 'Dasmariñas', 'Forbes Park', 'Guadalupe', 'La Paz', 'Legaspi Village', 'Pio del Pilar', 'San Lorenzo', 'San Isidro']
            ],
            'Malabon' => [
                'barangays' => ['Acacia', 'Baritan', 'Catmon', 'Longos', 'Magsaysay', 'San Agustin', 'San Jose', 'Tañong', 'Concepcion', 'Navotas']
            ],
            'Mandaluyong' => [
                'barangays' => ['Barangka', 'Buayang Bato', 'Hulo', 'Mabini', 'Malamig', 'New Zaniga', 'Poblacion', 'San Jose', 'Wack-Wack', 'Plainview']
            ],
            'Marikina' => [
                'barangays' => ['Barangka', 'Concepcion', 'Fortune', 'Industrial Valley', 'Malanday', 'San Isidro', 'Santo Niño', 'Tañong', 'Tumana', 'Sierra']
            ],
            'Muntinlupa' => [
                'barangays' => ['Alabang', 'Cupang', 'Poblacion', 'Tunasan', 'Bayanan', 'Putatan', 'Sucat', 'Alabang Hills', 'Ayala Alabang', 'New Alabang']
            ],
            'Navotas' => [
                'barangays' => ['Bagumbayan', 'Bayanihan', 'Daang Hari', 'Dulong Bayan', 'Navotas East', 'North Bay Boulevard', 'San Roque', 'Tangos', 'North Bay', 'San Rafael']
            ],
            'Parañaque' => [
                'barangays' => ['Baclaran', 'San Antonio', 'San Isidro', 'San Martin de Porres', 'Tambo', 'Moonwalk', 'Merville', 'Multinational Village', 'La Huerta', 'Don Bosco']
            ],
            'Pasay' => [
                'barangays' => ['Barangay 76', 'Barangay 77', 'Barangay 78', 'Barangay 79', 'Barangay 80', 'Barangay 81', 'Barangay 82', 'Barangay 83', 'Barangay 84', 'Barangay 85']
            ],
            'Pasig' => [
                'barangays' => ['Bagong Ilog', 'Kapitolyo', 'San Joaquin', 'San Juan', 'Dela Paz', 'Santo Niño', 'Pineda', 'Manggahan', 'Buting', 'Maybunga']
            ],
            'San Juan' => [
                'barangays' => ['Barangay I', 'Barangay II', 'Barangay III', 'Barangay IV', 'Barangay V', 'Barangay VI', 'Barangay VII', 'Barangay VIII', 'Barangay IX', 'Barangay X']
            ],
            'Taguig' => [
                'barangays' => ['Bagumbayan', 'Central Bicutan', 'Fort Bonifacio', 'Hagonoy', 'Ibayo-Tipas', 'Ligid-Tipas', 'North Signal Village', 'South Signal Village', 'Upper Bicutan', 'Western Bicutan']
            ],
            'Valenzuela' => [
                'barangays' => ['Balangkas', 'Bignay', 'Langaray', 'Malanday', 'Maysan', 'Poblacion', 'Punturin', 'Tagalag', 'Dalandanan', 'Tandang Sora']
            ]
        ]
    ],
    'Cordillera Administrative Region (CAR)' => [
        'provinces' => ['Abra', 'Apayao', 'Benguet', 'Ifugao', 'Kalinga', 'Mountain Province'],
        'districts' => ['CAR'],
        'cities' => [
            'Baguio' => [
                'barangays' => ['Loakan', 'Camp 7', 'Burnham', 'Magsaysay', 'San Luis', 'Upper QM', 'Lower QM', 'Kisad', 'Legarda', 'Poblacion']
            ],
            'Tabuk' => [
                'barangays' => ['Bulanao', 'Calanan', 'Dupag', 'Laya', 'Poblacion', 'San Juan', 'San Pablo', 'San Rafael', 'Tawit', 'Sangbay']
            ]
        ]
    ],
    'Ilocos Region (Region I)' => [
        'provinces' => ['Ilocos Norte', 'Ilocos Sur', 'La Union', 'Pangasinan'],
        'districts' => ['1st District', '2nd District', '3rd District', '4th District'],
        'cities' => [
            'Laoag' => [
                'barangays' => ['Barit', 'Burgos', 'Cabaruan', 'San Isidro', 'San Juan', 'San Nicolas', 'Suyo', 'Vira', 'Paoay', 'Batac']
            ],
            'Vigan' => [
                'barangays' => ['Alumnos', 'Dumaguete', 'Pangil', 'San Vicente', 'Sangil', 'Santa Catalina', 'Santa Maria', 'Tundag', 'San Pedro', 'San Pablo']
            ],
            'San Fernando' => [
                'barangays' => ['Bacnotan', 'Baño', 'Guiset', 'Mati', 'Balbalan', 'Tinga', 'Anac', 'La Paz', 'Dinalupihan', 'Bagumbayan']
            ],
            'Dagupan' => [
                'barangays' => ['Tayug', 'Mangaldan', 'San Fabian', 'Sison', 'Malasiqui', 'Mapandan', 'Binalonan', 'Urdaneta', 'San Manuel', 'Lingayen']
            ],
            'Alaminos' => [
                'barangays' => ['Lingayen', 'San Roque', 'Camasong', 'Canaman', 'Buenavista', 'Dulong Bayan', 'Pangapisan', 'Pantal', 'Lambayong', 'Maganao']
            ],
            'San Carlos' => [
                'barangays' => ['Paitan', 'San Vicente', 'Banaoang', 'Polo', 'Balisong', 'San Rafael', 'Manggangawa', 'Dumarao', 'Mahabang Parang', 'Saguday']
            ],
            'Urdaneta' => [
                'barangays' => ['Binalonan', 'Masangkay', 'San Gabriel', 'Pasong Buli', 'Mabini', 'Banawang', 'Mankilam', 'Nancayasan', 'Magsaysay', 'Longos']
            ]
        ]
    ],
    'Cagayan Valley (Region II)' => [
        'provinces' => ['Batanes', 'Cagayan', 'Isabela', 'Nueva Vizcaya', 'Quirino'],
        'districts' => ['1st District', '2nd District', '3rd District', '4th District'],
        'cities' => [
            'Tuguegarao' => [
                'barangays' => ['Bagumbayan', 'Balagan', 'Cagayan', 'Tuguegarao West', 'Bayabas', 'Tungay', 'San Pablo', 'Santa Ana', 'Panubig', 'Luna']
            ],
            'Ilagan' => [
                'barangays' => ['Bagumbayan', 'San Pedro', 'Dagu', 'Cagayan Norte', 'San Juan', 'Tungala', 'Amang', 'Dapdap', 'Balangobong', 'Caponan']
            ],
            'Cauayan' => [
                'barangays' => ['Bagumbayan', 'Balagan', 'Tungay', 'Canaan', 'San Francisco', 'Lansang', 'San Vicente', 'San Isidro', 'Pintor', 'Sampaguita']
            ]
        ]
    ],
    'Central Luzon (Region III)' => [
        'provinces' => ['Aurora', 'Bataan', 'Bulacan', 'Nueva Ecija', 'Pampanga', 'Tarlac', 'Zambales'],
        'districts' => ['1st District', '2nd District', '3rd District', '4th District'],
        'cities' => [
            'Balanga' => [
                'barangays' => ['Bagumbayan', 'Cataning', 'Dangcol', 'Ibayo', 'Poblacion', 'Puerto Rivas Ibaba', 'Puerto Rivas Itaas', 'San Jose', 'Tuyo', 'Tenejero']
            ],
            'Malolos' => [
                'barangays' => ['Anilao', 'Bagna', 'Bulihan', 'Calero', 'Canalate', 'Catmon', 'Dakila', 'Guinhawa', 'Longos', 'Mabolo']
            ],
            'Cabanatuan' => [
                'barangays' => ['Bagong Sikat', 'Bakero', 'Balite', 'Bitas', 'Camp Tinio', 'Dicarma', 'Kapitan Pepe', 'Licaong', 'Mabini', 'San Josef']
            ],
            'San Fernando' => [
                'barangays' => ['Baliti', 'Calulut', 'Del Pilar', 'Dolores', 'Juliana', 'Lourdes', 'Maimpis', 'San Agustin', 'San Isidro', 'San Jose']
            ],
            'Tarlac City' => [
                'barangays' => ['Amucao', 'Balanti', 'Balete', 'Balingcanaway', 'Banaba', 'Buenavista', 'Carangian', 'Cutcut', 'Dela Paz', 'Ligtasan']
            ],
            'Olongapo' => [
                'barangays' => ['Asinan', 'Barretto', 'East Bajac-Bajac', 'Gordon Heights', 'Kalaklan', 'Mabayuan', 'New Kababae', 'Pag-asa', 'Santa Rita', 'West Bajac-Bajac']
            ]
        ]
    ]
];
