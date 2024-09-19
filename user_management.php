<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

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

$user_id = $_SESSION['user_id'];
$error_message = "";
$password_verified = isset($_SESSION['password_verified']) ? $_SESSION['password_verified'] : false;

// Fetch current user details
$stmt = $conn->prepare("SELECT firstname, lastname, phone, email, profile_pic, password FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($first_name, $last_name, $phone, $email, $profile_pic, $stored_password);
$stmt->fetch();
$stmt->close();

// Handle password verification
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['verify_password'])) {
    $input_password = $_POST['verify_password'];

    if (password_verify($input_password, $stored_password)) {
        $_SESSION['password_verified'] = true;
        $password_verified = true;
    } else {
        $error_message = "Incorrect password. Please try again.";
    }
}

// Handle profile update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile']) && $password_verified) {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $profile_pic = $_POST['profile_pic']; // Predefined picture choice

    // Update user information
    $stmt = $conn->prepare("UPDATE users SET firstname = ?, lastname = ?, phone = ?, email = ?, profile_pic = ? WHERE id = ?");
    $stmt->bind_param("sssssi", $first_name, $last_name, $phone, $email, $profile_pic, $user_id);

    if ($stmt->execute()) {
        // Redirect with success message
        header("Location: user_management.php?success=" . urlencode("Profile updated successfully."));
        exit();
    } else {
        $error_message = "Profile update failed. Please try again. " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>User Management</title>
    <style>
       /* General Styles */
body {
    font-family: "Open Sans", Helvetica, Arial, sans-serif;
    background-color: #1b1b1b;
    color: #f5f5f5;
    margin: 0;
    padding: 0;
}

.container {
    max-width: 700px;
    width: 90%;
    margin: 50px auto;
    padding: 20px;
    border-radius: 10px;
    background-color: #2e2e2e;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
}

h2 {
    text-align: center;
    color: #dcdcdc;
    margin-bottom: 20px;
}

/* Form Styles */
form {
    display: flex;
    flex-direction: column;
}

label {
    font-weight: bold;
    margin-bottom: 8px;
}

input {
    padding: 12px;
    margin-bottom: 15px;
    border: 1px solid #444;
    border-radius: 5px;
    background-color: #333;
    color: #f5f5f5;
    font-size: 16px;
}

input[type="radio"] {
    display: none;
}

button {
    padding: 12px;
    border: none;
    border-radius: 5px;
    background: #f59e42;
    color: #000;
    font-size: 16px;
    cursor: pointer;
    transition: background 0.3s ease;
}

button:hover {
    background: #f58634;
}

/* Message Styles */
.success-message, .error-message {
    margin-bottom: 20px;
    padding: 10px;
    border-radius: 5px;
    text-align: center;
}

.success-message {
    background-color: #4caf50;
    color: #fff;
}

.error-message {
    background-color: #f44336;
    color: #fff;
}

/* Profile Picture Styles */
.profile-pics {
    display: flex;
    justify-content: space-around;
    margin-bottom: 20px;
}

.profile-pic-option {
    cursor: pointer;
    border: 2px solid transparent;
    border-radius: 50%;
    padding: 5px;
    width: 90px;
    height: 90px;
    transition: border-color 0.3s ease;
}

.profile-pic-option.selected {
    border-color: #f59e42;
}

.current-profile-pic {
    text-align: center;
    margin-bottom: 20px;
}

.current-profile-pic img {
    border-radius: 50%;
    width: 120px;
    height: 120px;
    object-fit: cover;
}

/* Back Button Styles */
.back-button {
    display: inline-block;
    padding: 12px 20px;
    background-color: #333;
    color: #f5f5f5;
    text-decoration: none;
    font-weight: bold;
    border-radius: 5px;
    border: 1px solid #444;
    text-align: center;
    transition: background-color 0.3s ease, color 0.3s ease;
}

.back-button:hover {
    background-color: #f59e42;
    color: #000;
}

    </style>
</head>

<body>
    <div class="container">
        <h2>User Management</h2>

        <!-- Success and Error Messages -->
        <?php if (isset($_GET['success'])) : ?>
            <div class="success-message"><?php echo htmlspecialchars($_GET['success']); ?></div>
        <?php endif; ?>
        <?php if (!empty($error_message)) : ?>
            <div class="error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <!-- Display current profile picture -->
        <?php
        // Mapping profile picture file names to URLs
        $profile_pic_urls = [
            "man.png" => "https://img.freepik.com/premium-vector/avatar-portrait-young-caucasian-boy-man-round-frame-vector-cartoon-flat-illustration_551425-19.jpg?w=740",
            "woman.png" => "https://img.freepik.com/premium-vector/avatar-portrait-young-caucasian-woman-round-frame-vector-cartoon-flat-illustration_551425-22.jpg?w=740",
        ];

        // Set a default image if no valid profile pic is selected
        $profile_pic_url = isset($profile_pic_urls[$profile_pic]) ? $profile_pic_urls[$profile_pic] : "images/profile_pics/default.png";
        ?>

        <div class="current-profile-pic">
            <p>Current Profile Picture:</p>
            <img src="<?php echo $profile_pic_url; ?>" alt="Profile Picture" style="border-radius: 50%; width: 100px; height: 100px;">
        </div>

        <!-- Password Verification Form -->
        <?php if (!$password_verified) : ?>
            <form action="user_management.php" method="post">
                <label for="verify_password">Current Password:</label>
                <input type="password" id="verify_password" name="verify_password" required>
                <button type="submit">Verify Password</button>
            </form>
        <?php else : ?>
            <!-- Edit Profile Form -->
            <form action="user_management.php" method="post">
                <label for="first_name">First Name:</label>
                <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($first_name); ?>" required>

                <label for="last_name">Last Name:</label>
                <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($last_name); ?>" required>

                <label for="phone">Phone Number:</label>
                <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($phone); ?>" required>

                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>

                <label>Choose a Profile Picture:</label>
                <div class="profile-pics">
                    <!-- Predefined Profile Picture Options -->
                    <label>
                        <input type="radio" name="profile_pic" value="man.png" <?php if ($profile_pic == "man.png") echo "checked"; ?> onclick="selectProfilePic(this)">
                        <img src="https://img.freepik.com/premium-vector/avatar-portrait-young-caucasian-boy-man-round-frame-vector-cartoon-flat-illustration_551425-19.jpg?w=740" class="profile-pic-option <?php if ($profile_pic == 'man.png') echo 'selected'; ?>">
                    </label>

                    <label>
                        <input type="radio" name="profile_pic" value="woman.png" <?php if ($profile_pic == "woman.png") echo "checked"; ?> onclick="selectProfilePic(this)">
                        <img src="https://img.freepik.com/premium-vector/avatar-portrait-young-caucasian-woman-round-frame-vector-cartoon-flat-illustration_551425-22.jpg?w=740" class="profile-pic-option <?php if ($profile_pic == 'woman.png') echo 'selected'; ?>">
                    </label>
                </div>

                <button type="submit" name="update_profile">Update Profile</button>
            </form>
        <?php endif; ?>
    </div>
    <a href="profile.php" class="back-button">Back</a>

    <script>
        // Function to highlight selected profile picture
        function selectProfilePic(el) {
            const options = document.querySelectorAll('.profile-pic-option');
            options.forEach(option => option.classList.remove('selected'));
            el.parentElement.querySelector('img').classList.add('selected');
        }
    </script>
</body>

</html>
