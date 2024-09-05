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

// Fetch income information
$sql = "SELECT date, amount, source, type FROM income WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Close connection
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Income Settings</title>
    <style>
        @import url("https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap");

        :root {
            --primary-color: #111317; /* Dark background */
            --primary-color-light: #1f2125; /* Slightly lighter dark background */
            --primary-color-extra-light: #35373b; /* Even lighter dark background */
            --secondary-color: #f9ac54; /* Bright orange */
            --secondary-color-dark: #d79447; /* Darker orange */
            --text-light: #d1d5db; /* Light text color */
            --white: #ffffff;
            --max-width: 1200px;
        }

        * {
            padding: 0;
            margin: 0;
            box-sizing: border-box;
        }

        body {
            font-family: "Poppins", sans-serif;
            background-color: var(--primary-color);
            display: flex;
            min-height: 100vh;
            margin: 0;
        }

        /* Sidebar Styling */
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
    justify-content: flex-start;
}

.content {
    background-color: var(--primary-color-light);
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    color: var(--text-light);
}

.content h1 {
    font-size: 2rem;
    margin-bottom: 20px;
    color: var(--white);
    text-align: center;
}

.income-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 20px;
}

.income-table th, .income-table td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid var(--primary-color-extra-light);
    color: var(--white);
}

.income-table th {
    background-color: var(--primary-color-extra-light);
    color: var(--secondary-color);
    text-transform: uppercase;
    font-weight: 600;
}

.income-table tr:nth-child(even) {
    background-color: var(--primary-color);
}

.income-table tr:hover {
    background-color: var(--primary-color-extra-light);
}

.income-table td {
    font-size: 1rem;
}

.income-table td[colspan="4"] {
    text-align: center;
    color: var(--text-light);
}


    </style>
