<?php
/**
 * Project: sqlValidator
 * User: Christian Steusloff
 * Date: 05.02.14
 * Time: 21:07
 */
session_start();

require_once("lib/define.inc.php");
require_once("lib/sqlConnection.class.php");
require_once("lib/oracleConnection.class.php");
require_once("lib/sqlValidator.class.php");
require_once("lib/taskHelper.php");
require_once("lib/queryTranslator.class.php");
require_once("lib/sqlFormatter.php");

$master = new oracleConnection();
$task = new taskHelper($master);
$qT = new queryTranslator();

//echo("<pre>");
//var_dump($_POST);
//die();
// check inputs
$empty = 0;
!empty($_POST['title']) ? $_SESSION['title'] = $_POST['title'] : $empty++;
!empty($_POST['text']) ? $_SESSION['text'] = $_POST['text'] : $empty++;
!empty($_POST['table']) ? $_SESSION['table'] = $_POST['table'] : $empty++;
!empty($_POST['right']) ? $_SESSION['right'] = $_POST['right'] : $empty++;
if(empty($_POST["sql"]) && (isset($_POST["right"][0]) || isset($_POST["right"][1]))) $empty++;
$_SESSION['sql'] = SqlFormatter::format($_POST["sql"],false);

if($empty > 0){
    $_SESSION["error"] = "Please fill out all fields.";
    header("LOCATION: createTask.php?s=0");
} else {
    $master->setSavePoint();

    // test query - no commit to database
    $master->setQuery($qT->translate($_POST['sql'],"MASTER_"));
    $master->executeNoCommit();
    $tableHeader = $master->getHeader(true);
    $tableContent = $master->getContent(true);

    $master->rollbackSavePoint();

    if($master->getErrortext()){
        $_SESSION["error"] = $master->getErrortext();

        header("LOCATION: createTask.php?s=0");
    } else {
        $error = $task->createTask($_POST['title'],
                $_POST['text'],
                $_POST['table'],
                $_POST['right'],
                str_replace("'","''",$_POST['sql']),
                $tableHeader,
                $tableContent);
        if(is_null($error)){
            header("LOCATION: createTask.php?s=1");
        } else {
            $_SESSION["error"] = "Task already exists or fields are not filled out correctly! ".$error;
            header("LOCATION: createTask.php?s=0");
        }
    }
}
?>