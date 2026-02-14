<?php
/**
 * ============================================
 * DATABASE CLASS (PDO WRAPPER)
 * ============================================
 * Project: Street2Screen ZA
 * Purpose: Secure database connection and query handling
 * Author: Ignatius Mayibongwe Khumalo
 * Institution: Eduvos Private Institution
 * Course: ITECA3-12 Initial Project
 * Date: February 2026
 * ============================================
 * 
 * WHAT THIS CLASS DOES:
 * - Creates a secure PDO connection to MySQL database
 * - Provides methods for running SQL queries safely
 * - Prevents SQL injection using prepared statements
 * - Handles database errors gracefully
 * - Supports transactions (for payment processing)
 * - Provides helper methods for common database operations
 * 
 * SECURITY FEATURES:
 * - Uses prepared statements (prevents SQL injection)
 * - Parameter binding (automatic escaping)
 * - Error handling (doesn't expose sensitive info)
 * - Connection singleton (one connection per request)
 * 
 * ============================================
 */

class Database {
    
    // ============================================
    // SECTION 1: CLASS PROPERTIES
    // ============================================
    // Purpose: Define private variables that store database connection and query info
    
    /**
     * @var PDO Database connection object
     * This stores the active PDO connection
     * It's private so only this class can access it
     */
    private $connection;
    
    /**
     * @var PDOStatement Prepared statement object
     * This stores the last prepared statement
     * Used for binding parameters and executing queries
     */
    private $statement;
    
    /**
     * @var string Error message from last operation
     * If a database operation fails, error details are stored here
     */
    private $error;
    
    // ============================================
    // SECTION 2: CONSTRUCTOR (Runs when object is created)
    // ============================================
    // Purpose: Establish database connection when class is instantiated
    // Called like: $db = new Database();
    
