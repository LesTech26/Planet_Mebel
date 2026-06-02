<?php
require_once __DIR__ . '/db.php';

// Форматирование цены
function formatPrice($price) {
    return number_format($price, 0, '', ' ') . ' ₽';
}

// Генерация slug (без transliterator)
function generateSlug($string) {
    // Приводим к нижнему регистру
    $string = mb_strtolower($string, 'UTF-8');
    
    // Замена русских букв на латинские
    $translit = [
        'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'e',
        'ж' => 'zh', 'з' => 'z', 'и' => 'i', 'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm',
        'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u',
        'ф' => 'f', 'х' => 'h', 'ц' => 'ts', 'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sch', 'ъ' => '',
        'ы' => 'y', 'ь' => '', 'э' => 'e', 'ю' => 'yu', 'я' => 'ya',
        ' ' => '-', '_' => '-', '.' => '-'
    ];
    
    $string = strtr($string, $translit);
    
    // Удаляем всё, кроме букв, цифр и дефисов
    $string = preg_replace('/[^a-z0-9-]/', '', $string);
    
    // Заменяем несколько дефисов подряд на один
    $string = preg_replace('/-+/', '-', $string);
    
    // Удаляем дефисы в начале и конце
    $string = trim($string, '-');
    
    // Если получилась пустая строка, возвращаем 'product'
    if (empty($string)) {
        $string = 'product';
    }
    
    return $string;
}

// Альтернативная функция генерации slug (если нужно уникальное значение)
function generateUniqueSlug($string, $table = 'products', $field = 'slug') {
    $slug = generateSlug($string);
    $originalSlug = $slug;
    $counter = 1;
    
    // Проверяем уникальность
    while (true) {
        $stmt = db()->prepare("SELECT COUNT(*) FROM $table WHERE $field = :slug");
        $stmt->execute(['slug' => $slug]);
        $count = $stmt->fetchColumn();
        
        if ($count == 0) {
            break;
        }
        
        $slug = $originalSlug . '-' . $counter;
        $counter++;
    }
    
    return $slug;
}

// Получение товаров
function getProducts($filters = []) {
    $sql = "SELECT * FROM products WHERE 1=1";
    $params = [];
    
    if (!empty($filters['category_id'])) {
        $sql .= " AND category_id = :category_id";
        $params['category_id'] = $filters['category_id'];
    }
    
    if (!empty($filters['is_new'])) {
        $sql .= " AND is_new = 1";
    }
    
    if (!empty($filters['is_hit'])) {
        $sql .= " AND is_hit = 1";
    }
    
    if (!empty($filters['search'])) {
        $sql .= " AND (name LIKE :search OR category LIKE :search)";
        $params['search'] = '%' . $filters['search'] . '%';
    }
    
    if (!empty($filters['min_price'])) {
        $sql .= " AND price >= :min_price";
        $params['min_price'] = $filters['min_price'];
    }
    
    if (!empty($filters['max_price'])) {
        $sql .= " AND price <= :max_price";
        $params['max_price'] = $filters['max_price'];
    }
    
    if (!empty($filters['sort'])) {
        $allowedSort = ['price_asc', 'price_desc', 'name_asc'];
        if (in_array($filters['sort'], $allowedSort)) {
            switch ($filters['sort']) {
                case 'price_asc': $sql .= " ORDER BY price ASC"; break;
                case 'price_desc': $sql .= " ORDER BY price DESC"; break;
                case 'name_asc': $sql .= " ORDER BY name ASC"; break;
                default: $sql .= " ORDER BY id DESC";
            }
        } else {
            $sql .= " ORDER BY id DESC";
        }
    } else {
        $sql .= " ORDER BY id DESC";
    }
    
    return db()->fetchAll($sql, $params);
}

// Получение одного товара
function getProduct($id) {
    return db()->fetchOne("SELECT * FROM products WHERE id = :id", ['id' => $id]);
}

// Получение товара по slug
function getProductBySlug($slug) {
    return db()->fetchOne("SELECT * FROM products WHERE slug = :slug", ['slug' => $slug]);
}

// Получение категорий
function getCategories() {
    return db()->fetchAll("SELECT * FROM categories ORDER BY sort_order");
}

// Получение пользователя по ID
function getUser($id = null) {
    if ($id === null) {
        $id = $_SESSION['user_id'] ?? null;
    }
    if (!$id) return null;
    
    return db()->fetchOne("SELECT * FROM users WHERE id = :id", ['id' => $id]);
}

