<?php
// app/views/auth/login.php
// User Login View - Modernized Design matching Nexus branding

require_once __DIR__ . '/../layouts/header.php';
?>

<div class="auth-card-wrapper w-100" style="max-width: 440px;">
    <div class="card-modern shadow-lg border-0 p-4 p-md-5 my-auto">
        <div class="text-center mb-4">
            <div class="brand-icon-box mx-auto mb-3" style="width: 52px; height: 52px; font-size: 1.6rem; background-color: var(--sidebar-bg); color: var(--accent-yellow);">
                <i class="bi bi-shield-lock-fill"></i>
            </div>
            <h3 class="fw-bold text-navy mb-1">Welcome Back</h3>
            <p class="text-muted small">Enter your credentials to access your Nexus account.</p>
        </div>

        <!-- Flash Success Message -->
        <?php if (!empty($_SESSION['success_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                <i class="bi bi-check-circle-fill me-1"></i>
                <?= htmlspecialchars($_SESSION['success_message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <!-- Flash Error Message -->
        <?php if (!empty($_SESSION['error_message'])): ?>
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-1"></i>
                <?= htmlspecialchars($_SESSION['error_message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>

        <!-- Form Validation Errors -->
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                <ul class="mb-0 ps-3">
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <form action="<?= BASE_URL ?>/index.php?action=login" method="POST" id="loginForm" novalidate>
            <?= csrf_field(); ?>
            <!-- Username -->
            <div class="mb-3">
                <label for="username" class="form-label fw-semibold small text-navy">Username</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-at"></i></span>
                    <input
                        type="text"
                        class="form-control border-start-0 ps-0"
                        id="username"
                        name="username"
                        value="<?= htmlspecialchars($username ?? ''); ?>"
                        placeholder="Enter your username"
                        required
                        autofocus
                    >
                </div>
            </div>

            <!-- Password -->
            <div class="mb-4">
                <label for="password" class="form-label fw-semibold small text-navy">Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-lock"></i></span>
                    <input
                        type="password"
                        class="form-control border-start-0 border-end-0 ps-0"
                        id="password"
                        name="password"
                        placeholder="Enter your password"
                        required
                    >
                    <button type="button" class="btn btn-light border border-start-0 text-muted toggle-password-btn" data-target="password" tabindex="-1" title="Toggle password visibility" aria-label="Toggle password visibility">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
            </div>

            <!-- Functional Login Submit Button -->
            <div class="d-grid mb-3">
                <button type="submit" class="btn btn-navy py-2-5 fw-semibold shadow-sm" id="loginSubmitBtn">
                    <i class="bi bi-box-arrow-in-right me-1"></i> Log In
                </button>
            </div>

            <!-- Form Navigation Links -->
            <div class="text-center pt-3 border-top">
                <div class="mb-2">
                    <span class="text-muted small">Don't have an account?</span>
                    <a href="<?= BASE_URL ?>/index.php?action=register" class="text-navy small fw-bold text-decoration-none ms-1">
                        Create Account
                    </a>
                </div>
                <div>
                    <a href="<?= BASE_URL ?>/index.php?action=welcome" class="text-muted small text-decoration-none d-inline-flex align-items-center gap-1">
                        <i class="bi bi-arrow-left"></i> Back to Welcome
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Toggle Password Visibility
    document.querySelectorAll('.toggle-password-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const targetId = this.getAttribute('data-target');
            const input = document.getElementById(targetId);
            if (!input) return;

            const icon = this.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                if (icon) {
                    icon.classList.remove('bi-eye');
                    icon.classList.add('bi-eye-slash');
                }
                this.setAttribute('title', 'Hide password');
            } else {
                input.type = 'password';
                if (icon) {
                    icon.classList.remove('bi-eye-slash');
                    icon.classList.add('bi-eye');
                }
                this.setAttribute('title', 'Show password');
            }
        });
    });
});
</script>

<?php
require_once __DIR__ . '/../layouts/footer.php';
?>
