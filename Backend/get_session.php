<?php
session_start();
header('Content-Type: application/json');
if (isset($_SESSION['user_id'])) {
    echo json_encode([
        'logged_in' => true,
        'user_name' => $_SESSION['user_name'],
        'user_email' => isset($_SESSION['user_email']) ? $_SESSION['user_email'] : '',
        'is_admin' => (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true)
    ]);
} else {
    echo json_encode([
        'logged_in' => false
    ]);
}
?>
