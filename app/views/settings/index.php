<?php
require_once __DIR__ . '/../layouts/header.php';

$activeTab = $activeTab ?? ($_GET['tab'] ?? 'account');
if (!in_array($activeTab, ['account', 'password', 'avatar'], true)) {
    $activeTab = 'account';
}

$avatarUrl = null;
if (!empty($user['profile_image'])) {
    $uploadedPath = __DIR__ . '/../../../public/uploads/avatars/' . basename($user['profile_image']);
    if (file_exists($uploadedPath)) {
        $avatarUrl = BASE_URL . '/public/uploads/avatars/' . htmlspecialchars(basename($user['profile_image']));
    } else {
        $avatarUrl = BASE_URL . '/public/uploads/avatars/' . htmlspecialchars($user['profile_image']);
    }
}

$userInitials = 'U';
if (!empty($user['full_name'])) {
    $parts = explode(' ', trim($user['full_name']));
    if (count($parts) >= 2) {
        $userInitials = strtoupper(substr($parts[0], 0, 1) . substr($parts[count($parts) - 1], 0, 1));
    } else {
        $userInitials = strtoupper(substr($user['full_name'], 0, 2));
    }
}
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-xl-7">

            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item">
                        <a href="<?= BASE_URL ?>/" class="text-decoration-none text-muted d-inline-flex align-items-center gap-1">
                            <i class="bi bi-house-door"></i>
                            Home
                        </a>
                    </li>
                    <li class="breadcrumb-item active text-primary fw-semibold" aria-current="page">Account Settings</li>
                </ol>
            </nav>

            <div class="d-flex align-items-center justify-content-between mb-4 pb-2 border-bottom">
                <div>
                    <h3 class="fw-bold mb-1 text-dark d-flex align-items-center gap-2">
                        <i class="bi bi-gear-fill text-primary"></i>
                        <span>Account Settings</span>
                    </h3>
                    <p class="text-muted small mb-0">Manage your profile information, password security, and avatar.</p>
                </div>
            </div>

            <?php if (!empty($_SESSION['error_message'])): ?>
                <div class="alert alert-danger fade show border-0 shadow-sm mb-4" role="alert">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-exclamation-triangle-fill flex-shrink-0"></i>
                        <span><?= htmlspecialchars($_SESSION['error_message']); ?></span>
                    </div>
                </div>
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>

            <?php if (!empty($_SESSION['success_message'])): ?>
                <div class="alert alert-success fade show border-0 shadow-sm mb-4" role="alert">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-check-circle-fill flex-shrink-0"></i>
                        <span><?= htmlspecialchars($_SESSION['success_message']); ?></span>
                    </div>
                </div>
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>

            <ul class="nav nav-pills fb-settings-tabs mb-4 gap-2 border-bottom pb-3" id="settingsTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link <?= ($activeTab === 'account') ? 'active' : '' ?> d-inline-flex align-items-center gap-2" id="tab-account-btn" data-bs-toggle="pill" data-bs-target="#tab-account-pane" type="button" role="tab" aria-controls="tab-account-pane" aria-selected="<?= ($activeTab === 'account') ? 'true' : 'false' ?>">
                        <i class="bi bi-person"></i>
                        <span>Account Information</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link <?= ($activeTab === 'password') ? 'active' : '' ?> d-inline-flex align-items-center gap-2" id="tab-password-btn" data-bs-toggle="pill" data-bs-target="#tab-password-pane" type="button" role="tab" aria-controls="tab-password-pane" aria-selected="<?= ($activeTab === 'password') ? 'true' : 'false' ?>">
                        <i class="bi bi-shield-lock"></i>
                        <span>Password & Security</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link <?= ($activeTab === 'avatar') ? 'active' : '' ?> d-inline-flex align-items-center gap-2" id="tab-avatar-btn" data-bs-toggle="pill" data-bs-target="#tab-avatar-pane" type="button" role="tab" aria-controls="tab-avatar-pane" aria-selected="<?= ($activeTab === 'avatar') ? 'true' : 'false' ?>">
                        <i class="bi bi-camera"></i>
                        <span>Profile Picture</span>
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="settingsTabContent">

                <div class="tab-pane fade <?= ($activeTab === 'account') ? 'show active' : '' ?>" id="tab-account-pane" role="tabpanel" aria-labelledby="tab-account-btn" tabindex="0">
                    <div class="card-modern p-4 p-md-5 shadow-sm border rounded-4">
                        <div class="d-flex align-items-center gap-3 mb-4 pb-3 border-bottom">
                            <div class="fb-icon-circle bg-primary-subtle text-primary">
                                <i class="bi bi-person-lines-fill fs-5"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-0 text-dark">Personal Information</h5>
                                <p class="text-muted small mb-0">Update your public display name and unique username handle.</p>
                            </div>
                        </div>

                        <form action="<?= BASE_URL ?>/index.php?action=settings_account" method="POST" id="accountInfoForm" novalidate>
                            <?= csrf_field(); ?>

                            <div class="mb-4">
                                <label for="full_name" class="form-label fw-semibold small text-secondary">
                                    Full Name <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted border-end-0">
                                        <i class="bi bi-person"></i>
                                    </span>
                                    <input
                                        type="text"
                                        name="full_name"
                                        id="full_name"
                                        data-initial="<?= htmlspecialchars(trim($user['full_name'] ?? '')); ?>"
                                        class="form-control border-start-0"
                                        value="<?= htmlspecialchars($user['full_name'] ?? ''); ?>"
                                        required
                                        minlength="2"
                                        maxlength="100"
                                        placeholder="Enter your full name"
                                        autocomplete="name"
                                    >
                                </div>
                                <div class="form-text small text-muted mt-1">
                                    Your name appears on your profile, updates, comments, and top navigation.
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="username" class="form-label fw-semibold small text-secondary">
                                    Username <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted border-end-0">
                                        <i class="bi bi-at"></i>
                                    </span>
                                    <input
                                        type="text"
                                        name="username"
                                        id="username"
                                        data-initial="<?= htmlspecialchars(trim($user['username'] ?? '')); ?>"
                                        class="form-control border-start-0"
                                        value="<?= htmlspecialchars($user['username'] ?? ''); ?>"
                                        required
                                        minlength="3"
                                        maxlength="50"
                                        pattern="[a-zA-Z0-9_]+"
                                        placeholder="Enter your username"
                                        autocomplete="username"
                                    >
                                </div>
                                <div class="form-text small text-muted mt-1">
                                    Letters, numbers, and underscores only (e.g. @<?= htmlspecialchars($user['username'] ?? 'username'); ?>).
                                </div>
                            </div>

                            <div class="d-flex align-items-center justify-content-end pt-3 border-top">
                                <button type="submit" class="btn btn-secondary btn-settings-action btn-gray-state rounded-pill px-4 fw-semibold d-inline-flex align-items-center gap-2" id="saveAccountBtn" disabled>
                                    <i class="bi bi-check2 fs-5"></i>
                                    <span>Save Changes</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="tab-pane fade <?= ($activeTab === 'password') ? 'show active' : '' ?>" id="tab-password-pane" role="tabpanel" aria-labelledby="tab-password-btn" tabindex="0">
                    <div class="card-modern p-4 p-md-5 shadow-sm border rounded-4">
                        <div class="d-flex align-items-center gap-3 mb-4 pb-3 border-bottom">
                            <div class="fb-icon-circle bg-warning-subtle text-warning">
                                <i class="bi bi-key-fill fs-5"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-0 text-dark">Update Password</h5>
                                <p class="text-muted small mb-0">Protect your account with a secure, strong password.</p>
                            </div>
                        </div>

                        <form action="<?= BASE_URL ?>/index.php?action=settings_password" method="POST" id="changePasswordForm" novalidate>
                            <?= csrf_field(); ?>

                            <div class="mb-3">
                                <label for="current_password" class="form-label fw-semibold small text-secondary">
                                    Current Password <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted border-end-0">
                                        <i class="bi bi-key"></i>
                                    </span>
                                    <input
                                        type="password"
                                        name="current_password"
                                        id="current_password"
                                        class="form-control border-start-0 border-end-0 ps-0"
                                        required
                                        placeholder="Enter current password"
                                        autocomplete="current-password"
                                    >
                                    <button type="button" class="btn btn-light border border-start-0 text-muted toggle-pwd-btn" data-target="current_password" tabindex="-1" title="Toggle visibility" aria-label="Toggle password visibility">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="new_password" class="form-label fw-semibold small text-secondary">
                                    New Password <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted border-end-0">
                                        <i class="bi bi-lock"></i>
                                    </span>
                                    <input
                                        type="password"
                                        name="new_password"
                                        id="new_password"
                                        class="form-control border-start-0 border-end-0 ps-0"
                                        required
                                        minlength="8"
                                        placeholder="Enter new password (min. 8 characters)"
                                        autocomplete="new-password"
                                    >
                                    <button type="button" class="btn btn-light border border-start-0 text-muted toggle-pwd-btn" data-target="new_password" tabindex="-1" title="Toggle visibility" aria-label="Toggle password visibility">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>

                                <div class="card bg-light border p-3 mt-2 rounded-3" id="passwordChecklist">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <span class="small fw-semibold text-secondary">Password Requirements:</span>
                                        <span class="badge bg-secondary-subtle text-secondary micro-text" id="requirementsStatus">Incomplete</span>
                                    </div>
                                    <ul class="list-unstyled mb-0 small">
                                        <li class="req-item text-muted mb-1 d-flex align-items-center gap-2" id="req-length">
                                            <i class="bi bi-circle small"></i> At least 8 characters long
                                        </li>
                                        <li class="req-item text-muted mb-1 d-flex align-items-center gap-2" id="req-uppercase">
                                            <i class="bi bi-circle small"></i> At least one uppercase letter (A–Z)
                                        </li>
                                        <li class="req-item text-muted mb-1 d-flex align-items-center gap-2" id="req-lowercase">
                                            <i class="bi bi-circle small"></i> At least one lowercase letter (a–z)
                                        </li>
                                        <li class="req-item text-muted mb-1 d-flex align-items-center gap-2" id="req-number">
                                            <i class="bi bi-circle small"></i> At least one number (0–9)
                                        </li>
                                        <li class="req-item text-muted d-flex align-items-center gap-2" id="req-special">
                                            <i class="bi bi-circle small"></i> At least one special character (@, #, $, %, etc.)
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="confirm_password" class="form-label fw-semibold small text-secondary">
                                    Confirm New Password <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted border-end-0">
                                        <i class="bi bi-shield-check"></i>
                                    </span>
                                    <input
                                        type="password"
                                        name="confirm_password"
                                        id="confirm_password"
                                        class="form-control border-start-0 border-end-0 ps-0"
                                        required
                                        minlength="8"
                                        placeholder="Re-enter new password"
                                        autocomplete="new-password"
                                    >
                                    <button type="button" class="btn btn-light border border-start-0 text-muted toggle-pwd-btn" data-target="confirm_password" tabindex="-1" title="Toggle visibility" aria-label="Toggle password visibility">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                                <div id="confirmFeedback" class="mt-1" style="font-size: 0.8rem; min-height: 20px;" aria-live="polite"></div>
                            </div>

                            <div class="d-flex align-items-center justify-content-end pt-3 border-top">
                                <button type="submit" class="btn btn-secondary btn-settings-action btn-gray-state rounded-pill px-4 fw-semibold d-inline-flex align-items-center gap-2" id="updatePasswordBtn" disabled>
                                    <i class="bi bi-check2 fs-5"></i>
                                    <span>Update Password</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="tab-pane fade <?= ($activeTab === 'avatar') ? 'show active' : '' ?>" id="tab-avatar-pane" role="tabpanel" aria-labelledby="tab-avatar-btn" tabindex="0">
                    <div class="card-modern p-4 p-md-5 shadow-sm border rounded-4">
                        <div class="d-flex align-items-center gap-3 mb-4 pb-3 border-bottom">
                            <div class="fb-icon-circle bg-info-subtle text-info">
                                <i class="bi bi-camera-fill fs-5"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-0 text-dark">Profile Picture</h5>
                                <p class="text-muted small mb-0">Upload or change your profile picture.</p>
                            </div>
                        </div>

                        <form action="<?= BASE_URL ?>/index.php?action=settings_avatar" method="POST" enctype="multipart/form-data" id="settingsAvatarForm">
                            <?= csrf_field(); ?>

                            <div class="d-flex flex-column flex-sm-row align-items-center gap-4 mb-4 p-3 bg-light rounded-3 border">
                                <div class="position-relative flex-shrink-0">
                                    <?php if ($avatarUrl): ?>
                                        <img src="<?= $avatarUrl ?>" alt="Current Avatar" id="settingsAvatarPreview" class="rounded-circle border border-3 border-white shadow-sm" style="width: 100px; height: 100px; object-fit: cover;">
                                    <?php else: ?>
                                        <div id="settingsAvatarPlaceholder" class="rounded-circle border border-3 border-white shadow-sm text-white d-flex align-items-center justify-content-center fs-2 fw-bold" style="width: 100px; height: 100px; background-color: var(--fb-blue, #1877f2);">
                                            <?= htmlspecialchars($userInitials); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="flex-grow-1 text-center text-sm-start">
                                    <h6 class="fw-bold mb-1 text-dark">Your Avatar Photo</h6>
                                    <p class="text-muted small mb-2">Supported formats: JPG, PNG, WEBP, GIF. Max file size: 5MB.</p>
                                    <input type="file" name="profile_image" id="settingsAvatarInput" class="form-control form-control-sm" accept="image/jpeg,image/png,image/webp,image/gif">
                                </div>
                            </div>

                            <div class="d-flex align-items-center justify-content-between pt-3 border-top">
                                <?php if (!empty($user['profile_image'])): ?>
                                    <button type="submit" name="remove_picture" value="1" class="btn btn-outline-danger btn-sm rounded-pill px-3" onclick="return confirm('Are you sure you want to remove your profile picture?');">
                                        <i class="bi bi-trash me-1"></i>
                                        Remove Photo
                                    </button>
                                <?php else: ?>
                                    <div></div>
                                <?php endif; ?>

                                <button type="submit" class="btn btn-secondary btn-settings-action btn-gray-state rounded-pill px-4 fw-semibold d-inline-flex align-items-center gap-2" id="saveAvatarBtn" disabled>
                                    <i class="bi bi-check2 fs-5"></i>
                                    <span>Upload & Save Picture</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>

        </div>
    </div>
</div>

<style>
.btn-settings-action.btn-gray-state,
.btn-settings-action:disabled,
#saveAccountBtn:disabled,
#updatePasswordBtn:disabled,
#saveAvatarBtn:disabled {
    background-color: #cbd5e1 !important;
    border-color: #cbd5e1 !important;
    color: #475569 !important;
    cursor: not-allowed !important;
    pointer-events: none !important;
    box-shadow: none !important;
    transform: none !important;
    opacity: 1 !important;
}