// Получение текущего пользователя из сессии
function getCurrentUser() {
    if (!isset($_SESSION['user_id'])) {
        return null;
    }
    return getUser($_SESSION['user_id']);
}

// Получение корзины из сессии
function getCart() {
    return $_SESSION['cart'] ?? [];
}

// Сохранение корзины
function saveCart($cart) {
    $_SESSION['cart'] = $cart;
}

// Добавление в корзину
function addToCart($productId, $quantity = 1, $options = []) {
    $cart = getCart();
    $key = $productId . '_' . md5(json_encode($options));
    
    if (isset($cart[$key])) {
        $cart[$key]['quantity'] += $quantity;
    } else {
        $product = getProduct($productId);
        if (!$product) return false;
        
        $cart[$key] = [
            'product_id' => $productId,
            'name' => $product['name'],
            'price' => (float)$product['price'],
            'quantity' => $quantity,
            'options' => $options,
            'image' => $product['image']
        ];
    }
    
    saveCart($cart);
    return true;
}

// Обновление количества в корзине
function updateCartQuantity($key, $quantity) {
    $cart = getCart();
    if (isset($cart[$key])) {
        if ($quantity <= 0) {
            unset($cart[$key]);
        } else {
            $cart[$key]['quantity'] = $quantity;
        }
        saveCart($cart);
        return true;
    }
    return false;
}

// Удаление из корзины
function removeFromCart($key) {
    $cart = getCart();
    if (isset($cart[$key])) {
        unset($cart[$key]);
        saveCart($cart);
    }
    return true;
}

// Очистка корзины
function clearCart() {
    $_SESSION['cart'] = [];
    return true;
}

// Получение итогов корзины
function getCartTotal() {
    $cart = getCart();
    $subtotal = 0;
    $count = 0;
    
    foreach ($cart as $item) {
        $subtotal += $item['price'] * $item['quantity'];
        $count += $item['quantity'];
    }
    
    $delivery = ($subtotal >= 10000 && $subtotal > 0) ? 0 : 500;
    if ($subtotal == 0) $delivery = 0;
    
    return [
        'subtotal' => $subtotal,
        'delivery' => $delivery,
        'total' => $subtotal + $delivery,
        'count' => $count
    ];
}

// Проверка авторизации
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Проверка админа
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

// Требование авторизации
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /login.php');
        exit;
    }
}

// Требование админа
function requireAdmin() {
    if (!isAdmin()) {
        header('Location: /admin/login.php');
        exit;
    }
}

// Вывод сообщения
function setFlash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash() {
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}

// Получение избранного пользователя
function getUserFavorites($userId = null) {
    if ($userId === null) {
        $userId = $_SESSION['user_id'] ?? null;
    }
    if (!$userId) return [];
    
    return db()->fetchAll(
        "SELECT p.* FROM favorites f 
         JOIN products p ON f.product_id = p.id 
         WHERE f.user_id = :user_id 
         ORDER BY f.created_at DESC",
        ['user_id' => $userId]
    );
}

// Проверка в избранном
function isFavorite($userId, $productId) {
    $result = db()->fetchOne(
        "SELECT id FROM favorites WHERE user_id = :user_id AND product_id = :product_id",
        ['user_id' => $userId, 'product_id' => $productId]
    );
    return $result !== false;
}

// Добавление в избранное
function addToFavorites($userId, $productId) {
    if (isFavorite($userId, $productId)) {
        return false;
    }
    return db()->insert('favorites', [
        'user_id' => $userId,
        'product_id' => $productId
    ]);
}

// Удаление из избранного
function removeFromFavorites($userId, $productId) {
    return db()->delete('favorites', 'user_id = :user_id AND product_id = :product_id', [
        'user_id' => $userId,
        'product_id' => $productId
    ]);
}

// Получение заказов пользователя
function getUserOrders($userId = null) {
    if ($userId === null) {
        $userId = $_SESSION['user_id'] ?? null;
    }
    if (!$userId) return [];
    
    return db()->fetchAll(
        "SELECT * FROM orders WHERE user_id = :user_id ORDER BY created_at DESC",
        ['user_id' => $userId]
    );
}

// Получение товаров заказа
function getOrderItems($orderId) {
    return db()->fetchAll(
        "SELECT * FROM order_items WHERE order_id = :order_id",
        ['order_id' => $orderId]
    );
}
?>