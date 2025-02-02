-- 创建管理员表 (admin)
CREATE TABLE admin (
    id INT AUTO_INCREMENT PRIMARY KEY COMMENT '管理员ID',
    username VARCHAR(255) NOT NULL UNIQUE COMMENT '管理员用户名',
    password VARCHAR(255) NOT NULL COMMENT '管理员密码（加密存储）',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间'
) COMMENT='管理员用户表，存储后台管理账号';

-- 创建演唱会信息表 (concerts)
CREATE TABLE concerts (
    id INT AUTO_INCREMENT PRIMARY KEY COMMENT '演唱会ID',
    singer_name VARCHAR(255) NOT NULL COMMENT '歌手名字',
    content TEXT NOT NULL COMMENT '内容'
) COMMENT='演唱会信息表，存储歌手和演唱会相关信息';

-- 创建网站设置表 (site_settings)
CREATE TABLE site_settings (
    id INT AUTO_INCREMENT PRIMARY KEY COMMENT '网站设置ID',
    site_title VARCHAR(255) NOT NULL COMMENT '网站标题',
    announcement1 TEXT NOT NULL COMMENT '公告文字1',
    announcement2 TEXT NOT NULL COMMENT '公告文字2',
    homepage_image VARCHAR(255) NOT NULL COMMENT '首页展示的图片路径'
) COMMENT='网站设置表，存储网站基本信息，如标题、公告和首页图片';
