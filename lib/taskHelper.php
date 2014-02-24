<?php
/**
 * Project: ss
 * User: Christian Steusloff
 * Date: 14.12.13
 * Time: 13:16
 */


class taskHelper {

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
     *
     * Select = 1
     * Insert/Update/Delete = 2
     * Create = 4
     * Drop = 8
     *
     *
     * @param int (array) $permission
     */
    public function setPermission($permission)
    {
        if(is_array($permission)){
            $this->permission = array_sum($permission);
        } else {
            $this->permission = $permission;
        }

        return $this->getPermission();
    }

//    private function pemissionToType(){
//        // TODO: Permission = Zahl 1 bis 15
//        // Type ist SELECT, UPDATE etc. Jedoch steht 2 für INSERT, UPDATE oder DELETE
//        // Type als String-Array?
//        // Oder kurz Solution laden als Connection und gucken, was es ist.
//    }

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
     * @param string $taskType
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

    private $table_ids;
    private $table_names;

    private $tableHeader;

    /**
     * @param mixed $tableData
     */
    public function setTableContent($tableData)
    {
        $this->tableData = $tableData;
    }

    /**
     * @return mixed
     */
    public function getTableData()
    {
        return $this->tableData;
    }

    /**
     * @param mixed $tableHeader
     */
    public function setTableHeader($tableHeader)
    {
        $this->tableHeader = $tableHeader;
    }

    /**
     * @return mixed
     */
    public function getTableHeader()
    {
        return $this->tableHeader;
    }
    private $tableData;

//    /**
//     * @var string sql Query
//     */
//    private $user_queryLast;
//
//    /**
//     * @var string sql Query
//     */
//    private $user_queryCorrect;
//
//    /**
//     * @param string sql Query
//     */
//    public function setUserQueryLast($user_queryLast)
//    {
//        $this->user_queryLast = $user_queryLast;
//    }
//
//    /**
//     * @return string sql Query
//     */
//    public function getUserQueryLast()
//    {
//        return $this->user_queryLast;
//    }
//
//    /**
//     * @param string sql Query
//     */
//    public function setUserQueryCorrect($user_queryCorrect)
//    {
//        $this->user_queryCorrect = $user_queryCorrect;
//    }
//
//    /**
//     * @return string sql Query
//     */
//    public function getUserQueryCorrect()
//    {
//        return $this->user_queryCorrect;
//    }

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
     * @param mixed $table_ids
     */
    public function setTableIds($table_ids)
    {
        $this->table_ids = $table_ids;
    }

    /**
     * @return mixed
     */
    public function getTableIds()
    {
        return $this->table_ids;
    }

    /**
     * @param mixed $table_names
     */
    public function setTableNames($table_names)
    {
        $this->table_names = $table_names;
    }

    /**
     * @return mixed
     */
    public function getTableNames()
    {
        return $this->table_names;
    }


    private $task_id;

    /**
     * @param mixed $task_id
     */
    public function setTaskId($task_id)
    {
        $this->task_id = $task_id;
    }

    /**
     * @return mixed
     */
    public function getTaskId()
    {
        return $this->task_id;
    }