    /**
     * Constructor - Connects to database automatically
     * 
     * WHAT IT DOES:
     * 1. Loads database configuration from config/database.php
     * 2. Creates PDO connection string (DSN)
     * 3. Attempts to connect to MySQL
     * 4. Stores connection or error message
     * 
     * EXAMPLE USAGE:
     * $db = new Database();  // Connection happens automatically
     */
    public function __construct() {
        
        // ----------------------------------------
        // Load Configuration File
        // ----------------------------------------
        // This file contains DB_HOST, DB_NAME, DB_USER, DB_PASS, etc.
        // We use dirname(__DIR__) to go up one level from includes/ to project root
        require_once dirname(__DIR__) . '/config/database.php';
        
        // ----------------------------------------
        // Build DSN (Data Source Name)
        // ----------------------------------------
        // DSN is the connection string that tells PDO:
        // - What database system we're using (mysql)
        // - Where the server is (localhost or sql305.infinityfree.com)
        // - What database to connect to (street2screen_db)
        // - What character set to use (utf8mb4 for SA languages)
        
        // Example DSN: "mysql:host=localhost;dbname=street2screen_db;charset=utf8mb4"
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        
        // ----------------------------------------
        // Attempt Database Connection
        // ----------------------------------------
        // We use try-catch to handle connection errors gracefully
        // If connection fails, we don't want to crash the whole site
        
        try {
            // Create new PDO connection
            // Parameters:
            // 1. $dsn - Connection string
            // 2. DB_USER - Database username (root or if0_41132529)
            // 3. DB_PASS - Database password
            // 4. DB_OPTIONS - Array of PDO options (from config file)
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, DB_OPTIONS);
            
            // If we reach here, connection was successful!
            // No need to do anything else
            
        } catch (PDOException $e) {
            // Connection failed!
            // PDOException contains details about what went wrong
            
            // Store error message (but hide sensitive details from users)
            $this->error = $e->getMessage();
            
            // Log error to file for debugging (in development only)
            if (defined('APP_ENV') && APP_ENV === 'development') {
                error_log("Database Connection Error: " . $e->getMessage());
            }
            
            // In production, we would show a generic error to users
            // and log the real error for administrators to review
        }
    }
    
    // ============================================
    // SECTION 3: QUERY PREPARATION
    // ============================================
    // Purpose: Prepare SQL statements for safe execution
    
    /**
     * Prepare a SQL query for execution
     * 
     * WHAT IT DOES:
     * 1. Takes a SQL query string
     * 2. Prepares it for execution (compiles it)
     * 3. Stores the prepared statement for parameter binding
     * 
     * WHY WE USE THIS:
     * - Prevents SQL injection attacks
     * - Improves performance (query is compiled once, executed many times)
     * - Allows parameter binding for safe data insertion
     * 
     * @param string $sql SQL query with placeholders
     * @return void
     * 
     * EXAMPLE:
     * $db->query("SELECT * FROM users WHERE email = :email");
     * $db->bind(':email', 'user@example.com');
     * $result = $db->execute();
     */
    public function query($sql) {
        // Prepare the SQL statement
        // This compiles the query and checks for syntax errors
        $this->statement = $this->connection->prepare($sql);
        
        // Return $this to allow method chaining
        // This lets us do: $db->query($sql)->execute();
        return $this;
    }
    
    // ============================================
    // SECTION 4: PARAMETER BINDING
    // ============================================
    // Purpose: Safely bind values to SQL placeholders
    
    /**
     * Bind a value to a placeholder in prepared statement
     * 
     * WHAT IT DOES:
     * 1. Takes a placeholder name (like :email)
     * 2. Takes a value to insert (like 'user@example.com')
     * 3. Automatically determines the data type
     * 4. Binds the value safely to prevent SQL injection
     * 
     * WHY THIS IS SECURE:
     * - Values are escaped automatically
     * - Special characters are handled safely
     * - SQL injection is impossible
     * 
     * @param string $param Placeholder name (e.g., ':email')
     * @param mixed $value Value to bind
     * @param int|null $type PDO parameter type (auto-detected if null)
     * @return void
     * 
     * EXAMPLE:
     * $db->query("INSERT INTO users (email, password) VALUES (:email, :password)");
     * $db->bind(':email', 'user@example.com');
     * $db->bind(':password', $hashedPassword);
     * $db->execute();
     */
    public function bind($param, $value, $type = null) {
        
        // ----------------------------------------
        // Auto-detect Data Type
        // ----------------------------------------
        // If type is not specified, we detect it automatically
        // This ensures proper handling of different data types
        
        if (is_null($type)) {
            switch (true) {
                case is_int($value):
                    // Integer (e.g., 123, 456)
                    $type = PDO::PARAM_INT;
                    break;
                case is_bool($value):
                    // Boolean (true/false)
                    $type = PDO::PARAM_BOOL;
                    break;
                case is_null($value):
                    // NULL value
                    $type = PDO::PARAM_NULL;
                    break;
                default:
                    // Default to string (most common)
                    $type = PDO::PARAM_STR;
            }
        }
        
        // ----------------------------------------
        // Bind the Parameter
        // ----------------------------------------
        // This safely attaches the value to the placeholder
        $this->statement->bindValue($param, $value, $type);
        
        // Return $this for method chaining
        return $this;
    }
    
    // ============================================
    // SECTION 5: QUERY EXECUTION
    // ============================================
    // Purpose: Execute prepared statements and handle errors
    
    /**
     * Execute the prepared statement
     * 
     * WHAT IT DOES:
     * 1. Runs the prepared SQL query with bound parameters
     * 2. Returns true if successful, false if failed
     * 3. Stores error message if execution fails
     * 
     * @return bool True on success, false on failure
     * 
     * EXAMPLE:
     * $db->query("DELETE FROM products WHERE product_id = :id");
     * $db->bind(':id', 123);
     * if ($db->execute()) {
     *     echo "Product deleted successfully!";
     * } else {
     *     echo "Error: " . $db->error();
     * }
     */
    public function execute() {
        try {
            // Execute the prepared statement
            // Returns true if successful, false otherwise
            return $this->statement->execute();
            
        } catch (PDOException $e) {
            // Query execution failed
            $this->error = $e->getMessage();
            
            // Log error for debugging
            error_log("Query Execution Error: " . $e->getMessage());
            
            return false;
        }
    }
    
    // ============================================
    // SECTION 6: FETCH RESULTS
    // ============================================
    // Purpose: Retrieve data from SELECT queries
    
    /**
     * Fetch all rows as associative array
     * 
     * WHAT IT DOES:
     * Returns all rows from the last query as an array
     * Each row is an associative array: ['column_name' => 'value']
     * 
     * @return array Array of rows
     * 
     * EXAMPLE:
     * $db->query("SELECT * FROM users WHERE user_type = :type");
     * $db->bind(':type', 'seller');
     * $db->execute();
     * $sellers = $db->fetchAll();
     * // $sellers = [
     * //     ['user_id' => 1, 'full_name' => 'John'],
     * //     ['user_id' => 2, 'full_name' => 'Jane']
     * // ]
     */
    public function fetchAll() {
        $this->execute();
        return $this->statement->fetchAll();
    }
    
    /**
     * Fetch single row as associative array
     * 
     * WHAT IT DOES:
     * Returns only the first row from query results
     * Useful when you expect only one result (e.g., login check)
     * 
     * @return array|false Single row or false if no results
     * 
     * EXAMPLE:
     * $db->query("SELECT * FROM users WHERE email = :email");
     * $db->bind(':email', 'user@example.com');
     * $user = $db->fetch();
     * if ($user) {
     *     echo "Welcome, " . $user['full_name'];
     * }
     */
    public function fetch() {
        $this->execute();
        return $this->statement->fetch();
    }
    
    /**
     * Fetch single column value
     * 
     * WHAT IT DOES:
     * Returns a single value from the first row
     * Useful for COUNT, SUM, or checking if something exists
     * 
     * @return mixed Single value
     * 
     * EXAMPLE:
     * $db->query("SELECT COUNT(*) FROM products WHERE status = 'active'");
     * $count = $db->fetchColumn();
     * echo "Active products: $count";
     */
    public function fetchColumn() {
        $this->execute();
        return $this->statement->fetchColumn();
    }
    
    // ============================================
    // SECTION 7: HELPER METHODS
    // ============================================
    // Purpose: Provide convenient shortcut methods
    
    /**
     * Get row count from last query
     * 
     * WHAT IT DOES:
     * Returns number of rows affected by last INSERT, UPDATE, or DELETE
     * 
     * @return int Number of rows affected
     * 
     * EXAMPLE:
     * $db->query("UPDATE products SET featured = 1 WHERE category_id = :cat");
     * $db->bind(':cat', 2);
     * $db->execute();
     * $affected = $db->rowCount();
     * echo "$affected products were featured";
     */
    public function rowCount() {
        return $this->statement->rowCount();
    }
    
    /**
     * Get last inserted ID
     * 
     * WHAT IT DOES:
     * After an INSERT, returns the auto-increment ID that was created
     * Crucial for getting new user_id, product_id, etc.
     * 
     * @return int Last insert ID
     * 
     * EXAMPLE:
     * $db->query("INSERT INTO users (full_name, email) VALUES (:name, :email)");
     * $db->bind(':name', 'John Doe');
     * $db->bind(':email', 'john@example.com');
     * $db->execute();
     * $newUserId = $db->lastInsertId();
     * echo "New user ID: $newUserId";
     */
    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }
    
    /**
     * Get last error message
     * 
     * WHAT IT DOES:
     * Returns the error message from the last failed operation
     * Useful for debugging and error handling
     * 
     * @return string Error message
     */
    public function error() {
        return $this->error;
    }
    
    // ============================================
    // SECTION 8: TRANSACTION SUPPORT
    // ============================================
    // Purpose: Handle multi-step database operations safely
    // Crucial for payment processing (order + transaction + stock update)
    
    /**
     * Begin a database transaction
     * 
     * WHAT IT DOES:
     * Starts a transaction - all following queries are grouped
     * If ANY query fails, ALL can be rolled back
     * 
     * WHY THIS MATTERS:
     * When processing payments, we need to:
     * 1. Create order
     * 2. Record transaction
     * 3. Update product stock
     * If step 2 fails, we need to undo step 1 (rollback)
     * 
     * @return bool True on success
     */
    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }
    
    /**
     * Commit a transaction (save all changes)
     * 
     * WHAT IT DOES:
     * Saves all queries made since beginTransaction()
     * Makes the changes permanent
     * 
     * @return bool True on success
     */
    public function commit() {
        return $this->connection->commit();
    }
    
    /**
     * Rollback a transaction (undo all changes)
     * 
     * WHAT IT DOES:
     * Cancels all queries made since beginTransaction()
     * Database returns to state before transaction started
     * 
     * @return bool True on success
     * 
     * EXAMPLE TRANSACTION:
     * try {
     *     $db->beginTransaction();
     *     
     *     // Create order
     *     $db->query("INSERT INTO orders (...) VALUES (...)");
     *     $db->execute();
     *     $orderId = $db->lastInsertId();
     *     
     *     // Record transaction
     *     $db->query("INSERT INTO transactions (...) VALUES (...)");
     *     $db->execute();
     *     
     *     // Update stock
     *     $db->query("UPDATE products SET stock_quantity = stock_quantity - 1 WHERE product_id = :id");
     *     $db->execute();
     *     
     *     // Everything worked! Save changes
     *     $db->commit();
     *     
     * } catch (Exception $e) {
     *     // Something failed! Undo everything
     *     $db->rollback();
     *     echo "Transaction failed: " . $e->getMessage();
     * }
     */
    public function rollback() {
        return $this->connection->rollback();
    }
    
    // ============================================
    // SECTION 9: CONNECTION CHECK
    // ============================================
    // Purpose: Verify database connection is active
    
    /**
     * Check if database is connected
     * 
     * WHAT IT DOES:
     * Tests if the PDO connection object exists and is valid
     * 
     * @return bool True if connected, false otherwise
     * 
     * EXAMPLE:
     * $db = new Database();
     * if (!$db->isConnected()) {
     *     die("Database connection failed!");
     * }
     */
    public function isConnected() {
        return ($this->connection instanceof PDO);
    }
    
    // ============================================
    // END OF DATABASE CLASS
    // ============================================
}

