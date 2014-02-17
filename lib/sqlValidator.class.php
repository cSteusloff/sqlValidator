<?php
/**
 * Project: ss
 * User: Christian Steusloff
 * Date: 19.12.13
 * Time: 14:36
 */

class sqlValidator {

    /**
     * @var masterConnection
     */
    private $masterConnection = null;

    /**
     * @var slaveConnection
     */
    private $slaveConnection = null;

    /**
     * @var checkConnection
     */
    private $checkConnection = null;

    /**
     * @param null $sqlConnection
     */
    public function setSqlConnection(sqlConnection $masterConnection, sqlConnection $slaveConnection)
    {
        $this->masterConnection = $masterConnection;
        $this->slaveConnection = $slaveConnection;
        $this->checkConnection = clone $slaveConnection;
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
    function __construct(sqlConnection $masterConnection, sqlConnection $slaveConnection)
    {
        $this->setSqlConnection($masterConnection,$slaveConnection);
    }

    public function validate(){

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

        // validate at moment only select
        $var = $this->_validate();

        $this->masterConnection->rollbackSavePoint();
        $this->slaveConnection->rollbackSavePoint();
        $this->checkConnection->rollbackSavePoint();

        return $var;
    }

    private function _validate(){
        // check same number of columns
        if($this->masterConnection->numColumns() == $this->slaveConnection->numColumns()){
            // check same number of rows
            if($this->masterConnection->numRows(false) == $this->slaveConnection->numRows(false)){
                // master without ORDER BY
                if(strpos($this->masterConnection->sqlquery,"ORDER BY") === false){
                    // TODO: remove ORDER BY from user_con->sqlquery
                    // if is query output the same - MINUS return empty content
                    $this->checkConnection->setQuery($this->masterConnection->sqlquery." MINUS ".$this->slaveConnection->sqlquery);
                    $this->checkConnection->executeNoCommit();
                    // must be zero
                    $emptyContentOneDirection = $this->checkConnection->numRows(false);
                    // String as HTML-Table with names of header
                    $headerOneDirection = $this->checkConnection->printTable();
                    $this->checkConnection->setQuery($this->slaveConnection->sqlquery." MINUS ".$this->masterConnection->sqlquery);
                    $this->checkConnection->executeNoCommit();
                    // must be zero
                    $emptyContentOtherDirection = $this->checkConnection->numRows(false);
                    // String as HTML-Table with names of header
                    $headerOtherDirection = $this->checkConnection->printTable();
                    if($emptyContentOneDirection == 0 && $emptyContentOtherDirection == 0){
                        if(!strcmp($headerOneDirection,$headerOtherDirection) == 0){
                            $this->setMistake("incorrect Solution - names of header incorrect");
                        }
                    } else {
                        $this->setMistake("incorrect Solution - different content");
                    }
                }   else {
                    // TODO: exact Match
                }
            } else {
                $this->setMistake("incorrect Solution - number of data");
            }
        } else {
            $this->setMistake("incorrect Solution  - number of columns");
        }

        return is_null($this->mistake);
    }
}