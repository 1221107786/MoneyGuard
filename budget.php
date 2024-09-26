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

// Initialize error and success messages
$error_message = "";
$success_message = "";

// Get the logged-in user's ID
$user_id = $_SESSION['user_id'];

// Get the user's total income
$income_query = "SELECT SUM(amount) AS total_income FROM income WHERE user_id = ?";
$stmt = $conn->prepare($income_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$income_result = $stmt->get_result()->fetch_assoc();
$total_income = $income_result['total_income'];
$stmt->close();

// Suggested percentages for each category
$suggested_percentages = [
    "Groceries" => 15,
    "Rent" => 30,
    "Utilities" => 10,
    "Transportation" => 10,
    "Entertainment" => 5
];

// Handle form submission for setting percentages
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_budget'])) {
    foreach ($suggested_percentages as $category => $suggested_percentage) {
        $percentage = $_POST["percentage_$category"];

        // Validate the percentage input
        if (!is_numeric($percentage) || $percentage < 0 || $percentage > 100) {
            $error_message = "Invalid percentage value for $category.";
            break;
        }

        // Calculate the amount for this category based on the user's total income
        $amount = ($percentage / 100) * $total_income;

        // Check if the category already exists in the database
        $check_query = "SELECT * FROM budget WHERE user_id = ? AND category = ?";
        $stmt = $conn->prepare($check_query);
        $stmt->bind_param("is", $user_id, $category);
        $stmt->execute();
        $existing_budget = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($existing_budget) {
            // Update existing budget entry
            $update_query = "UPDATE budget SET amount = ?, percentage = ? WHERE budget_id = ?";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param("dii", $amount, $percentage, $existing_budget['budget_id']);
        } else {
            // Insert new budget entry
            $insert_query = "INSERT INTO budget (user_id, category, amount, percentage) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($insert_query);
            $stmt->bind_param("isdi", $user_id, $category, $amount, $percentage);
        }

        if ($stmt->execute()) {
            $success_message = "Budget successfully saved!";
        } else {
            $error_message = "Error: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Fetch existing budget records
$query = "SELECT * FROM budget WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$budgets = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Close connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Budget Management</title>
    <style>
        /* CSS for the page */
        body {
            font-family: "Open Sans", Helvetica, Arial, sans-serif;
            line-height: 1.5;
            background-color: #111317;
            color: #FFF;
        }

        .container {
            max-width: 800px;
            width: 90%;
            margin: 50px auto;
            padding: 20px;
            border: 2px solid #c1cdcd;
            border-radius: 8px;
            background-color: #1f2125;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table th, table td {
            border: 1px solid #c1cdcd;
            padding: 10px;
            text-align: left;
        }

        table th {
            background-color: #333;
        }

        .error-message {
            color: red;
            margin-top: 10px;
        }

        .success-message {
            color: green;
            margin-top: 10px;
        }

        button {
            cursor: pointer;
            width: 100%;
            border: none;
            background: rgb(208, 147, 62);
            color: #000;
            margin: 10px 0 0;
            padding: 10px;
            font-size: 15px;
            border-radius: 4px;
        }

        button:hover {
            background-color: rgb(180, 130, 50);
        }

        input[type="number"] {
            width: 60px;
            padding: 5px;
            border-radius: 4px;
        }
    </style>
    <script>
        function validateForm() {
            let totalPercentage = 0;
            const inputs = document.querySelectorAll('input[type="number"]');
            
            inputs.forEach(input => {
                totalPercentage += parseFloat(input.value) || 0;
            });
            
            if (totalPercentage > 100) {
                alert("The total percentage cannot exceed 100%. Please adjust your values.");
                return false;
            }
            
            return true;
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>Budget Management</h1>

        <?php if (!empty($error_message)) : ?>
            <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
        <?php elseif (!empty($success_message)) : ?>
            <div class="success-message"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>

        <h2>Your Total Income: $<?php echo number_format($total_income, 2); ?></h2>

        <form action="" method="POST" onsubmit="return validateForm()">
            <table>
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Suggested %</th>
                        <th>Your %</th>
                        <th>Budgeted Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($suggested_percentages as $category => $suggested_percentage): 
                        $user_percentage = 0;
                        $amount = 0;

                        foreach ($budgets as $budget) {
                            if ($budget['category'] == $category) {
                                $user_percentage = $budget['percentage'];
                                $amount = $budget['amount'];
                            }
                        }
                    ?>
                        <tr>
                            <td><?php echo $category; ?></td>
                            <td><?php echo $suggested_percentage; ?>%</td>
                            <td>
                                <input type="number" name="percentage_<?php echo $category; ?>" value="<?php echo $user_percentage; ?>" min="0" max="100" required> %
                            </td>
                            <td>$<?php echo number_format(($user_percentage / 100) * $total_income, 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <button type="submit" name="save_budget">Save Budget</button>
            
            <button type="button" class="back-button" onclick="window.location.href='profile.php';">Back to Dashboard</button>
        </form>
    </div>
</body>
</html>

