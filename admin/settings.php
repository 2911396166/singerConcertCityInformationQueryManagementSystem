<?php
session_start();
require_once '../config.php';

// 检查管理员是否已登录
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

// 检查并初始化网站设置
$result = $conn->query("SELECT COUNT(*) as count FROM site_settings");
$row = $result->fetch_assoc();
if ($row['count'] == 0) {
    // 如果没有设置记录，创建一条初始记录
    $conn->query("INSERT INTO site_settings (site_title, announcement1, announcement2, homepage_image) 
                 VALUES ('演唱会票务网站', '欢迎来到演唱会票务网站', '这里是公告2', '')");
}

// 从数据库中获取当前的网站设置
$result = $conn->query("SELECT * FROM site_settings LIMIT 1");
$settings = $result->fetch_assoc();

// 如果依然没有获取到设置，使用默认值
if (!$settings) {
    $settings = [
        'site_title' => '演唱会票务网站',
        'announcement1' => '欢迎来到演唱会票务网站',
        'announcement2' => '这里是公告2',
        'homepage_image' => ''
    ];
}

// 处理网站设置更新
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_settings'])) {
    $site_title = $_POST['site_title'];
    $announcement1 = $_POST['announcement1'];
    $announcement2 = $_POST['announcement2'];

    // 处理上传的图片
    if (isset($_FILES['homepage_image']) && $_FILES['homepage_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/';
        
        // 检查上传目录是否存在，如果不存在则创建
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        // 生成唯一的文件名
        $filename = uniqid() . '_' . basename($_FILES['homepage_image']['name']);
        $filepath = $upload_dir . $filename;
        
        // 尝试移动文件
        if (move_uploaded_file($_FILES['homepage_image']['tmp_name'], $filepath)) {
            // 更新数据库中的网站设置
            $stmt = $conn->prepare("UPDATE site_settings SET site_title = ?, announcement1 = ?, announcement2 = ?, homepage_image = ?");
            $stmt->bind_param("ssss", $site_title, $announcement1, $announcement2, $filename);
        } else {
            echo "<script>Swal.fire('错误', '上传文件失败', 'error')</script>";
            exit;
        }
    } else {
        // 更新数据库中的网站设置(不更新图片)
        $stmt = $conn->prepare("UPDATE site_settings SET site_title = ?, announcement1 = ?, announcement2 = ?");
        $stmt->bind_param("sss", $site_title, $announcement1, $announcement2);
    }

    if ($stmt->execute()) {
        echo "<script>Swal.fire('成功', '网站设置已更新', 'success')</script>";
    } else {
        echo "<script>Swal.fire('错误', '更新网站设置时出错', 'error')</script>";
    }
}

// 处理密码修改
// 处理密码修改
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // 验证新密码是否匹配
    if ($new_password !== $confirm_password) {
        echo "<script>Swal.fire('错误', '两次输入的新密码不匹配', 'error')</script>";
        exit;
    }

    // 进一步验证密码长度
    if (strlen($new_password) < 6) {
        echo "<script>Swal.fire('错误', '新密码长度不能少于6个字符', 'error')</script>";
        exit;
    }

    // 确保 session 里有 admin_id
    if (!isset($_SESSION['admin_id'])) {
        echo "<script>Swal.fire('错误', '未找到管理员信息，请重新登录', 'error')</script>";
        exit;
    }

    $admin_id = $_SESSION['admin_id'];

    // 查询当前管理员密码
    $stmt = $conn->prepare("SELECT password FROM admin WHERE id = ?");
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $admin = $result->fetch_assoc();

    if (!$admin) {
        echo "<script>Swal.fire('错误', '管理员信息不存在', 'error')</script>";
        exit;
    }

    // 验证旧密码
    if (!password_verify($old_password, $admin['password'])) {
        echo "<script>Swal.fire('错误', '原密码不正确', 'error')</script>";
        exit;
    }

    // 更新新密码
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE admin SET password = ? WHERE id = ?");
    $stmt->bind_param("si", $hashed_password, $admin_id);

    if ($stmt->execute()) {
        echo "<script>Swal.fire('成功', '密码已更新，请重新登录', 'success').then(() => { window.location.href = 'logout.php'; })</script>";
    } else {
        echo "<script>Swal.fire('错误', '密码更新失败', 'error')</script>";
    }
}


?>

<?php include 'header.php'; ?>

<div class="card">
    <div class="card-body">
        <h5 class="card-title">网站基本设置</h5>
        <form method="post" enctype="multipart/form-data" class="row g-3" id="settingsForm">
            <div class="col-12">
                <label for="site_title" class="form-label">网站标题</label>
                <input type="text" class="form-control" id="site_title" name="site_title" value="<?php echo htmlspecialchars($settings['site_title']); ?>" required>
            </div>
            <div class="col-12">
                <label for="announcement1" class="form-label">公告1</label>
                <textarea class="form-control" id="announcement1" name="announcement1" rows="3" required><?php echo htmlspecialchars($settings['announcement1']); ?></textarea>
            </div>
            <div class="col-12">
                <label for="announcement2" class="form-label">公告2</label>
                <textarea class="form-control" id="announcement2" name="announcement2" rows="3" required><?php echo htmlspecialchars($settings['announcement2']); ?></textarea>
            </div>
            <div class="col-12">
                <label for="homepage_image" class="form-label">首页图片</label>
                <input class="form-control" type="file" id="homepage_image" name="homepage_image">
                <?php if (!empty($settings['homepage_image'])): ?>
                <div class="mt-2">
                    <img src="../uploads/<?php echo htmlspecialchars($settings['homepage_image']); ?>" alt="当前图片" class="img-thumbnail" style="max-width: 200px;">
                </div>
                <?php endif; ?>
            </div>
            <div class="col-12">
                <button type="submit" name="update_settings" class="btn btn-primary">保存更改</button>
            </div>
        </form>
    </div>
</div>

<!-- 密码修改卡片 -->
<div class="card mt-4">
    <div class="card-body">
        <h5 class="card-title">修改密码</h5>
        <form method="post" class="row g-3" id="passwordForm">
            <div class="col-12">
                <label for="old_password" class="form-label">原密码</label>
                <input type="password" class="form-control" id="old_password" name="old_password" required>
            </div>
            <div class="col-12">
                <label for="new_password" class="form-label">新密码</label>
                <input type="password" class="form-control" id="new_password" name="new_password" required>
            </div>
            <div class="col-12">
                <label for="confirm_password" class="form-label">确认新密码</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
            </div>
            <div class="col-12">
                <button type="submit" name="change_password" class="btn btn-warning">
                    <i class="bi bi-key"></i> 修改密码
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// 添加密码验证
document.getElementById('passwordForm').addEventListener('submit', function(e) {
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    
    if (newPassword !== confirmPassword) {
        e.preventDefault();
        Swal.fire('错误', '两次输入的新密码不匹配', 'error');
        return;
    }
    
    if (newPassword.length < 6) {
        e.preventDefault();
        Swal.fire('错误', '新密码长度不能少于6个字符', 'error');
        return;
    }
});
</script>

<?php include 'footer.php'; ?> 