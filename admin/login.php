<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

if (isLoggedIn() && isAdmin()) {
    header('Location: index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    $user = db()->fetchOne("SELECT * FROM users WHERE email = :email", ['email' => $email]);
    
    if ($user && $user['role'] === 'admin' && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        header('Location: index.php');
        exit;
    } else {
        $error = 'Неверный email или пароль';
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход в админ-панель</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Jost', sans-serif; background: #F5F0E8; display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        .login-form { background: #fff; padding: 40px; border-radius: 12px; width: 100%; max-width: 400px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
        h1 { font-family: 'Cormorant Garamond', serif; font-size: 28px; margin-bottom: 24px; text-align: center; }
        .form-group { margin-bottom: 20px; }
        label { display: block; font-size: 12px; margin-bottom: 6px; color: #666; }
        input { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; font-family: 'Jost', sans-serif; }
        button { width: 100%; padding: 12px; background: #7C5C3E; color: #fff; border: none; border-radius: 6px; font-size: 14px; cursor: pointer; }
        button:hover { background: #A07850; }
        .error { color: #B04040; margin-bottom: 16px; text-align: center; }
    </style>
</head>
<body>
    <div class="login-form">
        <h1>Вход в админ-панель</h1>
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required autofocus>
            </div>
            <div class="form-group">
                <label>Пароль</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit">Войти</button>
        </form>
    </div>
</body>
</html>