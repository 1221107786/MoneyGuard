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

// Initialize date filter variables
$start_date = isset($_POST['start_date']) ? $_POST['start_date'] : '';
$end_date = isset($_POST['end_date']) ? $_POST['end_date'] : '';

// Fetch user expense details with optional date filter
$sql = "SELECT id, amount, category, payment_method, date FROM expenses WHERE user_id = '$user_id'";

if ($start_date && $end_date) {
    $sql .= " AND date BETWEEN '$start_date' AND '$end_date'";
} elseif ($start_date) {
    $sql .= " AND date >= '$start_date'";
} elseif ($end_date) {
    $sql .= " AND date <= '$end_date'";
}

$result = $conn->query($sql);

// Handle form submission for deleting expenses
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['expense_id'])) {
    $expense_id = $_POST['expense_id'];

    $delete_sql = "DELETE FROM expenses WHERE id='$expense_id' AND user_id='$user_id'";
    if ($conn->query($delete_sql) === TRUE) {
        // Set success message in session
        $_SESSION['success_message'] = 'Expense deleted successfully!';
        // Redirect to the same page
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        echo "<p class='error-msg'>Error deleting expense: " . $conn->error . "</p>";
    }
}

// Check for success message in session
$success_message = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : '';
unset($_SESSION['success_message']);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Expense</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary-color: #0d0d0d;
            --secondary-color: #ff9500;
            --secondary-color-dark: #cc7700;
            --accent-color: #ff6500;
            --error-color: #e74c3c;
            --text-light: #f0f0f0;
            --white: #ffffff;
            --max-width: 800px;
            --font-family: 'Poppins', sans-serif;
            --transition-speed: 0.3s;
        }

        * {
            padding: 0;
            margin: 0;
            box-sizing: border-box;
        }

        body {
            font-family: var(--font-family);
            background-color: var(--primary-color);
            color: var(--text-light);
            display: flex;
            min-height: 100vh;
            padding: 0;
            margin: 0;
        }

       
        .sidebar {
            width: 250px;
            background-color: var(--primary-color);
            display: flex;
            flex-direction: column;
            padding: 2rem 1rem;
            position: fixed; /* Fixes the position of the sidebar */
            height: 100vh; /* Full height of the viewport */
            top: 0; /* Aligns the sidebar to the top */
            left: 0; /* Aligns the sidebar to the left */
            box-shadow: 2px 0 10px rgba(255, 165, 0, 0.6); /* Orange shadow for a futuristic glow */
        }

        .sidebar .logo-container {
            text-align: center;
            margin-bottom: 2rem;
        }

        .sidebar .logo-container img.logo {
            width: 150px; /* Adjust size as needed */
            height: auto;
        }

        .sidebar .logo-container .logo-text {
            font-size: 1.5rem; /* Adjust font size as needed */
            font-weight: 600;
            color: var(--white);
            margin-top: 10px;
        }

        .sidebar button {
            background-color: var(--secondary-color);
            color: var(--white);
            border: none;
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color var(--transition-speed), transform var(--transition-speed);
            font-size: 1rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 4px 15px rgba(255, 165, 0, 0.5); /* Futuristic button shadow */
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .sidebar button:hover {
            background-color: var(--secondary-color-dark);
            transform: scale(1.05); /* Slight grow on hover for a futuristic feel */
        }

        .sidebar button:active {
            transform: scale(0.98); /* Button presses in on click */
        }

        .sidebar button i {
            margin-right: 10px; /* Space between icon and text */
            font-size: 1.2rem; /* Adjust icon size */
        }

        .main-content {
            margin-left: 270px; /* Adjusted to accommodate sidebar width */
            padding: 2rem;
            width: calc(100% - 270px); /* Adjusted to accommodate sidebar width */
        }

        .content {
            flex: 1;
            padding: 2rem;
            max-width: var(--max-width);
            margin: auto;
        }

        .container {
            background-color: #1a1a1a;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.6);
            width: 100%;
        }

        h1 {
            font-size: 2.5rem;
            margin-bottom: 2rem;
            color: var(--secondary-color);
            text-align: center;
            text-shadow: 1px 1px 5px rgba(255, 149, 0, 0.8);
        }

        .expense-item {
            background-color: #262626;
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.4);
            transition: transform var(--transition-speed), box-shadow var(--transition-speed);
        }

        .expense-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 24px rgba(255, 149, 0, 0.4);
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        label {
            font-weight: 600;
            font-size: 1rem;
            margin-bottom: 0.5rem;
            color: var(--white);
        }

        select, input[type="number"], .date-display {
            width: 100%;
            padding: 0.75rem 1rem;
            border-radius: 6px;
            border: 1px solid #4d5158;
            background-color: #333333;
            color: var(--white);
            font-size: 1rem;
            transition: border-color var(--transition-speed), background-color var(--transition-speed);
        }

        select:focus, input[type="number"]:focus {
            outline: none;
            border-color: var(--secondary-color);
            background-color: #444444;
        }

        .date-display {
            background-color: #444444;
            pointer-events: none;
        }

        button {
            padding: 0.75rem 1rem;
            background-color: var(--secondary-color);
            border: none;
            border-radius: 6px;
            color: var(--white);
            font-size: 1rem;
            cursor: pointer;
            transition: background-color var(--transition-speed), transform var(--transition-speed);
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
        }

        button:hover {
            background-color: var(--secondary-color-dark);
            transform: translateY(-2px);
        }

        .success-msg {
            background-color: var(--accent-color);
            color: var(--white);
            padding: 1rem;
            border-radius: 6px;
            text-align: center;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.4);
        }

        .error-msg {
            background-color: var(--error-color);
            color: var(--white);
            padding: 1rem;
            border-radius: 6px;
            text-align: center;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.4);
        }

        .filter-container {
            margin-bottom: 2rem;
            background-color: #262626;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.4);
        }

        .filter-container label {
            margin-right: 1rem;
            color: var(--white);
        }

        .filter-container input[type="date"] {
            padding: 0.75rem 1rem;
            border-radius: 6px;
            border: 1px solid #4d5158;
            background-color: #333333;
            color: var(--white);
            font-size: 1rem;
            margin-right: 1rem;
        }

        .filter-container button[type="submit"] {
            padding: 0.75rem 1rem;
            background-color: var(--secondary-color);
            border: none;
            border-radius: 6px;
            color: var(--white);
            font-size: 1rem;
            cursor: pointer;
            transition: background-color var(--transition-speed), transform var(--transition-speed);
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
        }

        .filter-container button[type="submit"]:hover {
            background-color: var(--secondary-color-dark);
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                box-shadow: none;
                padding: 1rem;
            }

            .content {
                padding: 1rem;
                width: 100%;
            }
        }
    </style>
      <script>
        // JavaScript to hide success message after 1 second
        document.addEventListener('DOMContentLoaded', function() {
            var successMsg = document.querySelector('.success-msg');
            if (successMsg) {
                setTimeout(function() {
                    successMsg.classList.add('fade-out');
                    setTimeout(function() {
                        successMsg.style.display = 'none';
                    }, 1000); // Wait for opacity transition to complete
                }, 1000); // Show message for 1 second
            }
        });
    </script>
