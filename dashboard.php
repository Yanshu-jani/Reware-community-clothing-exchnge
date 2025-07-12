<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Get user data
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM users WHERE id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Get user's uploaded items
$user_items = [];

// Get user's swap requests (as requester)
$swaps_query = "SELECT s.*, i.title, i.image_path, u.username as item_owner 
                FROM swaps s 
                JOIN items i ON s.item_id = i.id 
                JOIN users u ON i.user_id = u.id 
                WHERE s.requester_id = ? 
                ORDER BY s.created_at DESC";
$swaps_stmt = $db->prepare($swaps_query);
$swaps_stmt->execute([$user_id]);
$user_swaps = $swaps_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get incoming swap requests (for user's items)
$incoming_query = "SELECT s.*, i.title, i.image_path, u.username as requester_name 
                   FROM swaps s 
                   JOIN items i ON s.item_id = i.id 
                   JOIN users u ON s.requester_id = u.id 
                   WHERE i.user_id = ? AND s.status = 'pending'
                   ORDER BY s.created_at DESC";
$incoming_stmt = $db->prepare($incoming_query);
$incoming_stmt->execute([$user_id]);
$incoming_swaps = $incoming_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - ReWear</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .dashboard-container {
            padding-top: 100px;
            min-height: 100vh;
            background: #f8f9fa;
        }
        
        .dashboard-header {
            background: white;
            padding: 30px 0;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .dashboard-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .user-info {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 30px;
            margin-bottom: 40px;
        }
        
        .profile-card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .profile-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: #2ecc71;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 2.5rem;
            color: white;
        }
        
        .points-display {
            background: linear-gradient(135deg, #2ecc71, #27ae60);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
        }
        
        .points-number {
            font-size: 2rem;
            font-weight: bold;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #2ecc71;
        }
        
        .section-title {
            font-size: 1.5rem;
            margin-bottom: 20px;
            color: #333;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .items-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .item-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        
        .item-card:hover {
            transform: translateY(-5px);
        }
        
        .item-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        
        .item-content {
            padding: 20px;
        }
        
        .item-title {
            font-size: 1.2rem;
            margin-bottom: 10px;
            color: #333;
        }
        
        .item-status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: bold;
        }
        
        .status-pending { background: #fff3cd; color: #856404; }
        .status-available { background: #d4edda; color: #155724; }
        .status-swapped { background: #cce5ff; color: #004085; }
        
        .swaps-section {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .swap-item {
            display: flex;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid #eee;
            gap: 15px;
        }
        
        .swap-item:last-child {
            border-bottom: none;
        }
        
        .swap-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
        }
        
        .swap-details {
            flex: 1;
        }
        
        .swap-status {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: bold;
        }
        
        .btn-action {
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }
        
        .btn-accept { background: #2ecc71; color: white; }
        .btn-reject { background: #e74c3c; color: white; }
        .btn-accept:hover { background: #27ae60; }
        .btn-reject:hover { background: #c0392b; }
        
        .btn-remove {
            background: #e74c3c;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }
        
        .btn-remove:hover {
            background: #c0392b;
            transform: translateY(-2px);
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
            .user-info {
                grid-template-columns: 1fr;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .items-grid {
                grid-template-columns: 1fr;
            }
        }
        .infographic-card {
            position: relative;
            background: #fff;
            border-radius: 24px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
            padding: 32px 18px 24px 18px;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 320px;
            justify-content: flex-start;
        }
        .infographic-sticky {
            position: absolute;
            top: -32px;
            left: 50%;
            transform: translateX(-50%) rotate(-8deg);
            width: 70px;
            height: 70px;
            border-radius: 8px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.10);
        }
        .infographic-clip {
            color: #222;
            font-size: 1.2rem;
            margin-bottom: 2px;
            opacity: 0.7;
        }
        .infographic-number {
            font-size: 2rem;
            font-weight: bold;
            color: #fff;
            letter-spacing: 1px;
        }
        .infographic-content {
            margin-top: 50px;
            text-align: center;
        }
        .infographic-title {
            font-size: 1.1rem;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .infographic-desc {
            font-size: 0.95rem;
            color: #555;
            margin-bottom: 18px;
        }
        .infographic-bar {
            width: 48px;
            height: 10px;
            border-radius: 8px 8px 16px 16px;
            margin: 18px auto 0 auto;
        }
        .infographic-actions {
            display: flex;
            gap: 8px;
            justify-content: center;
            margin-top: 10px;
        }
        .infographic-btn {
            width: auto;
            padding: 7px 18px;
            font-size: 0.97rem;
            border-radius: 18px;
            border: none;
            color: #fff;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.2s, box-shadow 0.2s, transform 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.10);
            margin: 0 2px;
            text-decoration: none;
        }
        .infographic-btn i {
            font-size: 1.08rem;
        }
        .infographic-btn span {
            margin: 0 4px;
            display: inline-block;
        }
        .infographic-btn:last-child i {
            margin-left: 8px;
        }
        .infographic-btn:hover {
            opacity: 0.92;
            transform: translateY(-2px) scale(1.04);
            box-shadow: 0 4px 16px rgba(0,0,0,0.13);
        }
        .infographic-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 16px;
            margin: 32px auto 10px auto;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            display: block;
            border: 2px solid #e0e4ea;
            background: #f6f8fa;
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
                <li><a href="dashboard.php" class="active">Dashboard</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
            <div class="hamburger">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </nav>

    <div class="dashboard-container">
        <div class="dashboard-header">
            <div class="dashboard-content">
                <h1>Welcome back, <?php echo htmlspecialchars($user['username']); ?>!</h1>
                <p>Manage your items, track swaps, and build your sustainable fashion community.</p>
            </div>
        </div>

        <div class="dashboard-content">
            <!-- User Info and Stats -->
            <div class="user-info">
                <div class="profile-card">
                    <div class="profile-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <h3><?php echo htmlspecialchars($user['username']); ?></h3>
                    <p><?php echo htmlspecialchars($user['email']); ?></p>
                    
                    <div class="points-display">
                        <div class="points-number"><?php echo number_format($user['points']); ?></div>
                        <div>Points Available</div>
                    </div>
                    
                    <a href="add-item.php" class="btn-primary" style="display: inline-block; margin-top: 20px;">
                        <i class="fas fa-plus"></i> Add New Item
                    </a>
                </div>

                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-number"><?php echo count($user_items); ?></div>
                        <div>Items Listed</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo count($user_swaps); ?></div>
                        <div>Swap Requests</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo count($incoming_swaps); ?></div>
                        <div>Incoming Requests</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo count(array_filter($user_swaps, function($s) { return $s['status'] === 'completed'; })); ?></div>
                        <div>Completed Swaps</div>
                    </div>
                </div>
            </div>

            <!-- User's Items -->
            <div class="section-title">
                <i class="fas fa-tshirt"></i>
                Your Listed Items
            </div>
            
            <?php if (empty($user_items)): ?>
                <div class="empty-state">
                    <i class="fas fa-tshirt"></i>
                    <h3>No items listed yet</h3>
                    <p>Start by adding your first item to the community!</p>
                    <a href="add-item.php" class="btn-primary">Add Your First Item</a>
                </div>
            <?php else: ?>
                <div class="items-grid">
                    <?php foreach ($user_items as $index => $item): ?>
                        <div class="item-card infographic-card">
                            <div class="infographic-sticky" style="background: <?php echo ['#183153','#f7a600','#217693','#1ccfc9'][$index%4]; ?>;">
                                <i class="fas fa-paperclip infographic-clip"></i>
                                <span class="infographic-number"><?php echo str_pad($index+1, 2, '0', STR_PAD_LEFT); ?></span>
                            </div>
                            <img src="<?php echo $item['image_path'] ?: 'assets/images/placeholder.jpg'; ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" class="infographic-image">
                            <div class="infographic-content">
                                <div class="infographic-title" style="color: <?php echo ['#183153','#f7a600','#217693','#1ccfc9'][$index%4]; ?>;">
                                    <?php echo htmlspecialchars($item['title']); ?>
                                </div>
                                <div class="infographic-desc">
                                    <?php echo htmlspecialchars($item['description']); ?>
                                </div>
                                <div class="infographic-actions">
                                    <?php $color = ['#183153','#f7a600','#217693','#1ccfc9'][$index%4]; ?>
                                    <a href="item-details.php?id=<?php echo $item['id']; ?>" class="infographic-btn" style="background: <?php echo $color; ?>;">
                                        <i class="fas fa-eye"></i><span>View</span>
                                    </a>
                                    <button class="infographic-btn" onclick="removeItem(<?php echo $item['id']; ?>, '<?php echo htmlspecialchars($item['title']); ?>')" style="background: <?php echo $color; ?>;">
                                        <span>Remove</span><i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="infographic-bar" style="background: <?php echo ['#183153','#f7a600','#217693','#1ccfc9'][$index%4]; ?>;"></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Incoming Swap Requests -->
            <?php if (!empty($incoming_swaps)): ?>
                <div class="swaps-section">
                    <div class="section-title">
                        <i class="fas fa-inbox"></i>
                        Incoming Swap Requests
                    </div>
                    
                    <?php foreach ($incoming_swaps as $swap): ?>
                        <div class="swap-item">
                            <img src="<?php echo $swap['image_path'] ?: 'assets/images/placeholder.jpg'; ?>" 
                                 alt="<?php echo htmlspecialchars($swap['title']); ?>" 
                                 class="swap-image">
                            <div class="swap-details">
                                <h4><?php echo htmlspecialchars($swap['title']); ?></h4>
                                <p>Requested by: <?php echo htmlspecialchars($swap['requester_name']); ?></p>
                                <small><?php echo date('M j, Y', strtotime($swap['created_at'])); ?></small>
                            </div>
                            <div style="display: flex; gap: 10px;">
                                <button class="btn-action btn-accept" onclick="handleSwap(<?php echo $swap['id']; ?>, 'accept')">
                                    Accept
                                </button>
                                <button class="btn-action btn-reject" onclick="handleSwap(<?php echo $swap['id']; ?>, 'reject')">
                                    Reject
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- User's Swap Requests -->
            <?php if (!empty($user_swaps)): ?>
                <div class="swaps-section">
                    <div class="section-title">
                        <i class="fas fa-exchange-alt"></i>
                        Your Swap Requests
                    </div>
                    
                    <?php foreach ($user_swaps as $swap): ?>
                        <div class="swap-item">
                            <img src="<?php echo $swap['image_path'] ?: 'assets/images/placeholder.jpg'; ?>" 
                                 alt="<?php echo htmlspecialchars($swap['title']); ?>" 
                                 class="swap-image">
                            <div class="swap-details">
                                <h4><?php echo htmlspecialchars($swap['title']); ?></h4>
                                <p>Owner: <?php echo htmlspecialchars($swap['item_owner']); ?></p>
                                <small><?php echo date('M j, Y', strtotime($swap['created_at'])); ?></small>
                            </div>
                            <span class="swap-status status-<?php echo $swap['status']; ?>">
                                <?php echo ucfirst($swap['status']); ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
    <script>
        function handleSwap(swapId, action) {
            if (confirm(`Are you sure you want to ${action} this swap request?`)) {
                // In a real app, this would make an AJAX call to update the swap status
                fetch('handle-swap.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        swap_id: swapId,
                        action: action
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
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
                        location.reload();
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