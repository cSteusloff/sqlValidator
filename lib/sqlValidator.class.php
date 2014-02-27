<?php

/**
 * Project: ss
 * User: Christian Steusloff
 * Date: 19.12.13
 * Time: 14:36
 */
class sqlValidator
{
    /**
     * @var sqlConnection
     */
    private $masterConnection = null;

    /**
     * @var sqlConnection
     */
    private $slaveConnection = null;

    /**
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
     * @internal param \sqlConnection $null
     */
    public function setSqlConnection(sqlConnection $masterConnection, sqlConnection $slaveConnection, taskHelper $task)
    {
        $this->masterConnection = $masterConnection;
        $this->slaveConnection = $slaveConnection;
        $this->checkConnection = clone $slaveConnection;
        $this->task = $task;
        $this->tablePrefix = "user2_";
    }

    /**
     * @var string - mistake message
     */
    private $mistake = null;

    /**
     * @param string $mistake
     */
    public function setMistake($mistake)
    {
        $this->mistake = $mistake;
    }

    /**
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
     * @internal param \sqlConnection $sqlConnection
     */
    function __construct(sqlConnection $masterConnection, sqlConnection $slaveConnection, taskHelper $task)
    {
        $this->setSqlConnection($masterConnection, $slaveConnection, $task);
    }

    /**
     * @return bool if answer is correct
     */
    public function validate()
    {
        $this->masterConnection->setSavePoint();
        $this->slaveConnection->setSavePoint();
        $this->checkConnection->setSavePoint();

        // TODO: notwendig?
        $this->masterConnection->executeNoCommit();
        $this->slaveConnection->executeNoCommit();

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
     * @return bool
     */
    private function validateDrop()
    {
        //TODO: check if Tables still exist
        $this->task->getTableNames();
        return false;
    }

    /**
     * @return bool
     */
    private function validateSelect()
    {
        if ($this->validateDimensions()) {
            // master without ORDER BY
            if (stripos($this->masterConnection->sqlquery, "ORDER BY") === false) {
                if ($this->validateHeader())
                    return $this->validateContentiO();
            } else {
                if ($this->validateHeader())
                    return $this->validateContent();
            }
        } else {
            return false;
        }
        return false;
    }

    /**
     * @return bool
     */
    private function validateUpdate()
    {
        if ($this->validateDimensions()) {

            $this->masterConnection->executeNoCommit();
            $this->slaveConnection->executeNoCommit();

            $TrueArray = array();

            foreach ($this->task->getTableNames() as $masterTable) {
                $this->masterConnection->setQuery("SELECT * FROM {$masterTable}");
                $this->masterConnection->executeNoCommit();
                $content1 = $this->masterConnection->getContent();

                $userTable = str_replace(ADMIN_TAB_PREFIX, $this->tablePrefix, $masterTable);
                $this->slaveConnection->setQuery("SELECT * FROM {$userTable}");
                $this->slaveConnection->executeNoCommit();
                $content2 = $this->slaveConnection->getContent();

                if (!$this->array_diff2($content1, $content2) && !$this->array_diff2($content2, $content1)) {
                    $TrueArray[] = true;
                } else {
                    $this->setMistake("Wrong content");
                    return false;
                }
            }

            if (in_array(false, $TrueArray)) {
                $this->setMistake("Wrong content");
                return false;
            } else {
                return true;
            }
        }
        return false;
    }

    /**
     * @return bool
     */
    private function validateCreate()
    {
        if ($this->validateHeader()) {
            return $this->validateContentiO();
            //TODO: Check Table parameters!!
        }
        return false;
    }

    /**
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
        if (strcmp($header1, $header2) === 0)
            return true;
        $this->setMistake("incorrect Solution - names of header incorrect");
        return false;

    }

    /**
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


        if (strcmp($content1, $content2) === 0)
            return true;
        $this->setMistake("incorrect Solution - content differs");
        return false;
    }

    /**
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

        if (!$this->array_diff2($content1, $content2) && !$this->array_diff2($content2, $content1)) {
            return true;
        }

        $this->setMistake("incorrect Solution - content differs");
        return false;
    }

    /**
     * @param $array1
     * @param $array2
     * @return array
     */
    private function array_diff2($array1, $array2)
    {
        array_walk($array1, function (&$arr) {
            $arr = serialize($arr);
        });
        array_walk($array2, function (&$arr) {
            $arr = serialize($arr);
        });
        return array_diff($array1, $array2);
    }

    /**
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