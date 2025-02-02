<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode(['status' => 'error', 'message' => '未登录']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if ($data) {
    $stmt = $conn->prepare("UPDATE concerts SET singer_name = ?, content = ? WHERE id = ?");
    $stmt->bind_param("ssi", $data['singer_name'], $data['content'], $data['id']);
    
    if ($stmt->execute()) {
        // 获取更新后的数据
        $stmt = $conn->prepare("SELECT * FROM concerts WHERE id = ?");
        $stmt->bind_param("i", $data['id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $updated_concert = $result->fetch_assoc();
        
        echo json_encode([
            'status' => 'success',
            'message' => '更新成功',
            'data' => $updated_concert
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => '更新失败'
        ]);
    }
}
?> 