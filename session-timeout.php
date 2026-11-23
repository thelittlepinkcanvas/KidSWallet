<?php
$idle_timeout = 30 * 60;
$absolute_timeout = 8 * 60 * 60;

if (isset($_SESSION["role"])) {
    $current_time = time();
    if (!isset($_SESSION["last_activity"]) || !isset($_SESSION["login_time"])) {
        session_unset();
        session_destroy();
        header("Location: login.php?timeout=1");
        exit;
    }

    $idle_time = $current_time - $_SESSION["last_activity"];
    $session_duration = $current_time - $_SESSION["login_time"];

    if ($idle_time >= $idle_timeout ||
        $session_duration >= $absolute_timeout) {
        session_unset();
        session_destroy();
        header("Location: login.php?timeout=1");
        exit;
    }

    $_SESSION["last_activity"] = $current_time;
}
?>
