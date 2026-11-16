<?php
/**
 * RIHANNA SIGNATURE SAFARI - PORTAL MANAGEMENT SUITE
 * Database & Core System Configuration
 * Plain PHP & MySQL Architecture with Self-Healing Auto-Migration
 */

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    // 30 days session cookie lifetime
    ini_set('session.cookie_lifetime', '2592000');
    ini_set('session.gc_maxlifetime', '2592000');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.use_only_cookies', '1');
    session_start();
}

// Timezone
date_default_timezone_set('Africa/Dar_es_Salaam');

// Database Configuration
define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_PORT', (int)(getenv('DB_PORT') ?: 3306));
define('DB_NAME', getenv('DB_NAME') ?: 'rihanna_signaturesafari');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');

// Application Constants
define('ATELIER_NAME', 'Rihanna Signature Safari');
define('PORTAL_TITLE', 'Rihanna Signature Safari Portal');
define('PORTAL_URL', '/portal');
define('SITE_URL', '/');
define('DEFAULT_ADMIN_USER', 'admin');
define('DEFAULT_ADMIN_PASS', 'RihannaSafari2026!');
define('DEFAULT_ADMIN_NAME', 'Redempotius Nalisis');
define('DEFAULT_ADMIN_ROLE', 'Founder & Master Safari Architect');

/**
 * PDO Database Connection with Self-Healing Initializer
 */
function getDbConnection(): PDO {
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    try {
        // Connect to server (first without db to ensure db exists)
        $dsnWithoutDb = sprintf('mysql:host=%s;port=%d;charset=utf8mb4', DB_HOST, DB_PORT);
        $initPdo = new PDO($dsnWithoutDb, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_TIMEOUT => 3,
        ]);
        $initPdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $initPdo = null;

        // Connect with database selected
        $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', DB_HOST, DB_PORT, DB_NAME);
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);

        ensureDatabaseSchema($pdo);
        return $pdo;
    } catch (Throwable $e) {
        die('<div style="font-family:sans-serif;max-width:600px;margin:80px auto;padding:30px;background:#fff5f5;border-left:4px solid #c8963e;border-radius:4px;box-shadow:0 4px 15px rgba(0,0,0,0.08);">' .
            '<h2 style="color:#233522;margin-top:0;">Rihanna Signature Safari</h2>' .
            '<p style="color:#666;">Unable to connect to the MySQL database engine.</p>' .
            '<pre style="background:#f0eae1;padding:12px;border-radius:3px;font-size:12px;overflow-x:auto;">' . htmlspecialchars($e->getMessage()) . '</pre>' .
            '<p style="font-size:13px;color:#888;">Please verify your MySQL service is running and credentials in config.php are valid.</p>' .
            '</div>');
    }
}

/**
 * Self-Healing Database Schema Migration and Default Seeds
 */
