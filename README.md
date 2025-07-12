# ReWear - Community Clothing Exchange Platform

A sustainable fashion platform that enables users to exchange clothing items, reducing textile waste and promoting eco-friendly behavior.

## 🚀 Features

- **User Authentication**: Secure login/registration system
- **Item Management**: Upload, browse, and manage clothing items
- **Swap System**: Direct item swapping and points-based redemption
- **Admin Panel**: Content moderation and approval system
- **Responsive Design**: Mobile-friendly interface
- **Points System**: Gamified exchange mechanism

## 🛠️ Tech Stack

- **Frontend**: HTML5, CSS3, JavaScript (Vanilla)
- **Backend**: PHP 7.4+
- **Database**: MySQL
- **Server**: XAMPP (Apache + MySQL)
- **Icons**: Font Awesome 6.0

## 📋 Prerequisites

- XAMPP installed and running
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Modern web browser

## ⚡ Quick Start

### 1. Setup XAMPP
1. Download and install [XAMPP](https://www.apachefriends.org/)
2. Start Apache and MySQL services
3. Place project in `htdocs` folder

### 2. Database Setup
1. Open phpMyAdmin (http://localhost/phpmyadmin)
2. The database will be created automatically on first run
3. Tables will be generated automatically

### 3. Project Structure
```
Reware-community-clothing-exchnge/
├── index.php              # Landing page
├── login.php              # User login
├── register.php           # User registration
├── dashboard.php          # User dashboard (to be created)
├── browse.php             # Browse items (to be created)
├── add-item.php          # Add new item (to be created)
├── config/
│   └── database.php      # Database configuration
├── assets/
│   ├── css/
│   │   └── style.css     # Main stylesheet
│   ├── js/
│   │   └── main.js       # Main JavaScript
│   └── images/           # Image assets
└── README.md
```

### 4. Access the Application
- Open your browser
- Navigate to: `http://localhost/Reware-community-clothing-exchnge/`
- The landing page should load with the ReWear theme

## 🎨 Design Features

### Color Scheme
- **Primary Green**: #2ecc71 (Sustainable theme)
- **Secondary Purple**: #667eea to #764ba2 (Gradient)
- **Neutral**: #333, #666, #f8f9fa

### Typography
- **Font Family**: Segoe UI, Tahoma, Geneva, Verdana, sans-serif
- **Responsive**: Mobile-first design approach

### Components
- **Navigation**: Fixed header with smooth scrolling
- **Hero Section**: Gradient background with call-to-action buttons
- **Cards**: Hover effects and modern styling
- **Forms**: Clean, accessible form design

## 🔧 Configuration

### Database Settings
Edit `config/database.php` if needed:
```php
private $host = 'localhost';
private $db_name = 'rewear_db';
private $username = 'root';
private $password = '';
```

### File Upload Settings
- Create `assets/images/` directory for item images
- Ensure proper write permissions
- Maximum file size: 5MB (configurable in PHP)

## 📱 Responsive Design

The platform is fully responsive with breakpoints:
- **Desktop**: 1200px+
- **Tablet**: 768px - 1199px
- **Mobile**: < 768px

## 🔒 Security Features

- **Password Hashing**: bcrypt with PHP password_hash()
- **SQL Injection Protection**: Prepared statements
- **XSS Protection**: Input sanitization
- **Session Management**: Secure session handling

## 🚀 Next Steps

### To Complete the Platform:

1. **Create Dashboard** (`dashboard.php`)
   - User profile display
   - Points balance
   - User's items list
   - Swap history

2. **Create Browse Page** (`browse.php`)
   - Item grid with filters
   - Search functionality
   - Category filtering

3. **Create Add Item Page** (`add-item.php`)
   - Image upload
   - Item details form
   - Admin approval system

4. **Create Item Details Page** (`item-details.php`)
   - Item information display
   - Swap/redemption buttons
   - Owner contact info

5. **Create Admin Panel** (`admin/`)
   - Item approval system
   - User management
   - Content moderation

## 🐛 Troubleshooting

### Common Issues:

1. **Database Connection Error**
   - Ensure MySQL is running in XAMPP
   - Check database credentials in `config/database.php`

2. **Images Not Loading**
   - Create `assets/images/` directory
   - Check file permissions

3. **PHP Errors**
   - Ensure PHP is enabled in XAMPP
   - Check error logs in XAMPP control panel

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## 📞 Support

For support or questions:
- Email: info@rewear.com
- Create an issue in the repository

---

**Built with ❤️ for sustainable fashion and community exchange** 