<?php
session_start();
$_SESSION = [];
session_destroy();

echo "<script>alert('You have logged out.'); window.location.href='login.php';</script>";
exit;
?>