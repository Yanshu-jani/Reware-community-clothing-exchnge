<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Check if user is admin
$admin_query = "SELECT role FROM admins WHERE user_id = ?";
$admin_stmt = $db->prepare($admin_query);
$admin_stmt->execute([$_SESSION['user_id']]);
$admin = $admin_stmt->fetch(PDO::FETCH_ASSOC);

if (!$admin) {
    header('Location: ../dashboard.php');
    exit();
}

$success = '';
$error = '';

// Handle admin actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $item_id = $_POST['item_id'] ?? null;
    $action = $_POST['action'] ?? null;
    
    if ($item_id && $action) {
        if ($action === 'approve') {
            $update_query = "UPDATE items SET status = 'available' WHERE id = ?";
            $stmt = $db->prepare($update_query);
            if ($stmt->execute([$item_id])) {
                $success = 'Item approved successfully!';
            } else {
                $error = 'Error approving item.';
            }
        } elseif ($action === 'reject') {
            $update_query = "UPDATE items SET status = 'removed' WHERE id = ?";
            $stmt = $db->prepare($update_query);
            if ($stmt->execute([$item_id])) {
                $success = 'Item rejected and removed.';
            } else {
                $error = 'Error rejecting item.';
            }
        }
    }
}

// Get pending items
$pending_query = "SELECT i.*, u.username as owner_name 
                 FROM items i 
                 JOIN users u ON i.user_id = u.id 
                 WHERE i.status = 'pending' 
                 ORDER BY i.created_at DESC";
$pending_stmt = $db->prepare($pending_query);
$pending_stmt->execute();
$pending_items = $pending_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get all items for overview
$all_items_query = "SELECT i.*, u.username as owner_name 
                   FROM items i 
                   JOIN users u ON i.user_id = u.id 
                   ORDER BY i.created_at DESC 
                   LIMIT 50";
$all_items_stmt = $db->prepare($all_items_query);
$all_items_stmt->execute();
$all_items = $all_items_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get user stats
$user_stats_query = "SELECT COUNT(*) as total_users FROM users";
$user_stats_stmt = $db->prepare($user_stats_query);
$user_stats_stmt->execute();
$total_users = $user_stats_stmt->fetch(PDO::FETCH_ASSOC)['total_users'];

$item_stats_query = "SELECT 
                        COUNT(*) as total_items,
                        SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END) as available_items,
                        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_items,
                        SUM(CASE WHEN status = 'swapped' THEN 1 ELSE 0 END) as swapped_items
                     FROM items";
