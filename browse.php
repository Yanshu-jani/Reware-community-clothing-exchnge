<?php
session_start();
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

// Get filter parameters
$category = $_GET['category'] ?? '';
$size = $_GET['size'] ?? '';
$condition = $_GET['condition'] ?? '';
$search = $_GET['search'] ?? '';
$sort = $_GET['sort'] ?? 'newest';

// Build query
$query = "SELECT i.*, u.username as owner_name 
          FROM items i 
          JOIN users u ON i.user_id = u.id 
          WHERE i.status = 'available'";

$params = [];

if ($category) {
    $query .= " AND i.category = ?";
    $params[] = $category;
}

if ($size) {
    $query .= " AND i.size = ?";
    $params[] = $size;
}

if ($condition) {
    $query .= " AND i.condition_rating = ?";
    $params[] = $condition;
}

if ($search) {
    $query .= " AND (i.title LIKE ? OR i.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

// Add sorting
switch ($sort) {
    case 'oldest':
        $query .= " ORDER BY i.created_at ASC";
        break;
    case 'points_low':
        $query .= " ORDER BY i.points_required ASC";
        break;
    case 'points_high':
        $query .= " ORDER BY i.points_required DESC";
        break;
    default:
        $query .= " ORDER BY i.created_at DESC";
}

$stmt = $db->prepare($query);
$stmt->execute($params);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get categories for filter
$categories_query = "SELECT DISTINCT category FROM items WHERE category IS NOT NULL AND category != ''";
$categories_stmt = $db->prepare($categories_query);
$categories_stmt->execute();
$categories = $categories_stmt->fetchAll(PDO::FETCH_COLUMN);

// Get sizes for filter
$sizes_query = "SELECT DISTINCT size FROM items WHERE size IS NOT NULL AND size != ''";
$sizes_stmt = $db->prepare($sizes_query);
$sizes_stmt->execute();
$sizes = $sizes_stmt->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Items - ReWear</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .browse-container {
            padding-top: 100px;
            min-height: 100vh;
            background: #f8f9fa;
        }
        
        .browse-header {
            background: white;
            padding: 30px 0;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .browse-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .filters-section {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .filters-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            align-items: end;
        }
        
        .filter-group {
            display: flex;
            flex-direction: column;
        }
        
        .filter-group label {
            margin-bottom: 5px;
            font-weight: 500;
            color: #333;
        }
        
        .filter-group select,
        .filter-group input {
            padding: 10px;
            border: 2px solid #e1e5e9;
            border-radius: 5px;
            font-size: 14px;
        }
        
        .filter-group select:focus,
        .filter-group input:focus {
            outline: none;
            border-color: #2ecc71;
        }
        
        .btn-filter {
            padding: 10px 20px;
            background: #2ecc71;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 500;
            transition: background 0.3s ease;
        }
        
        .btn-filter:hover {
            background: #27ae60;
        }
        
        .btn-clear {
            padding: 10px 20px;
            background: #6c757d;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 500;
            transition: background 0.3s ease;
        }
        
        .btn-clear:hover {
            background: #5a6268;
        }
        
        .results-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .results-count {
            color: #666;
        }
        
        .sort-select {
            padding: 8px 12px;
            border: 2px solid #e1e5e9;
            border-radius: 5px;
            font-size: 14px;
        }
        
        .items-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 32px;
            margin-bottom: 40px;
        }
        
        .item-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
            cursor: pointer;
        }
        
        .item-card:hover {
            transform: translateY(-5px);
        }
        
        .item-image {
            width: 100%;
            height: 250px;
            object-fit: cover;
        }
        
        .item-content {
            padding: 20px;
        }
        
        .item-title {
            font-size: 1.3rem;
            margin-bottom: 10px;
            color: #333;
        }
        
        .item-description {
            color: #666;
            margin-bottom: 15px;
            line-height: 1.5;
        }
        
        .item-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            font-size: 0.9rem;
            color: #888;
        }
        
        .item-owner {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .item-owner i {
            color: #2ecc71;
        }
        
        .item-details {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
        }
        
        .item-tag {
            background: #f8f9fa;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.8rem;
            color: #666;
        }
        
        .item-actions {
            display: flex;
            gap: 10px;
        }
        
        .btn-swap,
        .btn-redeem {
            flex: 1;
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-swap {
            background: #2ecc71;
            color: white;
        }
        
        .btn-redeem {
            background: #3498db;
            color: white;
        }
        
        .btn-swap:hover {
            background: #27ae60;
        }
        
        .btn-redeem:hover {
            background: #2980b9;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }
        
        .empty-state i {
            font-size: 4rem;
            color: #ddd;
            margin-bottom: 20px;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 40px;
        }
        
        .page-btn {
            padding: 10px 15px;
            border: 2px solid #e1e5e9;
            background: white;
            color: #333;
            text-decoration: none;
            border-radius: 5px;
            transition: all 0.3s ease;
        }
        
        .page-btn:hover,
        .page-btn.active {
            background: #2ecc71;
            color: white;
            border-color: #2ecc71;
        }
        
        @media (max-width: 768px) {
            .filters-grid {
                grid-template-columns: 1fr;
            }
            
            .results-header {
                flex-direction: column;
                gap: 15px;
                align-items: stretch;
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
            padding: 20px 12px 16px 12px;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 240px;
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
        .infographic-content {
            margin-top: 10px;
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
        .infographic-btn:hover {
            opacity: 0.92;
            transform: translateY(-2px) scale(1.04);
            box-shadow: 0 4px 16px rgba(0,0,0,0.13);
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
                <li><a href="browse.php" class="active">Browse Items</a></li>
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

    <div class="browse-container">
        <div class="browse-header">
            <div class="browse-content">
                <h1>Browse Items</h1>
                <p>Find your next favorite piece and swap sustainably!</p>
            </div>
        </div>

        <div class="browse-content">
            <!-- Filters and Search Bar Grouped -->
            <form class="filters-section" method="get" action="browse.php">
                <div class="filters-grid">
                    <div class="filter-group">
                        <label for="category">Category</label>
                        <select name="category" id="category">
                            <option value="">All</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo htmlspecialchars($cat); ?>" <?php if ($category === $cat) echo 'selected'; ?>><?php echo htmlspecialchars($cat); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="size">Size</label>
                        <select name="size" id="size">
                            <option value="">All</option>
                            <?php foreach ($sizes as $sz): ?>
                                <option value="<?php echo htmlspecialchars($sz); ?>" <?php if ($size === $sz) echo 'selected'; ?>><?php echo htmlspecialchars($sz); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="condition">Condition</label>
                        <select name="condition" id="condition">
                            <option value="">All</option>
                            <option value="5" <?php if ($condition === '5') echo 'selected'; ?>>Like New</option>
                            <option value="4" <?php if ($condition === '4') echo 'selected'; ?>>Excellent</option>
                            <option value="3" <?php if ($condition === '3') echo 'selected'; ?>>Good</option>
                            <option value="2" <?php if ($condition === '2') echo 'selected'; ?>>Fair</option>
                            <option value="1" <?php if ($condition === '1') echo 'selected'; ?>>Worn</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="search">Search</label>
                        <input type="text" name="search" id="search" placeholder="Search items..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="filter-group" style="align-self: flex-end;">
                        <button type="submit" class="btn-filter"><i class="fas fa-search"></i> Filter</button>
                        <a href="browse.php" class="btn-clear" style="margin-top:8px;display:inline-block;">Clear</a>
                    </div>
                </div>
            </form>

            <!-- Results Header -->
            <div class="results-header">
                <div class="results-count">
                    <?php echo count($items); ?> item<?php echo count($items) !== 1 ? 's' : ''; ?> found
                </div>
                <form method="get" style="margin:0;">
                    <!-- Keep filters in sort form -->
                    <input type="hidden" name="category" value="<?php echo htmlspecialchars($category); ?>">
                    <input type="hidden" name="size" value="<?php echo htmlspecialchars($size); ?>">
                    <input type="hidden" name="condition" value="<?php echo htmlspecialchars($condition); ?>">
                    <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                    <select name="sort" class="sort-select" onchange="this.form.submit()">
                        <option value="newest" <?php if ($sort === 'newest') echo 'selected'; ?>>Newest</option>
                        <option value="oldest" <?php if ($sort === 'oldest') echo 'selected'; ?>>Oldest</option>
                        <option value="points_low" <?php if ($sort === 'points_low') echo 'selected'; ?>>Points: Low to High</option>
                        <option value="points_high" <?php if ($sort === 'points_high') echo 'selected'; ?>>Points: High to Low</option>
                    </select>
                </form>
            </div>

            <!-- Items Grid -->
            <div class="items-grid">
                <?php if (empty($items)): ?>
                    <div class="empty-state" style="grid-column: 1/-1;">
                        <i class="fas fa-tshirt"></i>
                        <h3>No items found</h3>
                        <p>Try adjusting your filters or search terms.</p>
                    </div>
                <?php else: ?>
                    <?php $colors = ['#183153','#f7a600','#217693','#1ccfc9']; $i=0; ?>
                    <?php foreach ($items as $item): ?>
                        <?php $color = $colors[$i%4]; ?>
                        <div class="item-card infographic-card">
                            <div class="infographic-sticky" style="background: <?php echo $color; ?>;">
                                <i class="fas fa-paperclip infographic-clip"></i>
                                <span class="infographic-number"><?php echo str_pad($i+1, 2, '0', STR_PAD_LEFT); ?></span>
                            </div>
                            <img src="<?php echo $item['image_path'] ?: 'assets/images/placeholder.jpg'; ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" class="infographic-image">
                            <div class="infographic-content">
                                <div class="infographic-title" style="color: <?php echo $color; ?>;">
                                    <?php echo htmlspecialchars($item['title']); ?>
                                </div>
                                <div class="infographic-desc">
                                    <?php echo htmlspecialchars($item['description']); ?>
                                </div>
                                <div style="font-size:0.97rem; color:#666; margin-bottom:6px;">By <?php echo htmlspecialchars($item['owner_name']); ?></div>
                                <div style="font-size:0.97rem; color:#888; margin-bottom:6px;">Size: <?php echo htmlspecialchars($item['size']); ?> | <?php echo htmlspecialchars($item['category']); ?></div>
                                <span style="display:block; color:#222; font-weight:600; margin-bottom:8px;"> <?php echo $item['points_required']; ?> pts</span>
                                <a href="item-details.php?id=<?php echo $item['id']; ?>" class="infographic-btn" style="background: <?php echo $color; ?>; margin-top:8px;"><i class="fas fa-eye"></i><span>View</span></a>
                            </div>
                            <div class="infographic-bar" style="background: <?php echo $color; ?>;"></div>
                        </div>
                        <?php $i++; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
    <script>
        function viewItem(itemId) {
            window.location.href = `item-details.php?id=${itemId}`;
        }
        
        function requestSwap(itemId) {
            <?php if (!isset($_SESSION['user_id'])): ?>
                window.location.href = 'login.php';
            <?php else: ?>
                if (confirm('Request a swap for this item?')) {
                    fetch('request-swap.php', {
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
                            ReWear.showNotification('Swap request sent successfully!', 'info');
                        } else {
                            ReWear.showNotification(data.message, 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        ReWear.showNotification('An error occurred. Please try again.', 'error');
                    });
                }
            <?php endif; ?>
        }
        
        function redeemItem(itemId, pointsRequired) {
            <?php if (!isset($_SESSION['user_id'])): ?>
                window.location.href = 'login.php';
            <?php else: ?>
                if (confirm(`Redeem this item for ${pointsRequired} points?`)) {
                    fetch('redeem-item.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            item_id: itemId,
                            points_required: pointsRequired
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            ReWear.showNotification('Item redeemed successfully!', 'info');
                            setTimeout(() => location.reload(), 1500);
                        } else {
                            ReWear.showNotification(data.message, 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        ReWear.showNotification('An error occurred. Please try again.', 'error');
                    });
                }
            <?php endif; ?>
        }
    </script>
</body>
</html> 