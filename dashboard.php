<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            display: flex;
        }
        .sidebar {
            width: 220px;
            background-color: #2c3e50;
            color: white;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            padding-top: 60px;
            animation: slideIn 0.5s ease-out;
            z-index: 100;
        }
        @keyframes slideIn {
            from { transform: translateX(-100%); }
            to { transform: translateX(0); }
        }
        .sidebar a {
            display: block;
            padding: 15px;
            color: white;
            text-decoration: none;
            transition: background-color 0.3s, padding-left 0.3s;
        }
        .sidebar a:hover {
            background-color: #34495e;
            padding-left: 25px;
        }
        .sidebar a.active {
            background-color: #3498db;
            border-left: 4px solid #fff;
        }
        .main-content {
            margin-left: 220px;
            padding: 20px;
            width: calc(100% - 220px);
            margin-top: 60px; /* Added margin to account for navbar */
        }
        .navbar {
            position: fixed;
            top: 0;
            left: 220px;
            height: 60px;
            width: calc(100% - 220px);
            background-color: #2980b9;
            color: white;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            z-index: 99;
        }
        .navbar .welcome {
            font-weight: bold;
        }
        .logout {
            color: white;
            text-decoration: none;
        }
        .logout:hover {
            text-decoration: underline;
        }
        .content-loading {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 300px;
        }
        /* Added styling for page title in navbar */
        .page-title {
            font-size: 20px;
            font-weight: bold;
        }
    </style>
    <!-- Add Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>

<div class="sidebar">
    <a href="#dashboard" class="nav-link active" data-page="dashboard">
        <i class="fas fa-tachometer-alt" style="margin-right: 10px;"></i>Dashboard
    </a>
    <a href="#categories" class="nav-link" data-page="categories">
        <i class="fas fa-list" style="margin-right: 10px;"></i>Categories
    </a>
    <a href="#products" class="nav-link" data-page="products">
        <i class="fas fa-box" style="margin-right: 10px;"></i>Products
    </a>
    <a href="#profile" class="nav-link" data-page="profile">
        <i class="fas fa-user" style="margin-right: 10px;"></i>Profile
    </a>
    <a href="#settings" class="nav-link" data-page="settings">
        <i class="fas fa-cog" style="margin-right: 10px;"></i>Settings
    </a>
    <a href="logout.php">
        <i class="fas fa-sign-out-alt" style="margin-right: 10px;"></i>Logout
    </a>
</div>

<div class="navbar">
    <div class="page-title" id="currentPageTitle">Dashboard</div>
    <div class="welcome">
        Welcome, <?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?>
        <a href="logout.php" class="logout">
            <i class="fas fa-sign-out-alt" style="margin-left: 10px;"></i>
        </a>
    </div>
</div>

<div class="main-content" id="mainContent">
    <div class="content-loading">
        <i class="fas fa-spinner fa-spin fa-3x"></i>
    </div>
</div>


<script  src="categories.js"></script>
<script  src="products.js"></script>
<script>
// Page routing system
const routes = {
    dashboard: {
        title: 'Dashboard',
        load: loadDashboardContent
    },
    categories: {
        title: 'Categories',
        load: loadCategoriesContent
    },
    products: {
        title: 'Products',
        load: loadProductsContent
    },
    profile: {
        title: 'Profile',
        load: loadProfileContent
    },
    settings: {
        title: 'Settings',
        load: loadSettingsContent
    }
};

// Dashboard content
function loadDashboardContent() {
    document.getElementById('mainContent').innerHTML = `
        <div style="padding: 20px;">
            <h1>Dashboard</h1>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-top: 20px;">
                <div style="background-color: #2ecc71; color: white; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
                    <h3>Total Categories</h3>
                    <p style="font-size: 24px; font-weight: bold;">12</p>
                </div>
                <div style="background-color: #3498db; color: white; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
                    <h3>Total Products</h3>
                    <p style="font-size: 24px; font-weight: bold;">48</p>
                </div>
                <div style="background-color: #9b59b6; color: white; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
                    <h3>Total Users</h3>
                    <p style="font-size: 24px; font-weight: bold;">5</p>
                </div>
            </div>
            <div style="margin-top: 30px; background-color: white; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
                <h2>Recent Activity</h2>
                <ul style="list-style-type: none; padding: 0;">
                    <li style="padding: 10px 0; border-bottom: 1px solid #eee;">New category "Electronics" added</li>
                    <li style="padding: 10px 0; border-bottom: 1px solid #eee;">User profile updated</li>
                    <li style="padding: 10px 0; border-bottom: 1px solid #eee;">Category "Books" updated</li>
                    <li style="padding: 10px 0;">2 new products added</li>
                </ul>
            </div>
        </div>
    `;
}