/**
 * ============================================
 * USAGE EXAMPLES
 * ============================================
 * 
 * EXAMPLE 1: Simple SELECT Query
 * --------------------------------
 * $db = new Database();
 * $db->query("SELECT * FROM products WHERE category_id = :cat");
 * $db->bind(':cat', 2);
 * $products = $db->fetchAll();
 * 
 * EXAMPLE 2: INSERT with Last Insert ID
 * --------------------------------------
 * $db = new Database();
 * $db->query("INSERT INTO users (full_name, email, password_hash) VALUES (:name, :email, :pass)");
 * $db->bind(':name', 'John Doe');
 * $db->bind(':email', 'john@example.com');
 * $db->bind(':pass', password_hash('password123', PASSWORD_BCRYPT));
 * $db->execute();
 * $newUserId = $db->lastInsertId();
 * 
 * EXAMPLE 3: UPDATE Query
 * -----------------------
 * $db = new Database();
 * $db->query("UPDATE products SET featured = 1 WHERE product_id = :id");
 * $db->bind(':id', 123);
 * if ($db->execute()) {
 *     echo "Product featured successfully!";
 * }
 * 
 * EXAMPLE 4: Transaction for Payment
 * -----------------------------------
 * $db = new Database();
 * try {
 *     $db->beginTransaction();
 *     // ... multiple queries ...
 *     $db->commit();
 * } catch (Exception $e) {
 *     $db->rollback();
 * }
 * 
 * ============================================
 */
?>
