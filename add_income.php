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

// Initialize error message
$error_message = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $amount = $_POST['amount'];
    $source = $_POST['source'];
    $type = $_POST['type'];
    $user_id = $_SESSION['user_id']; // Ensure user_id is set in the session

    // Insert income into database
    $stmt = $conn->prepare("INSERT INTO income (amount, source, type, user_id) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("dssi", $amount, $source, $type, $user_id);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Income successfully added!";
        header("Location: add_income.php"); // Redirect to add_income.php after successful entry
        exit();
    } else {
        $error_message = "Error: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Income Entry</title>
    <style>
        /* Your existing CSS */
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
            background-color: #1f2125; /* Background color of the container */
        }

        header h1 {
            text-align: center;
            color: #FFF;
            margin-bottom: 20px;
        }

        form {
            width: 100%;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            font-weight: bold;
            margin-bottom: 10px;
            display: block;
            color: #FFF; /* Label color */
        }

        input, select {
            width: calc(100% - 22px);
            border: 2px solid #c1cdcd;
            background: #FFF;
            margin-bottom: 10px;
            padding: 10px;
            border-radius: 4px;
            color: #000; /* Text color inside inputs */
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
            transition: background 0.3s;
        }

        button:hover {
            background: rgb(184, 130, 53); /* Darker shade on hover */
        }

        .error-message {
            color: red;
            margin-top: 10px;
            text-align: center;
        }

        /* Success message styling */
        .success-message {
            color: green;
            text-align: center;
            margin-bottom: 20px;
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
        <header>
            <h1>Income Entry</h1>
        </header>
        <main>
            <?php if (!empty($error_message)) : ?>
                <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>
            <?php if (isset($_SESSION['success_message'])) : ?>
                <div class="success-message"><?php echo htmlspecialchars($_SESSION['success_message']); ?></div>
                <?php unset($_SESSION['success_message']); // Clear success message ?>
            <?php endif; ?>
            <form id="incomeForm" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="form-group">
                    <label for="amount">Income Amount:</label>
                    <input type="number" id="amount" name="amount" required>
                </div>
                <div class="form-group">
                    <label for="source">Source of Income:</label>
                    <select id="source" name="source" required>
                        <option value="">Select a source</option>
                        <option value="Salary">Salary</option>
                        <option value="Freelancing">Freelancing</option>
                        <option value="Investments">Investments</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="type">Type (Bank/Cash):</label>
                    <select id="type" name="type" required>
                        <option value="Bank">Bank</option>
                        <option value="Cash">Cash</option>
                    </select>
                </div>
                <button type="submit" class="btn">Add Income</button>
            </form>
        </main>
        <footer>
            <a href="profile.php" class="btn">View Dashboard</a>
        </footer>
        <footer>
            <a href="income_settings.php" class="btn">Back</a>
        </footer>
    </div>
</body>
</html>
