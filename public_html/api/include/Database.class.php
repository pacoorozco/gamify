<?php

require_once dirname(__FILE__) . '/Config.php';

class Database {

    private $host = DB_HOST;
    private $user = DB_USERNAME;
    private $pass = DB_PASSWORD;
    private $db_name = DB_NAME;

    private $dbh;
    private $error;

    private $stmt;

    function __construct() {
        // Set DSN
        $dsn = 'mysql:host=' . $this->host . ';dbname=' . $this->db_name;

        // Set options
        $options = array(
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        );

        try {
            $this->dbh = new PDO($dsn, $this->user, $this->pass, $options);
        }
        // Catch any errors
        catch (PDOException $e) {
            $this->error = $e->getMessage();
        }
    }

    /*
     * The prepare function allows you to bind values into your SQL statements.
     * This is important because it takes away the threat of SQL Injection
     * because you are no longer having to manually include the parameters
     * into the query string.
     *
     * Using the prepare function will also improve performance when running
     * the same query with different parameters multiple times.
     */
    public function query($query){
        $this->stmt = $this->dbh->prepare($query);
    }

    /*
     * In order to prepare our SQL queries, we need to bind the inputs with
     * the placeholders we put in place. This is what the Bind method is used
     * for.
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

    /*
     * execute method executes the prepared statement.
     */
    public function execute(){
        return $this->stmt->execute();
    }

    /*
     * returns an array of the result set rows
     * First we run the execute method, then we return the single result.
     */
    public function fetchAll(){
        $this->execute();
        return $this->stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /*
     * fetchOne method simply returns a single record from the database.
     * First we run the execute method, then we return the single result.
     */
    public function fetchOne(){
        $this->execute();
        return $this->stmt->fetch(PDO::FETCH_ASSOC);
    }

    /*
     * The next method simply returns the number of effected rows from the
     * previous delete, update or insert statement
     */
    public function rowCount(){
        return $this->stmt->rowCount();
    }

    /*
     * The Last Insert Id method returns the last inserted Id as a string
     */
    public function lastInsertId(){
        return $this->dbh->lastInsertId();
    }

    /*
     * Transactions allows you to run multiple changes to a database all in one
     * batch to ensure that your work will not be accessed incorrectly or there
     * will be no outside interferences before you are finished. If you are running
     * many queries that all rely upon each other, if one fails an exception will
     * be thrown and you can roll back any previous changes to the start of the
     * transaction.
     */

    /*
     * To begin a transaction
     */
    public function beginTransaction() {
        return $this->dbh->beginTransaction();
    }

    /*
     * To end a transaction and commit your changes
     */
    public function endTransaction() {
        return $this->dbh->commit();
    }

    /*
     * To cancel a transaction and roll back your changes
     */
    public function cancelTransaction() {
        return $this->dbh->rollBack();
    }

    /*
     * The Debug Dump Parameters methods dumps the the information that was contained in the Prepared Statement.
     */
    public function debugDumpParams() {
        return $this->stmt->debugDumpParams();
    }
}
