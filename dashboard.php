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
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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
};

// Dashboard content
function loadDashboardContent() {
    console.log("Loading dashboard content...");

document.getElementById('mainContent').innerHTML = `
    <div style="padding: 20px;">
        <h1>Dashboard</h1>
        <div style="margin-top: 30px; background-color: white; padding: 20px; border-radius: 8px;">
            <h2>Users Who Placed Orders</h2>
            <table id="usersTable" border="1" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr>
                        <th>User ID</th>
                        <th>Username</th>
                        <th>Email</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
            <div id="usersError" style="color: red; display: none;"></div>
        </div>

        <div style="margin-top: 30px; background-color: white; padding: 20px; border-radius: 8px;">
            <h2>Top Selling Products</h2>
            <div id="chartContainer" style="width: 250px; height: 250px; margin: 0 auto;">
                <canvas id="topSellingChart"></canvas>
            </div>
            <div id="chartError" style="color: red; display: none;"></div>
        </div>

        <div style="margin-top: 30px; background-color: white; padding: 20px; border-radius: 8px;">
            <h2>Orders Per Day</h2>
            <div id="ordersPerDayChartContainer" style="width: 250px; height: 250px; margin: 0 auto;">
                <canvas id="ordersPerDayChart"></canvas>
            </div>
            <div id="ordersError" style="color: red; display: none;"></div>
        </div>
    </div>
`;

loadTopSellingProductsChart();
loadOrdersPerDayChart();
loadUsersWhoOrdered();

}

function loadTopSellingProductsChart() {
    const canvas = document.getElementById('topSellingChart');
    const errorElement = document.getElementById('chartError');

    if (!canvas) {
        console.error("Canvas element not found!");
        errorElement.textContent = "Chart container not initialized properly";
        errorElement.style.display = 'block';
        return;
    }

    console.log("Fetching chart data...");

    fetch('orders_action.php')  // Corrected from orders_action.php to orders.php
        .then(response => {
            console.log("Response received", response);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log("Data received:", data);

            if (!data.success || !data.data || data.data.length === 0) {
                throw new Error(data.message || 'No data available');
            }

            errorElement.style.display = 'none';

            // Safely destroy previous chart
            if (window.topSellingChart && window.topSellingChart instanceof Chart) {
                window.topSellingChart.destroy();
            }

            const ctx = canvas.getContext('2d');
            window.topSellingChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: data.data.map(item => item.product_name),
                    datasets: [{
                        data: data.data.map(item => item.total_sold),
                        backgroundColor: [
                            '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#FF9F40'
                        ]
                    }]
                },
                plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                boxWidth: 15, // Smaller legend box
                                font: {
                                    size: 10 // Smaller font
                                }
                            }
                        }
                    }
            });

            console.log("Chart created successfully");
        })
        .catch(error => {
            console.error("Error loading chart:", error);
            errorElement.textContent = `Error loading chart: ${error.message}`;
            errorElement.style.display = 'block';
        });
}

function loadOrdersPerDayChart() {
    const canvas = document.getElementById('ordersPerDayChart');
    const errorElement = document.getElementById('ordersError');

    if (!canvas) {
        console.error("Canvas element not found!");
        errorElement.textContent = "Chart container not initialized properly";
        errorElement.style.display = 'block';
        return;
    }

    console.log("Fetching daily orders data...");

    fetch('orders_action.php?chart_type=orders_per_day')
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (!data.success || !data.data || data.data.length === 0) {
                throw new Error(data.message || 'No data available');
            }

            errorElement.style.display = 'none';

            // Safely destroy previous chart
            if (window.ordersPerDayChart && window.ordersPerDayChart instanceof Chart) {
                window.ordersPerDayChart.destroy();
            }

            const ctx = canvas.getContext('2d');
            window.ordersPerDayChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.data.map(item => item.order_date),
                    datasets: [{
                        label: 'Total Orders',
                        data: data.data.map(item => item.total_orders),
                        borderColor: '#36A2EB',
                        fill: false
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        x: { type: 'category' },
                        y: { beginAtZero: true }
                    }
                }
            });

            console.log("Orders per day chart created successfully");
        })
        .catch(error => {
            console.error("Error loading daily orders chart:", error);
            errorElement.textContent = `Error loading chart: ${error.message}`;
            errorElement.style.display = 'block';
        });
}
// Fetch the current user profile and load the content
function loadProfileContent() {
  fetch('user_info.php')
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        const user = data.user;
        const initials = user.username ? user.username.charAt(0).toUpperCase() : 'U';

        document.getElementById('mainContent').innerHTML = `
            <div style="padding: 20px;">
                <h1>User Profile</h1>
                <div style="background-color: white; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); max-width: 600px; margin: 0 auto;">
                    <div style="display: flex; align-items: center; margin-bottom: 20px;">
                        <div style="width: 100px; height: 100px; background-color: #3498db; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 40px; margin-right: 20px;">
                            ${initials}
                        </div>
                        <div>
                            <h2 style="margin: 0;">${user.username}</h2>
                            <p style="margin: 5px 0; color: #777;">${user.role}</p>
                        </div>
                    </div>
                    <form id="profileForm">
                        <div style="margin-bottom: 15px;">
                            <label style="display: block; margin-bottom: 5px; font-weight: bold;">Email</label>
                            <input type="email" value="${user.email}" style="width: 100%; padding: 8px; border-radius: 4px; border: 1px solid #ddd;">
                        </div>
                        <div style="margin-bottom: 15px;">
                            <label style="display: block; margin-bottom: 5px; font-weight: bold;">Full Name</label>
                            <input type="text" value="${user.user_name}" style="width: 100%; padding: 8px; border-radius: 4px; border: 1px solid #ddd;">
                        </div>
                        <div style="margin-bottom: 15px;">
                            <label style="display: block; margin-bottom: 5px; font-weight: bold;">Password</label>
                            <input type="password" value="" placeholder="Enter new password" style="width: 100%; padding: 8px; border-radius: 4px; border: 1px solid #ddd;">
                        </div>
                        <button type="button" onclick="updateProfile()" style="padding: 8px 16px; background-color: #3498db; color: white; border: none; border-radius: 4px; cursor: pointer;">
                            Update Profile
                        </button>
                    </form>
                </div>
            </div>
        `;
      } else {
        document.getElementById('mainContent').innerHTML = `
          <p>${data.message}</p>
        `;
      }
    })
    .catch(err => {
      console.error("Error fetching user data:", err);
      document.getElementById('mainContent').innerHTML = `
        <p>Error loading profile. Please try again later.</p>
      `;
    });
}


function loadUsersWhoOrdered() {
    fetch('orders_action.php?users_who_ordered=1')
        .then(response => response.json())
        .then(data => {
            console.log(data, "dataxxxxxxxxx")
            const tableBody = document.querySelector('#usersTable tbody');
            const errorElement = document.getElementById('usersError');

            if (!data.success || !Array.isArray(data.data)) {
                throw new Error(data.message || 'No user data found');
            }

            tableBody.innerHTML = '';

            data.data.forEach(user => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${user.id}</td>
                    <td>${user.username}</td>
                    <td>${user.email}</td>
                `;
                tableBody.appendChild(row);
            });

            errorElement.style.display = 'none';
        })
        .catch(error => {
            console.error("Error loading users:", error);
            const errorElement = document.getElementById('usersError');
            errorElement.textContent = `Error loading users: ${error.message}`;
            errorElement.style.display = 'block';
        });
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