<?php
/**
 * Database Setup Script
 * Run this script once to create the necessary database and tables
 */

// Database configuration - MODIFY THESE VALUES
$host = 'localhost';
$dbname = 'product_inventory';
$username = 'root';     // Change to your database username
$password = '';         // Change to your database password

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Connect to MySQL server (without database)
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database if it doesn't exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "Database '$dbname' created or already exists.<br>";
    
    // Connect to the database
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create product_types table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `product_types` (
            `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `name` VARCHAR(255) NOT NULL,
            `description` TEXT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "Table 'product_types' created or already exists.<br>";
    
    // Create products table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `products` (
            `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `name` VARCHAR(255) NOT NULL,
            `type_id` INT(11) UNSIGNED NULL,
            `date` DATE NOT NULL,
            `quantity` INT(11) NOT NULL DEFAULT 0,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            FOREIGN KEY (`type_id`) REFERENCES `product_types` (`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "Table 'products' created or already exists.<br>";
    
    // Insert sample data (if tables are empty)
    $typeCount = $pdo->query("SELECT COUNT(*) FROM product_types")->fetchColumn();
    if ($typeCount == 0) {
        $pdo->exec("
            INSERT INTO `product_types` (`name`, `description`) VALUES
            ('Electronics', 'Electronic devices and components'),
            ('Clothing', 'Apparel and accessories'),
            ('Food', 'Edible products'),
            ('Office Supplies', 'Items used in office environments');
        ");
        echo "Sample product types inserted.<br>";
    }
    
    $productCount = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
    if ($productCount == 0) {
        $currentDate = date('Y-m-d');
        $pdo->exec("
            INSERT INTO `products` (`name`, `type_id`, `date`, `quantity`) VALUES
            ('Laptop', 1, '$currentDate', 25),
            ('T-shirt', 2, '$currentDate', 100),
            ('Chocolate Bar', 3, '$currentDate', 150),
            ('Pen Set', 4, '$currentDate', 75);
        ");
        echo "Sample products inserted.<br>";
    }
    
    echo "<br>Database setup completed successfully!";
    echo "<br><br><a href='index.html'>Go to Product Management System</a>";
    
} catch (PDOException $e) {
    die("ERROR: " . $e->getMessage());
}
?>