<?php
session_start();

/**
 * @package    SqlValidator
 * @author     Christian Steusloff
 * @author     Jens Wiemann
 */

require_once("lib/define.inc.php");
require_once("lib/sqlConnection.class.php");
require_once("lib/oracleConnection.class.php");
require_once("lib/sqlValidator.class.php");
require_once("lib/taskHelper.php");
require_once("lib/queryTranslator.class.php");
require_once("lib/sqlFormatter.php");

$db = new oracleConnection();
$master = new oracleConnection();
$slave = new oracleConnection();


$qT = new queryTranslator();

$task_id = $_POST["taskid"];

$task = new taskHelper($db);
$task->loadTask($_POST["taskid"], $_SESSION["id"]);

$_POST["sql"] = $task->clearQuery($_POST["sql"]);


// this query has wrong names of table , at this time only check the allow statement type
$slave->setQuery($_POST["sql"]);

// format user input
$formattedQueryInput = SqlFormatter::format($_POST["sql"], false);


// SQL Query to correct format
$task->saveLastUserQuery($formattedQueryInput);

// check permission
$allow = ($slave->getStatementType() == $task->getTaskType());

if (!$allow) {
    // set error-message
    $_SESSION["error"] = $slave->getStatementType($_POST["sql"]) . " not allowed by this task";
    session_write_close();
    header("LOCATION: viewTask.php?id=" . $_POST["taskid"]);
    // stop task
    die();
}

// check Syntax-Error
$slave->setSavePoint();

if($slave->getStatementType() == "CREATE"){
    // delete exist table for syntax check
    foreach($task->getTableNames() as $table){
        $slave->setQuery("DROP TABLE ".str_replace("MASTER_","",$table));
        @$slave->executeNoCommit();
    }
}
// to default table NOT user-table
$slave->setQuery($formattedQueryInput);
// CREATE would commit by oracle
@$slave->executeNoCommit();
if($slave->getStatementType() == "CREATE"){
    $sql = "DROP TABLE ".$task->getTableNameFromCreate($formattedQueryInput);
    $db->setQuery($sql);
    $db->execute();
}
$_SESSION["error"] = $slave->getErrortext();
if (!empty($_SESSION["error"])) {
    // get correct position in formatted query
    $pos = $task->getErrorPositionInFormattedQuery($formattedQueryInput, $slave->getErrorPosition());
    $_SESSION["error"] .= "<br>Error in row: " . $pos["row"] . " column: " . $pos["column"];
    $_SESSION["error"] .= !empty($pos["word"]) ? " by <b>" . $pos["word"] . "</b>" : "";
}

$slave->rollbackSavePoint();

if (empty($_SESSION["error"])) {

    // USER-solution without prefix
    $queryTry = $_POST["sql"];
    // to correct table
    if($slave->getStatementType() == "CREATE"){
        $tab = $task->getTableNameFromCreate($queryTry);
        $querySlave = str_replace($tab,"user" . $_SESSION["id"] . "_".$tab,strtoupper($queryTry));

        // set master solution query
        $table = $task->getTableNames();
        $table = $table[0];
        // create Query (Master-Solution with prefix from exist table)
        $queryCreate = "SELECT DBMS_METADATA.GET_DDL('TABLE','".$table."') createQuery FROM dual";
        $master->setQuery($queryCreate);
        $master->execute();
        $master->Fetch();
        // Master-Solution with prefix
        $queryMaster = $master->row['CREATEQUERY']->load();

    } else {
        $querySlave = $qT->translate($queryTry, "user" . $_SESSION["id"] . "_");
        // Master-Solution without prefix
        $querySolution = $task->getSolution();
        // to correct table
        $queryMaster = $qT->translate($querySolution, ADMIN_TAB_PREFIX);
    }

    // slave connection with user-query
    $slave->setQuery($querySlave);

    // master connection with solution-query
    $master->setQuery($queryMaster);

    // save query by user
    $_SESSION["userquery"] = $querySlave;


    $validator = new sqlValidator($master, $slave, $task);
    if ($validator->validate()) {
        $_SESSION["correct"] = true;
        $task->saveCorrectUserQuery(SqlFormatter::format($_POST["sql"], false));
    } else {
        $_SESSION["valid"] = $validator->getMistake();
    }
}

session_write_close();
header("LOCATION: viewTask.php?id=" . $_POST["taskid"] . "#end");
