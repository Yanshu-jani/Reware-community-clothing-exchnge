<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
$item_id = $input['item_id'] ?? null;

if (!$item_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Item ID is required']);
    exit();
}

$database = new Database();
$db = $database->getConnection();

try {
    // Check if item exists and belongs to the user
    $check_query = "SELECT id, user_id, image_path FROM items WHERE id = ? AND user_id = ?";
    $check_stmt = $db->prepare($check_query);
    $check_stmt->execute([$item_id, $_SESSION['user_id']]);
    $item = $check_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$item) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Item not found or you do not have permission to remove it']);
        exit();
    }
    
    // Start transaction
    $db->beginTransaction();
    
    // Delete related swaps first (due to foreign key constraint)
    $delete_swaps_query = "DELETE FROM swaps WHERE item_id = ?";
    $delete_swaps_stmt = $db->prepare($delete_swaps_query);
    $delete_swaps_stmt->execute([$item_id]);
    
    // Delete the item
    $delete_item_query = "DELETE FROM items WHERE id = ? AND user_id = ?";
    $delete_item_stmt = $db->prepare($delete_item_query);
    $delete_item_stmt->execute([$item_id, $_SESSION['user_id']]);
    
    // Delete the image file if it exists
    if ($item['image_path'] && file_exists($item['image_path'])) {
        unlink($item['image_path']);
    }
    
    // Commit transaction
    $db->commit();
    
    echo json_encode(['success' => true, 'message' => 'Item removed successfully']);
    
} catch (Exception $e) {
    // Rollback transaction on error
    $db->rollback();
    
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error removing item: ' . $e->getMessage()]);
}
?> 