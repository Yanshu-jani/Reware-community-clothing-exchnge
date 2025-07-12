<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $category = $_POST['category'];
    $size = $_POST['size'];
    $condition = $_POST['condition'];
    $points_required = (int)$_POST['points_required'];
    
    if (empty($title) || empty($description) || empty($category)) {
        $error = 'Please fill in all required fields';
    } elseif ($points_required < 10 || $points_required > 1000) {
        $error = 'Points must be between 10 and 1000';
    } else {
        $database = new Database();
        $db = $database->getConnection();
        
        // Handle image upload
        $image_path = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            $file_type = $_FILES['image']['type'];
            
            if (!in_array($file_type, $allowed_types)) {
                $error = 'Please upload a valid image file (JPEG, PNG, GIF)';
            } elseif ($_FILES['image']['size'] > 5 * 1024 * 1024) { // 5MB limit
                $error = 'Image file size must be less than 5MB';
            } else {
                $upload_dir = 'assets/images/items/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $filename = uniqid() . '_' . time() . '.' . $file_extension;
                $image_path = $upload_dir . $filename;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $image_path)) {
                    // Image uploaded successfully
                } else {
                    $error = 'Failed to upload image. Please try again.';
                }
            }
        }
        
        if (empty($error)) {
            $insert_query = "INSERT INTO items (user_id, title, description, category, size, condition_rating, points_required, image_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $insert_stmt = $db->prepare($insert_query);
            
            if ($insert_stmt->execute([$_SESSION['user_id'], $title, $description, $category, $size, $condition, $points_required, $image_path])) {
                $success = 'Item added successfully! It will be reviewed by our team before going live.';
                // Clear form data
                $_POST = array();
            } else {
                $error = 'Error adding item. Please try again.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Item - ReWear</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .add-item-container {
            padding-top: 100px;
            min-height: 100vh;
            background: #f8f9fa;
        }
        
        .add-item-header {
            background: white;
            padding: 30px 0;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .add-item-content {
            max-width: 800px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .form-card {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .form-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .form-header h1 {
            color: #2ecc71;
            margin-bottom: 10px;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group.full-width {
            grid-column: 1 / -1;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #2ecc71;
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 120px;
        }
        
        .image-upload {
            border: 2px dashed #e1e5e9;
            border-radius: 8px;
            padding: 30px;
            text-align: center;
            transition: border-color 0.3s ease;
            cursor: pointer;
        }
        
        .image-upload:hover {
            border-color: #2ecc71;
        }
        
        .image-upload.dragover {
            border-color: #2ecc71;
            background: #f8fff8;
        }
        
        .image-preview {
            max-width: 200px;
            max-height: 200px;
            margin-top: 15px;
            border-radius: 8px;
            display: none;
        }
        
        .upload-icon {
            font-size: 3rem;
            color: #ddd;
            margin-bottom: 15px;
        }
        
        .btn-submit {
            width: 100%;
            padding: 15px;
            background: #2ecc71;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 20px;
        }
        
        .btn-submit:hover {
            background: #27ae60;
            transform: translateY(-2px);
        }
        
        .btn-submit:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
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
        
        .form-tips {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }
        
        .form-tips h4 {
            color: #2ecc71;
            margin-bottom: 10px;
        }
        
        .form-tips ul {
            margin: 0;
            padding-left: 20px;
            color: #666;
        }
        
        .form-tips li {
            margin-bottom: 5px;
        }
        
        .required {
            color: #e74c3c;
        }
        
        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .form-card {
                padding: 20px;
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
                <li><a href="add-item.php" class="active">Add Item</a></li>
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

    <div class="add-item-container">
        <div class="add-item-header">
            <div class="add-item-content">
                <h1>Add New Item</h1>
                <p>Share your clothing with the community and help promote sustainable fashion!</p>
            </div>
        </div>

        <div class="add-item-content">
            <div class="form-card">
                <div class="form-header">
                    <h1><i class="fas fa-plus-circle"></i> List Your Item</h1>
                    <p>Fill in the details below to add your item to the community</p>
                </div>
                
                <?php if ($error): ?>
                    <div class="error-message"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="success-message"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <form method="POST" action="add-item.php" enctype="multipart/form-data" id="addItemForm">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="title">Item Title <span class="required">*</span></label>
                            <input type="text" id="title" name="title" required 
                                   value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>"
                                   placeholder="e.g., Vintage Denim Jacket">
                        </div>
                        
                        <div class="form-group">
                            <label for="category">Category <span class="required">*</span></label>
                            <select id="category" name="category" required>
                                <option value="">Select Category</option>
                                <option value="Tops" <?php echo (isset($_POST['category']) && $_POST['category'] === 'Tops') ? 'selected' : ''; ?>>Tops</option>
                                <option value="Bottoms" <?php echo (isset($_POST['category']) && $_POST['category'] === 'Bottoms') ? 'selected' : ''; ?>>Bottoms</option>
                                <option value="Dresses" <?php echo (isset($_POST['category']) && $_POST['category'] === 'Dresses') ? 'selected' : ''; ?>>Dresses</option>
                                <option value="Outerwear" <?php echo (isset($_POST['category']) && $_POST['category'] === 'Outerwear') ? 'selected' : ''; ?>>Outerwear</option>
                                <option value="Shoes" <?php echo (isset($_POST['category']) && $_POST['category'] === 'Shoes') ? 'selected' : ''; ?>>Shoes</option>
                                <option value="Accessories" <?php echo (isset($_POST['category']) && $_POST['category'] === 'Accessories') ? 'selected' : ''; ?>>Accessories</option>
                                <option value="Other" <?php echo (isset($_POST['category']) && $_POST['category'] === 'Other') ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="size">Size</label>
                            <select id="size" name="size">
                                <option value="">Select Size</option>
                                <option value="XS" <?php echo (isset($_POST['size']) && $_POST['size'] === 'XS') ? 'selected' : ''; ?>>XS</option>
                                <option value="S" <?php echo (isset($_POST['size']) && $_POST['size'] === 'S') ? 'selected' : ''; ?>>S</option>
                                <option value="M" <?php echo (isset($_POST['size']) && $_POST['size'] === 'M') ? 'selected' : ''; ?>>M</option>
                                <option value="L" <?php echo (isset($_POST['size']) && $_POST['size'] === 'L') ? 'selected' : ''; ?>>L</option>
                                <option value="XL" <?php echo (isset($_POST['size']) && $_POST['size'] === 'XL') ? 'selected' : ''; ?>>XL</option>
                                <option value="XXL" <?php echo (isset($_POST['size']) && $_POST['size'] === 'XXL') ? 'selected' : ''; ?>>XXL</option>
                                <option value="One Size" <?php echo (isset($_POST['size']) && $_POST['size'] === 'One Size') ? 'selected' : ''; ?>>One Size</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="condition">Condition</label>
                            <select id="condition" name="condition">
                                <option value="excellent" <?php echo (isset($_POST['condition']) && $_POST['condition'] === 'excellent') ? 'selected' : ''; ?>>Excellent</option>
                                <option value="good" <?php echo (isset($_POST['condition']) && $_POST['condition'] === 'good') ? 'selected' : ''; ?>>Good</option>
                                <option value="fair" <?php echo (isset($_POST['condition']) && $_POST['condition'] === 'fair') ? 'selected' : ''; ?>>Fair</option>
                                <option value="poor" <?php echo (isset($_POST['condition']) && $_POST['condition'] === 'poor') ? 'selected' : ''; ?>>Poor</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="points_required">Points Required <span class="required">*</span></label>
                            <input type="number" id="points_required" name="points_required" required 
                                   min="10" max="1000" 
                                   value="<?php echo isset($_POST['points_required']) ? htmlspecialchars($_POST['points_required']) : '100'; ?>"
                                   placeholder="100">
                            <small style="color: #666;">Points between 10-1000</small>
                        </div>
                        
                        <div class="form-group full-width">
                            <label for="description">Description <span class="required">*</span></label>
                            <textarea id="description" name="description" required 
                                      placeholder="Describe your item in detail..."><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                        </div>
                        
                        <div class="form-group full-width">
                            <label for="image">Item Image</label>
                            <div class="image-upload" onclick="document.getElementById('image').click()">
                                <div class="upload-icon">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                </div>
                                <p>Click to upload image or drag and drop</p>
                                <p style="font-size: 0.9rem; color: #666;">JPEG, PNG, GIF up to 5MB</p>
                                <input type="file" id="image" name="image" accept="image/*" style="display: none;">
                                <img id="imagePreview" class="image-preview" alt="Preview">
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn-submit" id="submitBtn">
                        <i class="fas fa-plus"></i> Add Item
                    </button>
                </form>
                
                <div class="form-tips">
                    <h4><i class="fas fa-lightbulb"></i> Tips for Better Listings</h4>
                    <ul>
                        <li>Use clear, descriptive titles that highlight key features</li>
                        <li>Include detailed descriptions about condition, fit, and style</li>
                        <li>Upload high-quality images in good lighting</li>
                        <li>Set reasonable point values based on item quality and demand</li>
                        <li>Be honest about the condition of your items</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
    <script>
        // Image preview functionality
        document.getElementById('image').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const preview = document.getElementById('imagePreview');
            
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });
        
        // Drag and drop functionality
        const uploadArea = document.querySelector('.image-upload');
        
        uploadArea.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.classList.add('dragover');
        });
        
        uploadArea.addEventListener('dragleave', function(e) {
            e.preventDefault();
            this.classList.remove('dragover');
        });
        
        uploadArea.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('dragover');
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                document.getElementById('image').files = files;
                const event = new Event('change');
                document.getElementById('image').dispatchEvent(event);
            }
        });
        
        // Form validation
        document.getElementById('addItemForm').addEventListener('submit', function(e) {
            const title = document.getElementById('title').value.trim();
            const description = document.getElementById('description').value.trim();
            const category = document.getElementById('category').value;
            const points = parseInt(document.getElementById('points_required').value);
            
            if (!title || !description || !category) {
                e.preventDefault();
                ReWear.showNotification('Please fill in all required fields', 'error');
                return false;
            }
            
            if (points < 10 || points > 1000) {
                e.preventDefault();
                ReWear.showNotification('Points must be between 10 and 1000', 'error');
                return false;
            }
            
            // Show loading state
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding Item...';
        });
    </script>
</body>
</html> 