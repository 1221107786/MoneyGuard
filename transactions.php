<?php
session_start();

// Database connection settings
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

// Get the logged-in user's ID
$user_id = $_SESSION['user_id'];

// Fetch today's transactions
$query = "SELECT amount, category, date FROM expenses WHERE user_id = ? AND DATE(date) = CURDATE() ORDER BY date DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$transactions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Close connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Today's Transactions</title>
    <style>
        /* Black and orange theme styles */
        body {
            font-family: "Open Sans", Helvetica, Arial, sans-serif;
            color: #FFF;
            margin: 0;
            padding: 20px;
        }

        .transaction-list {
            max-width: 600px;
            margin: auto; /* Center the list */
            padding: 20px;
            background-color: #111; /* Darker background for the list */
            border-radius: 12px; /* Rounded corners */
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.5); /* Stronger shadow */
        }

        h2 {
            text-align: center; /* Center the title */
            margin-bottom: 20px; /* Space below the title */
            color: #FFA500; /* Orange color for title */
        }

        .transaction-item {
            display: flex;
            justify-content: space-between; /* Space between details and amount */
            padding: 15px;
            margin-bottom: 15px; /* Space between items */
            background-color: #222; /* Background for each transaction */
            border-radius: 8px; /* Rounded corners for items */
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.5); /* Shadow */
            opacity: 0; /* Start hidden for fade-in effect */
            transform: translateY(20px); /* Move down for entrance effect */
            transition: opacity 0.5s, transform 0.5s; /* Smooth transitions */
        }

        .transaction-item.show {
            opacity: 1; /* Fully visible */
            transform: translateY(0); /* Move to original position */
        }

        .transaction-details {
            display: flex;
            flex-direction: column; /* Stack date and category */
        }

        .date {
            font-size: 0.9em;
            color: #FFA500; /* Orange color for date */
        }

        .category {
            font-size: 1.1em; /* Slightly larger font for category */
            color: #FFF; /* White text for category */
        }

        .amount {
            font-size: 1.5em; /* Larger font for amount */
            color: #FFA500; /* Orange color for emphasis */
        }
    </style>
</head>
<body>
    <div class="transaction-list">
        <h2>Today's Transactions</h2>
        <?php if (empty($transactions)): ?>
            <p>No transactions found for today.</p>
        <?php else: ?>
            <?php foreach ($transactions as $transaction): ?>
                <div class="transaction-item">
                    <div class="transaction-details">
                        <span class="date"><?php echo htmlspecialchars($transaction['date']); ?></span>
                        <span class="category"><?php echo htmlspecialchars($transaction['category']); ?></span>
                    </div>
                    <span class="amount">$<?php echo number_format($transaction['amount'], 2); ?></span>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <script>
        // JavaScript for fade-in effect
        document.addEventListener("DOMContentLoaded", function() {
            const items = document.querySelectorAll('.transaction-item');
            items.forEach((item, index) => {
                setTimeout(() => {
                    item.classList.add('show');
                }, index * 200); // Staggered animation
            });
        });
    </script>
</body>
</html>