function ensureDatabaseSchema(PDO $pdo): void {
    // 1. Admins Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `admins` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `username` VARCHAR(100) NOT NULL UNIQUE,
        `password_hash` VARCHAR(255) NOT NULL,
        `name` VARCHAR(150) NOT NULL,
        `email` VARCHAR(150) NOT NULL,
        `role` VARCHAR(100) DEFAULT 'Master Safari Architect',
        `avatar` VARCHAR(255) DEFAULT 'images/founder-redempotius-1.jpg',
        `last_login` DATETIME NULL,
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

    // 2. Inquiries Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `inquiries` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `reference_no` VARCHAR(50) NOT NULL UNIQUE,
        `full_name` VARCHAR(150) NOT NULL,
        `email` VARCHAR(150) NOT NULL,
        `phone` VARCHAR(60) NOT NULL,
        `country` VARCHAR(100) DEFAULT 'International',
        `contact_method` VARCHAR(50) DEFAULT 'WhatsApp',
        `package_name` VARCHAR(255) NOT NULL,
        `package_price` VARCHAR(100) DEFAULT 'Custom Quote',
        `travel_dates` VARCHAR(150) DEFAULT 'Flexible',
        `duration` VARCHAR(100) DEFAULT '7-10 Days',
        `adults` VARCHAR(50) DEFAULT '2 Adults',
        `children` VARCHAR(50) DEFAULT '0 Children',
        `party_type` VARCHAR(100) DEFAULT 'Private Couple',
        `special_notes` TEXT NULL,
        `architect_notes` TEXT NULL,
        `status` VARCHAR(50) DEFAULT 'new',
        `ip_address` VARCHAR(60) NULL,
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX (`status`),
        INDEX (`created_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

    // 3. Posts (Journal Articles) Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `posts` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `title` VARCHAR(255) NOT NULL,
        `slug` VARCHAR(255) NOT NULL UNIQUE,
        `category` VARCHAR(100) NOT NULL DEFAULT 'Wildlife & Migration',
        `featured_image` VARCHAR(255) NOT NULL,
        `excerpt` TEXT NOT NULL,
        `content` LONGTEXT NOT NULL,
        `status` VARCHAR(50) NOT NULL DEFAULT 'published',
        `author` VARCHAR(150) NOT NULL DEFAULT 'Redempotius Nalisis',
        `author_title` VARCHAR(150) DEFAULT 'Founder & Master Safari Architect',
        `author_avatar` VARCHAR(255) DEFAULT 'images/logo.png',
        `read_time` VARCHAR(50) DEFAULT '6 min read',
        `focus_keyword` VARCHAR(255) NULL,
        `tags` VARCHAR(255) NULL,
        `views` INT DEFAULT 0,
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX (`slug`),
        INDEX (`status`),
        INDEX (`category`),
        INDEX (`created_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

    // Seed default admin if table is empty
    $adminCount = (int)$pdo->query("SELECT COUNT(*) FROM `admins`")->fetchColumn();
    if ($adminCount === 0) {
        $stmt = $pdo->prepare("INSERT INTO `admins` (`username`, `password_hash`, `name`, `email`, `role`) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            DEFAULT_ADMIN_USER,
            password_hash(DEFAULT_ADMIN_PASS, PASSWORD_BCRYPT),
            DEFAULT_ADMIN_NAME,
            'info@rihannasignaturesafari.com',
            DEFAULT_ADMIN_ROLE
        ]);
    }

    // Seed sample inquiries if table is empty
    $inqCount = (int)$pdo->query("SELECT COUNT(*) FROM `inquiries`")->fetchColumn();
    if ($inqCount === 0) {
        $sampleInquiries = [
            [
                'reference_no'    => 'RIH-984210',
                'full_name'       => 'Lord Alistair & Lady Cynthia Sterling',
                'email'           => 'alistair.sterling@kensington-partners.co.uk',
                'phone'           => '+44 7700 900821',
                'country'         => 'United Kingdom',
                'contact_method'  => 'Email & WhatsApp',
                'package_name'    => '10-Day Serengeti Great Migration & Ngorongoro Crater Luxe',
                'package_price'   => '$11,800 / person',
                'travel_dates'    => 'August 14 – 24, 2026',
                'duration'        => '10 Days / 9 Nights',
                'adults'          => '2 Adults',
                'children'        => '0 Children',
                'party_type'      => 'Couple / 25th Wedding Anniversary',
                'special_notes'   => 'Celebrating 25th wedding anniversary. Interested in private helicopter fly-in from Kilimanjaro (JRO) to Singita airstrip, luxury mobile tented suites, and hot air balloon safari over the Mara River crossing.',
                'architect_notes' => 'Drafted VIP dossier. Contacted Coastal Aviation for direct Pilatus PC-12 charter. Guest prefers French champagne and plant-based fine dining for Lady Cynthia.',
                'status'          => 'new',
                'created_at'      => date('Y-m-d H:i:s', strtotime('-1 hour'))
            ],
            [
                'reference_no'    => 'RIH-872155',
                'full_name'       => 'Dr. Henrik & Sophie Lindqvist',
                'email'           => 'henrik.lindqvist@karolinska.se',
                'phone'           => '+46 70 812 9043',
                'country'         => 'Sweden',
                'contact_method'  => 'WhatsApp',
                'package_name'    => '12-Day Bush to Beach: Serengeti Predators & Mnemba Private Island',
                'package_price'   => '$14,250 / person',
                'travel_dates'    => 'October 02 – 14, 2026',
                'duration'        => '12 Days / 11 Nights',
                'adults'          => '2 Adults',
                'children'        => '2 Children (Ages 10 & 14)',
                'party_type'      => 'Family Luxury Safari',
                'special_notes'   => 'Family trip with our two teenage children. We require private guide throughout, interconnecting luxury family tented chalets, and private dive instructor on Zanzibar for whale shark and dolphin swimming.',
                'architect_notes' => 'Quotation #Q-8721 sent via PDF. Offered complimentary private sundowner on kopjes.',
                'status'          => 'quoted',
                'created_at'      => date('Y-m-d H:i:s', strtotime('-6 hours'))
            ],
            [
                'reference_no'    => 'RIH-741908',
                'full_name'       => 'Vivienne Chen & Matthew Vance',
                'email'           => 'vivienne.chen@singapore-capital.sg',
                'phone'           => '+65 9123 4567',
                'country'         => 'Singapore',
                'contact_method'  => 'WhatsApp',
                'package_name'    => '8-Day Tarangire Giants, Manyara & Ngorongoro Caldera Odyssey',
                'package_price'   => '$8,950 / person',
                'travel_dates'    => 'November 18 – 26, 2026',
                'duration'        => '8 Days / 7 Nights',
                'adults'          => '2 Adults',
                'children'        => '0 Children',
                'party_type'      => 'Honeymoon Private Expedition',
                'special_notes'   => 'Honeymoon safari. Seeking intimate secluded properties, night game drives in Tarangire, and crater floor picnic away from tourist clusters.',
                'architect_notes' => 'Deposit received and confirmed! Bookings secured at Sanctuary Ngorongoro Crater Camp & Chem Chem Tarangire.',
                'status'          => 'confirmed',
                'created_at'      => date('Y-m-d H:i:s', strtotime('-1 day'))
            ],
            [
                'reference_no'    => 'RIH-620489',
                'full_name'       => 'Guillaume & Amélie de Montmirail',
                'email'           => 'guillaume.montmirail@lvmh-luxury.fr',
                'phone'           => '+33 6 12 34 56 78',
                'country'         => 'France',
                'contact_method'  => 'Email',
                'package_name'    => '14-Day East Africa Grand Circuit: Tanzania & Kenya Double Migration',
                'package_price'   => '$18,500 / person',
                'travel_dates'    => 'September 05 – 19, 2026',
                'duration'        => '14 Days / 13 Nights',
                'adults'          => '4 Adults',
                'children'        => '0 Children',
                'party_type'      => 'Private Friends Party',
                'special_notes'   => 'High-end wildlife photography group. Need open-sided modified Land Cruisers with camera gimbal clamps and beanbag mounts. Private twin-engine charter between Serengeti and Maasai Mara.',
                'architect_notes' => 'Custom cross-border flight clearance underway with Auric Air and SafariLink.',
                'status'          => 'in_review',
                'created_at'      => date('Y-m-d H:i:s', strtotime('-2 days'))
            ]
        ];

        $stmtInq = $pdo->prepare("INSERT INTO `inquiries` (
            `reference_no`, `full_name`, `email`, `phone`, `country`, `contact_method`,
            `package_name`, `package_price`, `travel_dates`, `duration`, `adults`, `children`,
            `party_type`, `special_notes`, `architect_notes`, `status`, `created_at`
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        foreach ($sampleInquiries as $i) {
            $stmtInq->execute([
                $i['reference_no'], $i['full_name'], $i['email'], $i['phone'], $i['country'], $i['contact_method'],
                $i['package_name'], $i['package_price'], $i['travel_dates'], $i['duration'], $i['adults'], $i['children'],
                $i['party_type'], $i['special_notes'], $i['architect_notes'], $i['status'], $i['created_at']
            ]);
        }
    }

    // Seed initial posts if table is empty
    $postCount = (int)$pdo->query("SELECT COUNT(*) FROM `posts`")->fetchColumn();
    if ($postCount === 0) {
        require_once __DIR__ . '/seed_articles.php';
        $initialPosts = getDefaultJournalSeedPosts();

        $stmtPost = $pdo->prepare("INSERT INTO `posts` (
            `title`, `slug`, `category`, `featured_image`, `excerpt`, `content`,
            `status`, `author`, `author_title`, `author_avatar`, `read_time`, `focus_keyword`, `tags`, `views`, `created_at`
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        foreach ($initialPosts as $p) {
            $stmtPost->execute([
                $p['title'], $p['slug'], $p['category'], $p['featured_image'], $p['excerpt'],
                $p['content'], $p['status'], $p['author'], $p['author_title'], $p['author_avatar'],
                $p['read_time'], $p['focus_keyword'], $p['tags'], $p['views'], $p['created_at']
            ]);
        }
    }
}

/**
 * Authentication Helper Functions
 */
function isLoggedIn(): bool {
    return !empty($_SESSION['admin_id']) && !empty($_SESSION['admin_username']);
}

function requireLogin(): void {
    if (!isLoggedIn()) {
        $returnUrl = urlencode($_SERVER['REQUEST_URI'] ?? '/portal/index.php');
        header('Location: ' . PORTAL_URL . '/login.php?redirect=' . $returnUrl);
        exit;
    }
}

function currentAdmin(): ?array {
    if (!isLoggedIn()) {
        return null;
    }
    return [
        'id'       => (int)$_SESSION['admin_id'],
        'username' => (string)$_SESSION['admin_username'],
        'name'     => (string)($_SESSION['admin_name'] ?? DEFAULT_ADMIN_NAME),
        'email'    => (string)($_SESSION['admin_email'] ?? 'info@rihannasignaturesafari.com'),
        'role'     => (string)($_SESSION['admin_role'] ?? DEFAULT_ADMIN_ROLE),
        'avatar'   => (string)($_SESSION['admin_avatar'] ?? 'images/logo.png')
    ];
}

/**
 * CSRF Protection
 */
function csrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrfToken(?string $token): bool {
    if (empty($token) || empty($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Flash Messages
 */
function setFlash(string $type, string $message): void {
    if (!isset($_SESSION['flash'])) {
        $_SESSION['flash'] = [];
    }
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function getFlashes(): array {
    $flashes = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $flashes;
}