$item_stats_stmt = $db->prepare($item_stats_query);
$item_stats_stmt->execute();
$item_stats = $item_stats_stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - ReWear</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .admin-container {
            padding-top: 100px;
            min-height: 100vh;
            background: #f8f9fa;
        }
        
        .admin-header {
            background: white;
            padding: 30px 0;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .admin-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: #2ecc71;
            margin-bottom: 10px;
        }
        
        .section-title {
            font-size: 1.5rem;
            margin-bottom: 20px;
            color: #333;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .items-table {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .table-header {
            background: #2ecc71;
            color: white;
            padding: 15px 20px;
            font-weight: bold;
        }
        
        .table-row {
            display: grid;
            grid-template-columns: 1fr 2fr 1fr 1fr 1fr 1fr;
            gap: 15px;
            padding: 15px 20px;
            border-bottom: 1px solid #eee;
            align-items: center;
        }
        
        .table-row:last-child {
            border-bottom: none;
        }
        
        .table-row:nth-child(even) {
            background: #f8f9fa;
        }
        
        .item-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 5px;
        }
        
        .status-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: bold;
        }
        
        .status-pending { background: #fff3cd; color: #856404; }
        .status-available { background: #d4edda; color: #155724; }
        .status-swapped { background: #cce5ff; color: #004085; }
        .status-removed { background: #f8d7da; color: #721c24; }
        
        .action-buttons {
            display: flex;
            gap: 5px;
        }
        
        .btn-admin {
            padding: 5px 10px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            font-size: 0.8rem;
            transition: all 0.3s ease;
        }
        
        .btn-approve { background: #2ecc71; color: white; }
        .btn-reject { background: #e74c3c; color: white; }
        .btn-view { background: #3498db; color: white; }
        
        .btn-admin:hover {
            transform: translateY(-1px);
        }
        
        .success-message {
            background: #2ecc71;
            color: white;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .error-message {
            background: #e74c3c;
            color: white;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        
        .empty-state i {
            font-size: 3rem;
            color: #ddd;
            margin-bottom: 20px;
        }
        
        @media (max-width: 768px) {
            .table-row {
                grid-template-columns: 1fr;
                gap: 10px;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <h2><i class="fas fa-recycle"></i> ReWear Admin</h2>
            </div>
            <ul class="nav-menu">
                <li><a href="../index.php">Home</a></li>
                <li><a href="../browse.php">Browse Items</a></li>
                <li><a href="../dashboard.php">Dashboard</a></li>
                <li><a href="../logout.php">Logout</a></li>
            </ul>
            <div class="hamburger">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </nav>

    <div class="admin-container">
        <div class="admin-header">
            <div class="admin-content">
                <h1><i class="fas fa-shield-alt"></i> Admin Panel</h1>
                <p>Manage items, moderate content, and oversee the community.</p>
            </div>
        </div>

        <div class="admin-content">
            <?php if ($success): ?>
                <div class="success-message"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>

            <!-- Statistics -->
            <div class="section-title">
                <i class="fas fa-chart-bar"></i>
                Platform Statistics
            </div>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $total_users; ?></div>
                    <div>Total Users</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $item_stats['total_items']; ?></div>
                    <div>Total Items</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $item_stats['available_items']; ?></div>
                    <div>Available Items</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $item_stats['pending_items']; ?></div>
                    <div>Pending Approval</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $item_stats['swapped_items']; ?></div>
                    <div>Completed Swaps</div>
                </div>
            </div>

            <!-- Pending Items -->
            <div class="section-title">
                <i class="fas fa-clock"></i>
                Pending Approvals
            </div>
            
            <?php if (empty($pending_items)): ?>
                <div class="empty-state">
                    <i class="fas fa-check-circle"></i>
                    <h3>No pending items</h3>
                    <p>All items have been reviewed!</p>
                </div>
            <?php else: ?>
                <div class="items-table">
                    <div class="table-header">
                        <div class="table-row">
                            <div>Image</div>
                            <div>Item Details</div>
                            <div>Owner</div>
                            <div>Points</div>
                            <div>Status</div>
                            <div>Actions</div>
                        </div>
                    </div>
                    
                    <?php foreach ($pending_items as $item): ?>
                        <div class="table-row">
                            <div>
                                <img src="<?php echo $item['image_path'] ?: '../assets/images/placeholder.jpg'; ?>" 
                                     alt="<?php echo htmlspecialchars($item['title']); ?>" 
                                     class="item-image">
                            </div>
                            <div>
                                <strong><?php echo htmlspecialchars($item['title']); ?></strong><br>
                                <small><?php echo htmlspecialchars($item['category']); ?> - <?php echo htmlspecialchars($item['size']); ?></small>
                            </div>
                            <div><?php echo htmlspecialchars($item['owner_name']); ?></div>
                            <div><?php echo $item['points_required']; ?> pts</div>
                            <div>
                                <span class="status-badge status-<?php echo $item['status']; ?>">
                                    <?php echo ucfirst($item['status']); ?>
                                </span>
                            </div>
                            <div class="action-buttons">
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                                    <button type="submit" name="action" value="approve" class="btn-admin btn-approve">
                                        <i class="fas fa-check"></i> Approve
                                    </button>
                                    <button type="submit" name="action" value="reject" class="btn-admin btn-reject">
                                        <i class="fas fa-times"></i> Reject
                                    </button>
                                </form>
                                <a href="../item-details.php?id=<?php echo $item['id']; ?>" class="btn-admin btn-view">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- All Items Overview -->
            <div class="section-title">
                <i class="fas fa-list"></i>
                Recent Items Overview
            </div>
            
            <div class="items-table">
                <div class="table-header">
                    <div class="table-row">
                        <div>Image</div>
                        <div>Item Details</div>
                        <div>Owner</div>
                        <div>Points</div>
                        <div>Status</div>
                        <div>Actions</div>
                    </div>
                </div>
                
                <?php foreach ($all_items as $item): ?>
                    <div class="table-row">
                        <div>
                            <img src="<?php echo $item['image_path'] ?: '../assets/images/placeholder.jpg'; ?>" 
                                 alt="<?php echo htmlspecialchars($item['title']); ?>" 
                                 class="item-image">
                        </div>
                        <div>
                            <strong><?php echo htmlspecialchars($item['title']); ?></strong><br>
                            <small><?php echo htmlspecialchars($item['category']); ?> - <?php echo htmlspecialchars($item['size']); ?></small>
                        </div>
                        <div><?php echo htmlspecialchars($item['owner_name']); ?></div>
                        <div><?php echo $item['points_required']; ?> pts</div>
                        <div>
                            <span class="status-badge status-<?php echo $item['status']; ?>">
                                <?php echo ucfirst($item['status']); ?>
                            </span>
                        </div>
                        <div class="action-buttons">
                            <?php if ($item['status'] === 'pending'): ?>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                                    <button type="submit" name="action" value="approve" class="btn-admin btn-approve">
                                        <i class="fas fa-check"></i> Approve
                                    </button>
                                    <button type="submit" name="action" value="reject" class="btn-admin btn-reject">
                                        <i class="fas fa-times"></i> Reject
                                    </button>
                                </form>
                            <?php endif; ?>
                            <a href="../item-details.php?id=<?php echo $item['id']; ?>" class="btn-admin btn-view">
                                <i class="fas fa-eye"></i> View
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <script src="../assets/js/main.js"></script>
    <script>
        // Confirm admin actions
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function(e) {
                const action = e.submitter.value;
                if (action === 'reject') {
                    if (!confirm('Are you sure you want to reject this item? This action cannot be undone.')) {
                        e.preventDefault();
                    }
                }
            });
        });
    </script>
</body>
</html> 