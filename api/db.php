<?php
require_once __DIR__ . '/config.php';

function getDbConnection() {
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_TIMEOUT => 5,
            ]
        );

        // Auto-initialize tables and seed data if missing
        initDatabaseTables($pdo);

        return $pdo;
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "message" => "Database connection error: " . $e->getMessage()
        ]);
        exit();
    }
}

function initDatabaseTables($pdo) {
    try {
        // 1. Companies Table
        $pdo->exec("CREATE TABLE IF NOT EXISTS companies (
            id INT AUTO_INCREMENT PRIMARY KEY,
            company_name VARCHAR(255) NOT NULL,
            slug VARCHAR(255) NOT NULL UNIQUE,
            logo TEXT,
            short_description TEXT,
            full_description TEXT,
            website_url VARCHAR(500),
            discount VARCHAR(100),
            promo_code VARCHAR(100),
            deal_url VARCHAR(500),
            featured TINYINT DEFAULT 0,
            status VARCHAR(50) DEFAULT 'active',
            max_funding VARCHAR(100),
            profit_split VARCHAR(100),
            start_price VARCHAR(100),
            rating DECIMAL(3,1) DEFAULT 4.8,
            platform VARCHAR(255),
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

        // 2. Giveaway Entries Table
        $pdo->exec("CREATE TABLE IF NOT EXISTS giveaway_entries (
            id INT AUTO_INCREMENT PRIMARY KEY,
            first_name VARCHAR(100) NOT NULL,
            last_name VARCHAR(100) NOT NULL,
            youtube_username VARCHAR(100) NOT NULL,
            email VARCHAR(255) NOT NULL UNIQUE,
            consent TINYINT DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

        // 3. Admins Table
        $pdo->exec("CREATE TABLE IF NOT EXISTS admins (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(100) NOT NULL UNIQUE,
            email VARCHAR(255) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

        // Seed default admin if missing
        $stmt = $pdo->query("SELECT COUNT(*) FROM admins");
        if ($stmt && $stmt->fetchColumn() == 0) {
            $hashPass = password_hash('admin123', PASSWORD_BCRYPT);
            $insertAdmin = $pdo->prepare("INSERT INTO admins (username, email, password) VALUES (?, ?, ?)");
            $insertAdmin->execute(['admin', 'admin@sga.com', $hashPass]);
        }

        // Seed initial companies if missing
        $compStmt = $pdo->query("SELECT COUNT(*) FROM companies");
        if ($compStmt && $compStmt->fetchColumn() == 0) {
            $seedCompanies = [
                ['Lucid Trading', 'lucid-trading', 'https://images.unsplash.com/photo-1611974789855-9c2a0a7236a3?w=200', 'DGT Trusted - Most Popular. 15-minute payouts, 90% profit split, instant funding, no consistency rule.', 'Lucid Trading is a premier futures prop trading firm providing instant funding, zero consistency rules, 90% profit splits, and ultra-fast 15-minute daily payouts.', 'https://lucidtrading.com', '40% OFF', 'DGT', 'https://lucidtrading.com', 1, 'active', '$750,000', '90/10', '$193', 4.8, 'Tradovate, NinjaTrader'],
                ['Take Profit Trader', 'take-profit-trader', 'https://images.unsplash.com/photo-1642543492481-44e81e3914a7?w=200', 'DGT Trusted - Best Daily Payouts. 1-hour payouts, 80% profit split, and trader-friendly evaluation rules.', 'Take Profit Trader gives futures traders direct access to funded accounts with daily payouts, 1-hour withdrawal speeds, and high profit share options.', 'https://takeprofittrader.com', '50% OFF', 'DGT', 'https://takeprofittrader.com', 1, 'active', '$750,000', '80/20', '$180', 4.7, 'Tradovate, NinjaTrader, Rithmic'],
                ['Tradeify', 'tradeify', 'https://images.unsplash.com/photo-1590283603385-17ffb3a7f29f?w=200', 'DGT Trusted - Best Instant Funding. 1-hour payouts, 90% profit split, instant funding, no consistency rule.', 'Tradeify specializes in instant funding evaluation models for futures traders, featuring 90% profit split, fast 1-hour payouts, and raw execution.', 'https://tradeify.co', '40% OFF', 'DGT', 'https://tradeify.co', 1, 'active', '$750,000', '90/10', '$99', 4.7, 'Tradovate, NinjaTrader'],
                ['Apex Trader Funding', 'apex-trader-funding', 'https://images.unsplash.com/photo-1559526324-4b87b5e36e44?w=200', 'The #1 Futures prop firm offering massive evaluation sales, 100% of first $25k profits, and daily payouts.', 'Apex Trader Funding leads the futures trading industry with generous evaluation discounts, rapid evaluation pass rates, and smooth payout distributions through Tradovate and NinjaTrader.', 'https://apextraderfunding.com', '80% OFF', 'APEX80', 'https://apextraderfunding.com', 1, 'active', '$300,000', '90/10', '$147', 4.7, 'Rithmic, Tradovate, NinjaTrader']
            ];
            $insertComp = $pdo->prepare("INSERT INTO companies (company_name, slug, logo, short_description, full_description, website_url, discount, promo_code, deal_url, featured, status, max_funding, profit_split, start_price, rating, platform) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            foreach ($seedCompanies as $c) {
                $insertComp->execute($c);
            }
        }
    } catch (Exception $ex) {
        // Log schema initialization warning gracefully
        error_log("Schema Init Warning: " . $ex->getMessage());
    }
}
?>
