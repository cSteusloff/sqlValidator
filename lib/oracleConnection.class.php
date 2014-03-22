<?php

/**
 * @package    SqlValidator
 * @author     Christian Steusloff
 * @author     Jens Wiemann
 */

/**
 * Class oracleConnection
 * require define.inc.php
 */

class oracleConnection extends sqlConnection
{

    /**
     * @var array string - error information
     */
    private $errortext = null;

    /**
     * @return string - error message
     */
    public function getErrortext()
    {
        return $this->errortext['message'];
    }

    /**
     * @return int - error position in string
     */
    public function getErrorPosition()
    {
        return $this->errortext['offset'];
    }

    /**
     * @param string $host
     * @param string $database
     * @param string $user
     * @param string $password
     * @param string $port
     */
    public function __construct($host = DB_HOST, $database = DB_DATABASE, $user = DB_USER, $password = DB_PASSWORD, $port = DB_PORT)
    {
        $this->hostname = $host;
        $this->database = $database;
        $this->username = $user;
        $this->password = $password;
        $this->port = $port;
        $this->openConnection();
    }

    /**
     * Open the connection
     *
     * @return boolean
     */
    public function openConnection()
    {
        try {
            $this->conn = oci_connect($this->username, $this->password, $this->hostname . '/' . $this->database . ':' . $this->port, DB_CHARSET);
            if (!$this->conn) {
                //$e = oci_error();
                // TODO error handling possible
            }
        } catch (Exception $e) {
            // TODO error handling possible
        }
    }

    /**
     * set database query
     *
     * @param string $sql SQL Query
     * @return mixed
     */
    public function setQuery($sql = '')
    {
        try {
            if (is_resource($this->recordset)) {
                // Frees resources associated with Oracle's cursor or statement
                oci_free_statement($this->recordset);
            }
            $this->sqlquery = empty($sql) ? $this->sqlquery : $sql;
            $this->recordset = oci_parse($this->conn, $this->sqlquery);
        } catch (Exception $e) {
            // TODO error handling possible
        }
    }

    /**
     * running SQL query with commit
     *
     * @return void
     */
    public function execute()
    {
        oci_execute($this->recordset);
        $this->errortext = oci_error($this->recordset);
    }

    /**
     * running SQL query without commit
     *
     * @return void
     */
    public function executeNoCommit()
    {
        oci_execute($this->recordset, OCI_NO_AUTO_COMMIT);
        $this->errortext = oci_error($this->recordset);
    }

    /**
     * @param string $name - name for savepoint
     * @return void
     */
    public function setSavePoint($name = null)
    {
        // set default unique savepoint name
        $point = "p" . time();
        $this->savepoint = is_null($name) ? $point : $name;
        $query = 'SAVEPOINT ' . $this->savepoint;
        $stid = oci_parse($this->conn, $query);
        oci_execute($stid, OCI_NO_AUTO_COMMIT);
    }

    /**
     * rollback to last savepoint
     *
     * @param bool $commit
     * @return void
     */
    public function rollbackSavePoint($commit = true)
    {
        $query = 'ROLLBACK TO SAVEPOINT ' . $this->savepoint;
        $stid = oci_parse($this->conn, $query);
        oci_execute($stid, OCI_NO_AUTO_COMMIT);
        if ($commit) $this->commit();
    }

    /**
     * commit queries
     */
    public function commit()
    {
        oci_commit($this->conn);
    }

    /**
     * Returns an (associative) array with the following record
     *
     * @param bool $assoc
     * @return bool
     */
    public function Fetch($assoc = true)
    {
        if (!empty($this->recordset)) {
            try {
                if ($assoc == true) {
                    $this->row = @oci_fetch_assoc($this->recordset);
                } else {
                    $this->row = @oci_fetch_array($this->recordset);
                }
                return is_array($this->row);
            } catch (Exception $e) {
                // TODO error handling possible
            }
        }
        return false;
    }

    /**
     * Returns the number of affected rows by SQL query
     *
     * @return int
     */
    public function affectedRows()
    {
        if (!empty($this->recordset)) {
            return oci_num_rows($this->conn);
        } else {
            return 0;
        }
    }

    /**
     * Returns the number of columns by SQL query
     *
     * @return int
     */
    public function numColumns()
    {
        if (!empty($this->recordset)) {
            return oci_num_fields($this->recordset);
        } else {
            return 0;
        }
    }

