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

// Fetch user income details with optional date filter
$sql = "SELECT id, source, amount, type, date FROM income WHERE user_id = '$user_id'";

if ($start_date && $end_date) {
    $sql .= " AND date BETWEEN '$start_date' AND '$end_date'";
} elseif ($start_date) {
    $sql .= " AND date >= '$start_date'";
} elseif ($end_date) {
    $sql .= " AND date <= '$end_date'";
}

$result = $conn->query($sql);

// Handle form submission for editing income
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['income_id'])) {
    $income_id = $_POST['income_id'];
    $source = $_POST['source'];
    $amount = $_POST['amount'];
    $type = $_POST['type'];

    $update_sql = "UPDATE income SET source='$source', amount='$amount', type='$type' WHERE id='$income_id'";
    if ($conn->query($update_sql) === TRUE) {
        echo "<p class='success-msg'>Income updated successfully!</p>";
        echo "<script>window.location.reload();</script>"; // Reload the page to show updated values
    } else {
        echo "<p class='error-msg'>Error updating income: " . $conn->error . "</p>";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Income</title>
    <style>
        :root {
    --primary-color: #0d0d0d; /* Deep black background */
    --secondary-color: #ff9500; /* Bright neon orange */
    --secondary-color-dark: #cc7700; /* Darker neon orange */
    --accent-color: #ff6500; /* Slightly different orange for accents */
    --error-color: #e74c3c; /* Error color */
    --text-light: #f0f0f0; /* Light text color */
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
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    padding: 20px;
}

.container {
    background-color: #1a1a1a;
    padding: 2rem;
    border-radius: 12px;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.6);
    width: 100%;
    max-width: var(--max-width);
}

h1 {
    font-size: 2.5rem;
    margin-bottom: 2rem;
    color: var(--secondary-color);
    text-align: center;
    text-shadow: 1px 1px 5px rgba(255, 149, 0, 0.8);
}

.income-item {
    background-color: #262626;
    padding: 1.5rem;
    border-radius: 10px;
    margin-bottom: 1.5rem;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.4);
    transition: transform var(--transition-speed), box-shadow var(--transition-speed);
}

.income-item:hover {
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

@media (max-width: 600px) {
    .container {
        padding: 1.5rem;
    }

    h1 {
        font-size: 2rem;
    }

    button {
        font-size: 0.9rem;
        padding: 0.6rem 0.8rem;
    }

    select, input[type="number"], .date-display {
        font-size: 0.9rem;
    }
}

.sidebar {
            width: 250px;
            background-color: var(--primary-color-light);
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
            transition: background-color 0.3s, transform 0.3s;
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

        /* Main Content Area */
        .content {
            margin-left: 250px; /* Space for the sidebar */
            padding: 2rem;
            background-color: var(--primary-color-light);
            color: var(--text-light);
            flex-grow: 1;
            min-height: 100vh; /* Ensures it fills the height of the viewport */
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .content h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: var(--secondary-color);
            text-shadow: 2px 2px 8px rgba(255, 165, 0, 0.6); /* Text glow effect */
        }

        .content p {
            font-size: 1.2rem;
            color: var(--text-light);
            text-align: center;
        }

        /* Responsive Sidebar */
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                padding: 1rem;
                position: relative; /* Changes position for smaller screens */
                height: auto; /* Allows the sidebar to adjust height */
            }

            .sidebar button {
                padding: 0.75rem;
            }

            .content {
                margin-left: 0; /* Removes margin for smaller screens */
                padding: 1rem;
            }
        }

        @media (max-width: 600px) {
            .sidebar button {
                padding: 0.5rem;
                font-size: 0.875rem;
            }
        }

    </style>
</head>

<div class="sidebar">
        <div class="logo-container">
            <img src="data:image/png;base64,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" alt="Logo" class="logo" />
            <div class="logo-text">Money Guard</div>
        </div>
        <button onclick="location.href='add_income.php'"><i class="ri-dashboard-line"></i>Add Income</button>
        <button onclick="location.href='edit_income.php'"><i class="fas fa-edit"></i>Edit Income</button>
        <button onclick="location.href='delete_income.php'"><i class="fas fa-trash-alt"></i>Delete Income</button>
        <button onclick="location.href='profile.php'"><i class="fas fa-trash-alt"></i>View Dashboard</button>
    </div>
<body>
    <div class="container">
        <h1>Edit Your Income</h1>

        <div class="filter-container">
            <form method="POST" action="">
                <label for="start_date">Start Date:</label>
                <input type="date" name="start_date" id="start_date" value="<?php echo htmlspecialchars($start_date); ?>">
                
                
                <button type="submit">Filter</button>
            </form>
        </div>

        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="income-item">
                    <form method="POST" action="">
                        <input type="hidden" name="income_id" value="<?php echo $row['id']; ?>">
                        <label for="source">Source:</label>
                        <select name="source" id="source" required>
                            <option value="salary" <?php echo ($row['source'] == 'salary') ? 'selected' : ''; ?>>Salary</option>
                            <option value="freelancing" <?php echo ($row['source'] == 'freelancing') ? 'selected' : ''; ?>>Freelancing</option>
                            <option value="investment" <?php echo ($row['source'] == 'investment') ? 'selected' : ''; ?>>Investment</option>
                            <option value="others" <?php echo ($row['source'] == 'others') ? 'selected' : ''; ?>>Others</option>
                        </select>
                        <label for="type">Type:</label>
                        <select name="type" id="type" required>
                            <option value="bank" <?php echo ($row['type'] == 'bank') ? 'selected' : ''; ?>>Bank</option>
                            <option value="cash" <?php echo ($row['type'] == 'cash') ? 'selected' : ''; ?>>Cash</option>
                        </select>
                        <label for="amount">Amount:</label>
                        <input type="number" name="amount" id="amount" value="<?php echo $row['amount']; ?>" required>
                        <button type="submit">Update</button>
                    </form>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No income records found.</p>
        <?php endif; ?>
    </div>
</body>
</html>
