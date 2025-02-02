<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode(['status' => 'error', 'message' => '未登录']);
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['status' => 'error', 'message' => '无效的ID']);
    exit;
}

$id = (int)$_GET['id'];

$stmt = $conn->prepare("DELETE FROM concerts WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo json_encode([
        'status' => 'success',
        'message' => '演唱会信息已删除'
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => '删除失败'
    ]);
}
?> 