    /**
     * @param string $solution
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
     * @param string array $tables - names of usable tables
     * @param int array $permission - access rights of table
     * @param string $solution - SQL query
     */
    public function createTask($topic,$text,$tables,$permission,$solution,$tableHeader,$tableContent){

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
                    VALUES ('".$this->getTopic()."',
                            '".$this->getText()."',
                            '".$this->getPermission()."',
                            '".$this->getSolution()."',
                            '".$tableHeader."',
                            '".$tableContent."')");
        $this->dbConnection->execute();
        $errors = $this->dbConnection->getErrorText();
//        var_dump($this->dbConnection->sqlquery);
//        die($errors);

        // get task-id from last new task (this current create task)
        $this->dbConnection->setQuery("SELECT MAX(ID) FROM SYS_TASK WHERE taskname = '".$this->getTopic()."'");
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

    public function __construct($sqlConnection) {
        $this->setDbConnection($sqlConnection);
//        if(!is_null($task_id)){
//            $this->loadTask($task_id);
//        }
    }

    /**
     * @param string $query - input by user
     * @return string - without ; and '
     */
    public function clearQuery($query){
        $newQuery = $query;
        if(strpos($newQuery,";") !== false){
            $piece = explode(";",$newQuery);

            var_dump($piece);
            $newQuery = $piece[0];
        }
        if(strpos($newQuery,'"') !== false){
            $newQuery = str_replace('"',"'",$newQuery);
        }

        return $newQuery;
    }

    public function saveLastUserQuery($lastQuery){
        $this->setUserInput($lastQuery);
        // mask ' with double '
        $lastQuery = str_replace("'","''",$lastQuery);
        $this->dbConnection->setQuery("MERGE INTO SYS_USER_TASK U
                                       USING (
                                            SELECT ".$this->getUserId()." as USER_ID,
                                                   ".$this->getTaskId()." as TASK_ID,
                                                   '".$lastQuery."' as QUERY_LAST,
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
    public function saveCorrectUserQuery($correctQuery){
        $this->dbConnection->setQuery("MERGE INTO SYS_USER_TASK U
                                       USING (
                                            SELECT ".$this->getUserId()." as USER_ID,
                                                   ".$this->getTaskId()." as TASK_ID,
                                                   '' as QUERY_LAST,
                                                   '".$correctQuery."' as QUERY_CORRECT
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





    public function getLastUserQuery(){
        $this->dbConnection->setQuery("SELECT QUERY_LAST
                                       FROM SYS_USER_TASK
                                       WHERE USER_ID = ".$this->getUserId()."
                                       AND TASK_ID = ".$this->getTaskId());
        $this->dbConnection->execute();
        $this->dbConnection->Fetch();
        $lastQuery = $this->dbConnection->row["QUERY_LAST"];
        $this->setUserInput($lastQuery);
        return $lastQuery;
    }

    public function getCorrectUserQuery(){
        $this->dbConnection->setQuery("SELECT QUERY_CORRECT
                                       FROM SYS_USER_TASK
                                       WHERE USER_ID = ".$this->getUserId()."
                                       AND TASK_ID = ".$this->getTaskId());
        $this->dbConnection->execute();
        $this->dbConnection->Fetch();
        return $this->dbConnection->row["QUERY_CORRECT"];
    }

    public function loadTask($task_id,$user_id){
        $this->dbConnection->setQuery("SELECT taskname,
                           tasktext,
                           permission,
                           solution,
                           tableheader,
                           tablecontent
                    FROM SYS_TASK WHERE ID = {$task_id}");
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
    public function resetTask(){
        foreach ($this->getTableNames() as $table) {
            $table = str_replace(ADMIN_TAB_PREFIX,"",$table);
            $userTable = "user".$this->getUserId()."_".$table;
            $query_drop = "DROP TABLE ".$userTable;
            $query_create = "CREATE TABLE ".$userTable." AS (SELECT * FROM MASTER_".$table.")";
            $this->dbConnection->setQuery($query_drop);
            // Oracle don't have drop if exist, don't show error
            @$this->dbConnection->execute();
            $this->dbConnection->setQuery($query_create);
            $this->dbConnection->execute();

        }

        // TODO: Zur Sicherheit anlegen ohne Benutzer-Namen
        foreach ($this->getTableNames() as $table) {
            $tmpTable = str_replace(ADMIN_TAB_PREFIX,"",$table);
            $query_drop = "DROP TABLE ".$tmpTable;
            $query_create = "CREATE TABLE ".$tmpTable." AS (SELECT * FROM ".$table.")";
            $this->dbConnection->setQuery($query_drop);
            // Oracle don't have drop if exist, don't show error
            @$this->dbConnection->execute();
            $this->dbConnection->setQuery($query_create);
            $this->dbConnection->execute();

        }
    }


    public function getPermissionSelect(){
        $select_array = array(1,3,5,7,9,11,13,15);
        return in_array($this->getPermission(),$select_array);
    }
    public function getPermissionModify(){
        $mod_array = array(2,3,6,7,10,11,14,15);
        return in_array($this->getPermission(),$mod_array);
    }
    public function getPermissionCreate(){
        $create_array = array(4,5,6,7,12,13,14,15);
        return in_array($this->getPermission(),$create_array);
    }
    public function getPermissionDrop(){
        $drop_array = array(8,9,10,11,12,13,14,15);
        return in_array($this->getPermission(),$drop_array);
    }


    /**
     * Tablenames are unique, insert via merge if it's in this table
     *
     * @param string array - names of tables
     * @return int array - Table IDs
     */
    private function addDatabaseTables($table_array){

        $insert_query = "MERGE INTO SYS_TABLES tab USING (";
        foreach($table_array as $table){
            $insert[] = "SELECT '".$table."' as name FROM DUAL";
        }
        $insert_query .= implode(" UNION ALL ",$insert);
        $insert_query .= ") src ON (src.name = tab.name) WHEN NOT MATCHED THEN INSERT(name) VALUES (src.name)";

        $this->dbConnection->setQuery($insert_query);
        $this->dbConnection->execute();

        return $this->getDatabaseTablesByTableNames($table_array);
    }


    /**
     * @param string array $table_names
     * @return int array - Table IDs
     */
    private function getDatabaseTablesByTableNames($table_names){
        foreach($table_names as $table){
            $search[] = "name = '".$table."'";
        }

        $id_result = array();
        $this->dbConnection->setQuery("SELECT ID FROM SYS_TABLES WHERE ".implode(" OR ",$search));
        $this->dbConnection->execute();
        while($this->dbConnection->Fetch()){
            $id_result[] = $this->dbConnection->row['ID'];
        }
        return $id_result;
    }

//    /**
//     *
//     * @return int array - Table IDs
//     */
//    private function getDatabaseTables(){
//        $id_result = array();
//        $name_result = array();
//        $this->dbConnection->Query("SELECT n.table_id,t.name FROM SYS_NEEDTABLES n,SYS_TABLES t ON n.table_id = t.ID WHERE task_id = ".$this->getTaskId());
//        while($this->dbConnection->Fetch()){
//            var_dump($this->dbConnection->row);
//            $id_result[] = $this->dbConnection->row['table_id'];
//            $name_result[] = $this->dbConnection->row['name'];
//        }
//        $this->setTables($name_result);
//        return $id_result;
//    }

    private function setDatabaseTables(){
        $tablename_array = array();
        $tableid_array = array();
        $this->dbConnection->setQuery("SELECT n.table_id, t.name
                    FROM SYS_NEEDTABLES n,
                    SYS_TABLES t
                    WHERE n.table_id = t.ID
                    AND task_id = ".$this->getTaskId());
        $this->dbConnection->execute();
        while($this->dbConnection->Fetch()){
            //var_dump($this->dbConnection->row);
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
    private function setNeedTables($task_id,$table_ids){
        $insert_query = "INSERT ALL ";
        foreach($table_ids as $id){
            $insert_query .= "into SYS_NEEDTABLES(task_id,table_id) VALUES ('".$task_id."','".$id."') ";
        }
        $insert_query .= "SELECT * FROM DUAL";

        $this->dbConnection->setQuery($insert_query);
        $this->dbConnection->execute();
    }

    public function printTable($classname = null,$commit = true){
        if(is_null($classname)){
            $classname = "defaultTableClassName";
        }

        $tablestr = "";
        foreach($this->getTableNames() as $table){
            $this->dbConnection->setQuery("SELECT * FROM {$table}");
            if($commit){
                $this->dbConnection->execute();
            } else {
                $this->dbConnection->executeNoCommit();
            }
            $tablestr .= $this->dbConnection->printTable($classname,substr(strtoupper($table),strlen(ADMIN_TAB_PREFIX)));
        }
        return $tablestr;
    }
} 