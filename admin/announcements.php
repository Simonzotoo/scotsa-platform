<?php
$pageTitle = 'Announcements';
require_once __DIR__ . '/../includes/admin_header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';
    if ($action === 'save') {
        $title = clean_text($_POST['title'] ?? '');
        $body = clean_text($_POST['body'] ?? '');
        $status = $_POST['status'] === 'published' ? 'published' : 'draft';
        $publishedAt = $status === 'published' ? date('Y-m-d H:i:s') : null;
        if (!empty($_POST['id'])) {
            db()->prepare('UPDATE announcements SET title = ?, body = ?, status = ?, published_at = IF(? = "published" AND published_at IS NULL, NOW(), published_at) WHERE id = ?')
                ->execute([$title, $body, $status, $status, (int) $_POST['id']]);
        } else {
            db()->prepare('INSERT INTO announcements (admin_id, title, body, status, published_at) VALUES (?, ?, ?, ?, ?)')
                ->execute([$admin['id'], $title, $body, $status, $publishedAt]);
        }
        flash('success', 'Announcement saved.');
    } elseif ($action === 'delete') {
        db()->prepare('DELETE FROM announcements WHERE id = ?')->execute([(int) $_POST['id']]);
        flash('success', 'Announcement deleted.');
    }
    header('Location: announcements.php');
    exit;
}

$edit = null;
if (isset($_GET['edit'])) {
    $stmt = db()->prepare('SELECT * FROM announcements WHERE id = ?');
    $stmt->execute([(int) $_GET['edit']]);
    $edit = $stmt->fetch();
}
$announcements = db()->query('SELECT * FROM announcements ORDER BY created_at DESC')->fetchAll();
?>
<div class="grid gap-6 lg:grid-cols-[420px_1fr]">
    <form method="post" class="rounded-lg border border-slate-200 bg-white p-6">
        <?= csrf_field() ?><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?= e((string) ($edit['id'] ?? '')) ?>">
        <h2 class="text-lg font-black"><?= $edit ? 'Edit Announcement' : 'New Announcement' ?></h2>
        <input class="form-input mt-4" name="title" value="<?= e($edit['title'] ?? '') ?>" placeholder="Title" required>
        <textarea class="form-input mt-4 min-h-36" name="body" placeholder="Announcement body" required><?= e($edit['body'] ?? '') ?></textarea>
        <select class="form-input mt-4" name="status"><option value="draft" <?= ($edit['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Draft</option><option value="published" <?= ($edit['status'] ?? '') === 'published' ? 'selected' : '' ?>>Published</option></select>
        <button class="btn-primary mt-5 w-full" type="submit">Save Announcement</button>
    </form>
    <div class="rounded-lg border border-slate-200 bg-white p-6">
        <h2 class="text-lg font-black">All Announcements</h2>
        <div class="mt-4 grid gap-3">
            <?php foreach ($announcements as $item): ?>
                <div class="rounded-lg border border-slate-100 p-4">
                    <div class="flex items-start justify-between gap-4">
                        <div><p class="font-black"><?= e($item['title']) ?></p><p class="mt-1 text-sm text-slate-500"><?= e($item['status']) ?></p></div>
                        <div class="flex gap-2"><a class="rounded border px-3 py-2 text-sm font-bold" href="?edit=<?= (int) $item['id'] ?>">Edit</a><form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int) $item['id'] ?>"><button data-confirm="Delete this announcement?" class="rounded bg-red-600 px-3 py-2 text-sm font-bold text-white">Delete</button></form></div>
                    </div>
                    <p class="mt-3 text-sm leading-6 text-slate-600"><?= e($item['body']) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>

