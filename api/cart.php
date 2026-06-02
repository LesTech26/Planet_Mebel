<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'add':
        $productId = (int)($input['product_id'] ?? 0);
        $quantity = (int)($input['quantity'] ?? 1);
        
        if ($productId <= 0) {
            echo json_encode(['success' => false, 'error' => 'Invalid product ID']);
            break;
        }
        
        addToCart($productId, $quantity);
        echo json_encode(['success' => true, 'count' => getCartTotal()['count']]);
        break;
    
    case 'update':
        $key = $input['key'] ?? '';
        $delta = (int)($input['delta'] ?? 0);
        
        if (empty($key)) {
            echo json_encode(['success' => false, 'error' => 'Invalid cart key']);
            break;
        }
        
        $cart = getCart();
        if (isset($cart[$key])) {
            $newQty = $cart[$key]['quantity'] + $delta;
            if ($newQty < 1) {
                unset($cart[$key]);
            } else {
                $cart[$key]['quantity'] = $newQty;
            }
            saveCart($cart);
        }
        
        echo json_encode(['success' => true, 'cart' => getCart(), 'total' => getCartTotal()]);
        break;
    
    case 'remove':
        $key = $input['key'] ?? '';
        
        if (empty($key)) {
            echo json_encode(['success' => false, 'error' => 'Invalid cart key']);
            break;
        }
        
        removeFromCart($key);
        echo json_encode(['success' => true, 'cart' => getCart(), 'total' => getCartTotal()]);
        break;
    
    case 'clear':
        clearCart();
        echo json_encode(['success' => true, 'cart' => [], 'total' => getCartTotal()]);
        break;
    
    case 'get':
        echo json_encode(['success' => true, 'cart' => getCart(), 'total' => getCartTotal()]);
        break;
    
    default:
        echo json_encode(['success' => false, 'error' => 'Unknown action: ' . $action]);
        break;
}