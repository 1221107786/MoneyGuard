<?php
session_start();

// Database connection details
$servername = "localhost";
$username = "MONEYGUARD";
$password = "Prasad123";
$dbname = "moneyguard";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect to login page if not logged in
    exit();
}

// Retrieve user details
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT firstname FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($firstname);
$stmt->fetch();
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Money Guard</title>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.4.0/fonts/remixicon.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.css" rel="stylesheet">
    <style>
 @import url("https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap");

:root {
    --primary-color: #111317; /* Dark background */
    --primary-color-light: #1f2125; /* Slightly lighter background */
    --primary-color-extra-light: #35373b; /* Even lighter background */
    --secondary-color: #f9ac54; /* Vibrant orange */
    --secondary-color-dark: #d79447; /* Darker orange */
    --accent-orange: #ff8c42; /* Futuristic bright orange */
    --accent-white: #ffffff; /* White text for contrast */
    --accent-black: #000000; /* Black text for deeper contrast */
    --text-light: #e0e0e0; /* Light grey text */
    --border-radius: 10px; /* Subtle rounded corners */
    --box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3); /* Softer, refined shadow */
    --glow-shadow-orange: 0 0 12px rgba(255, 140, 66, 0.7); /* Subtle orange glow */
    --transition-speed: 0.2s; /* Faster transitions */
    --font-weight-normal: 400;
    --font-weight-bold: 600;
    --header-font-size: calc(1.5rem + 1vw); /* Responsive font sizes */
    --body-font-size: calc(1rem + 0.3vw);
}

* {
    padding: 0;
    margin: 0;
    box-sizing: border-box;
}

body {
    font-family: "Poppins", sans-serif;
    background-color: var(--primary-color);
    color: var(--text-light);
    display: flex;
    min-height: 100vh;
    margin: 0;
    overflow-x: hidden;
    line-height: 1.6;
    font-size: var(--body-font-size);
    transition: background-color var(--transition-speed), color var(--transition-speed);
}

body.dark-mode {
    background-color: #000;
    color: #fff;
}

/* Sidebar Styles */
.sidebar {
    width: 250px;
    background-color: var(--primary-color-light);
    padding: 2rem;
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    position: fixed;
    height: 100vh;
    overflow-y: auto;
    transition: all var(--transition-speed) ease;
}

.sidebar .logo {
    font-size: var(--header-font-size);
    color: var(--secondary-color);
    font-weight: var(--font-weight-bold);
    text-align: center;
    margin-bottom: 2rem;
    transition: color var(--transition-speed) ease-in-out;
}

.sidebar .logo:hover {
    color: var(--accent-orange);
}

.sidebar ul {
    list-style: none;
    padding: 0;
}

.sidebar ul li {
    margin: 1rem 0;
}

.sidebar ul li a {
    color: var(--accent-white);
    text-decoration: none;
    font-size: 1.1rem;
    display: flex;
    align-items: center;
    padding: 0.75rem 1rem;
    border-radius: var(--border-radius);
    background-color: var(--primary-color-extra-light);
    transition: background-color var(--transition-speed), transform var(--transition-speed), box-shadow var(--transition-speed);
}

.sidebar ul li a:hover {
    background-color: var(--secondary-color-dark);
    transform: scale(1.03);
    box-shadow: var(--glow-shadow-orange);
}

.sidebar ul li.active a {
    background-color: var(--secondary-color);
    color: var(--accent-white);
}

/* Main Content Styles */
.main-content {
    margin-left: 270px;
    padding: 2rem;
    background-color: var(--primary-color-light);
    color: var(--text-light);
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    max-width: calc(100% - 270px);
    margin-top: 20px;
    transition: background var(--transition-speed) ease-in-out;
}

.main-content:hover {
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color-dark));
}

.main-content h1,
.main-content h2 {
    margin-bottom: 1rem;
    font-weight: var(--font-weight-bold);
    color: var(--secondary-color);
    border-bottom: 2px solid var(--primary-color-extra-light);
    padding-bottom: 0.5rem;
    transition: color var(--transition-speed) ease-in-out;
}

.main-content h2:hover {
    color: var(--accent-orange);
}

/* Welcome Message */
.welcome-message {
    padding: 1.5rem;
    background: linear-gradient(135deg, var(--secondary-color), var(--accent-orange));
    border-radius: var(--border-radius);
    color: var(--accent-white);
    font-size: 1.3rem;
    text-align: center;
    margin-bottom: 20px;
    box-shadow: var(--glow-shadow-orange);
    transition: transform var(--transition-speed) ease-in-out, background-color var(--transition-speed);
}

.welcome-message:hover {
    transform: scale(1.05);
    background-color: var(--accent-orange);
}

/* Dashboard Cards */
.dashboard-cards {
    display: flex;
    gap: 1.5rem;
    flex-wrap: wrap;
    margin-bottom: 2rem;
}

.dashboard-card {
    flex: 1;
    min-width: 250px;
    background-color: var(--primary-color-extra-light);
    border-radius: var(--border-radius);
    padding: 1rem;
    box-shadow: var(--box-shadow);
    color: var(--text-light);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    transition: box-shadow var(--transition-speed), transform var(--transition-speed);
}

.dashboard-card:hover {
    box-shadow: var(--glow-shadow-orange);
    transform: scale(1.03);
}

.dashboard-card h2 {
    color: var(--secondary-color);
    margin-bottom: 1rem;
    transition: color var(--transition-speed);
}

.dashboard-card:hover h2 {
    color: var(--accent-orange);
}

.dashboard-card iframe {
    border: none;
    border-radius: var(--border-radius);
    width: 100%;
    height: 200px;
}

