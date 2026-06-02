<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');
session_start();

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$productId = (int)($input['product_id'] ?? 0);

$exists = db()->query("SELECT id FROM favorites WHERE user_id = :user_id AND product_id = :product_id", 
    ['user_id' => $_SESSION['user_id'], 'product_id' => $productId])->fetch();

if ($exists) {
    db()->delete('favorites', 'user_id = :user_id AND product_id = :product_id', 
        ['user_id' => $_SESSION['user_id'], 'product_id' => $productId]);
    echo json_encode(['success' => true, 'action' => 'removed']);
} else {
    db()->insert('favorites', ['user_id' => $_SESSION['user_id'], 'product_id' => $productId]);
    echo json_encode(['success' => true, 'action' => 'added']);
}