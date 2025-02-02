<?php
session_start();
require_once '../config.php';

// 检查管理员是否已登录
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // 如果未登录,跳转到登录页面
    header("Location: login.php"); 
    exit;
}

// 获取演唱会数量
$concerts_count = $conn->query("SELECT COUNT(*) FROM concerts")->fetch_row()[0];

// 在此处编写管理员操作的代码
// ...

// 处理网站设置更新
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_settings'])) {
    $site_title = $_POST['site_title'];
    $notice1 = $_POST['notice1']; 
    $notice2 = $_POST['notice2'];
    // 在此处处理上传的图片
    // ...
    
    // 更新数据库中的网站设置
    // ...
}

// 处理添加新的歌手演唱会信息
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_concert'])) {
    $singer_name = $_POST['singer_name'];
    $concert_city = $_POST['concert_city'];
    
    // 将新的演唱会信息插入数据库
    // ...
}

// 从数据库中获取现有的歌手演唱会信息
// ...

include 'header.php';
?>

<div class="pagetitle">
    <h1>仪表盘</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item active">仪表盘</li>
        </ol>
    </nav>
</div>

<section class="section dashboard">
    <div class="row">
        <div class="col-xxl-4 col-md-6">
            <div class="card info-card sales-card">
                <div class="card-body">
                    <h5 class="card-title">演唱会总数</h5>
                    <div class="d-flex align-items-center">
                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                            <i class="bi bi-music-note-list"></i>
                        </div>
                        <div class="ps-3">
                            <h6><?php echo $concerts_count; ?></h6>
                            <span class="text-muted small pt-2">当前演唱会数量</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 可以添加更多统计卡片 -->
    </div>
</section>

<style>
.pagetitle {
    margin-bottom: 10px;
}

.pagetitle h1 {
    font-size: 24px;
    margin-bottom: 0;
    font-weight: 600;
    color: #012970;
}

.breadcrumb {
    font-size: 14px;
    font-family: "Nunito", sans-serif;
    color: #899bbd;
    font-weight: 600;
}

.card-icon {
    font-size: 32px;
    line-height: 0;
    width: 64px;
    height: 64px;
    flex-shrink: 0;
    flex-grow: 0;
    color: #4154f1;
    background: #f6f6fe;
}

.sales-card .card-icon {
    color: #4154f1;
    background: #f6f6fe;
}

.card h6 {
    font-size: 28px;
    color: #012970;
    font-weight: 700;
    margin: 0;
    padding: 0;
}
</style>

<?php include 'footer.php'; ?>
