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


$master->setSavePoint();

// test query - no commit to database
$master->setQuery($qT->translate($_POST['sql'],"MASTER_"));
$master->executeNoCommit();
$tableHeader = $master->getHeader(true);
$tableContent = $master->getContent(true);

$master->rollbackSavePoint();

$_SESSION['title'] = $_POST['title'];
$_SESSION['text'] = $_POST['text'];
$_SESSION['table'] = $_POST['table'];
// TODO radio: $_SESSION['right'
$_SESSION['sql'] = SqlFormatter::format($_POST["sql"],false);

//echo("<pre>");
//var_dump($_SESSION);
//die();

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
        $_SESSION["error"] = "Aufgabe existiert bereits oder Felder nicht korrekt ausgefüllt! ".$error;
        header("LOCATION: createTask.php?s=0");
    }
}


?>