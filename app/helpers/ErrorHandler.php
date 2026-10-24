<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class ErrorHandler {
    public static function register(): void {
        error_reporting(E_ALL);
        ini_set('display_errors', '0');
        ini_set('log_errors', '1');
    }

    public static function renderErrorPage(int $statusCode, string $title, string $message, string $backUrl = ''): void {
        http_response_code($statusCode);
        $baseUrl = defined('BASE_URL') ? BASE_URL : '';
        if (empty($backUrl)) {
            $backUrl = $baseUrl . '/';
        }

        $pageTitle = $title . ' - Unify';

        $headerPath = __DIR__ . '/../views/layouts/header.php';
        $footerPath = __DIR__ . '/../views/layouts/footer.php';

        if (file_exists($headerPath) && file_exists($footerPath)) {
            require_once $headerPath;
            ?>
            <div class="container py-5">
                <div class="card-modern p-5 text-center mx-auto" style="max-width: 620px;">
                    <div class="mb-3">
                        <div class="brand-icon-box mx-auto d-flex align-items-center justify-content-center rounded-circle" style="width: 64px; height: 64px; font-size: 2rem; background: var(--bg-hover, #f0f2f5); color: var(--navy-primary, #07193d);">
                            <?php if ($statusCode === 404): ?>
                                <i class="bi bi-question-circle"></i>
                            <?php elseif ($statusCode === 403 || $statusCode === 401): ?>
                                <i class="bi bi-shield-x"></i>
                            <?php else: ?>
                                <i class="bi bi-exclamation-triangle text-danger"></i>
                            <?php endif; ?>
                        </div>
                    </div>
                    <span class="badge bg-secondary-subtle text-secondary px-3 py-1 rounded-pill mb-2">Error <?= (int)$statusCode ?></span>
                    <h2 class="fw-bold text-dark mb-2"><?= htmlspecialchars($title) ?></h2>
                    <p class="text-muted mb-4"><?= htmlspecialchars($message) ?></p>
                    <div class="d-flex justify-content-center gap-2">
                        <a href="<?= htmlspecialchars($backUrl) ?>" class="btn btn-outline-primary px-4 rounded-pill">
                            <i class="bi bi-arrow-left me-1"></i> Return Home
                        </a>
                    </div>
                </div>
            </div>
            <?php
            require_once $footerPath;
        } else {
            echo '<!DOCTYPE html><html><head><title>' . htmlspecialchars($title) . '</title></head><body>';
            echo '<h1>' . htmlspecialchars($title) . '</h1>';
            echo '<p>' . htmlspecialchars($message) . '</p>';
            echo '<p><a href="' . htmlspecialchars($backUrl) . '">Return Home</a></p>';
            echo '</body></html>';
        }
    }
}
