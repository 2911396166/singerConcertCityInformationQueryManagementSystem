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
    $stmt = $conn->prepare("INSERT INTO concerts (singer_name, content) VALUES (?, ?)");
    $stmt->bind_param("ss", $data['singer_name'], $data['content']);
    
    if ($stmt->execute()) {
        $new_id = $conn->insert_id;
        // 获取新添加的数据
        $stmt = $conn->prepare("SELECT * FROM concerts WHERE id = ?");
        $stmt->bind_param("i", $new_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $new_concert = $result->fetch_assoc();
        
        echo json_encode([
            'status' => 'success',
            'message' => '演唱会信息已添加',
            'data' => $new_concert
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => '添加演唱会信息时出错'
        ]);
    }
}
?> 