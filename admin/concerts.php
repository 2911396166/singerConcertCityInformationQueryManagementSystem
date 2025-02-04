<?php
session_start();
require_once '../config.php';

// 检查管理员是否已登录 
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php"); 
    exit;
}

// 获取搜索关键词
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// 分页设置
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10; // 每页显示10条

// 根据是否有搜索词来构建不同的SQL
if (!empty($search)) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM concerts WHERE singer_name LIKE ?");
    $search_term = "%$search%";
    $stmt->bind_param("s", $search_term);
    $stmt->execute();
    $total_records = $stmt->get_result()->fetch_row()[0];
} else {
    $total_records = $conn->query("SELECT COUNT(*) FROM concerts")->fetch_row()[0];
}

$total_pages = ceil($total_records / $per_page);
$page = max(1, min($page, $total_pages)); // 确保页码在有效范围内
$offset = ($page - 1) * $per_page;

// 获取当前页的数据
if (!empty($search)) {
    $stmt = $conn->prepare("SELECT * FROM concerts WHERE singer_name LIKE ? ORDER BY id DESC LIMIT ?, ?");
    $search_term = "%$search%";
    $stmt->bind_param("sii", $search_term, $offset, $per_page);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query("SELECT * FROM concerts ORDER BY id DESC LIMIT $offset, $per_page");
}
$concerts = $result->fetch_all(MYSQLI_ASSOC);

// 处理添加新的歌手演唱会信息
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $singer_name = $_POST['singer_name'];
    $content = $_POST['content'];
    
    // 将新的演唱会信息插入数据库
    $stmt = $conn->prepare("INSERT INTO concerts (singer_name, content) VALUES (?, ?)");
    $stmt->bind_param("ss", $singer_name, $content);
    
    if ($stmt->execute()) {
        echo "<script>Swal.fire('成功', '演唱会信息已添加', 'success')</script>";
    } else {
        echo "<script>Swal.fire('错误', '添加演唱会信息时出错', 'error')</script>";
    }
}

include 'header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center mb-4">
    <h1 class="h2">演唱会管理</h1>
    <button class="btn btn-primary" onclick="showAddModal()">
        <i class="bi bi-plus-lg"></i> 添加演唱会
    </button>
</div>

<div class="card">
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-6">
                <form class="search-form" method="GET" action="">
                    <div class="input-group">
                        <input type="text" class="form-control" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="搜索歌手名字...">
                        <button class="btn btn-primary" type="submit">
                            <i class="bi bi-search"></i> 搜索
                        </button>
                        <?php if (!empty($search)): ?>
                        <a href="concerts.php" class="btn btn-secondary">
                            <i class="bi bi-x-lg"></i> 清除
                        </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>歌手</th>
                        <th>内容</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody id="concertsTableBody">
                    <?php if (empty($concerts)): ?>
                    <tr>
                        <td colspan="3" class="text-center">暂无数据</td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($concerts as $concert): ?>
                        <tr data-id="<?php echo $concert['id']; ?>">
                            <td><?php echo htmlspecialchars($concert['singer_name']); ?></td>
                            <td><?php echo htmlspecialchars($concert['content']); ?></td>
                            <td>
                                <button class="btn btn-sm btn-primary" onclick='editConcert(<?php echo htmlspecialchars(json_encode($concert)); ?>)'>
                                    <i class="bi bi-pencil"></i> 编辑
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="deleteConcert(<?php echo $concert['id']; ?>)">
                                    <i class="bi bi-trash"></i> 删除
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- 优化的分页 -->
        <?php if ($total_pages > 1): ?>
        <nav aria-label="Page navigation" class="mt-4">
            <ul class="pagination justify-content-center">
                <!-- 首页 -->
                <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=1<?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?>" aria-label="First">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>
                
                <!-- 页码 -->
                <?php
                $start_page = max(1, min($page - 2, $total_pages - 4));
                $end_page = min($total_pages, $start_page + 4);
                
                for ($i = $start_page; $i <= $end_page; $i++):
                ?>
                <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $i; ?><?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                </li>
                <?php endfor; ?>
                
                <!-- 末页 -->
                <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $total_pages; ?><?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?>" aria-label="Last">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
            </ul>
        </nav>
        <?php endif; ?>
    </div>
</div>

<style>
/* 美化分页样式 */
.pagination {
    margin-bottom: 0;
}

.page-link {
    color: var(--primary-color);
    padding: 0.5rem 0.75rem;
    margin: 0 2px;
    border-radius: 4px !important;
}

.page-item.active .page-link {
    background-color: var(--primary-color);
    border-color: var(--primary-color);
}

.page-link:hover {
    color: var(--primary-color);
    background-color: #e9ecef;
}

.page-item.disabled .page-link {
    color: #6c757d;
}

