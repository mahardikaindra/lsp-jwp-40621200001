<?php
// Redirect hanya saat index.php diakses langsung, bukan saat /todolist diproses.
$requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if ($requestPath === '/index.php') {
    header('Location: /todolist', true, 302);
    exit;
}

// Memulai sesi untuk menyimpan data array agar tidak hilang saat halaman direfresh
session_start();

// Inisialisasi struktur data array of objects (associative array di PHP) 
// Sesuai dengan skenario dari Kelompok Pekerjaan 2
if (!isset($_SESSION['tasks'])) {
    $_SESSION['tasks'] = [
        ["id" => 1, "title" => "Belajar PHP", "status" => "belum"],
        ["id" => 2, "title" => "Kerjakan tugas UX", "status" => "selesai"]
    ];
}

/**
 * Mengambil seluruh daftar tugas.
 */
function getTasks() {
    return $_SESSION['tasks'];
}

/**
 * Menambahkan tugas baru ke dalam array.
 */
function addTask($title) {
    // Mencari ID tertinggi untuk auto-increment manual
    $newId = count($_SESSION['tasks']) > 0 ? max(array_column($_SESSION['tasks'], 'id')) + 1 : 1;
    
    $_SESSION['tasks'][] = [
        "id" => $newId,
        "title" => htmlspecialchars(trim($title)), // Sanitasi input mencegah XSS
        "status" => "belum"
    ];
}

/**
 * Mengubah status tugas (belum -> selesai, selesai -> belum).
 */
function toggleTask($id) {
    foreach ($_SESSION['tasks'] as &$task) {
        if ($task['id'] == $id) {
            $task['status'] = ($task['status'] == 'belum') ? 'selesai' : 'belum';
            break;
        }
    }
}

/**
 * Menghapus tugas dari array berdasarkan ID.
 */
function deleteTask($id) {
    foreach ($_SESSION['tasks'] as $key => $task) {
        if ($task['id'] == $id) {
            unset($_SESSION['tasks'][$key]);
            break;
        }
    }
    // Re-index array untuk merapikan urutan index setelah penghapusan
    $_SESSION['tasks'] = array_values($_SESSION['tasks']);
}

/**
 * Menghapus beberapa tugas berdasarkan ID yang dipilih.
 */
function deleteSelectedTasks($ids) {
    $selectedIds = array_map('intval', (array) $ids);

    $_SESSION['tasks'] = array_values(array_filter(
        $_SESSION['tasks'],
        function ($task) use ($selectedIds) {
            return !in_array((int) $task['id'], $selectedIds, true);
        }
    ));
}

function completeSelectedTasks($ids) {
    $selectedIds = array_map('intval', (array) $ids);

    foreach ($_SESSION['tasks'] as &$task) {
        if (in_array((int) $task['id'], $selectedIds, true)) {
            $task['status'] = 'selesai';
        }
    }
    unset($task);
}

/**
 * Fungsi untuk merender ikon SVG berdasarkan nama ikon
 */
function renderIcon($name) {
    $icons = [
        'plus' => '<path d="M10 3a1 1 0 0 1 1 1v5h5a1 1 0 1 1 0 2h-5v5a1 1 0 1 1-2 0v-5H4a1 1 0 1 1 0-2h5V4a1 1 0 0 1 1-1Z" />',
        'trash' => '<path d="M6 4.5A1.5 1.5 0 0 1 7.5 3h5A1.5 1.5 0 0 1 14 4.5V5h1.5a1 1 0 1 1 0 2h-.7l-.68 9.11A2.5 2.5 0 0 1 11.63 18H8.37a2.5 2.5 0 0 1-2.49-2.89L5.2 7H4.5a1 1 0 0 1 0-2H6v-.5Zm2 2.5h4L11.8 15H8.2L8 7Zm1.5-2v.5h3v-.5a.5.5 0 0 0-.5-.5h-2a.5.5 0 0 0-.5.5Z" />',
        'check' => '<path fill-rule="evenodd" d="M16.700 5.300a1 1 0 0 1 0 1.400l-7 7a1 1 0 0 1-1.400 0l-3-3a1 1 0 1 1 1.400-1.400L9 11.600l6.300-6.300a1 1 0 0 1 1.400 0Z" clip-rule="evenodd" />',
    ];

    return '<svg viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4" aria-hidden="true">' . ($icons[$name] ?? '') . '</svg>';
}

