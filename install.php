<?php
require_once 'config.php';

// 创建管理员表
$conn->query("CREATE TABLE IF NOT EXISTS admin (
    id INT AUTO_INCREMENT PRIMARY KEY COMMENT '管理员ID',
    username VARCHAR(255) NOT NULL UNIQUE COMMENT '管理员用户名',
    password VARCHAR(255) NOT NULL COMMENT '管理员密码（加密存储）',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间'
) COMMENT='管理员用户表，存储后台管理账号'");

// 创建演唱会信息表
$conn->query("CREATE TABLE IF NOT EXISTS concerts (
    id INT AUTO_INCREMENT PRIMARY KEY COMMENT '演唱会ID',
    singer_name VARCHAR(255) NOT NULL COMMENT '歌手名字',
    content TEXT NOT NULL COMMENT '内容'
) COMMENT='演唱会信息表，存储歌手和演唱会相关信息'");

// 创建网站设置表
$conn->query("CREATE TABLE IF NOT EXISTS site_settings (
    id INT AUTO_INCREMENT PRIMARY KEY COMMENT '网站设置ID',
    site_title VARCHAR(255) NOT NULL COMMENT '网站标题',
    announcement1 TEXT NOT NULL COMMENT '公告文字1',
    announcement2 TEXT NOT NULL COMMENT '公告文字2',
    homepage_image VARCHAR(255) NOT NULL COMMENT '首页展示的图片路径'
) COMMENT='网站设置表，存储网站基本信息，如标题、公告和首页图片'");

// 检查是否已存在网站设置
$result = $conn->query("SELECT * FROM site_settings LIMIT 1");
if ($result->num_rows == 0) {
    // 插入初始设置
    $conn->query("INSERT INTO site_settings (site_title, announcement1, announcement2, homepage_image) 
                 VALUES ('演唱会票务网站', '欢迎来到演唱会票务网站', '这里是公告2', '')");
}

// 检查是否已存在管理员账号
$result = $conn->query("SELECT * FROM admin LIMIT 1");
if ($result->num_rows == 0) {
    // 创建默认管理员账号 (用户名: admin, 密码: 123456)
    $default_password = password_hash('123456', PASSWORD_DEFAULT);
    $conn->query("INSERT INTO admin (username, password) VALUES ('admin', '$default_password')");
}

echo "初始化完成！<br>";
echo "默认管理员账号：admin<br>";
echo "默认密码：123456<br>";
echo "请立即登录后台修改密码！"; 