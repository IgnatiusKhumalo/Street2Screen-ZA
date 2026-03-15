<?php
/**
 * ============================================
 * DATABASE CLASS - PDO WRAPPER (FIXED)
 * ============================================
 * Secure database operations with prepared statements
 * FIX: Added PDO::FETCH_ASSOC to fetch() and fetchAll()
 * ============================================
 */

class Database {
    private $conn;
    private $stmt;
    private $error;
    
    /**
     * Constructor - establish database connection
     */
    public function __construct() {
        try {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
            $this->conn = new PDO($dsn, DB_USER, DB_PASS, DB_OPTIONS);
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            error_log("Database Connection Error: " . $this->error);
            die("Database connection failed. Please try again later.");
        }
    }
    
    /**
     * Prepare SQL query
     * @param string $sql SQL query with placeholders
     */
    public function query($sql) {
        $this->stmt = $this->conn->prepare($sql);
    }
    
    /**
     * Bind values to prepared statement
     * @param mixed $param Parameter identifier
     * @param mixed $value Value to bind
     * @param mixed $type Data type
     */
    public function bind($param, $value, $type = null) {
        if (is_null($type)) {
            switch (true) {
                case is_int($value):
                    $type = PDO::PARAM_INT;
                    break;
                case is_bool($value):
                    $type = PDO::PARAM_BOOL;
                    break;
                case is_null($value):
                    $type = PDO::PARAM_NULL;
                    break;
                default:
                    $type = PDO::PARAM_STR;
            }
        }
        $this->stmt->bindValue($param, $value, $type);
    }
    
    /**
     * Execute prepared statement
     * @return bool Success status
     */
    public function execute() {
        try {
            return $this->stmt->execute();
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            error_log("Query Execution Error: " . $this->error);
            return false;
        }
    }
    
    /**
     * Fetch single row as associative array
     * @return array|false
     * FIXED: Added PDO::FETCH_ASSOC
     */
    public function fetch() {
        $this->execute();
        return $this->stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Fetch all rows as associative array
     * @return array
     * FIXED: Added PDO::FETCH_ASSOC
     */
    public function fetchAll() {
        $this->execute();
        return $this->stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get row count
     * @return int
     */
    public function rowCount() {
        return $this->stmt->rowCount();
    }
    
    /**
     * Get last inserted ID
     * @return string
     */
    public function lastInsertId() {
        return $this->conn->lastInsertId();
    }
    
    /**
     * Begin transaction
     */
    public function beginTransaction() {
        return $this->conn->beginTransaction();
    }
    
    /**
     * Commit transaction
     */
    public function commit() {
        return $this->conn->commit();
    }
    
    /**
     * Rollback transaction
     */
    public function rollback() {
        return $this->conn->rollback();
    }
}
?>
