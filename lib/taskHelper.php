<?php

/**
 * @package    SqlValidator
 * @author     Christian Steusloff
 * @author     Jens Wiemann
 */

/**
 * Class taskHelper
 */
class taskHelper
{
    /**
     * The permission on tables
     *
     * @var int - permission on tables
     */
    private $permission;

    /**
     * @var sqlConnection
     */
    private $dbConnection;

    /**
     * @param \sqlConnection $dbConnection
     */
    public function setDbConnection($dbConnection)
    {
        $this->dbConnection = $dbConnection;
    }

    /**
     * @var string error message
     */
    private $error;

    /**
     * @param string $error
     */
    public function setError($error)
    {
        $this->error = $error;
    }

    /**
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * Select = 1
     * Insert/Update/Delete = 2
     * Create = 4
     * Drop = 8
     *
     * @param int[] $permission
     * @return int
     */
    public function setPermission($permission)
    {
        if (is_array($permission)) {
            $this->permission = array_sum($permission);
        } else {
            $this->permission = $permission;
        }

        return $this->getPermission();
    }

    /**
     * @return int
     */
    public function getPermission()
    {
        return $this->permission;
    }

    /**
     * @var string - task subject
     */
    private $topic;

    /**
     * @var string - task description
     */
    private $text;

    /**
     * @var string - sql query solution
     */
    private $solution;

    /**
     * @var string - sql query by user
     */
    private $userInput;

    /**
     * @var string SELECT, UPDATE, etc.
     */
    private $taskType;

    /**
     * set task type from query type
     */
    public function setTaskType()
    {
        $this->dbConnection->setQuery($this->getSolution());
        $this->taskType = $this->dbConnection->getStatementType();
    }

    /**
     * @return string
     */
    public function getTaskType()
    {
        return $this->taskType;
    }

    /**
     * @param string $user_input
     */
    public function setUserInput($user_input)
    {
        $this->userInput = $user_input;
    }

    /**
     * @return string
     */
    public function getUserInput()
    {
        return $this->userInput;
    }

    /**
     * @var string array - necessary tables
     */
    private $tables;

    /**
     * @var int[] id's from necessary tables
     */

    private $table_ids;

    /**
     * @var string[] names's from necessary tables
     */
    private $table_names;

    /**
     * @var string
     */
    private $tableHeader;

    /**
     * @param string $tableData
     */
    public function setTableContent($tableData)
    {
        $this->tableData = $tableData;
    }

    /**
     * @return string
     */
    public function getTableData()
    {
        return $this->tableData;
    }

    /**
     * @param string $tableHeader
     */
    public function setTableHeader($tableHeader)
    {
        $this->tableHeader = $tableHeader;
    }

    /**
     * @return string
     */
    public function getTableHeader()
    {
        return $this->tableHeader;
    }

    /**
     * @var string
     */
    private $tableData;

    /**
     * user / student who works there
     *
     * @var int - User ID
     */
    private $user_id;

    /**
     * @param int $user_id
     */
    public function setUserId($user_id)
    {
        $this->user_id = $user_id;
    }

    /**
     * @return int
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * @param int[] $table_ids
     */
    public function setTableIds($table_ids)
    {
        $this->table_ids = $table_ids;
    }

    /**
     * @return int[]
     */
    public function getTableIds()
    {
        return $this->table_ids;
    }

    /**
     * @param string[] $table_names
     */
    public function setTableNames($table_names)
    {
        $this->table_names = $table_names;
    }

    /**
     * @return string[]
     */
    public function getTableNames()
    {
        return $this->table_names;
    }

    /**
     * @var int
     */
    private $task_id;

    /**
     * @param int $task_id
     */
    public function setTaskId($task_id)
    {
        $this->task_id = $task_id;
    }

    /**
     * @return int
     */
    public function getTaskId()
    {
        return $this->task_id;
    }

    /**
     * @param string $solution
     * @return string
     */
    public function setSolution($solution)
    {
        return $this->solution = $solution;
    }

    /**
     * @return string
     */
    public function getSolution()
    {
        return $this->solution;
    }

