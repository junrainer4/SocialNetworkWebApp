<?php
// app/views/auth/welcome.php
// Welcome & Authentication Landing View for Nexus - The SMCC Community Hub

require_once __DIR__ . '/../layouts/header.php';
?>

<<<<<<< HEAD
<div class="auth-card-wrapper w-100" style="max-width: 460px;">
    <!-- Flash Success Message (Above Container) -->
    <?php if (!empty($_SESSION['success_message'])): ?>
        <div class="alert alert-success fade show border-0 shadow-sm mb-3 text-start" role="alert">
            <i class="bi bi-check-circle-fill me-1"></i>
            <?= htmlspecialchars($_SESSION['success_message']); ?>
=======
<div class="landing-hero-container w-100 text-center my-auto py-5 px-3">

    <!-- Global Flash Notification Messages (if any) -->
    <?php if (!empty($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mx-auto mb-4 text-start" style="max-width: 580px;" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            <?= htmlspecialchars($_SESSION['success_message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
>>>>>>> b6e9240576e48f39db6dea044ddd922cc77d8534
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

<<<<<<< HEAD
    <!-- Flash Error Message (Above Container) -->
    <?php if (!empty($_SESSION['error_message'])): ?>
        <div class="alert alert-danger fade show border-0 shadow-sm mb-3 text-start" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-1"></i>
            <?= htmlspecialchars($_SESSION['error_message']); ?>
=======
    <?php if (!empty($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mx-auto mb-4 text-start" style="max-width: 580px;" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <?= htmlspecialchars($_SESSION['error_message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
>>>>>>> b6e9240576e48f39db6dea044ddd922cc77d8534
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

<<<<<<< HEAD
    <div class="card-modern shadow-lg border-0 p-4 p-md-5 my-auto text-center">
        <!-- Logo & Brand Icon -->
=======
    <div class="landing-hero-content mx-auto" style="max-width: 680px;">

        <!-- Prestigious Top Badge Pill -->
>>>>>>> b6e9240576e48f39db6dea044ddd922cc77d8534
        <div class="mb-3">
            <div class="landing-badge-pill">
                <span class="badge-dot"></span>
                <span>SMCC Collegiate Network</span>
                <span class="opacity-50">•</span>
                <span>Campus Community</span>
            </div>
        </div>

        <!-- Nexus Brand Emblem -->
        <div class="mb-3">
            <div class="landing-brand-emblem" title="Nexus">
                <i class="bi bi-people-fill"></i>
            </div>
        </div>

<<<<<<< HEAD
        <!-- Two Clear Authentication Choices -->
        <div class="d-grid gap-3 my-4">
            <!-- 1. Log In Button -->
            <a href="<?= BASE_URL ?>/index.php?action=login" class="btn btn-navy py-2-5 fw-semibold shadow-sm d-flex align-items-center justify-content-center gap-2" style="font-size: 1rem; border-radius: 10px;">
                <i class="bi bi-box-arrow-in-right fs-5"></i>
                <span>Log In</span>
=======
        <!-- 1. Main Heading: Nexus -->
        <h1 class="landing-heading fw-bold">Nexus</h1>

        <!-- 2. Subheading: The SMCC Community Hub -->
        <div class="landing-subheading mb-3">The SMCC Community Hub</div>

        <!-- 3. Website Description -->
        <p class="landing-description mx-auto mb-4">
            Connect with fellow SMCC students, share experiences, discover campus events, and be part of a growing community where everyone belongs.
        </p>

        <!-- 4. Login and Register Buttons -->
        <div class="landing-actions d-flex flex-column flex-sm-row justify-content-center align-items-center gap-3">
            <a href="<?= BASE_URL ?>/index.php?action=login" class="landing-btn-primary d-inline-flex align-items-center justify-content-center gap-2" aria-label="Log In to Nexus">
                <span>Login</span>
                <i class="bi bi-arrow-right fs-6 btn-arrow"></i>
>>>>>>> b6e9240576e48f39db6dea044ddd922cc77d8534
            </a>
            <a href="<?= BASE_URL ?>/index.php?action=register" class="landing-btn-secondary d-inline-flex align-items-center justify-content-center gap-2" aria-label="Register for a Nexus Account">
                <i class="bi bi-person-plus-fill fs-6 text-warning"></i>
                <span>Register</span>
            </a>
        </div>

        <!-- Trust Highlights Bar (Directly on page background) -->
        <div class="landing-trust-bar d-flex justify-content-center align-items-center flex-wrap gap-3 gap-md-4 mt-5 pt-4">
            <div class="trust-item d-flex align-items-center gap-2">
                <i class="bi bi-shield-check text-success fs-5"></i>
                <span>Verified Student Access</span>
            </div>
            <div class="trust-dot d-none d-sm-block">•</div>
            <div class="trust-item d-flex align-items-center gap-2">
                <i class="bi bi-newspaper text-primary fs-5"></i>
                <span>Real-Time Campus Feed</span>
            </div>
            <div class="trust-dot d-none d-sm-block">•</div>
            <div class="trust-item d-flex align-items-center gap-2">
                <i class="bi bi-calendar-event-fill text-warning fs-5"></i>
                <span>Events & Activities</span>
            </div>
        </div>

    </div>
</div>

<?php
require_once __DIR__ . '/../layouts/footer.php';
?>
