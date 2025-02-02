<?php
session_start();
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // 使用预处理语句防止SQL注入
    $stmt = $conn->prepare("SELECT * FROM admin WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $row['id'];
            header("Location: index.php");
            exit;
        }
    }

    $error = "无效的用户名或密码";
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>管理员登录</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .form-signin {
            width: 100%;
            max-width: 330px;
            padding: 15px;
            margin: auto;
        }
    </style>
</head>
<body>
    <main class="form-signin text-center">
        <form method="post">
            <h1 class="h3 mb-3 fw-normal">管理员登录</h1>
            <?php if (isset($error)) { echo "<p class='text-danger'>$error</p>"; } ?>
            <div class="form-floating mb-2">
                <input type="text" class="form-control" id="username" name="username" placeholder="用户名" required autofocus>
                <label for="username">用户名</label>
            </div>
            <div class="form-floating mb-2">
                <input type="password" class="form-control" id="password" name="password" placeholder="密码" required>
                <label for="password">密码</label>
            </div>
            <button class="w-100 btn btn-lg btn-primary" type="submit">登录</button>
        </form>
    </main>
</body>
</html> 