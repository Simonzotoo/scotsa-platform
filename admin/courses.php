<?php
$pageTitle = 'Courses';
require_once __DIR__ . '/../includes/admin_header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';
    if ($action === 'save') {
        $data = [(int) $_POST['program_id'], (int) $_POST['level_id'], (int) $_POST['semester_id'], clean_text($_POST['code'] ?? ''), clean_text($_POST['title'] ?? ''), clean_text($_POST['description'] ?? '')];
        if (!empty($_POST['id'])) {
            db()->prepare('UPDATE courses SET program_id = ?, level_id = ?, semester_id = ?, code = ?, title = ?, description = ? WHERE id = ?')->execute([...$data, (int) $_POST['id']]);
        } else {
            db()->prepare('INSERT INTO courses (program_id, level_id, semester_id, code, title, description) VALUES (?, ?, ?, ?, ?, ?)')->execute($data);
        }
        flash('success', 'Course saved.');
    } elseif ($action === 'delete') {
        db()->prepare('DELETE FROM courses WHERE id = ?')->execute([(int) $_POST['id']]);
        flash('success', 'Course deleted.');
    }
    header('Location: courses.php');
    exit;
}

$edit = null;
if (isset($_GET['edit'])) {
    $stmt = db()->prepare('SELECT * FROM courses WHERE id = ?');
    $stmt->execute([(int) $_GET['edit']]);
    $edit = $stmt->fetch();
}
$programs = db()->query('SELECT id, name FROM programs ORDER BY name')->fetchAll();
$levels = db()->query('SELECT id, name FROM levels ORDER BY sort_order')->fetchAll();
$semesters = db()->query('SELECT id, name FROM semesters ORDER BY sort_order')->fetchAll();
$courses = db()->query('SELECT c.*, p.name program_name, l.name level_name, s.name semester_name FROM courses c JOIN programs p ON p.id=c.program_id JOIN levels l ON l.id=c.level_id JOIN semesters s ON s.id=c.semester_id ORDER BY c.created_at DESC')->fetchAll();
?>
<div class="grid gap-6 xl:grid-cols-[420px_1fr]">
    <form method="post" class="rounded-lg border border-slate-200 bg-white p-6">
        <?= csrf_field() ?><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?= e((string) ($edit['id'] ?? '')) ?>">
        <h2 class="text-lg font-black"><?= $edit ? 'Edit Course' : 'Add Course' ?></h2>
        <div class="mt-4 grid gap-4 sm:grid-cols-2">
            <select class="form-input" name="program_id" required><?php foreach ($programs as $p): ?><option value="<?= (int) $p['id'] ?>" <?= (int)($edit['program_id'] ?? 0)===(int)$p['id']?'selected':'' ?>><?= e($p['name']) ?></option><?php endforeach; ?></select>
            <select class="form-input" name="level_id" required><?php foreach ($levels as $l): ?><option value="<?= (int) $l['id'] ?>" <?= (int)($edit['level_id'] ?? 0)===(int)$l['id']?'selected':'' ?>><?= e($l['name']) ?></option><?php endforeach; ?></select>
            <select class="form-input" name="semester_id" required><?php foreach ($semesters as $s): ?><option value="<?= (int) $s['id'] ?>" <?= (int)($edit['semester_id'] ?? 0)===(int)$s['id']?'selected':'' ?>><?= e($s['name']) ?></option><?php endforeach; ?></select>
            <input class="form-input" name="code" value="<?= e($edit['code'] ?? '') ?>" placeholder="Course code" required>
        </div>
        <input class="form-input mt-4" name="title" value="<?= e($edit['title'] ?? '') ?>" placeholder="Course title" required>
        <textarea class="form-input mt-4" name="description" placeholder="Description"><?= e($edit['description'] ?? '') ?></textarea>
        <button class="btn-primary mt-5 w-full" type="submit">Save Course</button>
    </form>
    <div class="rounded-lg border border-slate-200 bg-white p-6">
        <h2 class="text-lg font-black">All Courses</h2>
        <div class="mt-4 overflow-x-auto">
            <table class="w-full min-w-[780px] text-sm"><thead class="bg-slate-50 text-left text-xs uppercase text-slate-500"><tr><th class="p-3">Code</th><th class="p-3">Title</th><th class="p-3">Program</th><th class="p-3">Level</th><th class="p-3">Actions</th></tr></thead><tbody>
            <?php foreach ($courses as $course): ?><tr class="border-t"><td class="p-3 font-black"><?= e($course['code']) ?></td><td class="p-3"><?= e($course['title']) ?></td><td class="p-3"><?= e($course['program_name']) ?></td><td class="p-3"><?= e($course['level_name']) ?> / <?= e($course['semester_name']) ?></td><td class="flex gap-2 p-3"><a class="rounded border px-3 py-2 font-bold" href="?edit=<?= (int) $course['id'] ?>">Edit</a><form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int) $course['id'] ?>"><button data-confirm="Delete this course?" class="rounded bg-red-600 px-3 py-2 font-bold text-white">Delete</button></form></td></tr><?php endforeach; ?>
            </tbody></table>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>

