<?php
session_start();
header('Content-Type: application/json');

require_once '../includes/db.php';
require_once '../includes/functions.php';

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || empty($input['items'])) {
    echo json_encode(['success' => false, 'error' => 'Нет товаров в заказе']);
    exit;
}

// Генерируем номер заказа
$orderNumber = 'PM-' . date('Ymd') . '-' . rand(1000, 9999);

// Данные заказа
$orderData = [
    'order_number' => $orderNumber,
    'user_id' => $_SESSION['user_id'] ?? null,
    'fullname' => $input['fullname'],
    'phone' => $input['phone'],
    'email' => $input['email'],
    'address' => $input['address'] ?? null,
    'delivery_method' => $input['delivery_method'],
    'payment_method' => $input['payment_method'],
    'comment' => $input['comment'] ?? null,
    'subtotal' => $input['subtotal'],
    'delivery_price' => $input['delivery_price'],
    'total' => $input['total'],
    'status' => 'new',
    'created_at' => date('Y-m-d H:i:s')
];

try {
    // Сохраняем заказ
    $orderId = db()->insert('orders', $orderData);
    
    // Сохраняем товары заказа
    foreach ($input['items'] as $item) {
        db()->insert('order_items', [
            'order_id' => $orderId,
            'product_id' => $item['product_id'],
            'product_name' => $item['product_name'],
            'price' => $item['price'],
            'quantity' => $item['quantity'],
            'options' => json_encode($item['options'] ?? [])
        ]);
    }
    
    echo json_encode([
        'success' => true,
        'order_id' => $orderId,
        'order_number' => $orderNumber
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>