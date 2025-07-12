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

$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_test_items'])) {
    try {
        // Sample test items data
        $test_items = [
            [
                'title' => 'Vintage Denim Jacket',
                'description' => 'Classic blue denim jacket in excellent condition. Perfect for casual wear.',
                'category' => 'Outerwear',
                'size' => 'M',
                'condition_rating' => 'excellent',
                'points_required' => 150,
                'image_path' => 'assets/images/placeholder.jpg'
            ],
            [
                'title' => 'Summer Floral Dress',
                'description' => 'Light and breezy floral dress perfect for summer days. Size S.',
                'category' => 'Dresses',
                'size' => 'S',
                'condition_rating' => 'good',
                'points_required' => 100,
                'image_path' => 'assets/images/placeholder.jpg'
            ],
            [
                'title' => 'Leather Ankle Boots',
                'description' => 'Brown leather ankle boots, barely worn. Size 8, perfect for autumn.',
                'category' => 'Shoes',
                'size' => '8',
                'condition_rating' => 'excellent',
                'points_required' => 200,
                'image_path' => 'assets/images/placeholder.jpg'
            ],
            [
                'title' => 'Cotton Hoodie',
                'description' => 'Comfortable cotton hoodie in navy blue. Size L, great for layering.',
                'category' => 'Tops',
                'size' => 'L',
                'condition_rating' => 'good',
                'points_required' => 80,
                'image_path' => 'assets/images/placeholder.jpg'
            ],
            [
                'title' => 'Silk Scarf',
                'description' => 'Elegant silk scarf with geometric pattern. Perfect accessory.',
                'category' => 'Accessories',
                'size' => 'One Size',
                'condition_rating' => 'excellent',
                'points_required' => 50,
                'image_path' => 'assets/images/placeholder.jpg'
            ],
            [
                'title' => 'High-Waisted Jeans',
                'description' => 'Trendy high-waisted jeans in dark wash. Size 28, great fit.',
                'category' => 'Bottoms',
                'size' => '28',
                'condition_rating' => 'good',
                'points_required' => 120,
                'image_path' => 'assets/images/placeholder.jpg'
            ],
            [
                'title' => 'Wool Sweater',
                'description' => 'Cozy wool sweater in forest green. Size M, perfect for winter.',
                'category' => 'Tops',
                'size' => 'M',
                'condition_rating' => 'fair',
                'points_required' => 90,
                'image_path' => 'assets/images/placeholder.jpg'
            ],
            [
                'title' => 'Canvas Sneakers',
                'description' => 'White canvas sneakers, lightly used. Size 9, comfortable and stylish.',
                'category' => 'Shoes',
                'size' => '9',
                'condition_rating' => 'good',
                'points_required' => 75,
                'image_path' => 'assets/images/placeholder.jpg'
            ]
        ];

        $inserted_count = 0;
        
        foreach ($test_items as $item) {
            $insert_query = "INSERT INTO items (user_id, title, description, category, size, condition_rating, points_required, image_path, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'available')";
            $insert_stmt = $db->prepare($insert_query);
            
            if ($insert_stmt->execute([
                $_SESSION['user_id'],
                $item['title'],
                $item['description'],
                $item['category'],
                $item['size'],
                $item['condition_rating'],
                $item['points_required'],
                $item['image_path']
            ])) {
                $inserted_count++;
            }
        }
        
        $success_message = "Successfully added $inserted_count test items to your account!";
        
    } catch (Exception $e) {
        $error_message = "Error adding test items: " . $e->getMessage();
    }
}

// Get current user's items count
$user_items_query = "SELECT COUNT(*) as count FROM items WHERE user_id = ?";
$user_items_stmt = $db->prepare($user_items_query);
$user_items_stmt->execute([$_SESSION['user_id']]);
$user_items_count = $user_items_stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Get total items count
$total_items_query = "SELECT COUNT(*) as count FROM items";
$total_items_stmt = $db->prepare($total_items_query);
$total_items_stmt->execute();
$total_items_count = $total_items_stmt->fetch(PDO::FETCH_ASSOC)['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Test Items - ReWear</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .test-container {
            padding-top: 100px;
            min-height: 100vh;
            background: #f8f9fa;
        }
        
        .test-content {
            max-width: 800px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .test-card {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .test-card h1 {
            color: #2ecc71;
            margin-bottom: 20px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }
        
        .stat-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #2ecc71;
        }
        
        .btn-test {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 25px;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            margin: 10px;
        }
        
        .btn-test:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 25px;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            margin: 10px;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }
        
        .message {
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
        }
        
        .success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .item-list {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
            text-align: left;
        }
        
        .item-list h3 {
            color: #333;
            margin-bottom: 15px;
        }
        
        .item-list ul {
            list-style: none;
            padding: 0;
        }
        
        .item-list li {
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        
        .item-list li:last-child {
            border-bottom: none;
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

    <div class="test-container">
        <div class="test-content">
            <div class="test-card">
                <h1><i class="fas fa-flask"></i> Add Test Items</h1>
                <p>This will add 8 sample clothing items to your account for testing purposes.</p>
                
                <?php if ($success_message): ?>
                    <div class="message success">
                        <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($error_message): ?>
                    <div class="message error">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>
                
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $user_items_count; ?></div>
                        <div>Your Items</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $total_items_count; ?></div>
                        <div>Total Items</div>
                    </div>
                </div>
                
                <form method="POST">
                    <button type="submit" name="add_test_items" class="btn-test">
                        <i class="fas fa-plus"></i> Add 8 Test Items
                    </button>
                </form>
                
                <div class="item-list">
                    <h3><i class="fas fa-list"></i> Test Items That Will Be Added:</h3>
                    <ul>
                        <li><strong>Vintage Denim Jacket</strong> - Size M, 150 points</li>
                        <li><strong>Summer Floral Dress</strong> - Size S, 100 points</li>
                        <li><strong>Leather Ankle Boots</strong> - Size 8, 200 points</li>
                        <li><strong>Cotton Hoodie</strong> - Size L, 80 points</li>
                        <li><strong>Silk Scarf</strong> - One Size, 50 points</li>
                        <li><strong>High-Waisted Jeans</strong> - Size 28, 120 points</li>
                        <li><strong>Wool Sweater</strong> - Size M, 90 points</li>
                        <li><strong>Canvas Sneakers</strong> - Size 9, 75 points</li>
                    </ul>
                </div>
                
                <div style="margin-top: 30px;">
                    <a href="dashboard.php" class="btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                    <a href="browse.php" class="btn-secondary">
                        <i class="fas fa-search"></i> Browse Items
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
</body>
</html> 