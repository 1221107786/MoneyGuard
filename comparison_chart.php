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

// Fetch income details by payment method
$query_income = "SELECT type AS method, SUM(amount) AS total_income FROM income WHERE user_id = ? GROUP BY type";
$stmt_income = $conn->prepare($query_income);
$stmt_income->bind_param("i", $user_id);
$stmt_income->execute();
$result_income = $stmt_income->get_result();

// Prepare data for income
$income_data = [];
while ($row = $result_income->fetch_assoc()) {
    $income_data[$row['method']] = $row['total_income'];
}
$stmt_income->close();

// Fetch expense details by payment method
$query_expenses = "SELECT payment_method AS method, SUM(amount) AS total_expenses FROM expenses WHERE user_id = ? GROUP BY payment_method";
$stmt_expenses = $conn->prepare($query_expenses);
$stmt_expenses->bind_param("i", $user_id);
$stmt_expenses->execute();
$result_expenses = $stmt_expenses->get_result();

// Prepare data for expenses
$expense_data = [];
while ($row = $result_expenses->fetch_assoc()) {
    $expense_data[$row['method']] = $row['total_expenses'];
}
$stmt_expenses->close();

// Combine income and expense data
$dataArray = [];
$methods = array_unique(array_merge(array_keys($income_data), array_keys($expense_data)));
foreach ($methods as $method) {
    $income = isset($income_data[$method]) ? $income_data[$method] : 0;
    $expense = isset($expense_data[$method]) ? $expense_data[$method] : 0;
    $dataArray[] = "['" . $method . "', " . $income . ", " . $expense . "]";
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
        google.charts.load('current', {'packages':['corechart']});
        google.charts.setOnLoadCallback(drawChart);

        function drawChart() {
            var data = new google.visualization.DataTable();
            data.addColumn('string', 'Method');
            data.addColumn('number', 'Income');
            data.addColumn('number', 'Expenses');

            // Add rows from PHP data
            data.addRows([
                <?php echo implode(',', $dataArray); ?>
            ]);

            var options = {
                
                backgroundColor: 'transparent', // Transparent background
                legend: { 
                    position: 'top', 
                    textStyle: { color: '#ffffff' } // White text color for legend
                },
                vAxis: {
                    title: 'Amount (RM)',
                    textStyle: { color: '#ffffff' }, // White text color for axis labels
                    titleTextStyle: { color: '#ffffff' }
                },
                hAxis: {
                    textStyle: { color: '#ffffff' } // White text color for axis labels
                },
                colors: ['blue', 'purple'], // Green for income, red for expenses
                bar: { groupWidth: '50%' }, // Bar width
                chartArea: { 
                    width: '70%', 
                    height: '70%' 
                }
            };

            var chart = new google.visualization.ColumnChart(document.getElementById('columnchart'));
            chart.draw(data, options);
        }
    </script>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #ffffff; /* White text color */
            text-align: center;
            padding: 20px;
            margin: 0;
        }
        #columnchart {
            width: 900px;
            height: 500px;
            margin: auto;
            position: fixed; /* Keeps the chart in a fixed position */
            top: 30%; /* Adjusts the vertical position */
            left: 50%; /* Adjusts the horizontal position */
            transform: translate(-50%, -30%); /* Centers the chart and moves it slightly up */
        }
    </style>
</head>
<body>
    <div id="columnchart"></div>
</body>
</html>