/* 搜索框样式 */
.search-form .input-group {
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.search-form .form-control {
    border-right: none;
}

.search-form .form-control:focus {
    box-shadow: none;
}

.search-form .btn {
    border-color: #ced4da;
}

.search-form .btn-secondary {
    background-color: #fff;
    color: #6c757d;
}

.search-form .btn-secondary:hover {
    background-color: #f8f9fa;
}
</style>

<script>
function showAddModal() {
    Swal.fire({
        title: '添加演唱会信息',
        html: `
            <div class="mb-3">
                <input id="swal-singer" class="custom-swal-input" placeholder="歌手名字">
            </div>
            <div class="mb-3">
                <textarea id="swal-content" class="custom-swal-textarea" placeholder="演唱会内容"></textarea>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: '添加',
        cancelButtonText: '取消',
        customClass: {
            confirmButton: 'btn btn-primary',
            cancelButton: 'btn btn-secondary'
        },
        buttonsStyling: false,
        preConfirm: () => {
            const singer = document.getElementById('swal-singer').value;
            const content = document.getElementById('swal-content').value;
            if (!singer || !content) {
                Swal.showValidationMessage('请填写所有必填字段');
                return false;
            }
            return {
                singer_name: singer,
                content: content
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('add_concert.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(result.value)
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: '成功',
                        text: data.message,
                        customClass: {
                            confirmButton: 'btn btn-primary'
                        },
                        buttonsStyling: false
                    }).then(() => {
                        // 添加新行到表格顶部
                        const tbody = document.getElementById('concertsTableBody');
                        const newRow = document.createElement('tr');
                        newRow.setAttribute('data-id', data.data.id);
                        newRow.innerHTML = `
                            <td>${data.data.singer_name}</td>
                            <td>${data.data.content}</td>
                            <td>
                                <button class="btn btn-sm btn-primary" onclick='editConcert(${JSON.stringify(data.data)})'>
                                    <i class="bi bi-pencil"></i> 编辑
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="deleteConcert(${data.data.id})">
                                    <i class="bi bi-trash"></i> 删除
                                </button>
                            </td>
                        `;
                        tbody.insertBefore(newRow, tbody.firstChild);
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: '错误',
                        text: data.message,
                        customClass: {
                            confirmButton: 'btn btn-primary'
                        },
                        buttonsStyling: false
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: '错误',
                    text: '添加失败',
                    customClass: {
                        confirmButton: 'btn btn-primary'
                    },
                    buttonsStyling: false
                });
            });
        }
    });
}

function updateTableRow(concert) {
    const row = document.querySelector(`tr[data-id="${concert.id}"]`);
    if (row) {
        row.innerHTML = `
            <td>${concert.singer_name}</td>
            <td>${concert.content}</td>
            <td>
                <button class="btn btn-sm btn-primary" onclick='editConcert(${JSON.stringify(concert)})'>
                    <i class="bi bi-pencil"></i> 编辑
                </button>
                <button class="btn btn-sm btn-danger" onclick="deleteConcert(${concert.id})">
                    <i class="bi bi-trash"></i> 删除
                </button>
            </td>
        `;
    }
}

function editConcert(concert) {
    Swal.fire({
        title: '编辑演唱会信息',
        html: `
            <div class="mb-3">
                <input id="swal-singer" class="custom-swal-input" value="${concert.singer_name}" placeholder="歌手名字">
            </div>
            <div class="mb-3">
                <textarea id="swal-content" class="custom-swal-textarea" placeholder="演唱会内容">${concert.content}</textarea>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: '保存',
        cancelButtonText: '取消',
        customClass: {
            confirmButton: 'btn btn-primary',
            cancelButton: 'btn btn-secondary'
        },
        buttonsStyling: false,
        preConfirm: () => {
            const singer = document.getElementById('swal-singer').value;
            const content = document.getElementById('swal-content').value;
            if (!singer || !content) {
                Swal.showValidationMessage('请填写所有必填字段');
                return false;
            }
            return {
                id: concert.id,
                singer_name: singer,
                content: content
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('update_concert.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(result.value)
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: '成功',
                        text: data.message,
                        customClass: {
                            confirmButton: 'btn btn-primary'
                        },
                        buttonsStyling: false
                    });
                    updateTableRow(data.data);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: '错误',
                        text: data.message,
                        customClass: {
                            confirmButton: 'btn btn-primary'
                        },
                        buttonsStyling: false
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: '错误',
                    text: '更新失败',
                    customClass: {
                        confirmButton: 'btn btn-primary'
                    },
                    buttonsStyling: false
                });
            });
        }
    });
}

function deleteConcert(id) {
    Swal.fire({
        title: '确认删除',
        text: '您确定要删除这条演唱会信息吗？',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: '删除',
        cancelButtonText: '取消',
        customClass: {
            confirmButton: 'btn btn-danger',
            cancelButton: 'btn btn-secondary'
        },
        buttonsStyling: false
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('delete_concert.php?id=' + id)
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: '成功',
                        text: data.message,
                        customClass: {
                            confirmButton: 'btn btn-primary'
                        },
                        buttonsStyling: false
                    }).then(() => {
                        // 从DOM中移除被删除的行
                        const row = document.querySelector(`tr[data-id="${id}"]`);
                        if (row) {
                            row.remove();
                            
                            // 检查表格是否为空
                            const tbody = document.getElementById('concertsTableBody');
                            if (tbody.children.length === 0) {
                                // 如果当前页面没有数据了，刷新页面
                                location.reload();
                            }
                        }
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: '错误',
                        text: data.message,
                        customClass: {
                            confirmButton: 'btn btn-primary'
                        },
                        buttonsStyling: false
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: '错误',
                    text: '删除失败',
                    customClass: {
                        confirmButton: 'btn btn-primary'
                    },
                    buttonsStyling: false
                });
            });
        }
    });
}
</script>

<?php include 'footer.php'; ?> 