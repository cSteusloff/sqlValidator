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
     * @var oracleConnection
     */
    private $masterConnection = null;

    /**
     * @var oracleConnection
     */
    private $slaveConnection = null;

    /**
     * @var oracleConnection
     */
    private $checkConnection = null;

    /**
     * @var taskHelper - Task
     */
    private $task = null;

    /**
     * @param null oracleConnection
     */
    public function setSqlConnection(oracleConnection $masterConnection, oracleConnection $slaveConnection, taskHelper $task)
    {
        $this->masterConnection = $masterConnection;
        $this->slaveConnection = $slaveConnection;
        $this->checkConnection = clone $slaveConnection;
        $this->task = $task;
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
     * @param sqlConnection $sqlConnection
     */
    function __construct(sqlConnection $masterConnection, sqlConnection $slaveConnection, taskHelper $task)
    {
        $this->setSqlConnection($masterConnection, $slaveConnection, $task);
    }

    public function validate()
    {
        // nur fuer insert/update/delete wichtig
        // Alter/Drop auch

        // Master darf sich nicht ändern und User bei validate auch nicht!
        // Das User sich ändert muss expliziet aufgerufen werden.

        // Mit Tricks ist es möglich, INSERT/UPDATE/DELETE/DROP auf mehrere Tabellen anzuwenden,
        // daher erlaubt die Aufgabenertellung auch mehrere Tabellen.
        // Das heißt die Ausgabe danach sollte einfach mit allen "notwendigen" Tabellen durchgeführt werden
        // Ergebnis vergleich --> SELECT * --> FERTIG
        $this->masterConnection->setSavePoint();
        $this->slaveConnection->setSavePoint();
        $this->checkConnection->setSavePoint();

        // TODO: notwendig?
        $this->masterConnection->executeNoCommit();
        $this->slaveConnection->executeNoCommit();

        //1. Check if query is equal ignoring case and spaces
        //echo("<pre>");
        //var_dump($this->task->getTaskType());
        //var_dump(strcasecmp(preg_replace( '/\s+/', '',$this->masterConnection->origsqlquery), preg_replace( '/\s+/', '',$this->slaveConnection->origsqlquery)));
        //die();
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
                    break;

            }

            // validate only select
            //$this->masterConnection->getStatementType();
            //$this->task->getTaskType();


        }
        $this->masterConnection->rollbackSavePoint();
        $this->slaveConnection->rollbackSavePoint();
        $this->checkConnection->rollbackSavePoint();

        return $var;
    }

    private function validateSelect()
    {
        if ($this->validateDimensions()) {
            // master without ORDER BY
            if (stripos($this->masterConnection->sqlquery, "ORDER BY") === false) {
                if ($this->validateHeader())
                    return $this->validateContentiO();

                /*
                // TODO: remove ORDER BY from slaveConnection->sqlquery
                // if is query output the same - MINUS return empty content
                $this->checkConnection->setQuery($this->masterConnection->sqlquery . " MINUS " . $this->slaveConnection->sqlquery);
                $this->checkConnection->executeNoCommit();
                // must be zero
                $emptyContentOneDirection = $this->checkConnection->numRows(false);
                // String as HTML-Table with names of header
                $this->checkConnection->setQuery($this->slaveConnection->sqlquery . " MINUS " . $this->masterConnection->sqlquery);
                $this->checkConnection->executeNoCommit();
                // must be zero
                $emptyContentOtherDirection = $this->checkConnection->numRows(false);
                // String as HTML-Table with names of header
                if ($emptyContentOneDirection == 0 && $emptyContentOtherDirection == 0) {
                    return $this->validateHeader();
                } else {
                    $this->setMistake("incorrect Solution - content differs");
                    return false;
                }
                */

            } else {
                if ($this->validateHeader())
                    return $this->validateContent();
            }
        } else {
            return false;
        }

        return false;
    }

    private function validateUpdate()
    {
        if ($this->validateDimensions()) {

            // TODO: hier ist der FEHLER!!!!
            // du hättest das foreach um die beiden machen müssen!


            // TODO: only for presentation - fix it!!!
            $tables = "";
            $first = true;
            foreach($this->task->getTableNames() as $table){
                if ($first){
                    $tables .= $table;
                    $first = false;
                }
                else{
                    $tables .= ", ".$table;
                }
            }

            $this->checkConnection->setQuery($this->masterConnection->sqlquery);
            $this->checkConnection->executeNoCommit();
            $this->checkConnection->setQuery("SELECT * FROM {$tables}");
            $this->checkConnection->executeNoCommit();
            $content1 = $this->checkConnection->getContent();

            // TODO: only for presentation - fix it!!!
            $tables = "";
            $first = true;
            foreach($this->task->getTableNames() as $table){
                $userTab = str_replace(ADMIN_TAB_PREFIX,"user2_",$table);
                if ($first){
                    $tables .= $userTab;
                    $first = false;
                }
                else{
                    $tables .= ", ".$userTab;
                }
            }



            $this->checkConnection->setQuery($this->slaveConnection->sqlquery);
            $this->checkConnection->executeNoCommit();
            $this->checkConnection->setQuery("SELECT * FROM {$tables}");
            $this->checkConnection->executeNoCommit();
            $content2 = $this->checkConnection->getContent();


            //if (sort($content1) && sort($content2)) {
            //    if ($content1 == $content2) {
            //       return true;
            //    }
            // } else

//            var_dump($content1);
//            var_dump($content2);
//            var_dump($this->array_diff2($content1, $content2));
//            var_dump($this->array_diff2($content2, $content1));
//            var_dump(!$this->array_diff2($content1, $content2) && !$this->array_diff2($content2, $content1));
//            die();

            if (!$this->array_diff2($content1, $content2) && !$this->array_diff2($content2, $content1)) {
                return true;
            } else {
                $this->setMistake("incorrect Solution - content differs");
                return false;
            }
        }
        return false;
    }

    private function validateCreate()
    {
        if ($this->validateHeader()) {
            return $this->validateContentiO();
            //TODO: Check Table parameters!!
        }
        return false;
    }


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

    //Ignore Order
    private function validateContentiO()
    {
        $this->checkConnection->setQuery($this->masterConnection->sqlquery);
        $this->checkConnection->executeNoCommit();
        $content1 = $this->checkConnection->getContent();

        $this->checkConnection->setQuery($this->slaveConnection->sqlquery);
        $this->checkConnection->executeNoCommit();
        $content2 = $this->checkConnection->getContent();


        //if (sort($content1) && sort($content2)) {
        //    if ($content1 == $content2) {
        //       return true;
        //    }
        // } else
/*
        var_dump($content1);
        var_dump($content2);
        var_dump($this->array_diff2($content1, $content2));
        var_dump($this->array_diff2($content2, $content1));
        var_dump(!$this->array_diff2($content1, $content2) && !$this->array_diff2($content2, $content1));
        die();
*/

        if (!$this->array_diff2($content1, $content2) && !$this->array_diff2($content2, $content1)) {
            return true;
        }

        $this->setMistake("incorrect Solution - content differs");
        return false;
    }

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