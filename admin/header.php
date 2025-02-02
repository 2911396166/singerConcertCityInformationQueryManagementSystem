<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>管理员面板</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --primary-color: #4154f1;
            --secondary-color: #717ff5;
            --text-color: #012970;
            --light-text: #4154f1;
        }
        
        body {
            background-color: #f6f9ff;
            color: var(--text-color);
        }
        
        .sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 995;
            display: none;
            transition: all 0.3s;
        }

        .sidebar {
            position: fixed;
            top: 60px;
            left: 0;
            bottom: 0;
            width: 300px;
            z-index: 996;
            transition: all 0.3s;
            padding: 20px;
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: #aab7cf transparent;
            box-shadow: 0px 0px 20px rgba(1, 41, 112, 0.1);
            background-color: #fff;
        }

        .sidebar-nav {
            padding: 0;
            margin: 0;
            list-style: none;
        }

        .sidebar-nav li {
            padding: 0;
            margin: 0;
            list-style: none;
        }

        .sidebar-nav .nav-item {
            margin-bottom: 5px;
        }

        .sidebar-nav .nav-link {
            display: flex;
            align-items: center;
            font-size: 15px;
            font-weight: 600;
            color: var(--text-color);
            padding: 10px 15px;
            border-radius: 4px;
            background: #f6f9ff;
        }

        .sidebar-nav .nav-link i {
            font-size: 16px;
            margin-right: 10px;
            color: var(--text-color);
        }

        .sidebar-nav .nav-link.active {
            background-color: var(--primary-color);
            color: #fff;
        }

        .sidebar-nav .nav-link.active i {
            color: #fff;
        }

        .sidebar-nav .nav-link:hover {
            background-color: var(--secondary-color);
            color: #fff;
        }

        .sidebar-nav .nav-link:hover i {
            color: #fff;
        }

        .navbar {
            padding: 0 20px;
            background-color: #fff;
            box-shadow: 0px 0px 20px rgba(1, 41, 112, 0.1);
            height: 60px;
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 997;
        }

        .navbar-brand {
            font-size: 26px;
            font-weight: 700;
            color: var(--text-color) !important;
        }

        main {
            margin-top: 60px;
            margin-left: 300px;
            padding: 20px;
            transition: all 0.3s;
        }

        .card {
            margin-bottom: 30px;
            border: none;
            border-radius: 5px;
            box-shadow: 0px 0 30px rgba(1, 41, 112, 0.1);
        }

        .card-header {
            background-color: #fff;
            border-bottom: 1px solid #ededed;
            padding: 15px;
        }

        .card-title {
            padding: 20px 0 15px 0;
            font-size: 18px;
            font-weight: 500;
            color: var(--text-color);
            font-family: "Poppins", sans-serif;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }

        .form-control:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 0.25rem rgba(65, 84, 241, 0.25);
        }

        @media (max-width: 1199px) {
            .sidebar {
                left: -300px;
            }

            .sidebar.active {
                left: 0;
            }

            main {
                margin-left: 0;
            }

            main.active {
                margin-left: 300px;
            }
        }

        .search-bar {
            min-width: 360px;
            padding: 0 20px;
        }

        @media (max-width: 1199px) {
            .search-bar {
                position: fixed;
                top: 50px;
                left: 0;
                right: 0;
                padding: 20px;
                box-shadow: 0px 0px 15px 0px rgba(1, 41, 112, 0.1);
                background: white;
                z-index: 9999;
                transition: 0.3s;
                visibility: hidden;
                opacity: 0;
            }

            .search-bar-show {
                top: 60px;
                visibility: visible;
                opacity: 1;
            }
        }

        .dropdown-menu {
            border-radius: 4px;
            padding: 10px 0;
            animation-name: dropdown-animate;
            animation-duration: 0.2s;
            animation-fill-mode: both;
            border: 0;
            box-shadow: 0 5px 30px 0 rgba(82, 63, 105, 0.2);
        }

        .dropdown-menu .dropdown-header {
            text-align: center;
            font-size: 15px;
            padding: 10px 25px;
        }

        .dropdown-menu .dropdown-footer a {
            color: #444444;
            text-decoration: underline;
        }

        .dropdown-menu .dropdown-footer a:hover {
            text-decoration: none;
        }

        .dropdown-menu .dropdown-divider {
            color: #a5c5fe;
            margin: 0;
        }

        .dropdown-menu .dropdown-item {
            font-size: 14px;
            padding: 10px 15px;
            transition: 0.3s;
        }

        .dropdown-menu .dropdown-item i {
            margin-right: 10px;
            font-size: 18px;
            line-height: 0;
        }

        .dropdown-menu .dropdown-item:hover {
            background-color: #f6f9ff;
        }

        @keyframes dropdown-animate {
            0% {
                opacity: 0;
            }

            100% {
                opacity: 1;
            }

            0% {
                opacity: 0;
            }
        }

        .navbar-toggler {
            padding: 0.25rem 0.75rem;
            font-size: 1.25rem;
            background: transparent;
            border: 1px solid rgba(0, 0, 0, 0.1);
            border-radius: 0.25rem;
            color: var(--text-color);
        }

        .navbar-toggler:focus {
            box-shadow: none;
        }

        /* Sweet Alert 自定义样式 */
        .custom-swal-input {
            width: 100% !important;
            padding: 0.75rem !important;
            margin: 0.5rem 0 !important;
            border: 1px solid #ced4da !important;
            border-radius: 0.25rem !important;
            font-size: 1rem !important;
        }

        .custom-swal-textarea {
            width: 100% !important;
            min-height: 120px !important;
            padding: 0.75rem !important;
            margin: 0.5rem 0 !important;
            border: 1px solid #ced4da !important;
            border-radius: 0.25rem !important;
            font-size: 1rem !important;
            resize: vertical !important;
        }

        .custom-swal-input:focus,
        .custom-swal-textarea:focus {
            border-color: var(--primary-color) !important;
            box-shadow: 0 0 0 0.2rem rgba(65, 84, 241, 0.25) !important;
            outline: none !important;
        }

        @media (max-width: 991.98px) {
            .sidebar {
                left: -300px;
                transition: 0.3s;
            }

            .sidebar.show {
                left: 0;
            }

            .sidebar-overlay.show {
                display: block;
            }

            main {
                margin-left: 0 !important;
                padding: 20px;
            }
        }

        @media (min-width: 992px) {
            main {
                margin-left: 300px;
            }
        }
    </style>
