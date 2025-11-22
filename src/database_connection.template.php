<?php
/**
 * Database Connection Template
 * Copy this file to database_connection.php and update with your credentials
 * DO NOT commit database_connection.php to Git (it's in .gitignore)
 * 
 * INSTRUCTIONS:
 * 1. Copy this file: cp database_connection.template.php database_connection.php
 * 2. Edit database_connection.php with your actual credentials
 * 3. Never commit database_connection.php to version control
 */

class Database {
    private $host = "YOUR_DB_HOST";        // Usually: localhost or 127.0.0.1
    private $db_name = "YOUR_DB_NAME";     // Usually: propielequipo or propielequipo2
    private $username = "YOUR_DB_USER";    // Usually: root (for local) or your hosting username
    private $password = "YOUR_DB_PASSWORD"; // Usually: empty (for local XAMPP) or your hosting password
    private $conn;
    
    public function getConnection() {
        $this->conn = null;
        
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4",
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch(PDOException $e) {
            error_log("Connection error: " . $e->getMessage());
            die("Error de conexión a la base de datos. Por favor contacte al administrador.");
        }
        
        return $this->conn;
    }
    
    public function closeConnection() {
        $this->conn = null;
    }
}

// Legacy mysqli connection for compatibility
// Note: Change these values for production deployment
// If you get "No such file or directory" error, try using "127.0.0.1" instead of "localhost"
$db_host = "YOUR_DB_HOST";     // Change to "localhost", "127.0.0.1" or your hosting's MySQL hostname
$db_user = "YOUR_DB_USER";     // Change to your database username (usually "root" for local)
$db_pass = "YOUR_DB_PASSWORD"; // Change to your database password (usually empty for local XAMPP)
$db_name = "YOUR_DB_NAME";     // Change to your database name (e.g., "propielequipo2")

// Try connection with error suppression to handle it gracefully
$conex = @mysqli_connect($db_host, $db_user, $db_pass, $db_name);

// If localhost fails, automatically retry with 127.0.0.1
if (!$conex && $db_host === "localhost") {
    $conex = @mysqli_connect("127.0.0.1", $db_user, $db_pass, $db_name);
}

if (!$conex) {
    // Log the error for debugging
    error_log("Database connection failed: " . mysqli_connect_error());
    die("Error de conexión a la base de datos. Por favor contacte al administrador.");
}

// Set charset to UTF-8
mysqli_set_charset($conex, "utf8mb4");

?>