    /**
     * Returns the number of rows by SQL query
     *
     * @param bool $commit
     * @return int
     */
    public function numRows($commit = true)
    {
        $numRows = 0;
        if (!empty($this->recordset)) {
            $this->getStatementType();
            if (strcasecmp($this->getStatementType(), "SELECT") == 0) {
                $tmp = $this->sqlquery;
                $this->setQuery('SELECT COUNT(*) AS NUM FROM (' . $this->sqlquery . ')');
                ($commit) ? $this->execute() : $this->executeNoCommit();
                $this->Fetch();
                $numRows = $this->row["NUM"];
                $this->setQuery($tmp);
                ($commit) ? $this->execute() : $this->executeNoCommit();
            } else {
                return $this->affectedRows();
            }
        }
        return $numRows;
    }

    /**
     * Returns the type of SQL query statement
     *
     * @return string
     */
    public function getStatementType()
    {
        if (!empty($this->recordset)) {
            return oci_statement_type($this->recordset);
        } else {
            return "unknown";
        }
    }

    /**
     * Destroy
     */
    public function __destruct()
    {
        try {
            $this->closeConnection();
        } catch (Exception $e) {
            // TODO error handling possible
        }
    }

    /**
     * close oracle connection
     *
     * @return bool
     */
    public function closeConnection()
    {
        // get database type
        $type = (is_resource($this->conn) ? get_resource_type($this->conn) : 'unknown');
        if (strstr($type, "oci8")) {
            return oci_close($this->conn);
        } else {
            // TODO error handling possible
        }
    }

    /**
     * Returns information's of connection
     *
     * @return string
     */
    public function connectionInfo()
    {
        return $this->username . '@' . $this->database . ':' . $this->hostname;
    }

    /**
     * Returns the name of column
     *
     * @param $num - column position
     * @return string
     */
    public function getFieldname($num)
    {
        if (!empty($this->recordset)) {
            return oci_field_name($this->recordset, $num);
        } else {
            return '';
        }
    }

    /**
     * get names of header from table
     *
     * @param bool $toString - serialize header
     * @return array|string
     */
    public function getHeader($toString = false)
    {
        $arr = null;
        if (!empty($this->recordset)) {
            $arr = array();
            for ($i = 1; $i <= $this->numColumns(); $i++) {
                $arr[] = $this->getFieldname($i);
            }
        }
        if ($toString) {
            return serialize($arr);
        } else {
            return $arr;
        }
    }

    /**
     * get data from table
     *
     * @param bool $toString
     * @return array|string
     */
    public function getContent($toString = false)
    {
        $arr = array();
        $row = 0;
        while ($this->Fetch()) {
            $column = 0;
            foreach ($this->row as $item) {
                $arr[$row][$column++] = ($item !== null ? $item : "");
            }
            $row++;
        }
        if ($toString) {
            return serialize($arr);
        } else {
            return $arr;
        }
    }


    /**
     * Returns a complete table
     *
     * @param string $classname - css class for table div
     * @param null $tablename
     * @return string
     */
    public function printTable($classname = null, $tablename = null)
    {
        if (!empty($this->recordset)) {
            $tablestr = "<div ";
            if (is_null($classname)) {
                $tablestr .= ">\n";
            } else {
                $tablestr .= "class='" . $classname . "'>\n";
            }
            if (!is_null($tablename)) {
                $tablestr .= "<span>{$tablename}</span>";
            }
            $tablestr .= "<table>\n";
            $tablestr .= "<thead><tr>\n";

            for ($i = 1; $i <= $this->numColumns(); $i++) {
                $tablestr .= "<th>";
                $tablestr .= ucfirst(strtolower($this->getFieldname($i)));
                $tablestr .= "</th>";
            }
            $tablestr .= "</tr></thead><tbody>\n";
            while ($this->Fetch()) {
                $tablestr .= "<tr>\n";
                foreach ($this->row as $item) {
                    $tablestr .= "<td>" . ($item !== null ? $item : "") . "</td>\n";
                }
                $tablestr .= "</tr>\n";
            }
            $tablestr .= "<tbody></table>\n";

            $tablestr .= "</div>";
            return $tablestr;
        } else {
            return "";
        }
    }
}