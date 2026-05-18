<?php
$pageTitle = 'Users';
require_once __DIR__ . '/../includes/admin_header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';
    if ($action === 'save') {
        $fullName = clean_text($_POST['full_name'] ?? '');
        $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
        $programId = $_POST['program_id'] !== '' ? (int) $_POST['program_id'] : null;
        $levelId = $_POST['level_id'] !== '' ? (int) $_POST['level_id'] : null;
        if ($fullName && $email) {
            db()->prepare('INSERT INTO users (full_name, email, program_id, level_id) VALUES (?, ?, ?, ?)')->execute([$fullName, $email, $programId, $levelId]);
            flash('success', 'User added.');
        } else {
            flash('error', 'Provide a valid name and email.');
        }
    } elseif ($action === 'delete') {
        db()->prepare('DELETE FROM users WHERE id = ?')->execute([(int) $_POST['id']]);
        flash('success', 'User deleted.');
    }
    header('Location: users.php');
    exit;
}

$programs = db()->query('SELECT id, name FROM programs ORDER BY name')->fetchAll();
$levels = db()->query('SELECT id, name FROM levels ORDER BY sort_order')->fetchAll();
$users = db()->query('SELECT u.*, p.name program_name, l.name level_name FROM users u LEFT JOIN programs p ON p.id=u.program_id LEFT JOIN levels l ON l.id=u.level_id ORDER BY u.created_at DESC')->fetchAll();
?>
<div class="grid gap-6 lg:grid-cols-[380px_1fr]">
    <form method="post" class="rounded-lg border border-slate-200 bg-white p-6">
        <?= csrf_field() ?><input type="hidden" name="action" value="save">
        <h2 class="text-lg font-black">Add User</h2>
        <input class="form-input mt-4" name="full_name" placeholder="Full name" required>
        <input class="form-input mt-4" type="email" name="email" placeholder="Email" required>
        <select class="form-input mt-4" name="program_id"><option value="">Program</option><?php foreach ($programs as $p): ?><option value="<?= (int) $p['id'] ?>"><?= e($p['name']) ?></option><?php endforeach; ?></select>
        <select class="form-input mt-4" name="level_id"><option value="">Level</option><?php foreach ($levels as $l): ?><option value="<?= (int) $l['id'] ?>"><?= e($l['name']) ?></option><?php endforeach; ?></select>
        <button class="btn-primary mt-5 w-full" type="submit">Add User</button>
    </form>
    <div class="rounded-lg border border-slate-200 bg-white p-6">
        <h2 class="text-lg font-black">Registered Users</h2>
        <div class="mt-4 overflow-x-auto">
            <table class="w-full min-w-[680px] text-sm"><thead class="bg-slate-50 text-left text-xs uppercase text-slate-500"><tr><th class="p-3">Name</th><th class="p-3">Email</th><th class="p-3">Program</th><th class="p-3">Action</th></tr></thead><tbody>
                <?php foreach ($users as $user): ?><tr class="border-t"><td class="p-3 font-black"><?= e($user['full_name']) ?></td><td class="p-3"><?= e($user['email']) ?></td><td class="p-3"><?= e(($user['program_name'] ?? 'N/A') . ' ' . ($user['level_name'] ?? '')) ?></td><td class="p-3"><form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int) $user['id'] ?>"><button data-confirm="Delete this user?" class="rounded bg-red-600 px-3 py-2 font-bold text-white">Delete</button></form></td></tr><?php endforeach; ?>
            </tbody></table>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>

