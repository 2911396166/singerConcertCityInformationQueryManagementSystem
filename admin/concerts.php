<?php
session_start();
require_once '../config.php';

// 检查管理员是否已登录 
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php"); 
    exit;
}

// 分页设置
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10; // 每页显示10条
$offset = ($page - 1) * $per_page;

// 获取总记录数
$total_records = $conn->query("SELECT COUNT(*) FROM concerts")->fetch_row()[0];
$total_pages = ceil($total_records / $per_page);

// 获取当前页的数据
$result = $conn->query("SELECT * FROM concerts ORDER BY id DESC LIMIT $offset, $per_page");
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
?>

<?php include 'header.php'; ?>

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
                <div class="search-box">
                    <input type="text" class="form-control" id="searchInput" placeholder="搜索歌手名字...">
                </div>
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
                    <?php foreach ($concerts as $concert) { ?>
                    <tr data-id="<?php echo $concert['id']; ?>">
                        <td><?php echo $concert['singer_name']; ?></td>
                        <td><?php echo $concert['content']; ?></td>
                        <td>
                            <button class="btn btn-sm btn-primary" onclick="editConcert(<?php echo htmlspecialchars(json_encode($concert)); ?>)">
                                <i class="bi bi-pencil"></i> 编辑
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="deleteConcert(<?php echo $concert['id']; ?>)">
                                <i class="bi bi-trash"></i> 删除
                            </button>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
        
        <!-- 分页 -->
        <?php if ($total_pages > 1): ?>
        <nav aria-label="Page navigation" class="mt-4">
            <ul class="pagination justify-content-center">
                <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $page-1; ?>" tabindex="-1">上一页</a>
                </li>
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                </li>
                <?php endfor; ?>
                <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $page+1; ?>">下一页</a>
                </li>
            </ul>
        </nav>
        <?php endif; ?>
    </div>
</div>

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

// 添加搜索功能
document.getElementById('searchInput').addEventListener('keyup', function() {
    let searchText = this.value.toLowerCase();
    let tableRows = document.querySelectorAll('tbody tr');
    
    tableRows.forEach(row => {
        let singerName = row.querySelector('td:first-child').textContent.toLowerCase();
        if (singerName.includes(searchText)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});
</script>

<?php include 'footer.php'; ?> 