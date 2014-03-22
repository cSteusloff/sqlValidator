<?php

/**
 * This class validate SQL queries.
 *
 * @package    SqlValidator
 * @author     Christian Steusloff
 * @author     Jens Wiemann
 */

/**
 * Class sqlValidator
 */
class sqlValidator
{
    /**
     * Master connection containing solution query
     *
     * @var sqlConnection
     */
    private $masterConnection = null;

    /**
     * Slave connection containing user query
     *
     * @var sqlConnection
     */
    private $slaveConnection = null;

    /**
     * Copy of slave connection
     *
     * @var sqlConnection
     */
    private $checkConnection = null;

    /**
     * @var taskHelper - Task
     */
    private $task = null;

    /**
     * @var string - table prefix user + userId + "_"
     */
    private $tablePrefix = null;

    /**
     * @param sqlConnection $masterConnection
     * @param sqlConnection $slaveConnection
     * @param taskHelper $task
     */
    public function setSqlConnection(sqlConnection $masterConnection, sqlConnection $slaveConnection, taskHelper $task)
    {
        $this->masterConnection = $masterConnection;
        $this->slaveConnection = $slaveConnection;
        $this->checkConnection = clone $slaveConnection;
        $this->task = $task;
        // TODO: without login system, this parameter is fix
        $this->tablePrefix = "user2_";
    }

    /**
     * @var string - mistake message
     */
    private $mistake = null;

    /**
     * set found mistake
     *
     * @param string $mistake
     */
    public function setMistake($mistake)
    {
        $this->mistake = $mistake;
    }

    /**
     * Get the saved mistake
     *
     * @return string
     */
    public function getMistake()
    {
        return $this->mistake;
    }

    /**
     * @param sqlConnection $masterConnection
     * @param sqlConnection $slaveConnection
     * @param taskHelper $task
     */
    function __construct(sqlConnection $masterConnection, sqlConnection $slaveConnection, taskHelper $task)
    {
        $this->setSqlConnection($masterConnection, $slaveConnection, $task);
    }

    /**
     * Checks all possible querytypes(tasktypes) for content errors.
     * Requires syntactically correct queries
     *
     * @return bool if answer is correct
     */
    public function validate()
    {
        //Set savepoints for all connections
        $this->masterConnection->setSavePoint();
        $this->slaveConnection->setSavePoint();
        $this->checkConnection->setSavePoint();

        //for safety reasons it is performed
//        $this->masterConnection->executeNoCommit();
//        $this->slaveConnection->executeNoCommit();

        $var = false;
        if (strcasecmp(preg_replace('/\s+/', '', $this->task->getSolution()), preg_replace('/\s+/', '', $this->task->getUserInput())) == 0) {
            $var = true;
        } else {
            switch ($this->task->getTaskType()) {
                case "SELECT":
                    $var = $this->validateSelect();
                    break;
                case "UPDATE":
                    $var = $this->validateUpdate();
                    break;
                case "INSERT":
                    $var = $this->validateUpdate();
                    break;
                case "DELETE":
                    $var = $this->validateUpdate();
                    break;
                case "CREATE":
                    $var = $this->validateCreate();
                    break;
                case "DROP":
                    $var = $this->validateDrop();
                    break;
            }
        }
        $this->masterConnection->rollbackSavePoint();
        $this->slaveConnection->rollbackSavePoint();
        $this->checkConnection->rollbackSavePoint();

        return $var;
    }

    /**
     * Validates queries of type Drop
     *
     * @return bool
     */
    private function validateDrop()
    {
        $this->masterConnection->executeNoCommit();
        $this->slaveConnection->executeNoCommit();

        //Compares all tables headers of user and master
        foreach ($this->task->getTableNames() as $masterTable) {
            $this->masterConnection->setQuery("SELECT * FROM {$masterTable}");
            $this->masterConnection->executeNoCommit();
            $content1 = $this->masterConnection->getHeader(true);

            $userTable = str_replace(ADMIN_TAB_PREFIX, $this->tablePrefix, $masterTable);
            $this->slaveConnection->setQuery("SELECT * FROM {$userTable}");
            $this->slaveConnection->executeNoCommit();
            $content2 = $this->slaveConnection->getHeader(true);

            if (strcmp($content1, $content2) === 0) {
                $this->setMistake("The correct table was not dropped");
                return false;
            }
        }
        return true;
    }

    /**
     * Validates queries of type Select
     *
     * @return bool
     */
    private function validateSelect()
    {
        if ($this->validateDimensions()) {
            // master without ORDER BY
            if (stripos($this->masterConnection->sqlquery, "ORDER BY") === false) {
                if ($this->validateHeader()){
                    return $this->validateContentiO();
                }
            } else {
                if ($this->validateHeader()){
                    return $this->validateContent();
                }
            }
        } else {
            return false;
        }
        return false;
    }

    /**
     * Validates queries of type Update
     *
     * @return bool
     */
    private function validateUpdate()
    {

        $this->masterConnection->executeNoCommit();
        $this->slaveConnection->executeNoCommit();

        $return = true;

        //Compares all tables of User and Master
        foreach ($this->task->getTableNames() as $masterTable) {
            $this->masterConnection->setQuery("SELECT * FROM {$masterTable}");
            $this->masterConnection->executeNoCommit();
            $content1 = $this->masterConnection->getContent();

            $userTable = str_replace(ADMIN_TAB_PREFIX, $this->tablePrefix, $masterTable);
            $this->slaveConnection->setQuery("SELECT * FROM {$userTable}");
            $this->slaveConnection->executeNoCommit();
            $content2 = $this->slaveConnection->getContent();

            asort($content1);
            asort($content2);
            $a =serialize($content1);
            $b =serialize($content2);

            if($a != $b){
                $this->setMistake("Wrong content");
                $return = false;
                break;
            }
        }
        return $return;
    }

