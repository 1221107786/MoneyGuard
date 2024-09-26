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

// Fetch budget and total expenses for each category
$query = "
    SELECT b.category, COALESCE(SUM(b.amount), 0) AS total_budget, COALESCE(SUM(e.amount), 0) AS total_expenses
    FROM budget b
    LEFT JOIN expenses e ON b.category = e.category AND b.user_id = e.user_id
    WHERE b.user_id = ?
    GROUP BY b.category
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$results = $stmt->get_result();
$data = [];
$overspending = false;
$total_expenses = 0; // Initialize total expenses
$total_saved = 0;    // Initialize total saved

while ($row = $results->fetch_assoc()) {
    $row['total_saved'] = $row['total_budget'] - $row['total_expenses']; // Calculate total saved
    $data[] = $row;
    $total_expenses += $row['total_expenses']; // Add to total expenses
    $total_saved += $row['total_saved'];       // Add to total saved
    // Check if expenses exceed the budget
    if ($row['total_expenses'] > $row['total_budget']) {
        $overspending = true;
    }
}
$stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Budget Alert</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
    body {
        font-family: "Open Sans", Helvetica, Arial, sans-serif;
        color: #FFF;
        text-align: center;
        padding: 20px;
        overflow: hidden; /* Prevent scrolling */
    }
    canvas {
        max-width: 300px;
        margin: auto;
        padding: 10px;
    }
    table {
        width: 80%; /* Adjust width to make the table smaller */
        max-width: 600px; /* Set a maximum width */
        margin: 5px auto; /* Center the table */
        border-collapse: collapse;
    }
    th, td {
        border: 1px solid #c1cdcd;
        padding: 0.7px; /* Further reduce padding for smaller cells */
        font-size: 0.8em; /* Slightly smaller font size */
    }
    th {
        background-color: #2e2e2e;
    }
    .alert {
        color: #FF4D4D; /* Red color for alert */
        font-weight: bold;
        margin: 20px 0;
    }
    .totals {
        display: flex;          /* Use flexbox for layout */
        justify-content: center; /* Center both items horizontally */
        gap: 30px;               /* Space between total expenses and total saved */
        margin: 20px auto;
        width: fit-content;      /* Adjust width based on content */
        padding: 10px 20px;      /* Padding around the totals */
        border-radius: 5px;      /* Rounded corners */
    }
    .total-label {
        color: #FFF;
        font-size: 1em;
    }
</style>

</head>
<body>
    
    <canvas id="budgetChart"></canvas>

    <?php if ($overspending): ?>
        <div class="alert">You are overspending! Please check your expenses.</div>
    <?php endif; ?>

    <script>
        const ctx = document.getElementById('budgetChart').getContext('2d');
        const categories = <?php echo json_encode(array_column($data, 'category')); ?>;
        const budgets = <?php echo json_encode(array_column($data, 'total_budget')); ?>;
        const expenses = <?php echo json_encode(array_column($data, 'total_expenses')); ?>;
        const savedAmounts = budgets.map((b, i) => b - expenses[i]); // Calculate total saved for chart

        // Define distinct colors for each category
        const colors = [
            'rgba(75, 192, 192, 0.6)',  // Light Blue
            'rgba(255, 99, 132, 0.6)',  // Red
            'rgba(255, 205, 86, 0.6)',  // Yellow
            'rgba(54, 162, 235, 0.6)',  // Light Blue
            'rgba(153, 102, 255, 0.6)', // Purple
            'rgba(255, 159, 64, 0.6)',  // Orange
            'rgba(0, 255, 0, 0.6)',     // Green
            'rgba(0, 0, 255, 0.6)',     // Blue
        ];

        const budgetChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: categories,
                datasets: [{
                    data: savedAmounts, // Display Total Saved in the chart
                    backgroundColor: colors.slice(0, categories.length), // Use the distinct colors
                    borderColor: 'rgba(0, 0, 0, 0.1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: true,
                    },
                }
            }
        });
    </script>

    <table>
        <thead>
            <tr>
                <th>Category</th>
                <th>Budget</th>
                <th>Total Expenses</th>
                <th>Total Saved</th> <!-- New column for Total Saved -->
            </tr>
        </thead>
        <tbody>
            <?php foreach ($data as $row): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['category']); ?></td>
                    <td>$<?php echo number_format($row['total_budget'], 2); ?></td>
                    <td>$<?php echo number_format($row['total_expenses'], 2); ?></td>
                    <td>$<?php echo number_format($row['total_saved'], 2); ?></td> <!-- Display Total Saved -->
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="totals">
        <p><strong>Total Expenses:</strong> $<?php echo number_format($total_expenses, 2); ?></p>
        <p><strong>Total Saved:</strong> $<?php echo number_format($total_saved, 2); ?></p>
    </div>

</body>
</html>
