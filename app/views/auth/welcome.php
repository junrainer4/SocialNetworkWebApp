<?php
// app/views/auth/welcome.php
// Welcome & Authentication Landing View for SMCC Connect

require_once __DIR__ . '/../layouts/header.php';
?>

<div class="auth-card-wrapper w-100" style="max-width: 460px;">
    <div class="card-modern shadow-lg border-0 p-4 p-md-5 my-auto text-center">
        <!-- Logo & Brand Icon -->
        <div class="mb-3">
            <div class="brand-icon-box mx-auto mb-3" style="width: 58px; height: 58px; font-size: 1.75rem; background-color: var(--sidebar-bg); color: var(--accent-yellow); border-radius: 14px; box-shadow: 0 4px 12px rgba(245, 158, 11, 0.35);">
                <i class="bi bi-people-fill"></i>
            </div>
            <span class="badge rounded-pill px-3 py-1 mb-2 fw-semibold" style="background-color: var(--accent-yellow-light); color: #855b04; font-size: 0.75rem; letter-spacing: 0.4px;">
                <i class="bi bi-mortarboard-fill me-1"></i> CAMPUS SOCIAL NETWORK
            </span>
            <h2 class="fw-bold text-navy mb-2" style="letter-spacing: -0.5px;">Welcome to SMCC Connect</h2>
            <p class="text-muted small mb-0" style="line-height: 1.6;">
                Connect with your campus community, share updates, and stay informed.
            </p>
        </div>

        <!-- Flash Success Message -->
        <?php if (!empty($_SESSION['success_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm my-3 text-start" role="alert">
                <i class="bi bi-check-circle-fill me-1"></i>
                <?= htmlspecialchars($_SESSION['success_message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <!-- Flash Error Message -->
        <?php if (!empty($_SESSION['error_message'])): ?>
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm my-3 text-start" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-1"></i>
                <?= htmlspecialchars($_SESSION['error_message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>

        <!-- Two Clear Authentication Choices -->
        <div class="d-grid gap-3 my-4">
            <!-- 1. Log In Button -->
            <a href="<?= BASE_URL ?>/index.php?action=login" class="btn btn-navy py-2-5 fw-semibold shadow-sm d-flex align-items-center justify-content-center gap-2" style="font-size: 1rem; border-radius: 10px;">
                <i class="bi bi-box-arrow-in-right fs-5"></i>
                <span>Log In</span>
            </a>

            <!-- 2. Create Account Button -->
            <a href="<?= BASE_URL ?>/index.php?action=register" class="btn btn-accent py-2-5 fw-semibold shadow-sm d-flex align-items-center justify-content-center gap-2" style="font-size: 1rem; border-radius: 10px;">
                <i class="bi bi-person-plus-fill fs-5"></i>
                <span>Create Account</span>
            </a>
        </div>

        <!-- Community Highlights / Trust Footer -->
        <div class="pt-3 border-top">
            <div class="d-flex justify-content-center align-items-center flex-wrap gap-2 text-muted micro-text">
                <span class="d-inline-flex align-items-center gap-1">
                    <i class="bi bi-shield-check text-success"></i> Secure Access
                </span>
                <span class="d-none d-sm-inline">&bull;</span>
                <span class="d-inline-flex align-items-center gap-1">
                    <i class="bi bi-newspaper text-primary"></i> Campus Feed
                </span>
                <span class="d-none d-sm-inline">&bull;</span>
                <span class="d-inline-flex align-items-center gap-1">
                    <i class="bi bi-chat-dots text-warning"></i> Community
                </span>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../layouts/footer.php';
?>