// Profile content placeholder
function loadProfileContent() {
    document.getElementById('mainContent').innerHTML = `
        <div style="padding: 20px;">
            <h1>User Profile</h1>
            <div style="background-color: white; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); max-width: 600px; margin: 0 auto;">
                <div style="display: flex; align-items: center; margin-bottom: 20px;">
                    <div style="width: 100px; height: 100px; background-color: #3498db; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 40px; margin-right: 20px;">
                        U
                    </div>
                    <div>
                        <h2 style="margin: 0;">User Name</h2>
                        <p style="margin: 5px 0; color: #777;">Administrator</p>
                    </div>
                </div>
                <form>
                    <div style="margin-bottom: 15px;">
                        <label style="display: block; margin-bottom: 5px; font-weight: bold;">Email</label>
                        <input type="email" value="user@example.com" style="width: 100%; padding: 8px; border-radius: 4px; border: 1px solid #ddd;">
                    </div>
                    <div style="margin-bottom: 15px;">
                        <label style="display: block; margin-bottom: 5px; font-weight: bold;">Full Name</label>
                        <input type="text" value="User Name" style="width: 100%; padding: 8px; border-radius: 4px; border: 1px solid #ddd;">
                    </div>
                    <div style="margin-bottom: 15px;">
                        <label style="display: block; margin-bottom: 5px; font-weight: bold;">Password</label>
                        <input type="password" value="********" style="width: 100%; padding: 8px; border-radius: 4px; border: 1px solid #ddd;">
                    </div>
                    <button type="button" style="padding: 8px 16px; background-color: #3498db; color: white; border: none; border-radius: 4px; cursor: pointer;">
                        Update Profile
                    </button>
                </form>
            </div>
        </div>
    `;
}

// Settings content placeholder
function loadSettingsContent() {
    document.getElementById('mainContent').innerHTML = `
        <div style="padding: 20px;">
            <h1>Settings</h1>
            <div style="background-color: white; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
                <h2>Application Settings</h2>
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: bold;">Site Title</label>
                    <input type="text" value="Admin Dashboard" style="width: 100%; max-width: 400px; padding: 8px; border-radius: 4px; border: 1px solid #ddd;">
                </div>
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: bold;">Theme</label>
                    <select style="width: 100%; max-width: 400px; padding: 8px; border-radius: 4px; border: 1px solid #ddd;">
                        <option>Default</option>
                        <option>Dark</option>
                        <option>Light</option>
                    </select>
                </div>
                <div style="display: flex; align-items: center; margin-bottom: 15px;">
                    <input type="checkbox" id="notifications" checked style="margin-right: 10px;">
                    <label for="notifications">Enable Notifications</label>
                </div>
                <button type="button" style="padding: 8px 16px; background-color: #3498db; color: white; border: none; border-radius: 4px; cursor: pointer;">
                    Save Settings
                </button>
            </div>
        </div>
    `;
}

// Navigate to a specific page
function navigateTo(page) {
    if (routes[page]) {
        // Update URL hash
        window.location.hash = page;

        // Update active class in sidebar
        document.querySelectorAll('.nav-link').forEach(link => {
            link.classList.remove('active');
        });
        document.querySelector(`.nav-link[data-page="${page}"]`).classList.add('active');

        // Update page title
        document.getElementById('currentPageTitle').textContent = routes[page].title;

        // Load content
        routes[page].load();

        // Scroll to top
        window.scrollTo(0, 0);
    }
}

// Handle URL hash changes
function handleHashChange() {
    const hash = window.location.hash.substring(1) || 'dashboard';
    navigateTo(hash);
}

// Handle navigation link clicks
document.querySelectorAll('.nav-link').forEach(link => {
    link.addEventListener('click', function(e) {
        e.preventDefault();
        const page = this.getAttribute('data-page');
        navigateTo(page);
    });
});

// Initialize the app
window.addEventListener('hashchange', handleHashChange);
window.addEventListener('DOMContentLoaded', handleHashChange);
</script>

</body>
</html>