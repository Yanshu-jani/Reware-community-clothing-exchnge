// Mobile Navigation Toggle
document.addEventListener('DOMContentLoaded', function() {
    const hamburger = document.querySelector('.hamburger');
    const navMenu = document.querySelector('.nav-menu');

    hamburger.addEventListener('click', function() {
        navMenu.classList.toggle('active');
        hamburger.classList.toggle('active');
    });

    // Close mobile menu when clicking on a link
    document.querySelectorAll('.nav-menu a').forEach(link => {
        link.addEventListener('click', () => {
            navMenu.classList.remove('active');
            hamburger.classList.remove('active');
        });
    });
});

// Smooth Scrolling for Navigation Links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

// Load Featured Items Dynamically
function loadFeaturedItems() {
    const featuredContainer = document.getElementById('featured-items');
    
    // Sample featured items data (in real app, this would come from PHP backend)
    const featuredItems = [
        {
            id: 1,
            title: "Vintage Denim Jacket",
            description: "Classic blue denim jacket in excellent condition. Size M.",
            image: "assets/images/placeholder.jpg",
            owner: "Sarah M.",
            points: 150
        },
        {
            id: 2,
            title: "Summer Dress",
            description: "Light floral summer dress, perfect for warm days. Size S.",
            image: "assets/images/placeholder.jpg",
            owner: "Mike R.",
            points: 100
        },
        {
            id: 3,
            title: "Leather Boots",
            description: "Brown leather ankle boots, barely worn. Size 8.",
            image: "assets/images/placeholder.jpg",
            owner: "Emma L.",
            points: 200
        }
    ];

    featuredItems.forEach(item => {
        const itemCard = createItemCard(item);
        featuredContainer.appendChild(itemCard);
    });
}

// Create Item Card Element
function createItemCard(item) {
    const card = document.createElement('div');
    card.className = 'item-card';
    card.innerHTML = `
        <img src="${item.image}" alt="${item.title}" class="item-image">
        <div class="item-content">
            <h3 class="item-title">${item.title}</h3>
            <p class="item-description">${item.description}</p>
            <div class="item-meta">
                <span>By ${item.owner}</span>
                <span>${item.points} points</span>
            </div>
        </div>
    `;
    
    // Add click event to view item details
    card.addEventListener('click', () => {
        window.location.href = `item-details.php?id=${item.id}`;
    });
    
    return card;
}

// Form Validation
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return true;

    const inputs = form.querySelectorAll('input[required], textarea[required], select[required]');
    let isValid = true;

    inputs.forEach(input => {
        if (!input.value.trim()) {
            input.classList.add('error');
            isValid = false;
        } else {
            input.classList.remove('error');
        }
    });

    return isValid;
}

// Search Functionality
function searchItems(query) {
    // This would typically make an AJAX call to PHP backend
    console.log('Searching for:', query);
    // In real implementation, you'd fetch results from PHP
}

// Points System Helper
function formatPoints(points) {
    return points.toLocaleString();
}

// Image Upload Preview
function previewImage(input, previewId) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById(previewId).src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Notification System
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// Add CSS for notifications
const notificationStyles = `
    .notification {
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 20px;
        border-radius: 5px;
        color: white;
        z-index: 10000;
        animation: slideIn 0.3s ease;
    }
    
    .notification-info {
        background: #2ecc71;
    }
    
    .notification-error {
        background: #e74c3c;
    }
    
    .notification-warning {
        background: #f39c12;
    }
    
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    .error {
        border-color: #e74c3c !important;
    }
`;

// Inject notification styles
const styleSheet = document.createElement('style');
styleSheet.textContent = notificationStyles;
document.head.appendChild(styleSheet);

// Initialize the page
document.addEventListener('DOMContentLoaded', function() {
    // Load featured items with a small delay to prioritize critical rendering
    setTimeout(loadFeaturedItems, 100);
    
    // Add scroll effect to navbar with throttling
    let ticking = false;
    window.addEventListener('scroll', function() {
        if (!ticking) {
            requestAnimationFrame(function() {
                const navbar = document.querySelector('.navbar');
                if (window.scrollY > 100) {
                    navbar.classList.add('scrolled');
                } else {
                    navbar.classList.remove('scrolled');
                }
                ticking = false;
            });
            ticking = true;
        }
    });
});

// Export functions for use in other scripts
window.ReWear = {
    showNotification,
    validateForm,
    searchItems,
    formatPoints,
    previewImage
}; 