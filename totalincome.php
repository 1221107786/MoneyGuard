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

// Fetch income details
$query = "SELECT type, SUM(amount) AS total FROM income WHERE user_id = ? GROUP BY type";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Prepare totals
$totals = ['Bank' => 0, 'Cash' => 0];
while ($row = $result->fetch_assoc()) {
    if (isset($totals[$row['type']])) {
        $totals[$row['type']] = $row['total'];
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
    <title>Total Income</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #ffffff; /* White text color */
            margin: 0; /* Remove default margin */
            padding: 0; /* Remove default padding */
           
        }
        .total-text {
            position: fixed; /* Fixes the position of the box */
            top: 20px; /* Distance from the top of the viewport */
            left: 50%; /* Horizontally center the box */
            transform: translateX(-50%); /* Adjust the box's position to be centered */
            background-color:transparent; /* Slightly less transparent background for the container */
            padding: 20px; /* Add some padding inside the container */
            border-radius: 8px; /* Rounded corners */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Subtle shadow for better visibility */
            z-index: 1000; /* Ensures the box is on top of other elements */
        }
        h2 {
            margin-bottom: 10px; /* Adjust margin for the heading */
        }
    </style>
</head>
<body>
    <div class="total-text">
        <h2>Total Income</h2>
        <p>Bank: RM<?php echo htmlspecialchars(number_format($totals['Bank'], 2)); ?></p>
        <p>Cash: RM<?php echo htmlspecialchars(number_format($totals['Cash'], 2)); ?></p>
    </div>
</body>
</html>
