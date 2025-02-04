<?php
require_once 'config.php';

// 获取歌手名字
$singer = isset($_GET['singer']) ? trim($_GET['singer']) : '';
if (empty($singer)) {
    header('Location: index.php');
    exit;
}
$guanggao = isset($_GET['guanggao']) ? trim($_GET['guanggao']) : '';
// 查询演唱会信息
$stmt = $conn->prepare("SELECT * FROM concerts WHERE singer_name LIKE ? ORDER BY id DESC");
$search_term = "%$singer%";
$stmt->bind_param("s", $search_term);
$stmt->execute();
$result = $stmt->get_result();
$concerts = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>查询结果</title>
    <link rel="stylesheet" href="./assets/css/style.css">
    <style>
        .result-header {
            color: black;
            text-align: center;
            padding: 20px 20px 0;
            text-align: center;
            font-size: 22px;
            color: #333333;
            margin-bottom: 22px;
        }
        
        .singer-name {
            background-color: #0166CC;
            color: white;
            padding: 19px;
            text-align: center;
            font-size: 24px;
            margin: 10px 0;
        }
        
        .result-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }
        
        .result-table td {
            padding: 10px;
            border: 1px solid #A0CFFE;
            background: #fff;
        }
        
        .result-table td:first-child {
            width: 80px;
            color: #666;
            background: rgb(213, 235, 255);
        }
        
        .no-result {
            text-align: center;
            padding: 20px;
            color: #666;
        }
        .city {
            background-color:rgb(213, 235, 255);
            width: 30%;
        }
        .back-button {
            display: block;
            width: 100%;
            padding: 12px;
            background-color: #28a745;
            color: white;
            text-align: center;
            text-decoration: none;
            border: none;
            border-radius: 5px;
            margin-top: 20px;
            font-size: 16px;
        }
        
        @media (max-width: 768px) {
            .result-container {
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="main" style="padding: 0 20px;background-color:rgb(255, 255, 255);">
        <div class="result-header">
            查询结果
        </div>
        
        <?php if (!empty($concerts)): ?>
            <div class="singer-name">
                <?php echo htmlspecialchars($singer); ?>
            </div>
            
            <?php foreach ($concerts as $concert): ?>
                <table class="result-table">
                    <tr>
                        <td class="city">城市</td>
                        <td><?php echo htmlspecialchars($concert['content']); ?></td>
                    </tr>
                </table>
            <?php endforeach; ?>
                <!-- 添加额外的表格 -->
            <table class="result-table summary" 
            style="display: <?php echo $guanggao == 'no' ? 'none' : 'block'; ?>;"
            >
                <tr>
                    <td>这里是广告区域</td>
                    <td>我是一个广告区域，请在前端result.php修改
            </td>
                </tr>
            </table>

        <?php else: ?>
            <div class="no-result">
                "<?php echo htmlspecialchars($singer); ?>"暂无演出计划
            </div>
        <?php endif; ?>
        
        <a href="index.php<?php echo $guanggao == 'no' ? '?guanggao=no' : ''; ?>" class="back-button" style="background-color: #6c757d; margin-top: 10px;box-sizing: border-box;">
            返回首页
        </a>
    </div>
</body>
</html> 