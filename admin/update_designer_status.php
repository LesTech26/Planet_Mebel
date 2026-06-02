<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['application_id']) && isset($_POST['status'])) {
    $id = (int)$_POST['application_id'];
    $status = $_POST['status'];
    
    $stmt = db()->prepare("UPDATE designer_applications SET status = ?, updated_at = NOW() WHERE id = ?");
    $stmt->execute([$status, $id]);
    
    header('Location: messages.php?success=Статус обновлён');
    exit;
}

header('Location: messages.php');
exit;