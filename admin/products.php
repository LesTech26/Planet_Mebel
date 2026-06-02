<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

requireAdmin();

// Обработка удаления
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    // Получаем информацию о товаре для удаления файла изображения
    $product = getProduct($id);
    if ($product && !empty($product['image']) && strpos($product['image'], '/uploads/') === 0) {
        $imagePath = $_SERVER['DOCUMENT_ROOT'] . $product['image'];
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }
    
    db()->prepare("DELETE FROM products WHERE id = ?")->execute([$id]);
    header('Location: products.php?deleted=1');
    exit;
}

// Функция конвертации в WebP
function convertToWebP($sourcePath, $targetPath, $quality = 85) {
    $imageInfo = getimagesize($sourcePath);
    if (!$imageInfo) return false;
    
    switch ($imageInfo['mime']) {
        case 'image/jpeg':
            $image = imagecreatefromjpeg($sourcePath);
            break;
        case 'image/png':
            $image = imagecreatefrompng($sourcePath);
            imagepalettetotruecolor($image);
            imagealphablending($image, true);
            imagesavealpha($image, true);
            break;
        case 'image/gif':
            $image = imagecreatefromgif($sourcePath);
            break;
        case 'image/webp':
            $image = imagecreatefromwebp($sourcePath);
            break;
        default:
            return false;
    }
    
    if (!$image) return false;
    
    imagewebp($image, $targetPath, $quality);
    imagedestroy($image);
    
    return true;
}

// Обработка загрузки файла
function handleImageUpload($file, $productName) {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['error' => 'Ошибка загрузки файла'];
    }
    
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowedTypes)) {
        return ['error' => 'Разрешены только JPG, PNG, GIF, WEBP'];
    }
    
    $maxSize = 5 * 1024 * 1024; // 5MB
    if ($file['size'] > $maxSize) {
        return ['error' => 'Файл не должен превышать 5MB'];
    }
    
    $slug = generateSlug($productName);
    $filename = $slug . '_' . time() . '.webp';
    
    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/products/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $tempFile = $uploadDir . 'temp_' . $filename;
    $webpFile = $uploadDir . $filename;
    
    if (!move_uploaded_file($file['tmp_name'], $tempFile)) {
        return ['error' => 'Не удалось сохранить файл'];
    }
    
    if (!convertToWebP($tempFile, $webpFile, 85)) {
        unlink($tempFile);
        return ['error' => 'Не удалось конвертировать изображение в WebP'];
    }
    
    unlink($tempFile);
    
    return ['success' => true, 'path' => '/uploads/products/' . $filename];
}

// Обработка добавления/редактирования
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $imagePath = $_POST['existing_image'] ?? '';
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $uploadResult = handleImageUpload($_FILES['image'], $_POST['name']);
        if (isset($uploadResult['error'])) {
            $error = $uploadResult['error'];
        } else {
            $imagePath = $uploadResult['path'];
            
            if (!empty($_POST['existing_image']) && strpos($_POST['existing_image'], '/uploads/') === 0) {
                $oldImagePath = $_SERVER['DOCUMENT_ROOT'] . $_POST['existing_image'];
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }
        }
    }
    
    if (!isset($error)) {
        $data = [
            'name' => $_POST['name'],
            'slug' => generateSlug($_POST['name']),
            'category' => $_POST['category'],
            'category_id' => !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null,
            'price' => (float)$_POST['price'],
            'old_price' => !empty($_POST['old_price']) ? (float)$_POST['old_price'] : null,
            'image' => $imagePath,
            'material' => $_POST['material'] ?: null,
            'description' => $_POST['description'],
            'specs' => $_POST['specs'],
            'stock' => $_POST['stock'],
            'stock_text' => $_POST['stock_text'],
            'is_new' => isset($_POST['is_new']) ? 1 : 0,
            'is_hit' => isset($_POST['is_hit']) ? 1 : 0
        ];
        
        if ($id > 0) {
            $set = [];
            foreach ($data as $key => $value) {
                $set[] = "$key = :$key";
            }
            $set[] = "updated_at = NOW()";
            $sql = "UPDATE products SET " . implode(', ', $set) . " WHERE id = :id";
            $data['id'] = $id;
            db()->prepare($sql)->execute($data);
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
            db()->insert('products', $data);
        }
        header('Location: products.php?saved=1');
        exit;
    }
}

