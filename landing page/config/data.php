<?php
// Data Instruktur
$trainers = [
    [
        'id' => 1, 
        'name' => 'Sarah Johnson', 
        'specialty' => 'Yoga & Pilates', 
        'image' => 'https://images.unsplash.com/photo-1571019614242-c5c5dee9f50b?w=100&h=100&fit=crop',
        'certifications' => ['RYT-500', 'Pilates Certified'],
        'experience' => '8 tahun',
        'description' => 'Spesialisasi dalam Vinyasa dan Hatha Yoga'
    ],
    [
        'id' => 2, 
        'name' => 'Mike Anderson', 
        'specialty' => 'Body Combat & Strength', 
        'image' => 'https://images.unsplash.com/photo-1567598508481-65985588e295?w=100&h=100&fit=crop',
        'certifications' => ['NSCA-CPT', 'CrossFit Level 2'],
        'experience' => '10 tahun',
        'description' => 'Mantan atlet angkat besi nasional'
    ],
    [
        'id' => 3, 
        'name' => 'Lisa Martinez', 
        'specialty' => 'Zumba & Dance Fitness', 
        'image' => 'https://images.unsplash.com/photo-1594381898411-846e7d193883?w=100&h=100&fit=crop',
        'certifications' => ['Zumba B1 & B2', 'AFAA Certified'],
        'experience' => '6 tahun',
        'description' => 'Energik dan memotivasi!'
    ],
    [
        'id' => 4, 
        'name' => 'David Chen', 
        'specialty' => 'CrossFit & HIIT', 
        'image' => 'https://images.unsplash.com/photo-1605296867304-46d5465a13f1?w=100&h=100&fit=crop',
        'certifications' => ['CrossFit L-2', 'ACE Personal Trainer'],
        'experience' => '7 tahun',
        'description' => 'Fokus pada teknik yang tepat dan aman'
    ]
];

// Data Jadwal
$schedules = [
    ['id' => 1, 'day' => 'Senin', 'time' => '06:00', 'type' => 'Pagi'],
    ['id' => 2, 'day' => 'Senin', 'time' => '18:00', 'type' => 'Sore'],
    ['id' => 3, 'day' => 'Selasa', 'time' => '06:00', 'type' => 'Pagi'],
    ['id' => 4, 'day' => 'Selasa', 'time' => '18:00', 'type' => 'Sore'],
    ['id' => 5, 'day' => 'Rabu', 'time' => '06:00', 'type' => 'Pagi'],
    ['id' => 6, 'day' => 'Rabu', 'time' => '18:00', 'type' => 'Sore'],
    ['id' => 7, 'day' => 'Kamis', 'time' => '06:00', 'type' => 'Pagi'],
    ['id' => 8, 'day' => 'Kamis', 'time' => '18:00', 'type' => 'Sore'],
    ['id' => 9, 'day' => 'Jumat', 'time' => '06:00', 'type' => 'Pagi'],
    ['id' => 10, 'day' => 'Jumat', 'time' => '18:00', 'type' => 'Sore'],
    ['id' => 11, 'day' => 'Sabtu', 'time' => '08:00', 'type' => 'Pagi'],
    ['id' => 12, 'day' => 'Sabtu', 'time' => '16:00', 'type' => 'Sore']
];

// Data Kelas
$classes = [
    [
        'id' => 1,
        'name' => 'Yoga',
        'image' => 'https://images.unsplash.com/photo-1588286840104-8957b019727f?w=500&h=300&fit=crop',
        'description' => 'Meningkatkan fleksibilitas dan ketenangan pikiran',
        'schedule' => 'Semua Level',
        'duration' => '60 menit',
        'intensity' => 'Rendah',
        'trainer_ids' => [1], // Sarah Johnson
        'category' => 'Mind & Body'
    ],
    [
        'id' => 2,
        'name' => 'Zumba',
        'image' => 'https://images.unsplash.com/photo-1518611012118-696072aa579a?w=500&h=300&fit=crop',
        'description' => 'Olahraga kardio yang menyenangkan dengan musik',
        'schedule' => 'Pemula - Mahir',
        'duration' => '45 menit',
        'intensity' => 'Sedang',
        'trainer_ids' => [3], // Lisa Martinez
        'category' => 'Dance'
    ],
    [
        'id' => 3,
        'name' => 'Body Combat',
        'image' => 'https://images.unsplash.com/photo-1549719386-74dfcbf7dbed?w=500&h=300&fit=crop',
        'description' => 'Latihan kardio dengan gerakan bela diri',
        'schedule' => 'Menengah - Lanjut',
        'duration' => '55 menit',
        'intensity' => 'Tinggi',
        'trainer_ids' => [2], // Mike Anderson
        'category' => 'Martial Arts'
    ],
    [
        'id' => 4,
        'name' => 'Strength Training',
        'image' => 'https://images.unsplash.com/photo-1517836357463-d25dfeac3438?w=500&h=300&fit=crop',
        'description' => 'Membangun dan menguatkan otot tubuh',
        'schedule' => 'Semua Level',
        'duration' => '50 menit',
        'intensity' => 'Sedang-Tinggi',
        'trainer_ids' => [2, 4], // Mike Anderson & David Chen
        'category' => 'Strength'
    ],
    [
        'id' => 5,
        'name' => 'Pilates',
        'image' => 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=500&h=300&fit=crop',
        'description' => 'Meningkatkan kekuatan inti dan postur tubuh',
        'schedule' => 'Pemula - Menengah',
        'duration' => '60 menit',
        'intensity' => 'Rendah-Sedang',
        'trainer_ids' => [1], // Sarah Johnson
        'category' => 'Mind & Body'
    ],
    [
        'id' => 6,
        'name' => 'CrossFit',
        'image' => 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?w=500&h=300&fit=crop',
        'description' => 'Latihan intensitas tinggi untuk hasil maksimal',
        'schedule' => 'Lanjut',
        'duration' => '45 menit',
        'intensity' => 'Tinggi',
        'trainer_ids' => [4], // David Chen
        'category' => 'HIIT'
    ]
];

