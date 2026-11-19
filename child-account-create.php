<?php
session_start();
include 'connection.php';

$message = "";
$name_error = "";
$username_error = "";
$pin_error = "";
$pin2_error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $childName = trim($_POST["ChildName"] ?? "");
    $username = trim($_POST["username"] ?? "");
    $pin = $_POST["pin"] ?? "";
    $pin2 = $_POST["pin2"] ?? "";

    if ($childName == "" || $username == "" || $pin == "" || $pin2 == "") {
        $message = "Please fill in all fields.";
    } elseif ($childName == "") {
        $name_error = "Please enter your child's name.";
    } elseif ($username == "") {
        $username_error = "Please enter your username.";
    } elseif (!preg_match('/^[a-zA-Z0-9._]+$/', $username)) {
        $username_error = "Username cannot contain spaces or special characters.";
    }elseif ($pin == "") {
        $pin_error = "Please enter a pin.";
    } elseif ($pin2 == "") {
        $pin2_error = "Please confirm your pin.";
    } elseif ($pin !== $pin2) {
        $pin2_error = "Pins do not match.";
    } else {
        $check_sql = "SELECT child_id FROM children WHERE username = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("s", $username);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            $username_error = "This username is already taken.";
        } else {
            $hashed_pin = password_hash($pin, PASSWORD_DEFAULT);
            $sql = "INSERT INTO children (parent_id, name, username, pin_hash) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("isss", $_SESSION["parent_id"], $childName, $username, $hashed_pin);
            if ($stmt->execute()) {
                header("Location: parent-dashboard.php");
                exit;
            } else {
                $message = "Account creation failed. Please try again.";
            }
            $stmt->close();
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
                <input id="childName" name="ChildName" type="text" placeholder="Example: John Doe" value="<?= htmlspecialchars($childName ?? '') ?>" required>
                <p class="error"><?= htmlspecialchars($name_error) ?></p>
                <label for="username">Username:</label><br>
                <input id="username" name="username" type="text" placeholder="Example: john_doe" value="<?= htmlspecialchars($username ?? '') ?>" required>
                <p class="error"><?= htmlspecialchars($username_error) ?></p>
                <label for="pin">6 digit pin:</label><br>
                <input id="pin" name="pin" type="text" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" minlength="6" placeholder="Enter 6-digit pin" required>
                <p class="error"><?= htmlspecialchars($pin_error) ?></p>
                <label for="pin2">Confirm Pin:</label><br>
                <input id="pin2" name="pin2" type="text" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" minlength="6" placeholder="Re-enter pin" required>
                <p class="error"><?= htmlspecialchars($pin2_error) ?></p>
                <?php if ($message != ""): ?><p class="error"><?= htmlspecialchars($message) ?></p><?php endif; ?>
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