$products = getProducts();
$categories = getCategories();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <title>Товары — Админ-панель</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400;1,500;1,600&family=Jost:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        :root {
            --cream: #F5F0E8;
            --warm: #FDFAF5;
            --walnut: #7C5C3E;
            --walnut-l: #A07850;
            --charcoal: #2A2420;
            --mid: #8C7B6E;
            --line: rgba(124,92,62,0.18);
            --gold: #C9A96E;
            --green: #4A7C59;
            --red: #B04040;
            --blue: #4A7CA0;
        }
        body { font-family: 'Jost', sans-serif; background: #f5f5f5; color: var(--charcoal); font-weight: 400; font-size: 17px; line-height: 1.5; }
        
        /* Админ хедер */
        .admin-header { background: var(--charcoal); color: #fff; padding: 16px 24px; display: flex; justify-content: space-between; align-items: center; position: fixed; top: 0; left: 0; right: 0; z-index: 200; height: 70px; }
        .admin-header a { color: #fff; text-decoration: none; margin-left: 20px; transition: opacity 0.2s; }
        .admin-header a:hover { opacity: 0.8; }
        .burger-menu { display: none; background: none; border: none; color: #fff; font-size: 24px; cursor: pointer; }
        
        /* Контейнер */
        .admin-container { display: flex; margin-top: 70px; min-height: calc(100vh - 70px); }
        
        /* Сайдбар */
        .admin-sidebar { width: 280px; background: #fff; border-right: 1px solid var(--line); padding: 24px; position: fixed; height: calc(100vh - 70px); overflow-y: auto; transition: transform 0.3s ease; z-index: 100; }
        .admin-sidebar a { display: flex; align-items: center; gap: 12px; padding: 12px 16px; color: var(--charcoal); text-decoration: none; margin-bottom: 8px; border-radius: 12px; transition: all 0.2s; font-size: 15px; font-weight: 500; }
        .admin-sidebar a i { width: 22px; font-size: 16px; }
        .admin-sidebar a:hover, .admin-sidebar a.active { background: var(--walnut); color: #fff; }
        
        /* Основной контент */
        .admin-content { flex: 1; margin-left: 280px; padding: 32px; }
        
        /* Таблицы */
        .products-table { width: 100%; background: #fff; border-radius: 20px; overflow-x: auto; margin-top: 24px; box-shadow: 0 2px 10px rgba(0,0,0,0.03); border: 1px solid var(--line); }
        .products-table table { width: 100%; border-collapse: collapse; min-width: 900px; }
        .products-table th, .products-table td { padding: 16px 20px; text-align: left; border-bottom: 1px solid var(--line); font-size: 14px; vertical-align: middle; }
        .products-table th { background: var(--cream); font-weight: 600; font-size: 13px; text-transform: uppercase; letter-spacing: 0.08em; color: var(--mid); }
        .product-img { width: 60px; height: 60px; object-fit: cover; border-radius: 12px; background: #f0f0f0; }
        
        /* Кнопки */
        .btn { display: inline-block; padding: 8px 16px; background: var(--walnut); color: #fff; text-decoration: none; border-radius: 30px; font-size: 12px; font-weight: 500; border: none; cursor: pointer; transition: background 0.2s; }
        .btn:hover { background: var(--walnut-l); }
        .btn-danger { background: var(--red); }
        .btn-danger:hover { background: #8a3030; }
        .btn-success { background: var(--green); }
        .btn-success:hover { background: #3a6a4a; }
        .btn-edit { background: var(--blue); }
        .btn-edit:hover { background: #3a6a8a; }
        
        h2 { margin-bottom: 20px; font-size: 28px; font-weight: 400; font-family: 'Cormorant Garamond', serif; display: flex; align-items: center; gap: 10px; }
        
        /* Модальное окно */
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; }
        .modal.active { display: flex; }
        .modal-content { background: #fff; border-radius: 24px; max-width: 650px; width: 90%; max-height: 85vh; overflow-y: auto; padding: 32px; }
        .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 1px solid var(--line); }
        .modal-header h3 { font-size: 24px; font-weight: 500; font-family: 'Cormorant Garamond', serif; }
        .modal-close { background: none; border: none; font-size: 28px; cursor: pointer; color: var(--mid); transition: color 0.2s; }
        .modal-close:hover { color: var(--red); }
        
        /* Форма */
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-size: 12px; font-weight: 600; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px; color: var(--mid); }
        .form-group input, .form-group textarea, .form-group select { width: 100%; padding: 12px; border: 1px solid var(--line); border-radius: 12px; font-family: 'Jost', sans-serif; font-size: 14px; transition: border-color 0.2s; }
        .form-group input:focus, .form-group textarea:focus, .form-group select:focus { outline: none; border-color: var(--walnut); }
        
        /* Drag & Drop зона */
        .drop-zone { border: 2px dashed var(--line); border-radius: 16px; padding: 30px; text-align: center; cursor: pointer; transition: all 0.3s; background: #fafafa; }
        .drop-zone.drag-over { border-color: var(--walnut); background: var(--cream); }
        .drop-zone p { margin: 0; color: var(--mid); }
        .drop-zone .hint { font-size: 11px; margin-top: 8px; color: #999; }
        .image-preview { margin-top: 15px; text-align: center; }
        .image-preview img { max-width: 150px; max-height: 150px; border-radius: 12px; border: 1px solid var(--line); object-fit: cover; }
        
        .error-message { background: rgba(176,64,64,0.1); color: var(--red); padding: 12px 16px; border-radius: 12px; margin-bottom: 20px; font-size: 14px; display: flex; align-items: center; gap: 10px; }
        
        .checkbox-group { display: flex; align-items: center; gap: 12px; margin-bottom: 20px; }
        .checkbox-group label { margin-bottom: 0; cursor: pointer; }
        .checkbox-group input { width: 20px; height: 20px; margin: 0; cursor: pointer; }
        
        .form-actions { display: flex; gap: 12px; margin-top: 24px; }
        .form-actions .btn { flex: 1; text-align: center; }
        
        /* Уведомление */
        .success-message { position: fixed; top: 85px; right: 24px; background: var(--green); color: #fff; padding: 14px 24px; border-radius: 50px; z-index: 1001; display: none; font-size: 14px; font-weight: 500; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .success-message.show { display: block; animation: slideIn 0.3s ease; }
        @keyframes slideIn { from { opacity: 0; transform: translateX(100%); } to { opacity: 1; transform: translateX(0); } }
        
        /* Адаптивность */
        @media (max-width: 992px) {
            .admin-content { padding: 24px; }
            .products-table th, .products-table td { padding: 12px 16px; }
        }
        
        @media (max-width: 768px) {
            .burger-menu { display: block; }
            .admin-sidebar { transform: translateX(-100%); position: fixed; top: 70px; left: 0; width: 280px; height: calc(100vh - 70px); z-index: 200; }
            .admin-sidebar.mobile-open { transform: translateX(0); }
            .admin-content { margin-left: 0; padding: 20px; }
            .admin-header { padding: 12px 20px; height: 65px; }
            h2 { font-size: 24px; }
            .modal-content { padding: 24px; }
            .modal-header h3 { font-size: 22px; }
            .product-img { width: 50px; height: 50px; }
        }
        
        @media (max-width: 600px) {
            .admin-content { padding: 16px; }
            .products-table th, .products-table td { padding: 10px 12px; font-size: 12px; }
            h2 { font-size: 22px; }
            .btn { padding: 6px 12px; font-size: 11px; }
            .form-group input, .form-group textarea, .form-group select { padding: 10px; font-size: 13px; }
            .drop-zone { padding: 20px; }
        }
        
        @media (max-width: 480px) {
            .admin-header > div:first-child { font-size: 16px; }
            .admin-header a { margin-left: 12px; font-size: 14px; }
            .products-table { border-radius: 16px; }
            .product-img { width: 40px; height: 40px; }
            .modal-content { padding: 20px; }
        }
    </style>
</head>
<body>
    <div class="admin-header">
        <div style="display: flex; align-items: center; gap: 15px;">
            <button class="burger-menu" id="burgerMenu"><i class="fas fa-bars"></i></button>
            <div style="font-size: 20px; font-weight: 600;">Планета Мебели — Админ-панель</div>
        </div>
        <div><a href="/admin/logout.php"><i class="fas fa-sign-out-alt"></i> Выйти</a></div>
    </div>
    
    <div class="admin-container">
        <div class="admin-sidebar" id="sidebar">
            <a href="/admin/index.php">Главная</a>
            <a href="/admin/products.php" class="active">Товары</a>
            <a href="/admin/orders.php">Заказы</a>
            <a href="/admin/messages.php">Сообщения</a>
        </div>
        
        <div class="admin-content">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                <h2>Управление товарами</h2>
                <button class="btn btn-success" onclick="openModal()"><i class="fas fa-plus"></i> Добавить товар</button>
            </div>
            
            <?php if (isset($error)): ?>
                <div class="error-message"> <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <div class="products-table">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Изображение</th>
                            <th>Название</th>
                            <th>Категория</th>
                            <th>Цена</th>
                            <th>Хит</th>
                            <th>Новинка</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                        <tr>
                            <td><?= $product['id'] ?></td>
                            <td><img class="product-img" src="<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" onerror="this.src='https://via.placeholder.com/60?text=Нет+фото'"></td>
                            <td><?= htmlspecialchars($product['name']) ?></td>
                            <td><?= htmlspecialchars($product['category']) ?></td>
                            <td><?= formatPrice($product['price']) ?></td>
                            <td style="text-align: center;"><?= $product['is_hit'] ? '<i class="fas fa-fire" style="color: var(--orange);"></i>' : '<i class="fas fa-times" style="color: var(--mid);"></i>' ?></td>
                            <td style="text-align: center;"><?= $product['is_new'] ? '<i class="fas fa-star" style="color: var(--gold);"></i>' : '<i class="fas fa-times" style="color: var(--mid);"></i>' ?></td>
                            <td>
                                <button class="btn btn-edit" onclick='editProduct(<?= json_encode($product, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'><i class="fas fa-pen"></i></button>
                                <a href="?delete=<?= $product['id'] ?>" class="btn btn-danger" onclick="return confirm('Удалить товар?')"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (count($products) === 0): ?>
                        <tr><td colspan="8" style="text-align: center;">Нет товаров</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Модальное окно -->
    <div class="modal" id="productModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-couch" style="color: var(--walnut);"></i> <span id="modalTitle">Добавить товар</span></h3>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            <form method="POST" enctype="multipart/form-data" id="productForm">
                <input type="hidden" name="id" id="productId">
                <input type="hidden" name="existing_image" id="existingImage">
                
                <div class="form-group">
                    <label><i class="fas fa-tag"></i> Название *</label>
                    <input type="text" name="name" id="productName" required placeholder="Например: Диван 'Комфорт'">
                </div>
                <div class="form-group">
                    <label><i class="fas fa-folder"></i> Категория *</label>
                    <input type="text" name="category" id="productCategory" required placeholder="Например: Диваны">
                </div>
                <div class="form-group">
                    <label><i class="fas fa-hashtag"></i> ID категории</label>
                    <input type="number" name="category_id" id="productCategoryId" placeholder="Числовой идентификатор">
                </div>
                <div class="form-group">
                    <label><i class="fas fa-ruble-sign"></i> Цена *</label>
                    <input type="number" name="price" id="productPrice" step="0.01" required placeholder="19990">
                </div>
                <div class="form-group">
                    <label><i class="fas fa-ruble-sign"></i> Старая цена (скидка)</label>
                    <input type="number" name="old_price" id="productOldPrice" step="0.01" placeholder="24990">
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-image"></i> Изображение</label>
                    <div class="drop-zone" id="dropZone">
                        <p><i class="fas fa-cloud-upload-alt" style="font-size: 32px; display: block; margin-bottom: 10px;"></i> 📸 Перетащите изображение сюда или нажмите для выбора</p>
                        <p class="hint">Поддерживаются JPG, PNG, GIF, WEBP до 5MB</p>
                    </div>
                    <input type="file" name="image" id="imageInput" accept="image/jpeg,image/png,image/gif,image/webp" style="display: none;">
                    <div class="image-preview" id="imagePreview"></div>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-box"></i> Материал</label>
                    <input type="text" name="material" id="productMaterial" placeholder="Например: Велюр, Рогожка">
                </div>
                <div class="form-group">
                    <label><i class="fas fa-align-left"></i> Описание</label>
                    <textarea name="description" id="productDescription" rows="3" placeholder="Полное описание товара..."></textarea>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-list-ul"></i> Характеристики</label>
                    <textarea name="specs" id="productSpecs" rows="3" placeholder="Ширина: 220 см&#10;Глубина: 90 см&#10;Высота: 85 см"></textarea>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-warehouse"></i> Наличие</label>
                    <select name="stock" id="productStock">
                        <option value="in">В наличии</option>
                        <option value="low">Мало</option>
                        <option value="out">Нет в наличии</option>
                    </select>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-info-circle"></i> Текст наличия</label>
                    <input type="text" name="stock_text" id="productStockText" value="В наличии" placeholder="В наличии, Под заказ и т.д.">
                </div>
                <div class="checkbox-group">
                    <input type="checkbox" name="is_new" id="productIsNew">
                    <label for="productIsNew"><i class="fas fa-star" style="color: var(--gold);"></i> Новинка</label>
                </div>
                <div class="checkbox-group">
                    <input type="checkbox" name="is_hit" id="productIsHit">
                    <label for="productIsHit"><i class="fas fa-fire" style="color: var(--orange);"></i> Хит продаж</label>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Сохранить</button>
                    <button type="button" class="btn" onclick="closeModal()"><i class="fas fa-times"></i> Отмена</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Уведомление об успехе -->
    <div class="success-message" id="successMessage">
        <i class="fas fa-check-circle"></i> <span id="successText"></span>
    </div>
    
    <script>
    // Бургер-меню
    const burgerMenu = document.getElementById('burgerMenu');
    const sidebar = document.getElementById('sidebar');
    
    burgerMenu?.addEventListener('click', function() {
        sidebar.classList.toggle('mobile-open');
    });
    
    document.addEventListener('click', function(e) {
        if (window.innerWidth <= 768) {
            if (sidebar && !sidebar.contains(e.target) && burgerMenu && !burgerMenu.contains(e.target)) {
                sidebar.classList.remove('mobile-open');
            }
        }
    });
    
    // Drag & Drop
    const dropZone = document.getElementById('dropZone');
    const imageInput = document.getElementById('imageInput');
    const imagePreview = document.getElementById('imagePreview');
    
    dropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropZone.classList.add('drag-over');
    });
    
    dropZone.addEventListener('dragleave', () => {
        dropZone.classList.remove('drag-over');
    });
    
    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropZone.classList.remove('drag-over');
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            imageInput.files = files;
            previewImage(files[0]);
        }
    });
    
    dropZone.addEventListener('click', () => {
        imageInput.click();
    });
    
    imageInput.addEventListener('change', (e) => {
        if (e.target.files.length > 0) {
            previewImage(e.target.files[0]);
        }
    });
    
    function previewImage(file) {
        if (!file || !file.type || !file.type.startsWith('image/')) return;
        
        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreview.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
        };
        reader.readAsDataURL(file);
    }
    
    function openModal() {
        document.getElementById('modalTitle').innerText = 'Добавить товар';
        document.getElementById('productId').value = '';
        document.getElementById('productName').value = '';
        document.getElementById('productCategory').value = '';
        document.getElementById('productCategoryId').value = '';
        document.getElementById('productPrice').value = '';
        document.getElementById('productOldPrice').value = '';
        document.getElementById('productMaterial').value = '';
        document.getElementById('productDescription').value = '';
        document.getElementById('productSpecs').value = '';
        document.getElementById('productStock').value = 'in';
        document.getElementById('productStockText').value = 'В наличии';
        document.getElementById('productIsNew').checked = false;
        document.getElementById('productIsHit').checked = false;
        document.getElementById('existingImage').value = '';
        imagePreview.innerHTML = '';
        imageInput.value = '';
        document.getElementById('productModal').classList.add('active');
    }
    
    function editProduct(product) {
        document.getElementById('modalTitle').innerText = 'Редактировать товар';
        document.getElementById('productId').value = product.id;
        document.getElementById('productName').value = product.name;
        document.getElementById('productCategory').value = product.category;
        document.getElementById('productCategoryId').value = product.category_id || '';
        document.getElementById('productPrice').value = product.price;
        document.getElementById('productOldPrice').value = product.old_price || '';
        document.getElementById('productMaterial').value = product.material || '';
        document.getElementById('productDescription').value = product.description || '';
        document.getElementById('productSpecs').value = product.specs || '';
        document.getElementById('productStock').value = product.stock || 'in';
        document.getElementById('productStockText').value = product.stock_text || 'В наличии';
        document.getElementById('productIsNew').checked = product.is_new == 1;
        document.getElementById('productIsHit').checked = product.is_hit == 1;
        document.getElementById('existingImage').value = product.image || '';
        
        if (product.image && product.image !== '') {
            imagePreview.innerHTML = `<img src="${product.image}" alt="Preview" onerror="this.src='https://via.placeholder.com/150?text=Ошибка'">`;
        } else {
            imagePreview.innerHTML = '';
        }
        imageInput.value = '';
        
        document.getElementById('productModal').classList.add('active');
    }
    
    function closeModal() {
        document.getElementById('productModal').classList.remove('active');
    }
    
    window.onclick = function(event) {
        const modal = document.getElementById('productModal');
        if (event.target === modal) {
            closeModal();
        }
    }
    
    // Уведомление об успехе
    const urlParams = new URLSearchParams(window.location.search);
    const saved = urlParams.get('saved');
    const deleted = urlParams.get('deleted');
    
    if (saved) {
        const el = document.getElementById('successMessage');
        document.getElementById('successText').textContent = 'Товар успешно сохранён!';
        el.classList.add('show');
        setTimeout(() => el.classList.remove('show'), 3000);
        window.history.replaceState({}, document.title, window.location.pathname);
    } else if (deleted) {
        const el = document.getElementById('successMessage');
        document.getElementById('successText').textContent = 'Товар удалён!';
        el.classList.add('show');
        setTimeout(() => el.classList.remove('show'), 3000);
        window.history.replaceState({}, document.title, window.location.pathname);
    }
    </script>
</body>
</html>