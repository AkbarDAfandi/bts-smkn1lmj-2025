<?php
session_start();
require_once __DIR__ . '/../../config.php';

// Cek apakah user sudah login dan memiliki role admin
if (!isset($_SESSION['admin_id']) || $_SESSION['admin_role'] !== 'admin') {
    $_SESSION['error'] = "Anda tidak memiliki akses ke halaman ini";
    header("Location: " . BASE_URL . "index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modern Admin Dashboard</title>
    <link rel="stylesheet" href="../public/css/all.min.css">
    <link rel="stylesheet" href="../public/css/sidebar.css"> 
    <link rel="stylesheet" href="../public/css/dashboard.css">
    <style>
    @font-face {
        font-family: 'Poppins';
        font-style: normal;
        font-weight: 300;
        src: url('../public/fonts/poppins/Poppins-Light.ttf') format('truetype');
    }
    @font-face {
        font-family: 'Poppins';
        font-style: normal;
        font-weight: 400;
        src: url('../public/fonts/poppins/Poppins-Regular.ttf') format('truetype');
    }
    @font-face {
        font-family: 'Poppins';
        font-style: normal;
        font-weight: 500;
        src: url('../public/fonts/poppins/Poppins-Medium.ttf') format('truetype');
    }
    @font-face {
        font-family: 'Poppins';
        font-style: normal;
        font-weight: 600;
        src: url('../public/fonts/poppins/Poppins-SemiBold.ttf') format('truetype');
    }
    @font-face {
        font-family: 'Poppins';
        font-style: normal;
        font-weight: 700;
        src: url('../public/fonts/poppins/Poppins-Bold.ttf') format('truetype');
    }

    
    
    body {
        font-family: 'Poppins', sans-serif;
    }
</style>
</head>
<body>

    <?php include '../views/layout/sidebar.php'?>
    
    <div class="main-content">
        <div class="header">
            <h2 class="greeting">Good Morning, Jason!</h2>
            <div class="user-info">
                <div class="search-bar">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Search...">
                </div>
                <i class="far fa-bell"></i>
                <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="User Profile">
            </div>
        </div>
        
        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-icon blue">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-info">
                    <h3>236</h3>
                    <p>New Clients</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon green">
                    <i class="fas fa-book"></i>
                </div>
                <div class="stat-info">
                    <h3>$18,306</h3>
                    <p>Earnings of Month</p>
                </div>
            </div>
            
            <div class="stat-card">

<div class="stat-icon orange">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="stat-info">
                    <h3>13.5%</h3>
                    <p>Growth Rate</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon red">
                    <i class="fas fa-project-diagram"></i>
                </div>
                <div class="stat-info">
                    <h3>1,538</h3>
                    <p>New Projects</p>
                </div>
            </div>
        </div>
        
        
        <div class="chart">
            <div class="chart-header">
                <h3>Earnings by Location</h3>
                <div class="chart-actions">
                    <button class="active">All Regions</button>
                    <button>Top 5</button>
                </div>
            </div>
            <div class="location-chart">
                <div>
                    <div class="progress-item">
                        <div class="progress-info">
                            <span>United States</span>
                            <span>65%</span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill blue"></div>
                        </div>
                    </div>
                    
                    <div class="progress-item">
                        <div class="progress-info">
                            <span>United Kingdom</span>
                            <span>45%</span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill green"></div>
                        </div>
                    </div>
                    
                    <div class="progress-item">
                        <div class="progress-info">
                            <span>Australia</span>
                            <span>30%</span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill orange"></div>
                        </div>
                    </div>
                </div>
                
                <div>
                    <div class="progress-item">
                        <div class="progress-info">
                            <span>Germany</span>
                            <span>25%</span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill purple"></div>
                        </div>
                    </div>
                    
                    <div class="progress-item">
                        <div class="progress-info">
                            <span>Canada</span>
                            <span>20%</span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill blue" style="width: 20%"></div>
                        </div>
                    </div>
                    
                    <div class="progress-item">
                        <div class="progress-info">
                            <span>Other Countries</span>
                            <span>15%</span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill green" style="width: 15%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="chart-container">
            <div class="chart">
                <div class="chart-header">
                    <h3>Recent Activities</h3>
                    <div class="chart-actions">
                        <button class="active">All</button>
                        <button>Today</button>
                    </div>
                </div>
                <div class="activities">
                    <div class="activity-item">
                        <div class="activity-icon blue">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <div class="activity-info">
                            <h4>New client registered</h4>
                            <p>Client #2458 has been added</p>
                            <span class="activity-time">2 min ago</span>
                        </div>
                    </div>
                    
                    <div class="activity-item">
                        <div class="activity-icon green">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <div class="activity-info">
                            <h4>New payment received</h4>
                            <p>$2,500 from Project X</p>
                            <span class="activity-time">1 hour ago</span>
                        </div>
                    </div>
                    
                    <div class="activity-item">
                        <div class="activity-icon orange">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="activity-info">
                            <h4>Server alert</h4>
                            <p>Server load at 92%</p>
                            <span class="activity-time">3 hours ago</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="chart">
                <div class="chart-header">
                    <h3>Upcoming Tasks</h3>
                    <div class="chart-actions">
                        <button class="active">All</button>
                        <button>Priority</button>
                    </div>
                </div>
                <div class="tasks">
                    <div class="task-item">
                        <input type="checkbox" id="task1">
                        <label for="task1">Client meeting with John</label>
                        <span class="task-date">Today, 2:00 PM</span>
                    </div>
                    
                    <div class="task-item">
                        <input type="checkbox" id="task2">
                        <label for="task2">Project proposal deadline</label>
                        <span class="task-date">Tomorrow, 10:00 AM</span>
                    </div>
                    
                    <div class="task-item">
                        <input type="checkbox" id="task3">
                        <label for="task3">Team review meeting</label>
                        <span class="task-date">May 15, 9:30 AM</span>
                    </div>
                    
                    <div class="task-item">
                        <input type="checkbox" id="task4">
                        <label for="task4">Update documentation</label>
                        <span class="task-date">May 18, 11:00 AM</span>
                    </div>
                </div>
            </div>
        </div>
    </div>


  

    <!-- <script src="assets/js/sidebar.js"></script> -->
</body>
</html>
