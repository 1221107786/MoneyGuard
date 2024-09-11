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

// Fetch expense details by payment method
$query = "SELECT payment_method, SUM(amount) AS total FROM expenses WHERE user_id = ? GROUP BY payment_method";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Prepare totals
$totals = ['Cash' => 0, 'Bank' => 0]; // Adjust payment methods as needed
while ($row = $result->fetch_assoc()) {
    if (isset($totals[$row['payment_method']])) {
        $totals[$row['payment_method']] = $row['total'];
    }
}

$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #ffffff; /* White text color */
            margin: 0; /* Remove default margin */
            padding: 0; /* Remove default padding */
            background-color: #000000; /* Black background color */
        }
        .chart-container {
            position: fixed; /* Fixes the position of the chart */
            top: 20px; /* Distance from the top of the viewport */
            left: 50%; /* Horizontally center the chart */
            transform: translateX(-50%); /* Adjust the chart's position to be centered */
            width: 80%; /* Set width of the chart container */
            max-width: 600px; /* Maximum width of the chart container */
            background-color: #1e1e1e; /* Slightly less black background for the container */
            padding: 20px; /* Add some padding inside the container */
            border-radius: 8px; /* Rounded corners */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.5); /* Subtle shadow for better visibility */
            z-index: 1000; /* Ensures the chart is on top of other elements */
        }
        h2 {
            color: #f39c12; /* Orange color for heading */
            text-align: center; /* Center align the heading */
            margin-bottom: 20px; /* Add margin below the heading */
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@latest"></script>
</head>
<body>
    <div class="chart-container">
        <canvas id="expensesChart"></canvas>
    </div>
    <script>
        const ctx = document.getElementById('expensesChart').getContext('2d');
        const expensesChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Cash', 'Bank'],
                datasets: [{
                    label: 'Total Expenses (RM)',
                    data: [<?php echo htmlspecialchars($totals['Cash']); ?>, <?php echo htmlspecialchars($totals['Bank']); ?>],
                    backgroundColor: [
                    'rgba(255, 99, 132, 0.2)', // Different color for Cash
                        'rgba(54, 162, 235, 0.2)'  // Different color for Bank
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)', // Border color for Cash
                        'rgba(54, 162, 235, 1)'  // Border color for Bank
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                plugins: {
                    legend: {
                        display: false // Hide the legend box
                    },
                    tooltip: {
                        callbacks: {
                            label: function(tooltipItem) {
                                return tooltipItem.dataset.label + ': RM' + tooltipItem.raw;
                            }
                        }
                    },
                    datalabels: {
                        display: true,
                        color: '#f39c12',
                        anchor: 'end',
                        align: 'top',
                        formatter: function(value) {
                            return 'RM' + value;
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>
</html>