[data-bs-theme="dark"] .btn-settings-action.btn-gray-state,
[data-bs-theme="dark"] .btn-settings-action:disabled,
[data-bs-theme="dark"] #saveAccountBtn:disabled,
[data-bs-theme="dark"] #updatePasswordBtn:disabled,
[data-bs-theme="dark"] #saveAvatarBtn:disabled,
.dark-theme .btn-settings-action.btn-gray-state,
.dark-theme .btn-settings-action:disabled,
.dark-theme #saveAccountBtn:disabled,
.dark-theme #updatePasswordBtn:disabled,
.dark-theme #saveAvatarBtn:disabled {
    background-color: #334155 !important;
    border-color: #475569 !important;
    color: #94a3b8 !important;
    opacity: 1 !important;
}

.btn-settings-action.btn-blue-state,
.btn-settings-action:not(:disabled),
#saveAccountBtn:not(:disabled),
#updatePasswordBtn:not(:disabled),
#saveAvatarBtn:not(:disabled) {
    background-color: #1877f2 !important;
    border-color: #1877f2 !important;
    color: #ffffff !important;
    cursor: pointer !important;
    pointer-events: auto !important;
    box-shadow: 0 2px 8px rgba(24, 119, 242, 0.35) !important;
}

.btn-settings-action.btn-blue-state:hover,
.btn-settings-action:not(:disabled):hover,
#saveAccountBtn:not(:disabled):hover,
#updatePasswordBtn:not(:disabled):hover,
#saveAvatarBtn:not(:disabled):hover {
    background-color: #166fe5 !important;
    border-color: #166fe5 !important;
    color: #ffffff !important;
    box-shadow: 0 4px 14px rgba(24, 119, 242, 0.45) !important;
    transform: translateY(-1px) !important;
}

