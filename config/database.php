<?php
// Database configuration for ReWear platform
class Database {
    private $host = 'localhost';
    private $db_name = 'rewear_db';
    private $username = 'root';
    private $password = '';
    private $conn;

    // Get database connection
    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }

        return $this->conn;
    }
}

// Create database and tables if they don't exist
function initializeDatabase() {
    $host = 'localhost';
    $username = 'root';
    $password = '';
    
    try {
        // Connect to MySQL without specifying database
        $pdo = new PDO("mysql:host=$host", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Create database if it doesn't exist
        $pdo->exec("CREATE DATABASE IF NOT EXISTS rewear_db");
        $pdo->exec("USE rewear_db");
        
        // Create users table
        $pdo->exec("CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            points INT DEFAULT 100,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )");
        
        // Create items table
        $pdo->exec("CREATE TABLE IF NOT EXISTS items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            title VARCHAR(100) NOT NULL,
            description TEXT,
            category VARCHAR(50),
            size VARCHAR(20),
            condition_rating ENUM('excellent', 'good', 'fair', 'poor') DEFAULT 'good',
            points_required INT DEFAULT 100,
            status ENUM('available', 'pending', 'swapped', 'removed') DEFAULT 'pending',
            image_path VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )");
        
        // Create swaps table
        $pdo->exec("CREATE TABLE IF NOT EXISTS swaps (
            id INT AUTO_INCREMENT PRIMARY KEY,
            requester_id INT NOT NULL,
            item_id INT NOT NULL,
            status ENUM('pending', 'accepted', 'rejected', 'completed') DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (requester_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (item_id) REFERENCES items(id) ON DELETE CASCADE
        )");
        
        // Create admin table
        $pdo->exec("CREATE TABLE IF NOT EXISTS admins (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            role ENUM('admin', 'moderator') DEFAULT 'moderator',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )");
        
        echo "Database initialized successfully!";
        
    } catch(PDOException $e) {
        echo "Database initialization error: " . $e->getMessage();
    }
}

// Initialize database on first run - only if session is started
if (session_status() === PHP_SESSION_ACTIVE && !isset($_SESSION['db_initialized'])) {
    initializeDatabase();
    $_SESSION['db_initialized'] = true;
}
?> 