// Data Membership (untuk registration)
$membership_plans = [
    [
        'type' => 'bulanan-umum',
        'name' => 'Bulanan Umum',
        'price' => 285000,
        'period' => 'per bulan',
        'features' => [
            'Akses gym unlimited',
            'Semua peralatan fitness',
            'Locker gratis',
            'Konsultasi gratis'
        ]
    ],
    [
        'type' => 'bulanan-pelajar',
        'name' => 'Bulanan Pelajar',
        'price' => 200000,
        'period' => 'per bulan',
        'features' => [
            'Akses gym unlimited',
            'Semua peralatan fitness',
            'Locker gratis',
            'Konsultasi gratis'
        ]
    ],
    [
        'type' => '3bulan-umum',
        'name' => '3 Bulan Umum',
        'price' => 675000,
        'period' => '3 bulan',
        'features' => [
            'Akses gym unlimited',
            'Semua peralatan fitness',
            'Locker pribadi',
            'Konsultasi gratis',
            '1 sesi personal trainer'
        ]
    ],
    [
        'type' => '3bulan-pelajar',
        'name' => '3 Bulan Pelajar',
        'price' => 550000,
        'period' => '3 bulan',
        'features' => [
            'Akses gym unlimited',
            'Semua peralatan fitness',
            'Locker pribadi',
            'Konsultasi gratis',
            '1 sesi personal trainer'
        ]
    ]
];

// Data Kelas Tambahan (untuk registration)
$additional_classes = [
    [
        'type' => 'zumba',
        'name' => 'Zumba / Aero BL / Strong Nation',
        'price' => 20000,
        'description' => 'Kelas dance cardio yang menyenangkan'
    ],
    [
        'type' => 'body-shape',
        'name' => 'CID / Body Shape / Senam BL',
        'price' => 25000,
        'description' => 'Latihan pembentukan tubuh'
    ],
    [
        'type' => 'boxing',
        'name' => 'Boxing / Kapha Yoga',
        'price' => 30000,
        'description' => 'Latihan boxing dan yoga kombinasi'
    ],
    [
        'type' => 'boxing-bulanan',
        'name' => 'Boxing (Paket 1 Bulan)',
        'price' => 300000,
        'description' => 'Paket boxing unlimited 1 bulan'
    ],
    [
        'type' => 'trainer',
        'name' => 'Program Trainer (10x Pertemuan + Gym 1 Bulan + Boxing 4x)',
        'price' => 1500000,
        'description' => 'Program lengkap dengan personal trainer'
    ]
];

// Data Kontak
$contact_info = [
    'address' => 'Jl. Kaliwates 5, Jember, Jawa Timur',
    'phone' => '+62 812-3456-7890',
    'email' => 'info@arenafit.com',
    'operating_hours' => 'Senin - Minggu<br>05:00 - 22:00',
    'instagram' => 'arenafitclubjember'
];

// CEK APAKAH FUNGSI SUDAH ADA SEBELUM DIDEKLARASIKAN
if (!function_exists('format_currency')) {
    function format_currency($amount) {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }
}

if (!function_exists('get_class_by_id')) {
    function get_class_by_id($class_id) {
        global $classes;
        foreach ($classes as $class) {
            if ($class['id'] == $class_id) {
                return $class;
            }
        }
        return null;
    }
}

if (!function_exists('get_trainer_by_id')) {
    function get_trainer_by_id($trainer_id) {
        global $trainers;
        foreach ($trainers as $trainer) {
            if ($trainer['id'] == $trainer_id) {
                return $trainer;
            }
        }
        return null;
    }
}

if (!function_exists('get_schedule_by_id')) {
    function get_schedule_by_id($schedule_id) {
        global $schedules;
        foreach ($schedules as $schedule) {
            if ($schedule['id'] == $schedule_id) {
                return $schedule;
            }
        }
        return null;
    }
}
?>