</head>
<body>
    
    <div class="sidebar">
        <div class="logo-container">
            <img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAQMAAADCCAMAAAB6zFdcAAAAgVBMVEUAAAD///8SEhLq6uoHBwcUFBTm5uYPDw8LCwvz8/MFBQX29vYXFxf5+fn8/Pzj4+PS0tKRkZEtLS2srKzY2NjHx8dNTU2ysrIeHh5bW1ucnJw0NDQ6Ojp1dXXV1dUoKCiJiYlDQ0Nubm5paWl/f3++vr7KyspgYGChoaGxsbGCgoJ0UIpRAAAG/ElEQVR4nO2cC3OiOhSAT0JiAoLgC0GxPiptd///D7wBBBJEa+9Mdzfp+WY6LQQ65jOcnDwUAEEQBEEQBEEQBEEQBEEQBEEQBEEQBEEQBEEQBEEQBEEQBEEQBEEcQ7InL2TyW1/H3yMQRRg8Y4EFYSGCb389fwPKkw+Qn7/D6pKPhNM/8Iq+n+FbzkVM1icQn9wm4LQmseCf/DcrYIfBicoB8ec8eNQUJONzn4w4OFgoQfLNzmzPtQNCyh3cb+cUdmV10dAB3W24fXGSMn8tjYpcHZDpMYTx+kgIj1My4oDLtc/sixCUeWRrPPycTkhDmY5GBQFpeb1iQrlRsCWepQ6iix7KegfE3488DxT2PhlzwOASWeuARHut1WsOCFmnAwsU0nVfrDuQsI+IvQ6If2JdsmM4IJM9iL6RMAF7o7R3ELBT1TzsdUDiE20lmA4IeVsEsilTvxdvRlnvIKCnOpRa7IAk0Ka9QwckvjQDAzWMuMRk3EEgICG2OyCbNi7eOCCzkqqnXQItZ4OSzgGDDbHfAVles5tbB6pmv5WC397N+daB5Mv2SqsdzI5NFzDmgMyW6XLYCHoHFI5tod0OCHmvhwijDpSFsZONAxm89y3GcgdeWsWEOw5GqR0wSLX/YbkDsgrhfziAcNWfsd4BWWXiyw5EpilwwAHZZJR+yQGl2UY/44AD8gGZ6Bx4ZlbY8tbdMhEZfBhlLjggF+jbgQdznwzx59A7oHAxS51w4OegOYB8kB2TOAfNAeQDSU44UBK6anvAGVynjBqmR2C8dxAPFTjigKw0B0LlgK9JV5S8qmOhOVgN7nXFgVahai4tEPSjSRJnH7QaXGoORm5x0kE1Ls6rppDkzfj6Jzqoan1YRstDd/QTHagE+vpT8VMdqD+0P3+oAw2XHcQ3mfHTDt767tRuB5NiWLdnHXhFn1ra7cC7TXyfc6DS6/6f2O4A9uaM2XMOZntwyEEI84GDcOSWcOBgrp+x3oGgg9mAfGT1XUJuXPMBVDjkgAIPEr1+k9PILSdjnikJuLrPJQdAdy96DeNi2BBkYcwovFS7WBxzoN5mo47JYbD2fjAaSlw3FNccMEiNzmEj9bgYSmMGdVavSDjnQHV9Z33mSMW8voMUZsycnpsy5xyo3sHsIffdTh0Ge6NkThs97jlQv4wG75+vm1QCdjYSyU1/g3MOQIjSkJBf92CYuXQp2ofERQdBYKyfkTisSmho9BirLGi37zjnIOBpKFho5MNJJgOZGb2iFzIRprzW4JwDGq5yoPLVbPfAwXw+XiWFfBU2t7jmgFPPy4DJi5EmbGGrH84ukkHmee0+FOccTMjLQlXR6CGjMtIP50rS4qXfi+OgA1JyLmFJ7rEEyXlJ3HZAfqnfZgTQKEHV/Rdx3QG5qHwxGy46N8SZyg/rNXfHHfivIEU6tiVlkgoJTbfhnIOogKq3b/cjTXN1cLO2Xs+gqvPNsKpxEEDRBUzLHZCXXZX8dnuy4kIwON84OAMT7TxK40Bo8y62OyDJQeV9/b60l0JQmJtTzbM5UFG0da4cBFyfVrHeQT0S1PbmlSouhmYPuQxVPOz6i8qBOcq00oE0BgZzkFTfk1XtQ9GHCYm5D2VCqTRzKU/a50AOcuAjhMJwAPpYSY2djDWWiQj77do12zufhfunYeYeyyiX3HQAsGvr7O2qQ90Bl7mRQ28yCz/jWU2QrfVaeMbevMqBhLTpCadp/Sbre7IgNx6ltZ2f9QXGQmPCZLUYtgNRfWiPVB8BvB72DhbmrSGz04Hq7lPjzdwbe/MqJK2CxpY2j7rmYGXsUvVSYamCeklBf6jnq6EDqMaQyzbaaQ5e9IAapZY+CTXSCO7LWwcqcs67aKc5SLTkQXUpFnYJPcaESTnioP5c2xXdgTa6ntvcCiqENmGSjDnQKqg76FOH5affGvGvE/A+E1qNOujR40F3aZJx+78ahS9aCd7zDlbtX8li+IUYNhKw4jpX4N/0jSZ6ftDeUjD7WwFUD/x7U6PIe9rBpEkg/Xfb42GLhHOdJsymTzuY1l1qdLa7V9Sh5hjy6f2J2wffHmMdfLCk8JyDJbgQDzvEYf1lB+uD7YnBDfEXHcR//BV+Nwx2ky85mOxc6RJ6pHyNvuAgen3i2+WsQ2r7rj53sHenV9QRsJ096WC2tX6gdAdZLyk/4+CXm62gQrSzrJ84WLvaChSMXtcTHzuIC+pcl6BBm0X3hw4mqUMZ8ggM6qWTRw6i3L3EwETC79lDB7Pf7sbDFgHHhw6ODsfDDg6bBw42bo0V78GzN/+OA/8t+xEKVGAsytFnXkJZOB4ONRZfPO8i9yK/8z0CgiAIgiAIgiAIgiAIgiAIgiAIgiAIgiAIgiAIgiAIgiAIgiAI8oP5D02gTv8JQRBhAAAAAElFTkSuQmCC" alt="Logo" class="logo" />
            <div class="logo-text">Money Guard</div>
        </div>
        <button onclick="location.href='add_income.php'"><i class="ri-dashboard-line"></i>Add Income</button>
        <button onclick="location.href='edit_income.php'"><i class="fas fa-edit"></i>Edit Income</button>
        <button onclick="location.href='delete_income.php'"><i class="fas fa-trash-alt"></i>Delete Income</button>
        <button onclick="location.href='profile.php'"><i class="fas fa-trash-alt"></i>View Dashboard</button>
    </div>
    <div class="content">
        <h1>Income Details</h1>
        <table class="income-table">
            <thead>
                <tr>
                    <th>Amount</th>
                    <th>Source</th>
                    <th>Type</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['amount']); ?></td>
                            <td><?php echo htmlspecialchars($row['source']); ?></td>
                            <td><?php echo htmlspecialchars($row['type']); ?></td>
                            <td><?php echo htmlspecialchars($row['date']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4">No income records found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
