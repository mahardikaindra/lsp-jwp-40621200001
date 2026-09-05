<?php
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

// Menangkap request POST dari form untuk eksekusi aksi (Tambah/Ubah Status/Hapus)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    switch ($_POST['action']) {
        case 'add':
            if (!empty($_POST['title'])) {
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
    }
    
    // Redirect ke halaman utama untuk mencegah "Form Resubmission" saat user menekan refresh (F5)
    header("Location: index.php");
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
<body class="min-h-screen bg-[#e8f1ff] px-3 py-6 text-slate-900 sm:px-6 sm:py-10">

    <main class="mx-auto w-full max-w-2xl">
        <section class="overflow-hidden rounded-2xl bg-white shadow-xl shadow-[#0f172a]/10 ring-1 ring-[#dbeafe]">
            <div class="bg-[#0f172a] px-4 py-6 text-white sm:px-8 sm:py-8">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[#fbbf24] sm:text-sm">Produktivitas harian</p>
                <h1 class="mt-2 text-2xl font-bold tracking-tight sm:text-3xl">To-Do List</h1>
                <p class="mt-2 text-xs text-slate-300 sm:text-sm">Atur tugas, tandai yang selesai, dan tetap fokus.</p>
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

            <div class="p-4 sm:p-8">

                <!-- Form Tambah Tugas Baru -->
                <form method="POST" class="mb-6">
                    <input type="hidden" name="action" value="add">
                    <label for="title" class="mb-2 block text-sm font-semibold text-[#0f172a]">Tugas baru</label>
                    <div class="flex flex-col gap-3 sm:flex-row">
                        <input id="title" type="text" name="title" class="min-w-0 flex-1 rounded-lg border border-[#c7d2fe] bg-[#f8fbff] px-4 py-3 text-sm text-[#0f172a] outline-none transition placeholder:text-slate-400 focus:border-[#1d4ed8] focus:ring-2 focus:ring-[#bfdbfe]" placeholder="Tambahkan tugas baru..." required autocomplete="off">
                        <button class="inline-flex items-center justify-center gap-2 rounded-lg bg-[#f97316] px-5 py-3 text-sm font-semibold text-white transition hover:bg-[#ea580c] focus:outline-none focus:ring-2 focus:ring-[#fdba74] focus:ring-offset-2 sm:w-auto" type="submit" title="Tambah tugas">
                            <svg viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4" aria-hidden="true">
                                <path d="M10 3a1 1 0 0 1 1 1v5h5a1 1 0 1 1 0 2h-5v5a1 1 0 1 1-2 0v-5H4a1 1 0 1 1 0-2h5V4a1 1 0 0 1 1-1Z" />
                            </svg>
                            <span>Tambah</span>
                        </button>
                    </div>
                </form>

                <!-- Aksi massal -->
                <form id="bulk-delete-form" method="POST" class="mb-4 flex flex-col gap-3 rounded-xl border border-[#dbeafe] bg-[#f8fbff] p-3 sm:flex-row sm:items-center sm:justify-between">
                    <input type="hidden" name="action" value="delete_selected">
                    <div class="flex items-center gap-3">
                        <input id="select-all" type="checkbox" class="h-5 w-5 cursor-pointer rounded border-[#93c5fd] text-[#1d4ed8] focus:ring-[#93c5fd]">
                        <label for="select-all" class="text-sm font-semibold text-[#0f172a]">Pilih semua</label>
                        <button id="clear-selection" type="button" class="text-sm font-medium text-[#1d4ed8] underline-offset-2 hover:underline">Batal pilih</button>
                    </div>
                    <button id="delete-selected" type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-[#0f172a] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#1e3a8a] focus:outline-none focus:ring-2 focus:ring-[#93c5fd] focus:ring-offset-2 sm:w-auto" title="Hapus tugas terpilih">
                        <svg viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4" aria-hidden="true">
                            <path d="M6 4.5A1.5 1.5 0 0 1 7.5 3h5A1.5 1.5 0 0 1 14 4.5V5h1.5a1 1 0 1 1 0 2h-.7l-.68 9.11A2.5 2.5 0 0 1 11.63 18H8.37a2.5 2.5 0 0 1-2.49-2.89L5.2 7H4.5a1 1 0 0 1 0-2H6v-.5Zm2 2.5h4L11.8 15H8.2L8 7Zm1.5-2v.5h3v-.5a.5.5 0 0 0-.5-.5h-2a.5.5 0 0 0-.5.5Z" />
                        </svg>
                        <span>Hapus terpilih</span>
                    </button>
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
                                        <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-md border border-[#fed7aa] bg-[#fff7ed] px-3 py-2 text-sm font-medium text-[#c2410c] transition hover:bg-[#ffedd5] hover:text-[#9a4d12] focus:outline-none focus:ring-2 focus:ring-[#fdba74] focus:ring-offset-2" title="Hapus Tugas">
                                            <svg viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4" aria-hidden="true">
                                                <path d="M6 4.5A1.5 1.5 0 0 1 7.5 3h5A1.5 1.5 0 0 1 14 4.5V5h1.5a1 1 0 1 1 0 2h-.7l-.68 9.11A2.5 2.5 0 0 1 11.63 18H8.37a2.5 2.5 0 0 1-2.49-2.89L5.2 7H4.5a1 1 0 0 1 0-2H6v-.5Zm2 2.5h4L11.8 15H8.2L8 7Zm1.5-2v.5h3v-.5a.5.5 0 0 0-.5-.5h-2a.5.5 0 0 0-.5.5Z" />
                                            </svg>
                                            <span>Hapus</span>
                                        </button>
                                    </form>
                                </div>
                                
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <!-- Feedback UI jika array kosong -->
                        <li class="px-4 py-8 text-center text-sm text-slate-500">Belum ada tugas. Yay!</li>
                    <?php endif; ?>
                </ul>

            </div>
        </section>

        <footer class="mt-6 text-center text-xs text-slate-400">
            &copy; <?= date('Y') ?> LSP 40621200001. All rights reserved.
        </footer>
    </main>
    <script>
        const selectAll = document.getElementById('select-all');
        const clearSelection = document.getElementById('clear-selection');
        const deleteSelected = document.getElementById('delete-selected');
        const taskSelections = Array.from(document.querySelectorAll('.task-selection'));

        const updateSelectionState = () => {
            const selectedCount = taskSelections.filter((checkbox) => checkbox.checked).length;
            selectAll.checked = taskSelections.length > 0 && selectedCount === taskSelections.length;
            selectAll.indeterminate = selectedCount > 0 && selectedCount < taskSelections.length;
            deleteSelected.disabled = selectedCount === 0;
            deleteSelected.classList.toggle('cursor-not-allowed', selectedCount === 0);
            deleteSelected.classList.toggle('opacity-50', selectedCount === 0);
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