/**
 * Fungsi untuk merender tombol aksi dengan ikon dan label
 */
function renderActionButton($label, $icon, $classes, $title, $type = 'submit', $id = '', $name = '', $value = '') {
    $idAttribute = $id === '' ? '' : ' id="' . htmlspecialchars($id, ENT_QUOTES, 'UTF-8') . '"';
    $nameAttribute = $name === '' ? '' : ' name="' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '"';
    $valueAttribute = $value === '' ? '' : ' value="' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '"';

    return '<button' . $idAttribute . $nameAttribute . $valueAttribute . ' type="' . $type . '" class="' . $classes . '" title="' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '">' . renderIcon($icon) . '<span>' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</span></button>';
}

// Menangkap request POST dari form untuk eksekusi aksi (Tambah/Ubah Status/Hapus/Hapus Terpilih)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    switch ($_POST['action']) {
        case 'add':
            if (isset($_POST['title']) && trim($_POST['title']) !== '') {
                addTask($_POST['title']);
            }
            break;
        case 'toggle':
            if (isset($_POST['id'])) {
                toggleTask($_POST['id']);
            }
            break;
        case 'delete':
            if (isset($_POST['id'])) {
                deleteTask($_POST['id']);
            }
            break;
        case 'delete_selected':
            if (!empty($_POST['selected_ids'])) {
                deleteSelectedTasks($_POST['selected_ids']);
            }
            break;
        case 'complete_selected':
            if (!empty($_POST['selected_ids'])) {
                completeSelectedTasks($_POST['selected_ids']);
            }
            break;
    }
    
    // Redirect ke halaman utama untuk mencegah "Form Resubmission" saat user menekan refresh (F5)
    header("Location: /todolist");
    exit;
}

