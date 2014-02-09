<?php
/**
 * Project: sqlValidator
 * User: Christian Steusloff
 * Date: 05.02.14
 * Time: 21:07
 */

require_once("lib/define.inc.php");
require_once("lib/sqlConnection.class.php");
require_once("lib/oracleConnection.class.php");
require_once("lib/taskHelper.php");

$db = new oracleConnection();
$tH = new taskHelper($db);
if($tH->createTask($_POST['title'],$_POST['text'],$_POST['table'],$_POST['right'],str_replace("'","''",$_POST['sql']))){
    header("LOCATION: createTask.php?s=1");
} else {
    $db->getErrortext();
    header("LOCATION: createTask.php?s=0");
}


?>