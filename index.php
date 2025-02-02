<?php
require_once 'config.php';

// 获取网站设置
$result = $conn->query("SELECT * FROM site_settings LIMIT 1");
$settings = $result->fetch_assoc();

// 获取连接中guanggao= 是否是no
$guanggao = isset($_GET['guanggao']) ? trim($_GET['guanggao']) : '';
// 获取演唱会信息
$singer = isset($_GET['singer']) ? trim($_GET['singer']) : '';
if (!empty($singer)) {
    $stmt = $conn->prepare("SELECT * FROM concerts WHERE singer_name LIKE ? LIMIT 1");
    $search_term = "%$singer%";
    $stmt->bind_param("s", $search_term);
    $stmt->execute();
    $concert = $stmt->get_result()->fetch_assoc();
}

//echo htmlspecialchars($settings['site_title']);  标题
//echo htmlspecialchars($settings['homepage_image']);  首页图片
//echo nl2br(htmlspecialchars($settings['announcement1']));  公告
//echo nl2br(htmlspecialchars($settings['announcement2']));  公告

?>

<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo htmlspecialchars($settings['site_title']); ?></title>
    <!-- 引入css -->
    <link rel="stylesheet" href="./assets/css/style.css">
    <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"> -->

</head>
<body>

  <div class="main">
    <div class="header">
      <span><?php echo htmlspecialchars($settings['site_title']); ?></span>
    </div>
    <div class="content">
      <p class="contentText"><?php echo nl2br(htmlspecialchars($settings['announcement1'])); ?></p>
      <div class="contentImg">
        <img src="<?php echo '/uploads/'.$settings['homepage_image']; ?>" alt="公告图片" class="car-image">
      </div>
      <p class="contentText"><?php echo nl2br(htmlspecialchars($settings['announcement2'])); ?></p>
    </div>
    <!-- 下面是一个输入框输入歌手名字，并且前面有一个歌手label -->
    <form action="result.php" method="GET">
        <div class="inputBox">
            <label for="singer" class="singer-label">歌手</label>
            <input type="text" id="singer" name="singer" class="input-field" placeholder="请输入歌手名字" required>
        <?php  
    //如果guanggao=no 则跳转到result.php?guanggao=no
    if($guanggao == 'no'){
        echo '<input type="text"  name="guanggao" style="display: none;" value="no">';
    }
    ?>
          </div>
        <div class="search_box">
            <button type="submit" class="query-button">立即查询</button>
        </div>
    </form>
  </div>
    
</body>
</html>
