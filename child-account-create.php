<?php
session_start();
include 'connection.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["ChildName"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $phno = trim($_POST["phno"] ?? "");
    $password = $_POST["password"] ?? "";
    $password2 = $_POST["password2"] ?? "";

    if ($username == "" || $email == "" || $phno == "" || $password == "") {
        $message = "Please fill in all fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
    } elseif (!preg_match('/^[0-9]+$/', $phno)) {
        $message = "Phone number must contain numbers only.";
    } elseif (mb_strlen($password, "UTF-8") < 15) {
        $message = "Password must be at least 15 characters long.";
    } elseif ($password !== $password2) {
        $message = "Passwords do not match.";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $check_sql = "SELECT parent_id FROM `parents` WHERE email = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            $message = "This email is already registered.";
        } else {
            $sql = "INSERT INTO `parents` (name, email, phone, password_hash) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssss", $username, $email, $phno, $hashed_password);

            if ($stmt->execute()) {
                $parent_id = $conn->insert_id;
                $_SESSION["parent_id"] = $parent_id;
                $_SESSION["parent_name"] = $username;
                header("Location: child-account-create.php");
                exit;
            } else {
                $message = "Registration failed. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KidSWallet</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sniglet:wght@400;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/childAcc.css">
</head>
<body>
    <header>
        <h3>KidSWallet</h3>
    </header>
    <main class="create">
        <div class="create-head">
            <h1>Create your child's account</h1>
        </div>

        <section class="create-body" id="create-body">
            <form method="post" id="create-form">
                <label for="childName">Child name:</label><br>
                <input id="childName" name="ChildName" type="text" placeholder="Example: John Doe" required>
                <p class="error" id="name-error"></p>
                <label for="username">Username:</label><br>
                <input id="username" name="username" type="text" placeholder="Example: john_doe" required>
                <p class="error" id="username-error"></p>
                <label for="pin">6 digit pin:</label><br>
                <input id="pin" name="pin" type="text" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" minlength="6" placeholder="Enter 6-digit pin" required>
                <p class="error" id="pin-error"></p>
                <label for="pin2">Confirm Pin:</label><br>
                <input id="pin2" name="pin2" type="text" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" minlength="6" placeholder="Re-enter pin" required>
                <p class="error" id="pin2-error"></p>
                <div id="btn-cont">
                    <button type="submit">Create</button>
                    <button type="button" id="later-btn">Later</button>
                </div>
            </form>
        </section>

    </main>
     <script src="js/childAcc.js"></script>
</body>
</html>