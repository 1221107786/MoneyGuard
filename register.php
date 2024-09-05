<?php
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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash the password

    // Check if email already exists
    $check_email = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check_email->bind_param("s", $email);
    $check_email->execute();
    $check_email->store_result();

    if ($check_email->num_rows > 0) {
        $error_message = "Email already exists. Please use another email.";
    } else {
        // Insert user into database
        $stmt = $conn->prepare("INSERT INTO users (firstname, lastname, phone, email, password) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $first_name, $last_name, $phone, $email, $password);

        if ($stmt->execute()) {
            header("Location: login.php"); // Redirect to login page after successful registration
            exit();
        } else {
            $error_message = "Registration failed. Please try again.";
        }

        $stmt->close();
    }

    $check_email->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Register</title>
    <style>
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

        input {
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

        .login-link {
            display: block;
            text-align: center;
            margin-top: 10px;
            color: rgb(208, 147, 62);
            text-decoration: none;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Registration Form</h2>
        <?php if (!empty($error_message)) : ?>
            <div class="error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <label for="first_name">First Name:</label>
            <input type="text" id="first_name" name="first_name" placeholder="First Name" required>

            <label for="last_name">Second Name:</label>
            <input type="text" id="last_name" name="last_name" placeholder="Second Name" required>

            <label for="phone">Phone Number:</label>
            <input type="tel" id="phone" name="phone" placeholder="Phone Number" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" placeholder="Email" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" placeholder="Password" required>

            <button type="submit" name="submit">Submit</button>
        </form>
        <a href="login.php" class="login-link">Already have an account? Login here</a>
    </div>
</body>

</html>