</head>
<body>
<div class="sidebar">
        <div class="logo-container">
            <img src="data:image/png;base64,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" alt="Logo" class="logo" />
            <div class="logo-text">Money Guard</div>
        </div>
        <button onclick="location.href='add_expense.php'"><i class=></i>Add Expense</button>
        <button onclick="location.href='edit_expense.php'"><i class></i>Edit Expense</button>
        <button onclick="location.href='delete_expense.php'"><i class=></i>Delete Expense</button>
        <button onclick="location.href='expense_settings.php'"><i class=></i>View expenses</button>
    </div> 
    <div class="content">
        <div class="container">
            <h1>Delete Expenses</h1>

            <?php if ($success_message): ?>
                <div class="success-msg"><?php echo htmlspecialchars($success_message); ?></div>
            <?php endif; ?>

            <div class="filter-container">
            <form method="POST" action="">
                <label for="start_date">Date:</label>
                <input type="date" name="start_date" id="start_date" value="<?php echo htmlspecialchars($start_date); ?>">
                
                
                <button type="submit">Filter</button>
            </form>
            </div>

            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="expense-item">
                        <p><strong>Amount:</strong> $<?php echo htmlspecialchars($row['amount']); ?></p>
                        <p><strong>Category:</strong> <?php echo htmlspecialchars($row['category']); ?></p>
                        <p><strong>Payment Method:</strong> <?php echo htmlspecialchars($row['payment_method']); ?></p>
                        <p><strong>Date:</strong> <?php echo htmlspecialchars($row['date']); ?></p>
                        <form method="POST" action="">
                            <input type="hidden" name="expense_id" value="<?php echo htmlspecialchars($row['id']); ?>">
                            <button type="submit">Delete</button>
                        </form>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No expenses found.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
