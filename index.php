<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ReWear - Community Clothing Exchange</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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
                <li><a href="#about">About</a></li>
                <li><a href="login.php" class="btn-login">Login</a></li>
                <li><a href="register.php" class="btn-signup">Sign Up</a></li>
            </ul>
            <div class="hamburger">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero" style="background: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)), url('assets/images/hero-bg.jpg') center/cover no-repeat; color: white; padding: 120px 0 100px 0; position: relative; min-height: 600px;">
        <div class="container" style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 50px; max-width: 1200px; margin: 0 auto; padding: 0 20px;">
            <div class="hero-content" style="flex: 1; min-width: 320px;">
                <h1 style="font-size: 3.5rem; font-weight: bold; margin-bottom: 25px; line-height: 1.1; text-shadow: 2px 2px 4px rgba(0,0,0,0.5);">Give Your Clothes a Second Life</h1>
                <p style="font-size: 1.4rem; margin-bottom: 40px; opacity: 0.95; line-height: 1.6; text-shadow: 1px 1px 3px rgba(0,0,0,0.4);">Join our community clothing exchange platform. Swap, share, and reduce textile waste together while building a sustainable future.</p>
                <div style="display: flex; gap: 25px; flex-wrap: wrap;">
                    <a href="browse.php" class="btn-primary" style="font-size: 1.2rem; padding: 18px 35px; background: #27ae60; color: white; text-decoration: none; border-radius: 10px; font-weight: 600; transition: all 0.3s ease; box-shadow: 0 6px 20px rgba(39,174,96,0.4); display: inline-block;">Start Swapping</a>
                    <a href="add-item.php" class="btn-secondary" style="font-size: 1.2rem; padding: 18px 35px; background: transparent; color: white; text-decoration: none; border: 3px solid white; border-radius: 10px; font-weight: 600; transition: all 0.3s ease; display: inline-block;">List an Item</a>
                </div>
            </div>
            <div class="hero-image" style="flex: 1; min-width: 280px; text-align: center;">
                <i class="fas fa-recycle" style="font-size: 9rem; color: rgba(255,255,255,0.95); margin-bottom: 20px; text-shadow: 3px 3px 6px rgba(0,0,0,0.4);"></i>
                <div style="font-size: 1.6rem; opacity: 0.9; font-weight: 600; text-shadow: 1px 1px 3px rgba(0,0,0,0.4);">Sustainable Fashion</div>
            </div>
        </div>
    </section>

    <!-- Search Bar -->
    <section class="search-section" style="background: #f8f9fa; padding: 40px 0 20px 0;">
        <div class="container">
            <form class="search-form" action="browse.php" method="get" style="max-width: 700px; margin: 0 auto;">
                <input type="text" name="q" placeholder="Search for clothing, brands, or categories..." class="search-input">
                <button type="submit" class="btn-primary"><i class="fas fa-search"></i> Search</button>
            </form>
        </div>
    </section>

    <!-- Categories Section -->
    <section class="categories-section" style="background: #fff; padding: 40px 0 30px 0; border-bottom: 1px solid #eee;">
        <div class="container">
            <h2 style="text-align:center; color:#27ae60; margin-bottom: 30px;">Categories</h2>
            <div class="categories-grid" style="display: grid; grid-template-columns: repeat(2, 1fr); grid-template-rows: repeat(3, 1fr); gap: 24px; max-width: 600px; margin: 0 auto;">
                <a href="browse.php?category=Tops" class="category-card">Tops</a>
                <a href="browse.php?category=Bottoms" class="category-card">Bottoms</a>
                <a href="browse.php?category=Dresses" class="category-card">Dresses</a>
                <a href="browse.php?category=Outerwear" class="category-card">Outerwear</a>
                <a href="browse.php?category=Shoes" class="category-card">Shoes</a>
                <a href="browse.php?category=Accessories" class="category-card">Accessories</a>
            </div>
        </div>
    </section>

    <!-- How It Works -->
    <section class="how-it-works">
        <div class="container">
            <h2>How It Works</h2>
            <div class="steps">
                <div class="step">
                    <i class="fas fa-upload"></i>
                    <h3>1. List Your Item</h3>
                    <p>Upload photos and details of clothes you want to exchange</p>
                </div>
                <div class="step">
                    <i class="fas fa-search"></i>
                    <h3>2. Browse & Choose</h3>
                    <p>Find items you like and request swaps or redeem with points</p>
                </div>
                <div class="step">
                    <i class="fas fa-exchange-alt"></i>
                    <h3>3. Swap & Share</h3>
                    <p>Complete the exchange and give clothes a new home</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Product Listings -->
    <section class="featured" style="padding: 40px 0 60px 0; background: #f8f9fa;">
        <div class="container">
            <h2 style="text-align:center; color:#27ae60; margin-bottom: 30px;">Product Listings</h2>
            <div class="items-grid" id="featured-items">
                <?php
                $demo_items = [
                    // All items removed
                ];
                $colors = ['#183153','#f7a600','#217693','#1ccfc9'];
                foreach ($demo_items as $i => $item):
                    $color = $colors[$i%4];
                ?>
                <div class="infographic-card" style="margin:0;">
                    <div class="infographic-image" style="width:80px;height:80px;display:flex;align-items:center;justify-content:center;margin:32px auto 10px auto;background:#f6f8fa;border-radius:16px;border:2px solid #e0e4ea;box-shadow:0 2px 8px rgba(0,0,0,0.08);color:<?php echo $color; ?>;font-size:2.2rem;">
                        <i class="fas <?php echo $item['icon']; ?>"></i>
                    </div>
                    <div class="infographic-content">
                        <div class="infographic-title" style="color: <?php echo $color; ?>;">
                            <?php echo htmlspecialchars($item['title']); ?>
                        </div>
                        <div class="infographic-desc">
                            <?php echo htmlspecialchars($item['desc']); ?>
                        </div>
                        <span style="display:block; color:#222; font-weight:600; margin-bottom:8px;"> <?php echo $item['points']; ?> pts</span>
                        <a href="item-details.php?id=<?php echo $item['id']; ?>" class="infographic-btn" style="background: <?php echo $color; ?>; margin-top:8px;"><i class="fas fa-eye"></i><span>View</span></a>
                    </div>
                    <div class="infographic-bar" style="background: <?php echo $color; ?>;"></div>
                </div> 
                <?php endforeach; ?>
                <!-- End example cards -->
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>ReWear</h3>
                    <p>Promoting sustainable fashion through community exchange.</p>
                </div>
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="browse.php">Browse</a></li>
                        <li><a href="login.php">Login</a></li>
                        <li><a href="register.php">Sign Up</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Contact</h4>
                    <p>Email: info@rewear.com</p>
                    <p>Follow us on social media</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 ReWear. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
    <style>
        .search-section { background: #f8f9fa; padding: 30px 0 10px 0; }
        .search-form { display: flex; max-width: 600px; margin: 0 auto; }
        .search-input { flex: 1; padding: 12px; border: 1px solid #ddd; border-radius: 5px 0 0 5px; font-size: 1rem; }
        .search-form .btn-primary { border-radius: 0 5px 5px 0; }
        .categories-section { background: #fff; padding: 10px 0; border-bottom: 1px solid #eee; }
        .categories-bar { display: flex; gap: 10px; justify-content: center; flex-wrap: wrap; }
        .category-btn { background: #f8f9fa; border: 1px solid #ddd; border-radius: 20px; padding: 8px 18px; font-size: 1rem; cursor: pointer; transition: background 0.2s; }
        .category-btn:hover { background: #27ae60; color: #fff; border-color: #27ae60; }
        .item-card, .item-image, .item-content, .item-title, .item-points {
            /* Old two-tone card styles removed. All product boxes now use .infographic-card and related classes only. */
        }
        @media (max-width: 768px) {
            .search-form { flex-direction: column; }
            .search-input, .search-form .btn-primary { border-radius: 5px; margin-bottom: 10px; }
            .categories-bar { flex-direction: column; gap: 6px; }
            .item-image { height: 80px; }
        }
        .categories-grid {
            margin-bottom: 0;
            display: grid !important;
            grid-template-columns: repeat(4, 1fr) !important;
            grid-template-rows: repeat(2, 1fr) !important;
            gap: 18px;
            max-width: 1000px;
            margin: 0 auto;
        }
        @media (max-width: 900px) {
            .categories-grid { grid-template-columns: repeat(2, 1fr) !important; grid-template-rows: repeat(4, 1fr) !important; gap: 12px; }
        }
        @media (max-width: 600px) {
            .categories-grid { grid-template-columns: 1fr !important; grid-template-rows: repeat(8, 1fr) !important; gap: 8px; }
        }
        .category-card {
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8f9fa;
            border: 2px solid #e1e5e9;
            border-radius: 16px;
            font-size: 1rem;
            color: #27ae60;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s;
            min-height: 90px;
            height: 90px;
            box-shadow: 0 2px 10px rgba(39,174,96,0.06);
            margin: 0;
        }
        .category-card:hover {
            background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
            color: #fff;
            border-color: #27ae60;
            box-shadow: 0 8px 24px rgba(39,174,96,0.10);
        }
        .items-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 18px;
            margin-top: 30px;
        }
        @media (max-width: 900px) {
            .items-grid { grid-template-columns: repeat(2, 1fr); gap: 12px; }
        }
        @media (max-width: 600px) {
            .items-grid { grid-template-columns: 1fr; gap: 8px; }
        }
        .item-card {
            background: #fff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 5px 18px rgba(0,0,0,0.10);
            transition: transform 0.3s, box-shadow 0.3s;
            min-width: 0;
            display: flex;
            flex-direction: column;
            align-items: stretch;
            height: 260px;
            margin: 0;
        }
        .item-card:hover {
            transform: translateY(-5px) scale(1.03);
            box-shadow: 0 12px 32px rgba(39,174,96,0.13);
        }
        .item-image {
            width: 100%;
            height: 110px;
            object-fit: cover;
            font-size: 2rem !important;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #eafaf1;
        }
        .item-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 10px 8px 8px 8px;
            text-align: center;
        }
        .item-title {
            font-size: 1rem;
            margin-bottom: 4px;
            color: #333;
            font-weight: 600;
        }
        .item-points {
            background: #27ae60;
            color: #fff;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 0.85rem;
            margin-top: 4px;
            display: inline-block;
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
            min-height: 220px;
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
            margin-bottom: 12px;
        }
        .infographic-bar {
            width: 48px;
            height: 10px;
            border-radius: 8px 8px 16px 16px;
            margin: 14px auto 0 auto;
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
</body>
</html> 