    /**
     * Validates queries of type Create
     *
     * @return bool
     */
    private function validateCreate()
    {
        $table = $this->task->getTableNames();
        $masterTable = $table[0];
        $prefix = "CREATE_";


        $userTable = $this->task->getTableNameFromCreate($this->slaveConnection->sqlquery);
        $prePrefix = $prefix.substr($userTable,0,strpos($userTable,"_")+1);
        $userCreateTable = $prefix.$userTable;

        // create table from user input
        $this->slaveConnection->setQuery(str_replace($userTable,$userCreateTable,strtoupper($this->slaveConnection->sqlquery)));
        $this->slaveConnection->executeNoCommit();

        // check schema:
        $this->slaveConnection->setQuery($this->task->getTableSchema($prePrefix,$userCreateTable));
        $this->slaveConnection->executeNoCommit();

        $this->masterConnection->setQuery($this->task->getTableSchema(ADMIN_TAB_PREFIX,$masterTable));
        $this->masterConnection->executeNoCommit();

        if($this->slaveConnection->printTable() != $this->masterConnection->printTable()){
            $this->setMistake("Wrong schema");
            return false;
        }

        // check primary
        $this->slaveConnection->setQuery($this->task->getTablePrimary($userCreateTable));
        $this->slaveConnection->executeNoCommit();

        $this->masterConnection->setQuery($this->task->getTablePrimary($masterTable));
        $this->masterConnection->executeNoCommit();

        if($this->slaveConnection->printTable() != $this->masterConnection->printTable()){
            $this->setMistake("Wrong primary key");
            return false;
        }

        // check index
        $this->slaveConnection->setQuery($this->task->getTableIndex($userCreateTable));
        $this->slaveConnection->executeNoCommit();

        $this->masterConnection->setQuery($this->task->getTableIndex($masterTable));
        $this->masterConnection->executeNoCommit();

        if($this->slaveConnection->printTable() != $this->masterConnection->printTable()){
            $this->setMistake("Wrong indices");
            return false;
        }

        return true;
    }

    /**
     * Checks if the header of both queries equal
     *
     * @return bool
     */
    private function validateHeader()
    {
        $this->checkConnection->setQuery($this->masterConnection->sqlquery);
        $this->checkConnection->executeNoCommit();
        $header1 = $this->checkConnection->getHeader(true);

        $this->checkConnection->setQuery($this->slaveConnection->sqlquery);
        $this->checkConnection->executeNoCommit();
        $header2 = $this->checkConnection->getHeader(true);
        if (strcmp($header1, $header2) === 0){
            return true;
        }
        $this->setMistake("incorrect Solution - names of header incorrect");
        return false;

    }

    /**
     * Checks if the content of the queries matches
     *
     * @return bool
     */
    private function validateContent()
    {
        $this->checkConnection->setQuery($this->masterConnection->sqlquery);
        $this->checkConnection->executeNoCommit();
        $content1 = $this->checkConnection->getContent(true);

        $this->checkConnection->setQuery($this->slaveConnection->sqlquery);
        $this->checkConnection->executeNoCommit();
        $content2 = $this->checkConnection->getContent(true);

        if (strcmp($content1, $content2) === 0){
            return true;
        }
        $this->setMistake("incorrect Solution - content differs");
        return false;
    }

    /**
     * Checks if the content of the queries matches (ignoring order of elements)
     *
     * @return bool
     */
    private function validateContentiO()
    {
        $this->checkConnection->setQuery($this->masterConnection->sqlquery);
        $this->checkConnection->executeNoCommit();
        $content1 = $this->checkConnection->getContent();

        $this->checkConnection->setQuery($this->slaveConnection->sqlquery);
        $this->checkConnection->executeNoCommit();
        $content2 = $this->checkConnection->getContent();

        if ($this->array_diff_reku($content1, $content2) && $this->array_diff_reku($content2, $content1)) {
            return true;
        }

        $this->setMistake("incorrect Solution - content differs");
        return false;
    }

    /**
     *Checks if $array1 contains all elements $array2 contains (ignoring order of elements)
     *
     * @param array $array1
     * @param array $array2
     * @return bool True if $array1 = $array2 (ignoring order)
     */
    private function array_diff_reku($array1, $array2)
    {
        array_walk($array1, function (&$arr) {
            $arr = serialize($arr);
        });
        array_walk($array2, function (&$arr) {
            $arr = serialize($arr);
        });
        $difference = array_diff($array1, $array2);

        return empty($difference);
    }

    /**
     * Checks if the dimensions of both queries equal
     *
     * @return bool
     */
    private function validateDimensions()
    {
        // check same number of columns
        if ($this->masterConnection->numColumns() == $this->slaveConnection->numColumns()) {
            // check same number of rows
            if ($this->masterConnection->numRows(false) == $this->slaveConnection->numRows(false)) {
                return true;
            } else {
                $this->setMistake("incorrect Solution - number of rows");
            }
        } else {
            // check same number of rows
            if ($this->masterConnection->numRows(false) == $this->slaveConnection->numRows(false)) {
                $this->setMistake("incorrect Solution  - number of columns");
            } else {
                $this->setMistake("incorrect Solution - number of rows and collumns");
            }
        }
        return false;
    }
}