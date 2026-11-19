<?php
session_start();
include 'connection.php';

/*RBAC*/
if (!isset($_SESSION["child_id"]) || ($_SESSION["role"] ?? "") !== "CHILD") {
    header("Location: login.php");
    exit;
}
$child_id = $_SESSION["child_id"];
$child_name = $_SESSION["child_name"];

$sql = "SELECT balance
        FROM wallets
        WHERE child_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $child_id);
$stmt->execute();

$wallet = $stmt->get_result()->fetch_assoc();
$balance = $wallet["balance"] ?? 0;
$sql = "SELECT t.transaction_type,t.amount,t.description,t.created_at,p.name FROM transactions t LEFT JOIN parents p ON t.parent_id = p.parent_id WHERE t.child_id = ? ORDER BY t.created_at DESC LIMIT 5";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $child_id);
$stmt->execute();

$transactions = $stmt->get_result();

$sql = "SELECT SUM(amount) AS total_spending FROM transactions WHERE child_id = ? AND transaction_type = 'PAYMENT' AND created_at >= DATE_FORMAT(CURDATE(), '%Y-%m-01') AND created_at < DATE_ADD( DATE_FORMAT(CURDATE(), '%Y-%m-01'), INTERVAL 1 MONTH )";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $child_id);
$stmt->execute();
$spending_result = $stmt->get_result()->fetch_assoc();
$total_spending = $spending_result["total_spending"] ?? 0;
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
    <link rel="stylesheet" href="css/child-dashboard.css">
</head>
<body>
    <header>
        <h3>KidSWallet</h3>
        <nav>
            <ul class="nav-links">
                <li>Home</li>
                <li>Transactions</li>
                <li>Your account</li>
                <li><a href="logout.php" onclick="return confirm('Are you sure you want to log out?')" id="logout">Logout</a></li>
            </ul>
        </nav>
    </header>
    <h1>Welcome, <?= htmlspecialchars($child_name) ?>!</h1>
    <main class="dashboard">
        <div class="left-cont">
            <div class="cont1">
                <div class="cont1-head">
                    <p>Wallet Balance</p>
                </div>
                <div class="balance">
                    <img src="image/wallet.png">
                    <h3>RM <?= $balance ?></h3>
                </div>
            </div>
            <div class="cont2">
                <div class="cont2-head">
                    <h3>Transaction history</h3>
                </div>
                <hr>
                <?php if ($transactions->num_rows > 0): ?>
                    <?php while ($transaction = $transactions->fetch_assoc()): ?>
                        <div class="history">
                            <div class="desc">
                                <p id="dates"><?= date("d/m/Y", strtotime($transaction["created_at"])) ?></p>
                                <p>
                                    <?php if ($transaction["transaction_type"] == "TOP_UP"): ?>
                                        Received from <?= htmlspecialchars($transaction["parent_name"] ?? "Parent") ?>
                                    <?php elseif ($transaction["transaction_type"] == "PAYMENT"): ?>
                                        Paid to <?= htmlspecialchars($transaction["description"] ?? "Shop") ?>
                                    <?php endif; ?>
                                </p>
                            </div>
                            <p class="amount">
                                <?php if ($transaction["transaction_type"] == "TOP_UP"): ?>
                                    + RM <?= number_format((float)$transaction["amount"], 2) ?>
                                <?php elseif ($transaction["transaction_type"] == "PAYMENT"): ?>
                                    - RM <?= number_format((float)$transaction["amount"], 2) ?>
                                <?php endif; ?>
                            </p>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No transactions yet.</p>
                <?php endif; ?>
                <a href="#" class="viewall">View All</a>
            </div>
        </div>
        <div class="right-cont">
            <div class="cont3">
                <div class="cont3-head">
                    <h3>Total spending</h3>
                    <p>October 2026</p>
                </div>
                <div class="spending">
                   <h2>RM <?=$total_spending?></h2>
                </div>
            </div>
            <div class="cont4">
                <div class="cont4-head">
                    <h3>Monthly Budget</h3>
                    <p>1/10 - 31/10</p>
                </div>
                <hr>
                <div class="budget">
                    <h2>RM 150.00</h2>
                    <p class="spending-left">RM 100.00 left</p>
                </div>
            </div>
        </div>
    </main>
</body>
</html>