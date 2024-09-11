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

// Initialize error message and success message
$error_message = "";
$success_message = "";

// Get the logged-in user's ID
$user_id = $_SESSION['user_id'];

// Fetch total income for Cash
$query_income_cash = "SELECT SUM(amount) AS total_income_cash FROM income WHERE user_id = ? AND type = 'Cash'";
$stmt_income_cash = $conn->prepare($query_income_cash);
$stmt_income_cash->bind_param("i", $user_id);
$stmt_income_cash->execute();
$result_income_cash = $stmt_income_cash->get_result();
$row_income_cash = $result_income_cash->fetch_assoc();
$total_income_cash = $row_income_cash['total_income_cash'] ?? 0;
$stmt_income_cash->close();

// Fetch total income for Bank
$query_income_bank = "SELECT SUM(amount) AS total_income_bank FROM income WHERE user_id = ? AND type = 'Bank'";
$stmt_income_bank = $conn->prepare($query_income_bank);
$stmt_income_bank->bind_param("i", $user_id);
$stmt_income_bank->execute();
$result_income_bank = $stmt_income_bank->get_result();
$row_income_bank = $result_income_bank->fetch_assoc();
$total_income_bank = $row_income_bank['total_income_bank'] ?? 0;
$stmt_income_bank->close();

// Fetch total expenses for Cash
$query_expenses_cash = "SELECT SUM(amount) AS total_expenses_cash FROM expenses WHERE user_id = ? AND payment_method = 'Cash'";
$stmt_expenses_cash = $conn->prepare($query_expenses_cash);
$stmt_expenses_cash->bind_param("i", $user_id);
$stmt_expenses_cash->execute();
$result_expenses_cash = $stmt_expenses_cash->get_result();
$row_expenses_cash = $result_expenses_cash->fetch_assoc();
$total_expenses_cash = $row_expenses_cash['total_expenses_cash'] ?? 0;
$stmt_expenses_cash->close();

// Fetch total expenses for Bank
$query_expenses_bank = "SELECT SUM(amount) AS total_expenses_bank FROM expenses WHERE user_id = ? AND payment_method = 'Bank'";
$stmt_expenses_bank = $conn->prepare($query_expenses_bank);
$stmt_expenses_bank->bind_param("i", $user_id);
$stmt_expenses_bank->execute();
$result_expenses_bank = $stmt_expenses_bank->get_result();
$row_expenses_bank = $result_expenses_bank->fetch_assoc();
$total_expenses_bank = $row_expenses_bank['total_expenses_bank'] ?? 0;
$stmt_expenses_bank->close();

// Calculate remaining balance for Cash and Bank
$remaining_balance_cash = $total_income_cash - $total_expenses_cash;
$remaining_balance_bank = $total_income_bank - $total_expenses_bank;

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $amount = $_POST['amount'];
    $category = $_POST['category'];
    $paymentMethod = $_POST['payment-method'];

    // Validate based on payment method
    if ($paymentMethod == "Cash" && $amount > $remaining_balance_cash) {
        $error_message = "You cannot add an expense that exceeds your remaining cash balance.";
    } elseif ($paymentMethod == "Bank" && $amount > $remaining_balance_bank) {
        $error_message = "You cannot add an expense that exceeds your remaining bank balance.";
    } else {
        // Insert expense into the database
        $stmt = $conn->prepare("INSERT INTO expenses (amount, category, payment_method, user_id) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $amount, $category, $paymentMethod, $user_id);

        if ($stmt->execute()) {
            $success_message = "Expense successfully added!";
        } else {
            $error_message = "Error: " . $stmt->error;
        }

        $stmt->close();
    }
}

// Close the connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Add Expense</title>
    <style>
        /* Your original CSS styles */
        body {
            font-family: "Open Sans", Helvetica, Arial, sans-serif;
            line-height: 1.5;
            background-color: #111317;
            color: #FFF;
        }

        .container {
            max-width: 600px;
            width: 80%;
            margin: 50px auto;
            padding: 20px;
            border: 2px solid #c1cdcd;
            border-radius: 8px;
        }

        form {
            width: 100%;
        }

        label {
            font-weight: bold;
            margin-bottom: 10px;
            display: block;
        }

        input, select {
            width: calc(100% - 22px);
            border: 2px solid #c1cdcd;
            background: #FFF;
            margin: 0 0 10px;
            padding: 10px;
            border-radius: 4px;
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

        .error-message {
            color: red;
            margin-top: 10px;
        }

        .success-message {
            color: green;
            margin-top: 10px;
        }

        footer {
            text-align: center;
            margin-top: 20px;
        }

        footer a {
            text-decoration: none;
            color: rgb(208, 147, 62);
            font-weight: bold;
        }

        footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Expense Form</h1>
        <?php if ($error_message): ?>
            <p class="error-message"><?php echo htmlspecialchars($error_message); ?></p>
        <?php elseif ($success_message): ?>
            <p class="success-message"><?php echo htmlspecialchars($success_message); ?></p>
        <?php endif; ?>
        <form id="expenseForm" action="add_expense.php" method="POST">
            <div class="form-group">
                <label for="amount">Expense Amount:</label>
                <input type="number" id="amount" name="amount" required>
            </div>
            <div class="form-group">
                <label for="category">Category:</label>
                <select id="category" name="category" required>
                    <option value="Groceries">Groceries</option>
                    <option value="Rent">Rent</option>
                    <option value="Utilities">Utilities</option>
                    <option value="Transportation">Transportation</option>
                    <option value="Entertainment">Entertainment</option>
                </select>
            </div>
            <div class="form-group">
                <label for="payment-method">Payment Method:</label>
                <select id="payment-method" name="payment-method" required>
                    <option value="Cash">Cash</option>
                    <option value="Bank">Bank</option>
                </select>
            </div>
            <button type="submit" class="btn">Add Expense</button>
        </form>
    </div>



    <footer>
        <a href="profile.php" class="btn">View Dashboard</a>
    </footer>
    <footer>
        <a href="expense_settings.php" class="btn">Back</a>
    </footer>
</body>
</html>
