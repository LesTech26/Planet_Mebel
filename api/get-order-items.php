<?php
session_start();
header('Content-Type: application/json');

require_once '../includes/db.php';
require_once '../includes/functions.php';

$orderId = (int)($_GET['order_id'] ?? 0);

if (!$orderId) {
    echo json_encode(['success' => false, 'error' => 'Не указан ID заказа']);
    exit;
}

// Получаем товары заказа
$items = db()->fetchAll(
    "SELECT * FROM order_items WHERE order_id = :order_id",
    ['order_id' => $orderId]
);

// Добавляем изображения для товаров (если есть)
foreach ($items as &$item) {
    // Пытаемся получить изображение товара из таблицы products
    $product = db()->fetchOne(
        "SELECT image FROM products WHERE id = :id",
        ['id' => $item['product_id']]
    );
    $item['image'] = $product['image'] ?? '/uploads/no-image.webp';
}

echo json_encode([
    'success' => true,
    'items' => $items
]);
?>