/* Chart Container */
.chart-container {
    margin-top: 40px;
    background-color: var(--primary-color-light);
    border-radius: var(--border-radius);
    padding: 1rem;
    box-shadow: var(--box-shadow);
}

.comparison-chart iframe {
    width: 100%;
    height: 500px;
    border: none;
    border-radius: var(--border-radius);
}

/* Logo Styling */
.logo-container {
    text-align: center;
    margin: 20px 0;
}

.logo-container img.logo {
    width: 150px; /* Adjust size */
    height: auto;
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
}

.logo-container .logo-text {
    font-size: 1.5rem;
    font-weight: var(--font-weight-bold);
    color: var(--accent-white);
    margin-top: 10px;
}

/* Responsive Styles */
@media (max-width: 768px) {
    .sidebar {
        width: 100%;
        position: relative;
        height: auto;
        padding: 1rem;
    }

    .main-content {
        margin-left: 0;
        max-width: 100%;
    }
}

@media (max-width: 600px) {
    .sidebar {
        display: flex;
        justify-content: space-around;
        flex-direction: row;
    }

    .sidebar ul {
        display: flex;
        justify-content: space-around;
        width: 100%;
    }

    .sidebar ul li {
        margin: 0;
    }

    .sidebar ul li a {
        padding: 0.5rem;
        font-size: 0.875rem;
    }
}

/* Accessibility */
a:focus {
    outline: 2px dashed var(--accent-orange);
}


    </style>
</head>

<body>
<div class="sidebar">
        <h1 class="logo-container">
            <img src="data:image/png;base64,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" alt="Logo" class="logo">
            <div class="logo-text">Money Guard</div>
        </h1>
        
        <ul>
            <li class="active"><a href="#"><i class="ri-dashboard-line"></i>Dashboard</a></li>
            <li><a href="income_settings.php"><i class="ri-wallet-line"></i>Income</a></li>
            <li><a href="expense_settings.php"><i class="ri-money-dollar-circle-line"></i>Expense</a></li>
            <li><a href="logout.html"><i class="ri-logout-circle-line"></i>Logout</a></li>
            
        </ul>
    </div>

    <div class="main-content">
    <h2>Dashboard</h2>
    <div class="welcome-message">
      <?php if (isset($firstname)): ?>
        Welcome, <?php echo htmlspecialchars($firstname); ?>!
      <?php else: ?>
        Welcome!
      <?php endif; ?>
    </div>

        <div class="dashboard-cards">
            <div class="dashboard-card">
                <h2>Total Income</h2>
                <iframe src="totalincome.php" style="width: 100%; height: 200px; border: none;"></iframe>
            </div>
            <div class="dashboard-card">
                <h2>Total Expenses</h2>
                <iframe src="totalexpense.php" style="width: 100%; height: 200px; border: none;"></iframe>
            </div>
            <div class="dashboard-card">
                <h2>Total Balance</h2>
                <iframe src="totalbalance.php" style="width: 100%; height: 200px; border: none;"></iframe>
            </div>
        </div>
        <h2>Income Summary</h2>
    <div class="income-summary" id="income-summary">
    <iframe src="income_summary.php" style="width: 100%; height: 500px; border: none;"></iframe>
</div>

        
    <h2>Expense Summary</h2>
    <div class="expense-summary" id="expense-summary">
    <iframe src="expense_summary.php" style="width: 100%; height: 500px; border: none;"></iframe>
</div>

        <div class="chart-container">
        <h2> Income vs Expenses Comparison</h2>
        <div class="comparison-chart" id="comparison-chart">
            <iframe src="comparison_chart.php" style="width: 100%; height: 500px; border: none;"></iframe>
</div>
        </div>

        

    </div>



    <script>
        // Retrieve and display income data
        document.addEventListener('DOMContentLoaded', function() {
            const incomeData = localStorage.getItem('incomeData');
            const incomeDetails = document.getElementById('income-details');
            const expenseData = localStorage.getItem('expenseData');
            const expenseDetails = document.getElementById('expense-details');

            if (incomeData) {
                const { amount, source, type, budget } = JSON.parse(incomeData);
                incomeDetails.innerHTML = `
                    <strong>Amount:</strong> $${amount}<br>
                    <strong>Source:</strong> ${source}<br>
                    <strong>Type:</strong> ${type}<br>
                    <strong>Budget Allocation:</strong> ${budget}%
                `;
            } else {
                incomeDetails.textContent = 'No income data available.';
            }

            if (expenseData) {
                const { amount, category, type } = JSON.parse(expenseData);
                expenseDetails.innerHTML = `
                    <strong>Amount:</strong> $${amount}<br>
                    <strong>Category:</strong> ${category}<br>
                    <strong>Type:</strong> ${type}
                `;
            } else {
                expenseDetails.textContent = 'No expense data available.';
            }

            // Prepare data for the chart
            const incomeAmount = incomeData ? JSON.parse(incomeData).amount : 0;
            const expenseAmount = expenseData ? JSON.parse(expenseData).amount : 0;

            // Chart.js code
            const ctx = document.getElementById('income-vs-expense-chart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Income', 'Expenses'],
                    datasets: [{
                        label: 'Amount ($)',
                        data: [incomeAmount, expenseAmount],
                        backgroundColor: [
                            'rgba(54, 162, 235, 0.2)', // Blue for income
                            'rgba(255, 99, 132, 0.2)'  // Red for expenses
                        ],
                        borderColor: [
                            'rgba(54, 162, 235, 1)', // Blue border for income
                            'rgba(255, 99, 132, 1)'  // Red border for expenses
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        });
    </script>
</body>
</html>
