<?php
session_start();
/**
 * Project: sqlValidator
 * User: Christian Steusloff
 * Date: 06.02.14
 * Time: 19:34
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
$task->loadTask($_POST["taskid"],$_SESSION["id"]);


$_POST["sql"] = $task->clearQuery($_POST["sql"]);


// this query has wrong names of table , at this time only check the allow statement type
$slave->setQuery($_POST["sql"]);

// SQL Query to correct format
$task->saveLastUserQuery(SqlFormatter::format($_POST["sql"],false));


$allow = false;
switch($slave->getStatementType()){
    case 'SELECT' : $allow = $task->getPermissionSelect();
        break;
    case 'INSERT' :
    case 'DELETE' :
    case 'UPDATE' : $allow = $task->getPermissionModify();
        break;
    case 'ALTER' :
    case 'CREATE' : $allow = $task->getPermissionCreate();
        break;
    case 'DROP' : $allow = $task->getPermissionDrop();
        break;
    default : $allow = false;
        // CALL, BEGIN, DECLARE, UNKNOWN
        break;
}
if(!$allow){
    // set error-message
    $_SESSION["error"] = $slave->getStatementType($_POST["sql"])." not allowed by this task";
    session_write_close();
    header("LOCATION: viewTask.php?id=".$_POST["taskid"]);
    // TODO: notwendig oder else?
    die();
}

// USER-solution without prefix
$queryTry = $_POST["sql"];
// to correct table
$querySlave = $qT->translate($queryTry,"user".$_SESSION["id"]."_");

// slave connection with user-query
$slave->setQuery($querySlave);


// Syntax-Error
$slave->setSavePoint();
@$slave->executeNoCommit();
$_SESSION["error"] = $slave->getErrortext();
$slave->rollbackSavePoint();



if(empty($_SESSION["error"])){
    // save query by user
    $_SESSION["userquery"] = $querySlave;
    // Master-Solution without prefix
    $querySolution = $task->getSolution();
    // to correct table
    $queryMaster = $qT->translate($querySolution,ADMIN_TAB_PREFIX);
    // master connection with solution-query
    $master->setQuery($queryMaster);

    $validator = new sqlValidator($master,$slave);
    if($validator->validate()){
        $_SESSION["correct"] = true;
        $task->saveCorrectUserQuery(SqlFormatter::format($_POST["sql"],false));
    }
    $_SESSION["valid"] = $validator->getMistake();
}


session_write_close();
header("LOCATION: viewTask.php?id=".$_POST["taskid"]."#end");
