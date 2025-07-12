<?php
session_start();
require_once 'config/database.php';

// Get item ID from URL
$item_id = $_GET['id'] ?? null;

if (!$item_id) {
    header('Location: browse.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Get item details with owner info
$query = "SELECT i.*, u.username as owner_name, u.email as owner_email 
          FROM items i 
          JOIN users u ON i.user_id = u.id 
          WHERE i.id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$item_id]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$item) {
    header('Location: browse.php');
    exit();
}

// Check if user is logged in
$is_logged_in = isset($_SESSION['user_id']);
$is_owner = $is_logged_in && $_SESSION['user_id'] == $item['user_id'];
$can_swap = $is_logged_in && !$is_owner && $item['status'] === 'available';
$can_redeem = $is_logged_in && !$is_owner && $item['status'] === 'available' && $_SESSION['points'] >= $item['points_required'];

$error = '';
$success = '';

// Handle swap request
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if (!$is_logged_in) {
        header('Location: login.php');
        exit();
    }
    
    if ($_POST['action'] === 'swap' && $can_swap) {
        // Check if user already has a pending swap for this item
        $check_query = "SELECT id FROM swaps WHERE requester_id = ? AND item_id = ? AND status = 'pending'";
        $check_stmt = $db->prepare($check_query);
        $check_stmt->execute([$_SESSION['user_id'], $item_id]);
        
        if ($check_stmt->rowCount() > 0) {
            $error = 'You already have a pending swap request for this item.';
        } else {
            $insert_query = "INSERT INTO swaps (requester_id, item_id) VALUES (?, ?)";
            $insert_stmt = $db->prepare($insert_query);
            
            if ($insert_stmt->execute([$_SESSION['user_id'], $item_id])) {
                $success = 'Swap request sent successfully! The owner will be notified.';
            } else {
                $error = 'Error sending swap request. Please try again.';
            }
        }
    } elseif ($_POST['action'] === 'redeem' && $can_redeem) {
        // Handle redemption
        $db->beginTransaction();
        
        try {
            // Update user points
            $update_points = "UPDATE users SET points = points - ? WHERE id = ?";
            $points_stmt = $db->prepare($update_points);
            $points_stmt->execute([$item['points_required'], $_SESSION['user_id']]);
            
            // Update item status
            $update_item = "UPDATE items SET status = 'swapped' WHERE id = ?";
            $item_stmt = $db->prepare($update_item);
            $item_stmt->execute([$item_id]);
            
            // Create swap record
            $insert_swap = "INSERT INTO swaps (requester_id, item_id, status) VALUES (?, ?, 'completed')";
            $swap_stmt = $db->prepare($insert_swap);
            $swap_stmt->execute([$_SESSION['user_id'], $item_id]);
            
            $db->commit();
            $success = 'Item redeemed successfully! You can now arrange pickup with the owner.';
            
            // Update session points
            $_SESSION['points'] -= $item['points_required'];
            
        } catch (Exception $e) {
            $db->rollback();
            $error = 'Error redeeming item. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($item['title']); ?> - ReWear</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .item-details-container {
            padding-top: 100px;
            min-height: 100vh;
            background: #f8f9fa;
        }
        
        .item-details-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .item-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-bottom: 40px;
        }
        
        .item-images {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .main-image {
            width: 100%;
            height: 400px;
            object-fit: cover;
        }
        
        .item-info {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .item-title {
            font-size: 2rem;
            margin-bottom: 15px;
            color: #333;
        }
        
        .item-description {
            color: #666;
            line-height: 1.6;
            margin-bottom: 25px;
        }
        
        .item-meta {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 25px;
        }
        
        .meta-item {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .meta-item i {
            color: #2ecc71;
            width: 20px;
        }
        
        .item-status {
            display: inline-block;
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: bold;
            margin-bottom: 20px;
        }
        
        .status-available { background: #d4edda; color: #155724; }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-swapped { background: #cce5ff; color: #004085; }
        
        .owner-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 25px;
        }
        
        .owner-info h4 {
            color: #2ecc71;
            margin-bottom: 10px;
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 25px;
        }
        
        .btn-action {
            flex: 1;
            padding: 15px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            text-align: center;
        }
        
        .btn-swap {
            background: #2ecc71;
            color: white;
        }
        
        .btn-redeem {
            background: #3498db;
            color: white;
        }
        
        .btn-contact {
            background: #f39c12;
            color: white;
        }
        
        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .btn-action:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        
        .btn-remove {
            background: #e74c3c;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }
        
        .btn-remove:hover {
            background: #c0392b;
            transform: translateY(-2px);
        }
        
        .points-info {
            background: #e8f5e8;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .points-info.insufficient {
            background: #ffe8e8;
        }
        
        .error-message {
            background: #e74c3c;
            color: white;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .success-message {
            background: #2ecc71;
            color: white;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .breadcrumb {
            margin-bottom: 30px;
        }
        
        .breadcrumb a {
            color: #2ecc71;
            text-decoration: none;
        }
        
        .breadcrumb a:hover {
            text-decoration: underline;
        }
        
        @media (max-width: 768px) {
            .item-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .item-meta {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <h2><i class="fas fa-recycle"></i> ReWear</h2>
            </div>
            <ul class="nav-menu">
                <li><a href="index.php">Home</a></li>
                <li><a href="browse.php">Browse Items</a></li>
                <li><a href="add-item.php">Add Item</a></li>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
            <div class="hamburger">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </nav>

    <div class="item-details-container">
        <div class="item-details-content">
            <!-- Breadcrumb -->
            <div class="breadcrumb">
                <a href="index.php">Home</a> > 
                <a href="browse.php">Browse Items</a> > 
                <span><?php echo htmlspecialchars($item['title']); ?></span>
            </div>

            <?php if ($error): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="success-message"><?php echo $success; ?></div>
            <?php endif; ?>

            <div class="item-grid">
                <!-- Item Images -->
                <div class="item-images">
                    <img src="<?php echo $item['image_path'] ?: 'assets/images/placeholder.jpg'; ?>" 
                         alt="<?php echo htmlspecialchars($item['title']); ?>" 
                         class="main-image">
                </div>

                <!-- Item Information -->
                <div class="item-info">
                    <span class="item-status status-<?php echo $item['status']; ?>">
                        <?php echo ucfirst($item['status']); ?>
                    </span>
                    <h1 class="item-title"><?php echo htmlspecialchars($item['title']); ?></h1>
                    <div class="item-description"><?php echo nl2br(htmlspecialchars($item['description'])); ?></div>
                    <div class="item-meta">
                        <div class="meta-item"><i class="fas fa-tshirt"></i> Category: <?php echo htmlspecialchars($item['category']); ?></div>
                        <div class="meta-item"><i class="fas fa-ruler-combined"></i> Size: <?php echo htmlspecialchars($item['size']); ?></div>
                        <div class="meta-item"><i class="fas fa-star"></i> Condition: <?php echo htmlspecialchars($item['condition_rating']); ?>/5</div>
                        <div class="meta-item"><i class="fas fa-coins"></i> Points: <?php echo $item['points_required']; ?></div>
                    </div>
                    <div class="owner-info">
                        <h4>Owner Info</h4>
                        <div><i class="fas fa-user"></i> <?php echo htmlspecialchars($item['owner_name']); ?></div>
                        <div><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($item['owner_email']); ?></div>
                    </div>
                    <?php if ($error): ?>
                        <div class="error-message" style="color:#e74c3c; margin-bottom:10px;"> <?php echo $error; ?> </div>
                    <?php endif; ?>
                    <?php if ($success): ?>
                        <div class="success-message" style="color:#2ecc71; margin-bottom:10px;"> <?php echo $success; ?> </div>
                    <?php endif; ?>
                    <div class="action-buttons">
                        <?php if ($is_owner): ?>
                            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                                <a href="dashboard.php" class="btn-action btn-primary">
                                    <i class="fas fa-cog"></i> Manage Item
                                </a>
                                <button class="btn-action btn-remove" onclick="removeItem(<?php echo $item['id']; ?>, '<?php echo htmlspecialchars($item['title']); ?>')">
                                    <i class="fas fa-trash"></i> Remove Item
                                </button>
                            </div>
                        <?php elseif (!$is_logged_in): ?>
                            <a href="login.php" class="btn-action btn-swap">
                                <i class="fas fa-sign-in-alt"></i> Login to Swap
                            </a>
                        <?php elseif ($item['status'] !== 'available'): ?>
                            <button class="btn-action btn-swap" disabled>
                                <i class="fas fa-times"></i> Item Unavailable
                            </button>
                        <?php else: ?>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="action" value="swap">
                                <button type="submit" class="btn-action btn-swap">
                                    <i class="fas fa-exchange-alt"></i> Request Swap
                                </button>
                            </form>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="action" value="redeem">
                                <button type="submit" class="btn-action btn-redeem">
                                    <i class="fas fa-coins"></i> Redeem (<?php echo $item['points_required']; ?> pts)
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
    <script>
        // Confirm actions
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function(e) {
                const action = e.submitter.value;
                if (action === 'swap') {
                    if (!confirm('Send swap request for this item?')) {
                        e.preventDefault();
                    }
                } else if (action === 'redeem') {
                    if (!confirm(`Redeem this item for ${<?php echo $item['points_required']; ?>} points?`)) {
                        e.preventDefault();
                    }
                }
            });
        });
        
        function removeItem(itemId, itemTitle) {
            if (confirm(`Are you sure you want to remove "${itemTitle}"? This action cannot be undone.`)) {
                fetch('remove-item.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        item_id: itemId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Item removed successfully!');
                        window.location.href = 'dashboard.php';
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred. Please try again.');
                });
            }
        }
    </script>
</body>
</html> 