.btn-settings-action {
    transition: background-color 0.25s ease, border-color 0.25s ease, color 0.25s ease, box-shadow 0.25s ease, transform 0.15s ease !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    function setButtonState(btn, isReady) {
        if (!btn) return;
        btn.disabled = !isReady;
        if (isReady) {
            btn.classList.remove('btn-secondary', 'btn-gray-state');
            btn.classList.add('btn-primary', 'btn-blue-state');
        } else {
            btn.classList.remove('btn-primary', 'btn-blue-state');
            btn.classList.add('btn-secondary', 'btn-gray-state');
        }
    }

    const toggleButtons = document.querySelectorAll('.toggle-pwd-btn');
    toggleButtons.forEach(function (btn) {
        btn.addEventListener('click', function () {
            const targetId = this.getAttribute('data-target');
            const targetInput = document.getElementById(targetId);
            if (!targetInput) return;

            const icon = this.querySelector('i');
            if (targetInput.type === 'password') {
                targetInput.type = 'text';
                if (icon) icon.className = 'bi bi-eye-slash';
            } else {
                targetInput.type = 'password';
                if (icon) icon.className = 'bi bi-eye';
            }
        });
    });

    const fullNameInput = document.getElementById('full_name');
    const usernameInput = document.getElementById('username');
    const saveAccountBtn = document.getElementById('saveAccountBtn');

    const initialFullName = (fullNameInput ? (fullNameInput.getAttribute('data-initial') || fullNameInput.defaultValue || fullNameInput.value) : '').trim();
    const initialUsername = (usernameInput ? (usernameInput.getAttribute('data-initial') || usernameInput.defaultValue || usernameInput.value) : '').trim();

    function checkAccountState() {
        if (!saveAccountBtn || !fullNameInput || !usernameInput) return;
        const currentFullName = fullNameInput.value.trim();
        const currentUsername = usernameInput.value.trim();

        const isNameValid = currentFullName.length >= 2;
        const isUsernameValid = currentUsername.length >= 3 && /^[a-zA-Z0-9_]+$/.test(currentUsername);
        const hasChanged = (currentFullName !== initialFullName) || (currentUsername !== initialUsername);

        setButtonState(saveAccountBtn, hasChanged && isNameValid && isUsernameValid);
    }

    if (fullNameInput) {
        fullNameInput.addEventListener('input', checkAccountState);
        fullNameInput.addEventListener('change', checkAccountState);
    }
    if (usernameInput) {
        usernameInput.addEventListener('input', checkAccountState);
        usernameInput.addEventListener('change', checkAccountState);
    }
    checkAccountState();

    const currentPasswordInput = document.getElementById('current_password');
    const newPasswordInput = document.getElementById('new_password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    const updatePasswordBtn = document.getElementById('updatePasswordBtn');
    const requirementsStatus = document.getElementById('requirementsStatus');
    const confirmFeedback = document.getElementById('confirmFeedback');

    function updateReqItem(id, isValid) {
        const item = document.getElementById(id);
        if (!item) return;
        const icon = item.querySelector('i');
        if (isValid) {
            item.classList.remove('text-muted');
            item.classList.add('text-success');
            if (icon) icon.className = 'bi bi-check-circle-fill small';
        } else {
            item.classList.remove('text-success');
            item.classList.add('text-muted');
            if (icon) icon.className = 'bi bi-circle small';
        }
    }

    function validatePassword() {
        if (!newPasswordInput) return;
        const pwd = newPasswordInput.value;

        const hasLength = pwd.length >= 8;
        const hasUpper = /[A-Z]/.test(pwd);
        const hasLower = /[a-z]/.test(pwd);
        const hasNumber = /[0-9]/.test(pwd);
        const hasSpecial = /[^a-zA-Z0-9]/.test(pwd);

        updateReqItem('req-length', hasLength);
        updateReqItem('req-uppercase', hasUpper);
        updateReqItem('req-lowercase', hasLower);
        updateReqItem('req-number', hasNumber);
        updateReqItem('req-special', hasSpecial);

        let satisfiedCount = 0;
        if (hasLength) satisfiedCount++;
        if (hasUpper) satisfiedCount++;
        if (hasLower) satisfiedCount++;
        if (hasNumber) satisfiedCount++;
        if (hasSpecial) satisfiedCount++;

        if (requirementsStatus) {
            if (satisfiedCount === 5) {
                requirementsStatus.textContent = 'All Met';
                requirementsStatus.className = 'badge bg-success-subtle text-success micro-text';
            } else if (satisfiedCount > 0) {
                requirementsStatus.textContent = satisfiedCount + ' of 5 Met';
                requirementsStatus.className = 'badge bg-warning-subtle text-warning micro-text';
            } else {
                requirementsStatus.textContent = 'Incomplete';
                requirementsStatus.className = 'badge bg-secondary-subtle text-secondary micro-text';
            }
        }

        validateConfirmation();
        checkPasswordState();
    }

    function validateConfirmation() {
        if (!newPasswordInput || !confirmPasswordInput || !confirmFeedback) return;
        const pwd = newPasswordInput.value;
        const confirm = confirmPasswordInput.value;

        if (confirm.length === 0) {
            confirmFeedback.textContent = '';
            confirmFeedback.className = 'mt-1';
            confirmPasswordInput.classList.remove('is-valid', 'is-invalid');
            checkPasswordState();
            return;
        }

        if (pwd === confirm) {
            confirmFeedback.innerHTML = '<span class="text-success d-inline-flex align-items-center gap-1"><i class="bi bi-check-circle"></i> Passwords match</span>';
            confirmPasswordInput.classList.remove('is-invalid');
            confirmPasswordInput.classList.add('is-valid');
        } else {
            confirmFeedback.innerHTML = '<span class="text-danger d-inline-flex align-items-center gap-1"><i class="bi bi-x-circle"></i> Passwords do not match</span>';
            confirmPasswordInput.classList.remove('is-valid');
            confirmPasswordInput.classList.add('is-invalid');
        }
        checkPasswordState();
    }

    function checkPasswordState() {
        if (!updatePasswordBtn) return;
        const curPwd = currentPasswordInput ? currentPasswordInput.value.trim() : '';
        const newPwd = newPasswordInput ? newPasswordInput.value : '';
        const confirmPwd = confirmPasswordInput ? confirmPasswordInput.value : '';

        const hasLength = newPwd.length >= 8;
        const hasUpper = /[A-Z]/.test(newPwd);
        const hasLower = /[a-z]/.test(newPwd);
        const hasNumber = /[0-9]/.test(newPwd);
        const hasSpecial = /[^a-zA-Z0-9]/.test(newPwd);
        const isComplexityValid = hasLength && hasUpper && hasLower && hasNumber && hasSpecial;

        const isCurrentFilled = curPwd.length > 0;
        const isMatches = (newPwd.length > 0 && newPwd === confirmPwd);

        setButtonState(updatePasswordBtn, isCurrentFilled && isComplexityValid && isMatches);
    }

    if (currentPasswordInput) {
        currentPasswordInput.addEventListener('input', checkPasswordState);
        currentPasswordInput.addEventListener('change', checkPasswordState);
    }
    if (newPasswordInput) newPasswordInput.addEventListener('input', validatePassword);
    if (confirmPasswordInput) confirmPasswordInput.addEventListener('input', validateConfirmation);
    checkPasswordState();

    const avatarInput = document.getElementById('settingsAvatarInput');
    const saveAvatarBtn = document.getElementById('saveAvatarBtn');

    function checkAvatarState() {
        if (!saveAvatarBtn || !avatarInput) return;
        const hasFile = !!(avatarInput.files && avatarInput.files.length > 0 && avatarInput.files[0].size > 0);
        setButtonState(saveAvatarBtn, hasFile);
    }

    if (avatarInput) {
        avatarInput.addEventListener('change', function () {
            checkAvatarState();
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    const currentAvatarPreview = document.getElementById('settingsAvatarPreview');
                    if (currentAvatarPreview) {
                        currentAvatarPreview.src = e.target.result;
                    } else {
                        const placeholder = document.getElementById('settingsAvatarPlaceholder');
                        if (placeholder) {
                            placeholder.outerHTML = '<img src="' + e.target.result + '" id="settingsAvatarPreview" class="rounded-circle border border-3 border-white shadow-sm" style="width: 100px; height: 100px; object-fit: cover;">';
                        }
                    }
                };
                reader.readAsDataURL(this.files[0]);
            }
        });
    }
    checkAvatarState();
});
</script>

<?php
require_once __DIR__ . '/../layouts/footer.php';
?>
