<?php

require_once __DIR__ . '/../models/UserModel.php';

class AuthController {
    private $userModel;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->userModel = new UserModel();
    }

    public function welcome() {
        if (isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/index.php');
            exit;
        }

        header('Location: ' . BASE_URL . '/index.php?action=login');
        exit;
    }

    public function register() {
        if (isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/index.php');
            exit;
        }

        $errors = [];
        $formData = [
            'full_name' => '',
            'username'  => ''
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!validate_csrf_token()) {
                $errors[] = 'Security Error: Invalid or expired CSRF token. Please try submitting again.';
            }

            $fullName        = trim($_POST['full_name'] ?? '');
            $username        = trim($_POST['username'] ?? '');
            $password        = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            $formData['full_name'] = $fullName;
            $formData['username']  = $username;

            if (empty($fullName)) {
                $errors[] = 'Full Name is required.';
            } elseif (strlen($fullName) < 2 || strlen($fullName) > 100) {
                $errors[] = 'Full Name must be between 2 and 100 characters.';
            }

            if (empty($username)) {
                $errors[] = 'Username is required.';
            } elseif (strlen($username) < 3 || strlen($username) > 50) {
                $errors[] = 'Username must be between 3 and 50 characters.';
            } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
                $errors[] = 'Username may only contain letters, numbers, and underscores.';
            } elseif ($this->userModel->usernameExists($username)) {
                $errors[] = 'Username is already taken. Please choose another.';
            }

            $pwdErrors = self::validatePasswordComplexity($password, $confirmPassword);
            if (!empty($pwdErrors)) {
                $errors = array_merge($errors, $pwdErrors);
            }

            if (empty($errors)) {
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                $created = false;
                try {
                    $created = $this->userModel->create($username, $passwordHash, $fullName);
                } catch (PDOException $e) {
                    error_log("User registration DB error: " . $e->getMessage());
                    if ($e->getCode() == 23000 || strpos($e->getMessage(), 'Duplicate') !== false) {
                        $errors[] = 'Username is already taken. Please choose another.';
                    } else {
                        $errors[] = 'A database error occurred while creating your account. Please try again.';
                    }
                } catch (Throwable $e) {
                    error_log("User registration unexpected error: " . $e->getMessage());
                    $errors[] = 'An unexpected error occurred while creating your account. Please try again.';
                }

                if ($created) {
                    $_SESSION['success_message'] = 'Registration successful! You can now log in.';
                    header('Location: ' . BASE_URL . '/index.php?action=login');
                    exit;
                }
            }
        }

        $pageTitle = 'Register';
        require __DIR__ . '/../views/auth/register.php';
    }

    public function login() {
        if (isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/index.php');
            exit;
        }

        $errors = [];
        $username = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!validate_csrf_token()) {
                $errors[] = 'Security Error: Invalid or expired CSRF token. Please try submitting again.';
            }

            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';

            if (empty($username)) {
                $errors[] = 'Username is required.';
            }
            if (empty($password)) {
                $errors[] = 'Password is required.';
            }

            if (empty($errors)) {
                $user = $this->userModel->findByUsername($username);

                if (!$user) {
                    $errors[] = 'No account found with that username.';
                } elseif (!password_verify($password, $user['password'])) {
                    $errors[] = 'Incorrect password.';
                } else {
                    session_regenerate_id(true);

                    $_SESSION['user_id']       = $user['id'];
                    $_SESSION['username']      = $user['username'];
                    $_SESSION['full_name']     = $user['full_name'];
                    $_SESSION['profile_image'] = $user['profile_image'] ?? null;

                    $_SESSION['success_message'] = 'Welcome back, ' . htmlspecialchars($user['full_name']) . '!';
                    header('Location: ' . BASE_URL . '/index.php');
                    exit;
                }
            }
        }

        $pageTitle = 'Login';
        require __DIR__ . '/../views/auth/login.php';
    }

    public function logout() {
        $_SESSION = [];

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }

        $_SESSION['success_message'] = 'You have been logged out successfully.';
        header('Location: ' . BASE_URL . '/index.php?action=login');
        exit;
    }

    public static function validatePasswordComplexity(string $password, ?string $confirmPassword = null): array {
        $errors = [];

        if ($password === '') {
            $errors[] = 'Password is required.';
            return $errors;
        }

        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters long.';
        }
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Password must contain at least one uppercase letter (A–Z).';
        }
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'Password must contain at least one lowercase letter (a–z).';
        }
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password must contain at least one number (0–9).';
        }
        if (!preg_match('/[^a-zA-Z0-9]/', $password)) {
            $errors[] = 'Password must contain at least one special character (e.g., @, #, $, %, !).';
        }

        if ($confirmPassword !== null) {
            if ($confirmPassword === '') {
                $errors[] = 'Please confirm your password.';
            } elseif ($password !== $confirmPassword) {
                $errors[] = 'Passwords do not match.';
            }
        }

        return $errors;
    }
}
