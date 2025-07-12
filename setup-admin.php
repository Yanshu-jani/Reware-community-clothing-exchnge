<?php
session_start();
require_once 'config/database.php';

// Only run this once to set up the first admin
if (isset($_GET['setup']) && $_GET['setup'] === 'admin') {
    $database = new Database();
    $db = $database->getConnection();
    
    // Check if admin already exists
    $check_query = "SELECT COUNT(*) as admin_count FROM admins";
    $check_stmt = $db->prepare($check_query);
    $check_stmt->execute();
    $admin_count = $check_stmt->fetch(PDO::FETCH_ASSOC)['admin_count'];
    
    if ($admin_count == 0) {
        // Get the first user and make them admin
        $user_query = "SELECT id FROM users ORDER BY id ASC LIMIT 1";
        $user_stmt = $db->prepare($user_query);
        $user_stmt->execute();
        $first_user = $user_stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($first_user) {
            $insert_query = "INSERT INTO admins (user_id, role) VALUES (?, 'admin')";
            $insert_stmt = $db->prepare($insert_query);
            
            if ($insert_stmt->execute([$first_user['id']])) {
                echo "Admin setup successful! User ID " . $first_user['id'] . " is now an admin.";
                echo "<br><a href='admin/'>Go to Admin Panel</a>";
            } else {
                echo "Error setting up admin.";
            }
        } else {
            echo "No users found. Please register a user first.";
        }
    } else {
        echo "Admin already exists.";
    }
} else {
    echo "To set up admin, visit: <a href='?setup=admin'>Setup Admin</a>";
}
?> 