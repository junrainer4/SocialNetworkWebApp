<?php
<<<<<<< HEAD
=======

>>>>>>> b6e9240576e48f39db6dea044ddd922cc77d8534
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class ErrorHandler {
<<<<<<< HEAD
=======
    private static $logFile = __DIR__ . '/../../logs/app.log';

    public static function sanitizeLogMessage(string $msg): string {
        $msg = preg_replace('/(password|passwd|pwd)(\s*[:=]\s*|\s*=>\s*[\'"]?)[^\s,;\'"]+/i', '$1$2***', $msg);
        $msg = preg_replace('/(csrf_token|token|session_id)(\s*[:=]\s*|\s*=>\s*[\'"]?)[^\s,;\'"]+/i', '$1$2***', $msg);
        $msg = preg_replace('/(mysql:host=[^;]+;port=\d+;dbname=[^;]+;charset=[^;]+)/i', '$1', $msg);
        return $msg;
    }

    public static function writeLog(string $level, string $message, array $context = []): void {
        $timestamp = date('Y-m-d H:i:s');
        $sanitizedMessage = self::sanitizeLogMessage($message);
        $contextStr = !empty($context) ? ' ' . json_encode(array_map(function($v) {
            return is_string($v) ? self::sanitizeLogMessage($v) : $v;
        }, $context)) : '';

        $entry = "[{$timestamp}] [{$level}] {$sanitizedMessage}{$contextStr}" . PHP_EOL;

        error_log("[Nexus {$level}] {$sanitizedMessage}");

    }

>>>>>>> b6e9240576e48f39db6dea044ddd922cc77d8534
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

<<<<<<< HEAD
        $pageTitle = $title . ' - Unify';
=======
        $pageTitle = $title . ' - Nexus';
>>>>>>> b6e9240576e48f39db6dea044ddd922cc77d8534

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
