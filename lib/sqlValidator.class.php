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
        // check same number of columns
        if($this->masterConnection->numColumns() == $this->slaveConnection->numColumns()){
            // check same number of rows
            if($this->masterConnection->numRows() == $this->slaveConnection->numRows()){
                // master without ORDER BY
                if(strpos($this->masterConnection->sqlquery,"ORDER BY") === false){
                    // TODO: remove ORDER BY from user_con->sqlquery
                    // if is query output the same - MINUS return empty content
                    $this->checkConnection->setQuery($this->masterConnection->sqlquery." MINUS ".$this->slaveConnection->sqlquery);
                    $this->checkConnection->execute();
                    // must be zero
                    $emptyContentOneDirection = $this->checkConnection->numRows();
                    // String as HTML-Table with names of header
                    $headerOneDirection = $this->checkConnection->printTable();
                    $this->checkConnection->setQuery($this->slaveConnection->sqlquery." MINUS ".$this->masterConnection->sqlquery);
                    $this->checkConnection->execute();
                    // must be zero
                    $emptyContentOtherDirection = $this->checkConnection->numRows();
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