<?php
// config/database.php
// Database configuration and PDO connection for Nexus

class Database {
    private static $host = '127.0.0.1';
    private static $port = '3306';
    private static $db_name = 'smcc_connect_db';
    private static $username = 'root';
    private static $password = '';
    private static $charset = 'utf8mb4';
    private static $conn = null;

    /**
     * Get the PDO database connection.
     *
     * @return PDO
     */
    public static function connect() {
        if (self::$conn === null) {
            $host = getenv('DB_HOST') ?: self::$host;
            $port = getenv('DB_PORT') ?: self::$port;
            $dbName = getenv('DB_NAME') ?: (defined('DB_NAME') ? DB_NAME : self::$db_name);
            $username = getenv('DB_USER') ?: self::$username;
            $password = (getenv('DB_PASS') !== false) ? getenv('DB_PASS') : self::$password;
            $charset = self::$charset;

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            $candidateDbs = array_unique(array_filter([
                $dbName,
                'smcc_connect_db',
                'social_app'
            ]));

            $lastException = null;
            foreach ($candidateDbs as $tryDb) {
                $dsn = "mysql:host=" . $host . ";port=" . $port . ";dbname=" . $tryDb . ";charset=" . $charset;
                try {
                    self::$conn = new PDO($dsn, $username, $password, $options);
                    self::$conn->exec("SET time_zone = '+08:00';");
                    $lastException = null;
                    break;
                } catch (PDOException $e) {
                    $lastException = $e;
                    // If error code is 1049 (Unknown database), continue to try next candidate
                    if ($e->getCode() == 1049 || strpos($e->getMessage(), 'Unknown database') !== false) {
                        continue;
                    }
                    break;
                }
            }

            if (self::$conn === null && $lastException !== null) {
                // Log actual error securely to server log without exposing details to users
                error_log("Database connection error: " . $lastException->getMessage());
                if (class_exists('ErrorHandler')) {
                    ErrorHandler::renderErrorPage(500, 'Database Unavailable', 'The database is temporarily unavailable. Please try again later.');
                    exit;
                }
                die("Database connection failed. Please try again later.");
            }
        }

        return self::$conn;
    }

    /**
     * Inject a test or mock PDO instance (for automated testing).
     */
    public static function setConnection(?PDO $conn): void {
        self::$conn = $conn;
    }

    /**
     * Disconnect and clear the cached PDO connection.
     */
    public static function disconnect(): void {
        self::$conn = null;
    }
}

// Establish and provide the connection instance
$pdo = Database::connect();
