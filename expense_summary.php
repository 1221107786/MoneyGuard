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

// Fetch expense details grouped by category
$query = "SELECT category, SUM(amount) AS total FROM expenses WHERE user_id = ? GROUP BY category";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Prepare data for Google Charts
$dataArray = [];
while ($row = $result->fetch_assoc()) {
    $dataArray[] = "['" . $row['category'] . "', " . $row['total'] . "]";
}

$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Expense Summary</title>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
        google.charts.load('current', {'packages':['corechart']});
        google.charts.setOnLoadCallback(drawChart);

        function drawChart() {
            var data = new google.visualization.DataTable();
            data.addColumn('string', 'Category');
            data.addColumn('number', 'Amount');

            // Add rows from PHP data
            data.addRows([
                <?php echo implode(',', $dataArray); ?>
            ]);

            var options = {
                pieSliceText: 'label', // Display slice labels
                slices: {
                    0: {offset: 0.1},
                    1: {offset: 0.1},
                    2: {offset: 0.1},
                    3: {offset: 0.1}
                },
                backgroundColor: 'transparent', // Make chart background transparent to blend with the page background
                legend: { 
                    position: 'labeled', // Show legend
                    textStyle: { color: '#ffffff' } // White text color for legend
                },
                pieSliceTextStyle: {
                    color: 'white' // White text color for pie slices
                }
            };

            var chart = new google.visualization.PieChart(document.getElementById('piechart'));
            chart.draw(data, options);
        }
    </script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: transparent black; /* Transparent black background */
            color: #ffffff; /* White text color */
            text-align: center;
            padding: 20px;
            margin: 0;
        }
        #piechart {
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
    <div id="piechart"></div>
</body>
</html>
