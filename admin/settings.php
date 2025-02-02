<?php
session_start();
require_once '../config.php';

// 检查管理员是否已登录
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

// 处理网站设置更新
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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

// 从数据库中获取当前的网站设置
$result = $conn->query("SELECT * FROM site_settings LIMIT 1");
$settings = $result->fetch_assoc();
?>

<?php include 'header.php'; ?>

<div class="card">
    <div class="card-body">
        <h5 class="card-title">网站基本设置</h5>
        <form method="post" enctype="multipart/form-data" class="row g-3">
            <div class="col-12">
                <label for="site_title" class="form-label">网站标题</label>
                <input type="text" class="form-control" id="site_title" name="site_title" value="<?php echo $settings['site_title']; ?>" required>
            </div>
            <div class="col-12">
                <label for="announcement1" class="form-label">公告1</label>
                <textarea class="form-control" id="announcement1" name="announcement1" rows="3" required><?php echo $settings['announcement1']; ?></textarea>
            </div>
            <div class="col-12">
                <label for="announcement2" class="form-label">公告2</label>
                <textarea class="form-control" id="announcement2" name="announcement2" rows="3" required><?php echo $settings['announcement2']; ?></textarea>
            </div>
            <div class="col-12">
                <label for="homepage_image" class="form-label">首页图片</label>
                <input class="form-control" type="file" id="homepage_image" name="homepage_image">
                <?php if (!empty($settings['homepage_image'])): ?>
                <div class="mt-2">
                    <img src="../uploads/<?php echo $settings['homepage_image']; ?>" alt="当前图片" class="img-thumbnail" style="max-width: 200px;">
                </div>
                <?php endif; ?>
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary">保存更改</button>
            </div>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?> 