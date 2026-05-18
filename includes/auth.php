<?php
declare(strict_types=1);

require_once __DIR__ . '/session.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/security.php';

/* ── Login failure codes (internal use only) ─────────────────
   Never expose these directly to end users.
──────────────────────────────────────────────────────────── */
define('AUTH_OK',               0);
define('AUTH_FAIL_NOT_FOUND',   1);
define('AUTH_FAIL_PASSWORD',    2);
define('AUTH_FAIL_DB',          3);
define('AUTH_FAIL_SESSION',     4);

function current_admin(): ?array
{
    if (empty($_SESSION['admin_id'])) {
        return null;
    }

    try {
        $stmt = db()->prepare('SELECT id, name, email, role FROM admins WHERE id = ? LIMIT 1');
        $stmt->execute([$_SESSION['admin_id']]);
        return $stmt->fetch() ?: null;
    } catch (Throwable $e) {
        error_log('[SCOTSA Auth] current_admin DB error: ' . $e->getMessage());
        unset($_SESSION['admin_id']);
        return null;
    }
}

function require_admin(): void
{
    if (!current_admin()) {
        header('Location: ' . BASE_URL . '/admin/login.php');
        exit;
    }
}

/**
 * Attempt to authenticate an admin.
 *
 * Returns an AUTH_* constant so callers can surface specific error messages
 * without exposing internal details to the HTTP layer.
 */
function login_admin(string $email, string $password): int
{
    // ── 1. Fetch admin record ───────────────────────────────
    try {
        $stmt = db()->prepare('SELECT id, email, password_hash FROM admins WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $admin = $stmt->fetch();
    } catch (Throwable $e) {
        error_log('[SCOTSA Auth] login_admin DB query failed for <' . $email . '>: ' . $e->getMessage());
        return AUTH_FAIL_DB;
    }

    if (!$admin) {
        error_log('[SCOTSA Auth] login_admin: no account found for <' . $email . '>');
        return AUTH_FAIL_NOT_FOUND;
    }

    // ── 2. Verify password ──────────────────────────────────
    if (!password_verify($password, (string) $admin['password_hash'])) {
        error_log('[SCOTSA Auth] login_admin: wrong password for <' . $email . '>');
        return AUTH_FAIL_PASSWORD;
    }

    // ── 3. Establish authenticated session ──────────────────
    try {
        session_regenerate_id(true);
        $_SESSION['admin_id'] = (int) $admin['id'];
        session_write_close();      // flush to disk before redirect
        session_start();            // reopen so subsequent code can still read it
    } catch (Throwable $e) {
        error_log('[SCOTSA Auth] login_admin session error for <' . $email . '>: ' . $e->getMessage());
        return AUTH_FAIL_SESSION;
    }

    return AUTH_OK;
}

function logout_admin(): void
{
    unset($_SESSION['admin_id']);
    session_regenerate_id(true);
}