    /**
     * @param string $tables
     * @return string
     */
    public function setTables($tables)
    {
        return $this->tables = $tables;
    }

    /**
     * @return string
     */
    public function getTables()
    {
        return $this->tables;
    }

    /**
     * @param string $text
     * @return string
     */
    public function setText($text)
    {
        return $this->text = $text;
    }

    /**
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param string $topic
     * @return string
     */
    public function setTopic($topic)
    {
        return $this->topic = $topic;
    }

    /**
     * @return string
     */
    public function getTopic()
    {
        return $this->topic;
    }

    /**
     * @param string $topic - subject from task
     * @param string $text - task description
     * @param $tables
     * @param $permission
     * @param string $solution - SQL query
     * @param $tableHeader
     * @param $tableContent
     * @return mixed
     * @internal param array $string $tables - names of usable tables
     * @internal param array $int $permission - access rights of table
     */
    public function createTask($topic, $text, $tables, $permission, $solution, $tableHeader, $tableContent)
    {
        // set object-attributes
        $this->setTopic($topic);
        $this->setText($text);
        $this->setPermission($permission);
        $this->setSolution($solution);
        $this->setTableHeader($tableHeader);
        $this->setTableContent($tableContent);
        $this->setTaskType();

        // create Task in SYS_TASK
        $this->dbConnection->setQuery("INSERT INTO SYS_TASK (Taskname,tasktext,permission,solution,tableheader,tablecontent)
                    VALUES ('" . $this->getTopic() . "',
                            '" . $this->getText() . "',
                            '" . $this->getPermission() . "',
                            '" . $this->getSolution() . "',
                            '" . $tableHeader . "',
                            '" . $tableContent . "')");
        $this->dbConnection->execute();
        $errors = $this->dbConnection->getErrorText();

        // get task-id from last new task (this current create task)
        $this->dbConnection->setQuery("SELECT MAX(ID) FROM SYS_TASK WHERE taskname = '" . $this->getTopic() . "'");
        $this->dbConnection->execute();
        $this->dbConnection->Fetch(false);
        $task_id = $this->dbConnection->row[0];

        $this->setTaskId($task_id);
        // add necessary tables and get IDs
        $table_ids = $this->addDatabaseTables($tables);

        // create connection between SYS_TASK and SYS_TABLE
        $this->setNeedTables($task_id, $table_ids);

        // set object-attribute table-name and table-id
        $this->setDatabaseTables();

        return $errors;
    }

    /**
     * @param sqlConnection $sqlConnection
     */
    public function __construct($sqlConnection)
    {
        $this->setDbConnection($sqlConnection);
    }

    /**
     * @param string $query - input by user
     * @return string - without ; and '
     */
    public function clearQuery($query)
    {
        $newQuery = $query;
        if (strpos($newQuery, ";") !== false) {
            $piece = explode(";", $newQuery);

            // var_dump($piece);
            $newQuery = $piece[0];
        }
        if (strpos($newQuery, '"') !== false) {
            $newQuery = str_replace('"', "'", $newQuery);
        }

        return $newQuery;
    }

    /**
     * @param $lastQuery
     */
    public function saveLastUserQuery($lastQuery)
    {
        $this->setUserInput($lastQuery);
        // mask ' with double '
        $lastQuery = str_replace("'", "''", $lastQuery);
        $this->dbConnection->setQuery("MERGE INTO SYS_USER_TASK U
                                       USING (
                                            SELECT " . $this->getUserId() . " as USER_ID,
                                                   " . $this->getTaskId() . " as TASK_ID,
                                                   '" . $lastQuery . "' as QUERY_LAST,
                                                   '' as QUERY_CORRECT
                                            FROM DUAL
                                            ) N
                                       ON (U.USER_ID = N.USER_ID AND U.TASK_ID = N.TASK_ID)
                                       WHEN MATCHED THEN
                                            UPDATE SET U.QUERY_LAST = N.QUERY_LAST
                                       WHEN NOT MATCHED THEN
                                            INSERT(USER_ID,TASK_ID,QUERY_LAST,QUERY_CORRECT)
                                            VALUES(N.USER_ID,N.TASK_ID,N.QUERY_LAST,N.QUERY_CORRECT)");
        $this->dbConnection->execute();
    }

    /**
     * @param string $correctQuery - save solution by user
     */
    public function saveCorrectUserQuery($correctQuery)
    {
        $this->dbConnection->setQuery("MERGE INTO SYS_USER_TASK U
                                       USING (
                                            SELECT " . $this->getUserId() . " as USER_ID,
                                                   " . $this->getTaskId() . " as TASK_ID,
                                                   '' as QUERY_LAST,
                                                   '" . $correctQuery . "' as QUERY_CORRECT
                                            FROM DUAL
                                            ) N
                                       ON (U.USER_ID = N.USER_ID AND U.TASK_ID = N.TASK_ID)
                                       WHEN MATCHED THEN
                                            UPDATE SET U.QUERY_CORRECT = N.QUERY_CORRECT
                                       WHEN NOT MATCHED THEN
                                            INSERT(USER_ID,TASK_ID,QUERY_LAST,QUERY_CORRECT)
                                            VALUES(N.USER_ID,N.TASK_ID,N.QUERY_LAST,N.QUERY_CORRECT)");
        $this->dbConnection->execute();
    }

    /**
     * @return string
     */
    public function getLastUserQuery()
    {
        $this->dbConnection->setQuery("SELECT QUERY_LAST
                                       FROM SYS_USER_TASK
                                       WHERE USER_ID = " . $this->getUserId() . "
                                       AND TASK_ID = " . $this->getTaskId());
        $this->dbConnection->execute();
        $this->dbConnection->Fetch();
        $lastQuery = $this->dbConnection->row["QUERY_LAST"];
        $this->setUserInput($lastQuery);
        return $lastQuery;
    }

    /**
     * @return string
     */
    public function getCorrectUserQuery()
    {
        $this->dbConnection->setQuery("SELECT QUERY_CORRECT
                                       FROM SYS_USER_TASK
                                       WHERE USER_ID = " . $this->getUserId() . "
                                       AND TASK_ID = " . $this->getTaskId());
        $this->dbConnection->execute();
        $this->dbConnection->Fetch();
        return $this->dbConnection->row["QUERY_CORRECT"];
    }

    /**
     * @param int $task_id
     * @param int $user_id
     */
    public function loadTask($task_id, $user_id)
    {
        $this->dbConnection->setQuery("SELECT taskname,
                           tasktext,
                           permission,
                           solution,
                           tableheader,
                           tablecontent
                    FROM SYS_TASK WHERE ID = ".$task_id);
        $this->dbConnection->execute();
        $this->dbConnection->Fetch();
        $this->setTopic($this->dbConnection->row['TASKNAME']);
        $this->setText($this->dbConnection->row['TASKTEXT']);
        $this->setPermission($this->dbConnection->row['PERMISSION']);
        $this->setSolution($this->dbConnection->row['SOLUTION']);
        $this->setTableHeader($this->dbConnection->row['TABLEHEADER']);
        $this->setTableContent($this->dbConnection->row['TABLECONTENT']);
        $this->setTaskId($task_id);
        $this->setUserId($user_id);
        $this->setDatabaseTables();
        $this->setTaskType();
    }

    /*
     * drop and create user tables for task
     */
    public function resetTask()
    {
        foreach ($this->getTableNames() as $table) {
            $table = str_replace(ADMIN_TAB_PREFIX, "", $table);
            $userTable = "user" . $this->getUserId() . "_" . $table;
            $query_drop = "DROP TABLE " . $userTable;
            $query_create = "CREATE TABLE " . $userTable . " AS (SELECT * FROM MASTER_" . $table . ")";
            $this->dbConnection->setQuery($query_drop);
            // Oracle don't have drop if exist, don't show error
            @$this->dbConnection->execute();
            $this->dbConnection->setQuery($query_create);
            $this->dbConnection->execute();

        }

        // security mechanism
        foreach ($this->getTableNames() as $table) {
            $tmpTable = str_replace(ADMIN_TAB_PREFIX, "", $table);
            $query_drop = "DROP TABLE " . $tmpTable;
            $query_create = "CREATE TABLE " . $tmpTable . " AS (SELECT * FROM " . $table . ")";
            $this->dbConnection->setQuery($query_drop);
            // Oracle don't have drop if exist, don't show error
            @$this->dbConnection->execute();
            $this->dbConnection->setQuery($query_create);
            $this->dbConnection->execute();

        }
    }

    /**
     * @return bool
     */
    public function getPermissionSelect()
    {
        $select_array = array(1, 3, 5, 7, 9, 11, 13, 15);
        return in_array($this->getPermission(), $select_array);
    }

    /**
     * @return bool
     */
    public function getPermissionModify()
    {
        $mod_array = array(2, 3, 6, 7, 10, 11, 14, 15);
        return in_array($this->getPermission(), $mod_array);
    }

    /**
     * @return bool
     */
    public function getPermissionCreate()
    {
        $create_array = array(4, 5, 6, 7, 12, 13, 14, 15);
        return in_array($this->getPermission(), $create_array);
    }

    /**
     * @return bool
     */
    public function getPermissionDrop()
    {
        $drop_array = array(8, 9, 10, 11, 12, 13, 14, 15);
        return in_array($this->getPermission(), $drop_array);
    }

    /**
     * Tablenames are unique, insert via merge if it's in this table
     *
     * @param string array - names of tables
     * @return int array - table IDs
     */
    private function addDatabaseTables($table_array)
    {
        $insert = null;
        $insert_query = "MERGE INTO SYS_TABLES tab USING (";
        foreach ($table_array as $table) {
            $insert[] = "SELECT '" . $table . "' as name FROM DUAL";
        }
        $insert_query .= implode(" UNION ALL ", $insert);
        $insert_query .= ") src ON (src.name = tab.name) WHEN NOT MATCHED THEN INSERT(name) VALUES (src.name)";

        $this->dbConnection->setQuery($insert_query);
        $this->dbConnection->execute();

        return $this->getDatabaseTablesByTableNames($table_array);
    }

    /**
     * @param string array $table_names
     * @return int array - Table IDs
     */
    private function getDatabaseTablesByTableNames($table_names)
    {
        $search = null;
        foreach ($table_names as $table) {
            $search[] = "name = '" . $table . "'";
        }

        $id_result = array();
        $this->dbConnection->setQuery("SELECT ID FROM SYS_TABLES WHERE " . implode(" OR ", $search));
        $this->dbConnection->execute();
        while ($this->dbConnection->Fetch()) {
            $id_result[] = $this->dbConnection->row['ID'];
        }
        return $id_result;
    }

    /**
     * set tables from necessary tables (SYS_NEEDTABLES)
     */
    private function setDatabaseTables()
    {
        $tablename_array = array();
        $tableid_array = array();
        $this->dbConnection->setQuery("SELECT n.table_id, t.name
                    FROM SYS_NEEDTABLES n,
                    SYS_TABLES t
                    WHERE n.table_id = t.ID
                    AND task_id = " . $this->getTaskId());
        $this->dbConnection->execute();
        while ($this->dbConnection->Fetch()) {
            $tablename_array[] = $this->dbConnection->row['NAME'];
            $tableid_array[] = $this->dbConnection->row['TABLE_ID'];
        }

        $this->setTableNames($tablename_array);
        $this->setTableIds($tableid_array);
    }

    /**
     * @param int $task_id - task ID
     * @param int[] $table_ids - necessary tables for task
     */
    private function setNeedTables($task_id, $table_ids)
    {
        $insert_query = "INSERT ALL ";
        foreach ($table_ids as $id) {
            $insert_query .= "into SYS_NEEDTABLES(task_id,table_id) VALUES ('" . $task_id . "','" . $id . "') ";
        }
        $insert_query .= "SELECT * FROM DUAL";

        $this->dbConnection->setQuery($insert_query);
        $this->dbConnection->execute();
    }

    /**
     * @param string $classname
     * @param bool $commit
     * @return string
     */
    public function printTable($classname = null, $commit = true)
    {
        if (is_null($classname)) {
            $classname = "defaultTableClassName";
        }

        $tablestr = "";
        foreach ($this->getTableNames() as $table) {
            $this->dbConnection->setQuery("SELECT * FROM {$table}");
            if ($commit) {
                $this->dbConnection->execute();
            } else {
                $this->dbConnection->executeNoCommit();
            }
            $tablestr .= $this->dbConnection->printTable($classname, substr(strtoupper($table), strlen(ADMIN_TAB_PREFIX)));
        }
        return $tablestr;
    }

    /**
     * from error position in query to correct position in formatted query by line (row) and position in line (column)
     *
     * @param string $formattedQueryInput
     * @param int $posError
     * @param string $formattedDelimiter
     * @return array
     */
    public function getErrorPositionInFormattedQuery($formattedQueryInput, $posError, $formattedDelimiter = "\n")
    {
        $lines = explode($formattedDelimiter, $formattedQueryInput);
        $row = 1;
        $column = 0;
        $word = "";
        $checkLine = $posError;
        foreach ($lines as $line) {
            $checkLine -= strlen($line);
            if ($checkLine < 0) {
                $column = $checkLine + strlen($line);

                $word_start = strrpos(substr($line, 0, $column), ' ');
                $word_length = strpos($line, ' ', $column) - $word_start;
                $word = trim(substr($line, $word_start, $word_length));
                break;
            } else {
                $row++;
            }
        }

        if (empty($word)) {
            return array("row" => $row, "column" => $column);
        } else {
            return array("row" => $row, "column" => $column, "word" => $word);
        }
    }

    /**
     * @param string $prefix - displayed name of table without prefix
     * @param string $table - information from table
     * @return string - sql query
     */
    function getTableSchema($prefix = ADMIN_TAB_PREFIX,$table = null){
        if(is_null($table)){
            $table = $this->getTableNames();
            $table = $table[0];
        }

        $sql = "SELECT
                  substr(TABLE_NAME,".(1+strlen($prefix)).") tablename,
                  COLUMN_NAME columnname,
                  DATA_TYPE columntype,
                  DATA_LENGTH typelength
                FROM
                  ALL_TAB_COLUMNS
                WHERE
                  TABLE_NAME = '".$table."'
                ORDER BY COLUMN_ID";
        return $sql;
    }

    /**
     * @param string $table - index from table
     * @return string - sql query
     */
    function getTableIndex($table = null){
        if(is_null($table)){
            $table = $this->getTableNames();
            $table = $table[0];
        }

        $sql = "SELECT user_indexes.uniqueness as uniqueness,
                  user_ind_columns.column_name as Columnname
                FROM user_tables
                JOIN user_indexes on user_indexes.table_name = user_tables.table_name
                JOIN user_ind_columns ON user_indexes.index_name = user_ind_columns.index_name
                WHERE user_tables.table_name = '".$table."'
                ORDER BY user_ind_columns.column_name";
        return $sql;
    }

    /**
     * @param string $table - primary key from table
     * @return string - sql query
     */
    function getTablePrimary($table = null){
        if(is_null($table)){
            $table = $this->getTableNames();
            $table = $table[0];
        }

        $sql = "SELECT cols.column_name primarykey
                FROM all_constraints cons, all_cons_columns cols
                WHERE cols.table_name = '".$table."'
                AND cons.constraint_type = 'P'
                AND cons.constraint_name = cols.constraint_name
                AND cons.owner = cols.owner";
        return $sql;
    }


    /**
     * @param string $query - create statement
     * @return string - name from table
     */
    function getTableNameFromCreate($query){
        $query = strtoupper($query);
        $query = str_replace(" ","",$query);
        $query = str_replace("\"","'",$query);
        $query = str_replace("'","",$query);
        // at position 11 is name of table
        return substr($query,11,strpos($query,"(")-11);
    }
} 