<?php
session_start();
require_once '../config.php';

// 检查管理员是否已登录
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

// 检查是否提供了有效的演唱会ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: concerts.php");
    exit;
}

$concert_id = $_GET['id'];

// 处理演唱会信息更新
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $singer_name = $_POST['singer_name'];
    $content = $_POST['content'];

    // 更新数据库中的演唱会信息
    $stmt = $conn->prepare("UPDATE concerts SET singer_name = ?, content = ? WHERE id = ?");
    $stmt->bind_param("ssi", $singer_name, $content, $concert_id);
    
    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "error";
    }
}

// 从数据库中获取演唱会信息
$stmt = $conn->prepare("SELECT * FROM concerts WHERE id = ?");
$stmt->bind_param("i", $concert_id);
$stmt->execute();
$result = $stmt->get_result();
$concert = $result->fetch_assoc();
?>

<?php include 'header.php'; ?>

<h1>编辑演唱会信息</h1>
<form method="post">
    <div class="mb-3">
        <label for="singer_name" class="form-label">歌手名字:</label>
        <input type="text" id="singer_name" name="singer_name" class="form-control" value="<?php echo $concert['singer_name']; ?>" required>
    </div>
    <div class="mb-3">
        <label for="content" class="form-label">内容:</label>
        <textarea id="content" name="content" class="form-control" rows="3" required><?php echo $concert['content']; ?></textarea>
    </div>
    <button type="submit" class="btn btn-primary">更新演唱会信息</button>
</form>

<script>
document.querySelector('form').addEventListener('submit', function(e) {
    e.preventDefault();
    fetch('edit_concert.php?id=<?php echo $concert_id; ?>', {
        method: 'POST',
        body: new FormData(this)
    })
    .then(response => response.text())
    .then(data => {
        if (data.trim() === 'success') {
            Swal.fire('成功', '演唱会信息已更新', 'success').then(() => {
                window.location.href = 'concerts.php';
            });
        } else {
            Swal.fire('错误', '更新演唱会信息时出错', 'error');
        }
    });
});
</script>

<?php include 'footer.php'; ?> 