// Mengambil data terbaru untuk ditampilkan ke UI
$tasks = getTasks();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aplikasi To-Do List</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-[#e8f1ff] text-slate-900">

    <main class="min-h-screen w-full">
        <section class="min-h-screen w-full overflow-hidden bg-white shadow-xl shadow-[#0f172a]/10 ring-1 ring-[#dbeafe]">
            <div class="bg-[#0f172a] px-4 py-6 text-white sm:px-8 sm:py-8">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[#fbbf24] sm:text-sm">LSP Certificate - Web Development</p>
                <h1 class="mt-2 text-2xl font-bold tracking-tight sm:text-3xl">Task: To-Do List</h1>
                <p class="mt-2 text-xs text-slate-300 sm:text-sm">Buat tugas baru dan kelola daftar tugas Anda.</p>
                <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-3">
                    <div class="rounded-xl border border-white/10 bg-white/10 px-3 py-3">
                        <p class="text-xs text-slate-300">Belum selesai</p>
                        <p class="mt-1 text-2xl font-bold text-[#fbbf24]">
                            <?= count(array_filter($tasks, fn($task) => $task['status'] === 'belum')) ?>
                        </p>
                    </div>
                    <div class="rounded-xl border border-white/10 bg-white/10 px-3 py-3">
                        <p class="text-xs text-slate-300">Selesai</p>
                        <p class="mt-1 text-2xl font-bold text-[#fbbf24]">
                            <?= count(array_filter($tasks, fn($task) => $task['status'] === 'selesai')) ?>
                        </p>
                    </div>
                    <div class="rounded-xl border border-white/10 bg-white/10 px-3 py-3">
                        <p class="text-xs text-slate-300">Total tugas</p>
                        <p class="mt-1 text-2xl font-bold text-[#fbbf24]">
                            <?= count($tasks) ?>
                        </p>
                    </div>
                </div>
            </div>

            <div class="mx-auto w-full max-w-6xl p-4 sm:p-8 lg:p-10">

                <!-- Form Tambah Tugas Baru -->
                <form method="POST" class="mb-6">
                    <input type="hidden" name="action" value="add">
                    <label for="title" class="mb-2 block text-sm font-semibold text-[#0f172a]">Tugas baru</label>
                    <div class="flex flex-col gap-3 sm:flex-row">
                        <input id="title" type="text" name="title" class="min-w-0 flex-1 rounded-lg border border-[#c7d2fe] bg-[#f8fbff] px-4 py-3 text-sm text-[#0f172a] outline-none transition placeholder:text-slate-400 focus:border-[#1d4ed8] focus:ring-2 focus:ring-[#bfdbfe]" placeholder="Tambahkan tugas baru..." required autocomplete="off">
                        <?= renderActionButton('Tambah', 'plus', 'add-task-button inline-flex items-center justify-center gap-2 rounded-lg bg-[#f97316] px-5 py-3 text-sm font-semibold text-white transition hover:bg-[#ea580c] focus:outline-none focus:ring-2 focus:ring-[#fdba74] focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 sm:w-auto', 'Tambah tugas', 'submit', 'add-task-button') ?>
                    </div>
                </form>

                <!-- Aksi massal -->
                <form id="bulk-delete-form" method="POST" class="mb-4 flex flex-col gap-3 rounded-xl border border-[#dbeafe] bg-[#f8fbff] p-3 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-3">
                        <input id="select-all" type="checkbox" class="h-5 w-5 cursor-pointer rounded border-[#93c5fd] text-[#1d4ed8] focus:ring-[#93c5fd]">
                        <label for="select-all" class="text-sm font-semibold text-[#0f172a]">Pilih semua</label>
                        <button id="clear-selection" type="button" class="text-sm font-medium text-[#1d4ed8] underline-offset-2 hover:underline">Batal pilih</button>
                    </div>
                    <div class="flex w-full gap-2 sm:w-auto">
                        <?= renderActionButton('Selesaikan terpilih', 'check', 'bulk-action-button inline-flex flex-1 items-center justify-center gap-2 rounded-lg bg-[#1d4ed8] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#1e40af] focus:outline-none focus:ring-2 focus:ring-[#93c5fd] focus:ring-offset-2 sm:flex-none', 'Selesaikan tugas terpilih', 'submit', 'complete-selected', 'action', 'complete_selected') ?>
                        <?= renderActionButton('Hapus terpilih', 'trash', 'bulk-action-button inline-flex flex-1 items-center justify-center gap-2 rounded-lg bg-[#0f172a] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#1e3a8a] focus:outline-none focus:ring-2 focus:ring-[#93c5fd] focus:ring-offset-2 sm:flex-none', 'Hapus tugas terpilih', 'submit', 'delete-selected', 'action', 'delete_selected') ?>
                    </div>
                </form>

                <!-- Daftar Tugas -->
                <ul class="divide-y divide-[#dbeafe] overflow-hidden rounded-xl border border-[#dbeafe] bg-[#f8fbff]">
                    <?php if (count($tasks) > 0): ?>
                        <?php foreach ($tasks as $task): ?>
                            <li class="flex flex-col gap-3 bg-white px-3 py-3 transition hover:bg-[#f8fbff] sm:flex-row sm:items-center sm:justify-between sm:gap-4 sm:px-4 sm:py-4">
                                
                                <!-- Kolom checkbox dan judul tugas -->
                                <div class="flex min-w-0 items-start gap-3">
                                    <input type="checkbox" name="selected_ids[]" value="<?= (int) $task['id'] ?>" form="bulk-delete-form" class="task-selection h-5 w-5 shrink-0 cursor-pointer rounded border-[#93c5fd] text-[#1d4ed8] focus:ring-[#93c5fd]" aria-label="Pilih tugas <?= htmlspecialchars($task['title'], ENT_QUOTES, 'UTF-8') ?>">
                                    
                                    <!-- Tampilan tugas selesai tetap ditandai secara visual -->
                                    <span class="min-w-0 break-words text-sm <?= $task['status'] === 'selesai' ? 'text-slate-400 line-through' : 'text-[#0f172a]' ?>">
                                        <?= $task['title'] ?>
                                    </span>
                                </div>

                                <!-- Bagian Kanan: Action Selesaikan dan Hapus -->
                                <div class="flex w-full gap-2 sm:w-auto sm:shrink-0">
                                    <form method="POST" class="flex-1 sm:flex-none">
                                        <input type="hidden" name="action" value="toggle">
                                        <input type="hidden" name="id" value="<?= (int) $task['id'] ?>">
                                        <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-md border <?= $task['status'] === 'selesai' ? 'border-[#1d4ed8] bg-[#dbeafe] text-[#1d4ed8]' : 'border-[#fed7aa] bg-[#fff7ed] text-[#c2410c]' ?> px-3 py-2 text-sm font-medium transition hover:bg-[#ffedd5] focus:outline-none focus:ring-2 focus:ring-[#fdba74] focus:ring-offset-2" title="<?= $task['status'] === 'selesai' ? 'Tandai belum selesai' : 'Selesaikan tugas' ?>">
                                            <?php if ($task['status'] === 'selesai'): ?>
                                                <span class="text-base leading-none">&#8634;</span>
                                                <span>Batalkan</span>
                                            <?php else: ?>
                                                <span class="text-base leading-none">&#10003;</span>
                                                <span>Selesai</span>
                                            <?php endif; ?>
                                        </button>
                                    </form>

                                    <form method="POST" class="flex-1 sm:flex-none">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= (int) $task['id'] ?>">
                                        <?= renderActionButton('Hapus', 'trash', 'inline-flex w-full items-center justify-center gap-2 rounded-md border border-[#fed7aa] bg-[#fff7ed] px-3 py-2 text-sm font-medium text-[#c2410c] transition hover:bg-[#ffedd5] hover:text-[#9a4d12] focus:outline-none focus:ring-2 focus:ring-[#fdba74] focus:ring-offset-2', 'Hapus tugas') ?>
                                    </form>
                                </div>
                                
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <!-- Feedback UI jika array kosong -->
                        <li class="px-4 py-8 text-center text-sm text-slate-500">Belum ada tugas. Silakan tambahkan tugas baru.</li>
                    <?php endif; ?>
                </ul>

            </div>
        </section>

        <footer class="mt-6 text-center text-xs text-slate-400">
            &copy; <?= date('Y') ?> LSP 40621200001. All rights reserved.
        </footer>
    </main>
    <script>
        const titleInput = document.getElementById('title');
        const addTaskButton = document.getElementById('add-task-button');
        const selectAll = document.getElementById('select-all');
        const clearSelection = document.getElementById('clear-selection');
        const bulkActionButtons = Array.from(document.querySelectorAll('.bulk-action-button'));
        const taskSelections = Array.from(document.querySelectorAll('.task-selection'));

        const updateAddButtonState = () => {
            addTaskButton.disabled = titleInput.value.trim() === '';
        };

        titleInput.addEventListener('input', updateAddButtonState);
        updateAddButtonState();

        const updateSelectionState = () => {
            const selectedCount = taskSelections.filter((checkbox) => checkbox.checked).length;
            selectAll.checked = taskSelections.length > 0 && selectedCount === taskSelections.length;
            selectAll.indeterminate = selectedCount > 0 && selectedCount < taskSelections.length;
            bulkActionButtons.forEach((button) => {
                button.disabled = selectedCount === 0;
                button.classList.toggle('cursor-not-allowed', selectedCount === 0);
                button.classList.toggle('opacity-50', selectedCount === 0);
            });
        };

        selectAll.addEventListener('change', () => {
            taskSelections.forEach((checkbox) => {
                checkbox.checked = selectAll.checked;
            });
            updateSelectionState();
        });

        clearSelection.addEventListener('click', () => {
            taskSelections.forEach((checkbox) => {
                checkbox.checked = false;
            });
            updateSelectionState();
        });

        taskSelections.forEach((checkbox) => {
            checkbox.addEventListener('change', updateSelectionState);
        });

        updateSelectionState();
    </script>
</body>
</html>