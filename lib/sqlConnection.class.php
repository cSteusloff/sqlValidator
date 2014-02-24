<?php
/**
 * User: Christian Steusloff
 * Date: 27.11.13
 * Time: 18:40
 */

abstract class sqlConnection {
    /**
     * The active connection
     *
     * @var object - connection to database
     */
    protected $conn;

    /**
     * The database host
     *
     * @var string - database host
     */
    public $hostname;

    /**
     * The name of database
     *
     * @var string - database name
     */
    public $database;

    /**
     * The login username to connect to database
     *
     * @var string - database username
     */
    public $username;

    /**
     * The login password to connect to database
     *
     * @var string - database password
     */
    public $password;

    /**
     * The port to connect to database
     *
     * @var string - database port
     */
    public $port;

    /**
     * Array with current Row from Fetch
     * Data come from $recordset
     *
     * @var array
     */
    public $row = array();

    /**
     * The current recordset
     *
     * @var object - active recordset
     */
    public $recordset;

    /**
     * The last/active SQL query
     *
     * @var string - SQL query
     */
    public $sqlquery;


//    public $origsqlquery;

    /**
     * Savepoint for rollback - state of database
     *
     * @var string - SQL Savepoint
     */
    protected $savepoint;


    /**
     * Abstract class to handle connections
     *
     * @param $host string
     * @param $database string
     * @param $user string
     * @param $password string
     * @param $port string
     */
    abstract public function __construct($host, $database, $user, $password, $port);

    /**
     * set SQL query
     *
     * @param string $sql SQL Query
     * @return mixed
     */
    abstract public function setQuery($sql = '');


    //abstract public function setOrigQuery($sql = '');


    /**
     * Try to running SQL query
     *
     *
     * @return mixed
     */
    abstract public function execute();

    /**
     * Create an (associative) array with the following record.
     * Returns true if array can create
     *
     * @param bool $assoc
     * @return bool
     */
    abstract public function Fetch($assoc = true);

    /**
     * Returns the number of affected Rows by SQL query
     *
     * @return int
     */
    abstract public function affectedRows();

    /**
     * Returns the number of columns by SQL query
     *
     * @return int
     */
    abstract public function numColumns();

    /**
     * Returns the number of rows by SQL query
     *
     * @return int
     */
    abstract public function numRows();

    /**
     * Open the connection
     *
     * @return boolean
     */
    abstract public function openConnection();

    /**
     * Close the connection
     *
     * @return boolean
     */
    abstract public function closeConnection();

    /**
     * Destroy
     */
    abstract public function __destruct();

    /**
     * Returns info of connection
     *
     * @return string
     */
    abstract public function connectionInfo();

    /**
     * Returns the columnname
     *
     * @param $number
     * @return string
     */
    abstract public function getFieldname($number);

    /**
     * Returns a complete table
     *
     * @param string $classname - css class from table
     * @return string
     */
    abstract public function printTable($classname = null);


    /**
     * Returns the type of SQL query statement
     *
     * @return string
     */
    abstract public function getStatementType();
} 