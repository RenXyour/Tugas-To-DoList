<?php
session_start();

// Inisialisasi session untuk to-do list 
if (!isset($_SESSION['todo_list'])) {
    $_SESSION['todo_list'] = [];
}

// Fungsi untuk mengurutkan task berdasarkan prioritas
function sortTasks(&$tasks) {
    usort($tasks, function($a, $b) {
        // Pastikan a dan b adalah array
        if (is_array($a) && is_array($b)) {
            $priority_order = ['Tinggi' => 1, 'Sedang' => 2, 'Rendah' => 3];
            return $priority_order[$a['priority']] <=> $priority_order[$b['priority']];
        }
        return 0; 
    });
}

// Fungsi untuk menambah atau memperbarui task
if (isset($_POST['save_task'])) {
    $task = htmlspecialchars($_POST['task']);
    $priority = htmlspecialchars($_POST['priority']);

    // Jika ada task yang sedang di-update
    if (isset($_POST['task_index']) && $_POST['task_index'] !== '') {
        $task_index = $_POST['task_index'];
        // Pastikan indeks valid sebelum mengupdate
        if (isset($_SESSION['todo_list'][$task_index])) {
            $_SESSION['todo_list'][$task_index] = ['task' => $task, 'priority' => $priority, 'completed' => false];
        }
    } else {
        // Jika menambahkan task baru
        if (!empty($task)) {
            $_SESSION['todo_list'][] = ['task' => $task, 'priority' => $priority, 'completed' => false];
        }
    }

    // Urutkan task setelah ditambahkan atau di-update
    sortTasks($_SESSION['todo_list']);

    // Redirect untuk menghindari pengiriman ulang form
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Fungsi untuk menghapus task
if (isset($_GET['delete_task'])) {
    $task_index = $_GET['delete_task'];
    if (isset($_SESSION['todo_list'][$task_index])) {
        unset($_SESSION['todo_list'][$task_index]);
        $_SESSION['todo_list'] = array_values($_SESSION['todo_list']); // Reindex array
    }

    // Redirect setelah menghapus task
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Fungsi untuk menandai task sebagai selesai
if (isset($_GET['complete_task'])) {
    $task_index = $_GET['complete_task'];
    if (isset($_SESSION['todo_list'][$task_index])) {
        $_SESSION['todo_list'][$task_index]['completed'] = !$_SESSION['todo_list'][$task_index]['completed'];
    }

    // Redirect setelah menandai task
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Menyiapkan data untuk pengeditan
$task_to_edit = '';
$priority_to_edit = '';
$task_index_to_edit = '';

if (isset($_GET['edit_task'])) {
    $task_index_to_edit = $_GET['edit_task'];
    // Periksa apakah task index benar-benar ada di dalam session
    if (isset($_SESSION['todo_list'][$task_index_to_edit])) {
        $task_to_edit = $_SESSION['todo_list'][$task_index_to_edit]['task'];
        $priority_to_edit = $_SESSION['todo_list'][$task_index_to_edit]['priority'];
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>To-Do-List Web</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="container mt-5">
        <div class="card shadow-lg">
            <div class="card-header bg-black text-white">
                <h2 class="card-title text-center">To-Do List Sederhana Dengan PHP</h2>
            </div>
            <div class="card-body">
                <!-- Form untuk menambah/memperbarui task -->
                <form action="" method="POST" class="mb-4">
                    <input type="hidden" name="task_index" value="<?php echo $task_index_to_edit; ?>">
                    <div class="mb-3">
                        <input type="text" name="task" class="form-control" placeholder="Tambahkan tugas baru" value="<?php echo $task_to_edit; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="priority" class="form-label">Prioritas:</label>
                        <select name="priority" id="priority" class="form-select" required>
                            <option value="Tinggi" <?php echo $priority_to_edit == 'Tinggi' ? 'selected' : ''; ?>>Tinggi</option>
                            <option value="Sedang" <?php echo $priority_to_edit == 'Sedang' ? 'selected' : ''; ?>>Sedang</option>
                            <option value="Rendah" <?php echo $priority_to_edit == 'Rendah' ? 'selected' : ''; ?>>Rendah</option>
                        </select>
                    </div>
                    <button type="submit" name="save_task" class="btn btn-success">
                        <?php echo $task_index_to_edit !== '' ? 'Update Task' : 'Tambah Task'; ?>
                    </button>
                </form>

                <ul class="list-group">
                    <?php if (!empty($_SESSION['todo_list'])): ?>
                        <?php foreach ($_SESSION['todo_list'] as $index => $task_data): ?>
                            <?php if (is_array($task_data)): // Pastikan $task_data adalah array ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center" style="<?php echo $task_data['completed'] ? 'text-decoration: line-through; color: grey;' : ''; ?>">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" onchange="location.href='?complete_task=<?php echo $index; ?>'" <?php echo $task_data['completed'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label" style="<?php echo $task_data['completed'] ? 'text-decoration: line-through; color: grey;' : ''; ?>">
                                            <strong><?php echo $task_data['task']; ?></strong> 
                                            <span class="badge bg-<?php echo $task_data['priority'] == 'Tinggi' ? 'danger' : ($task_data['priority'] == 'Sedang' ? 'warning' : 'secondary'); ?>">
                                                <?php echo $task_data['priority']; ?>
                                            </span>
                                        </label>
                                    </div>
                                    <div>
                                        <a href="?edit_task=<?php echo $index; ?>" class="btn btn-warning btn-sm">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="?delete_task=<?php echo $index; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus task ini?');">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li class="list-group-item">Tidak ada tugas</li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
