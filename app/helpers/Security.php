<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}

function validate_csrf_token(?string $token = null): bool {
    if (empty($_SESSION['csrf_token'])) {
        return false;
    }

    if ($token === null) {
        $token = $_POST['csrf_token'] ?? '';

        if (empty($token)) {
            $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_SERVER['HTTP_X_XSRF_TOKEN'] ?? '';
        }
    }

    if (empty($token) || !is_string($token)) {
        return false;
    }

    return hash_equals($_SESSION['csrf_token'], $token);
}

function e($value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

date_default_timezone_set('Asia/Manila');

function format_time_ago($timestamp): string {
    if (empty($timestamp)) {
        return 'Just now';
    }

    $time = is_numeric($timestamp) ? (int)$timestamp : strtotime($timestamp);
    if ($time === false || $time <= 0) {
        return 'Just now';
    }

    $diff = time() - $time;

    if ($diff < 60) {
        return 'Just now';
    } elseif ($diff < 3600) {
        return floor($diff / 60) . 'm ago';
    } elseif ($diff < 86400) {
        return floor($diff / 3600) . 'h ago';
    } elseif ($diff < 604800) {
        return floor($diff / 86400) . 'd ago';
    } else {
        return date('M j, Y', $time);
    }
}