</head>
<body>
    <div class="sidebar-overlay"></div>
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                管理员面板
            </a>
            <button class="navbar-toggler" type="button" id="sidebarToggle">
                <i class="bi bi-list"></i>
            </button>
            <div class="ms-auto">
                <div class="dropdown">
                    <button class="btn dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-circle"></i> 管理员
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton">
                        <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right"></i> 退出登录</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>
    
    <aside id="sidebar" class="sidebar">
        <ul class="sidebar-nav">
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>" href="index.php">
                    <i class="bi bi-grid"></i>
                    <span>仪表盘</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''; ?>" href="settings.php">
                    <i class="bi bi-gear"></i>
                    <span>网站设置</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'concerts.php' ? 'active' : ''; ?>" href="concerts.php">
                    <i class="bi bi-music-note-list"></i>
                    <span>演唱会管理</span>
                </a>
            </li>
        </ul>
    </aside>

    <main>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.querySelector('.sidebar-overlay');

    function toggleSidebar() {
        sidebar.classList.toggle('show');
        overlay.classList.toggle('show');
    }

    sidebarToggle.addEventListener('click', function(e) {
        e.preventDefault();
        toggleSidebar();
    });

    // 点击遮罩层关闭侧边栏
    overlay.addEventListener('click', function() {
        toggleSidebar();
    });

    // 监听窗口大小变化
    window.addEventListener('resize', function() {
        if (window.innerWidth >= 992) {
            sidebar.classList.remove('show');
            overlay.classList.remove('show');
        }
    });
});
</script>