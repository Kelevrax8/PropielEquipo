<?php
/**
 * Database Connection Template
 * Copy this file to database_connection.php and update with your credentials
 * DO NOT commit database_connection.php to Git (it's in .gitignore)
 */

class Database {
    private $host = "YOUR_DB_HOST";        // Usually: localhost or 127.0.0.1
    private $db_name = "YOUR_DB_NAME";     // Usually: propielequipo
    private $username = "YOUR_DB_USER";     // Usually: root (for local) or your hosting username
    private $password = "YOUR_DB_PASSWORD"; // Usually: empty (for local XAMPP) or your hosting password
    private $charset = "utf8mb4";
    public $conn;

    public function getConnection() {
        $this->conn = null;

        try {
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=" . $this->charset;
            $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Connection error: " . $e->getMessage());
            die("Error de conexión a la base de datos. Por favor, contacte al administrador.");
        }

        return $this->conn;
    }
}
?>
