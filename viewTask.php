<?php
session_start();

/**
 * @package    SqlValidator
 * @author     Christian Steusloff
 * @author     Jens Wiemann
 */

?>
<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="SQL - Validierung">
    <meta name="author" content="Christian Steusloff, Jens Wiemann">
    <link rel="shortcut icon" href="assets/ico/favicon.ico">

    <title>SQL - Validierung</title>

    <!-- SQL-Syntax Highlighting -->
    <link rel="stylesheet" href="css/codemirror.css">

    <!-- automatic arrangement of table -->
    <link rel="stylesheet" href="css/viewtables.css">

    <!-- Bootstrap core CSS -->
    <link href="css/bootstrap.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="css/style.css" rel="stylesheet">


    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>

<body>
<div class="container">
<div class="navbar navbar-default" role="navigation">
    <div class="container-fluid">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="viewTask.php">SQL - Validierung</a>
        </div>
        <div class="navbar-collapse collapse">
            <ul class="nav navbar-nav">
                <li><a href="createTask.php">create Task</a></li>
                <li class="active"><a href="viewTask.php">view Task</a></li>
            </ul>
            <ul class="nav navbar-nav navbar-right">
                <li><a href="about.php">about</a></li>
            </ul>
        </div>
    </div>
</div>

<?php
require_once("lib/define.inc.php");
require_once("lib/sqlConnection.class.php");
require_once("lib/oracleConnection.class.php");
require_once("lib/sqlValidator.class.php");
require_once("lib/taskHelper.php");
require_once("lib/queryTranslator.class.php");
require_once("lib/frontendHelper.class.php");
$fH = new frontendHelper();

$db = new oracleConnection();
$qT = new queryTranslator();

// TODO without login system, this parameter is fix
$_SESSION["username"] = "user";
$_SESSION["id"] = 2;
$_userPrefix = "user2_";

// special task
if (isset($_GET["id"])) {

    $task_id = $_GET["id"];

    $task = new taskHelper($db);
    $task->loadTask($_GET["id"], $_SESSION["id"]);

    if(!@isset($_POST["userquery"])){
        // create table for task depending on user
        $task->resetTask();
    }


    $last_sql = $task->getLastUserQuery();

    ?>
    <div class="alert alert-warning">
        <strong>Demo!</strong> Your Username is '<?php echo $_SESSION["username"]; ?>'<br>
    </div>


    <div class="FormText">
        <h2><?php echo $task->getTopic(); ?></h2>

        <p>
            <?php echo $task->getText(); ?>
        </p>
    </div>

    <div id="container" class="js-masonry" data-masonry-options='{ "columnWidth": 2, "itemSelector": ".task" }'>
        <?php
        if ($task->getTaskType() == "CREATE") {

            $db->setQuery($task->getTableSchema());
            $db->execute();
            echo $db->printTable("task","Schema");

            $db->setQuery($task->getTableIndex());
            $db->execute();
            echo $db->printTable("task","Index");

            $db->setQuery($task->getTablePrimary());
            $db->execute();
            echo $db->printTable("task","Primary-Key");
        } else {
            echo($task->printTable("task"));
        }
        ?>
    </div>


    <div class="FormText">
        <h2>solution output</h2>
        <?php
        if ($task->getTaskType() == "CREATE") {
            echo("See above");
        } elseif ($task->getTaskType() == "DROP") {
            echo("No output!");
        } else {
            $db->setSavePoint();

            $querySolution = $task->getSolution();
            $queryMaster = $qT->translate($querySolution, ADMIN_TAB_PREFIX);
            $db->setQuery($queryMaster);

            if ($db->getStatementType() == "SELECT") {
                $db->executeNoCommit();
                echo $db->printTable("task");
            } else {
                $db->executeNoCommit();
                echo($task->printTable("task", false));
            }

            $db->rollbackSavePoint();
        }
        ?>

    </div>

    <div class="FormText">
        <form class="form-horizontal" role="form" action="validateTask.inc.php" method="post">
            <fieldset>
                <!-- Hidden -->
                <input type="hidden" name="taskid" value="<?php echo $_GET["id"]; ?>">

                <!-- Textarea -->
                <div class="form-group">
                    <label class="col-sm-1 control-label" for="sql">Your Solution</label>

                    <div class="col-sm-8">
                        <textarea id="sql" class="form-control"
                                  name="sql"><?php echo $task->getLastUserQuery(); ?></textarea>
                    </div>
                    <div class="col-sm-3">
                        <h4>Infos</h4>

                        <p>
                        <ol>
                            <li>" becomes '</li>
                            <li>; at the end is not necessary</li>
                            <li>use tables aliases for attribute access</li>
                        </ol>
                    </div>
                </div>

                <!-- Button -->
                <div class="form-group">
                    <label class="col-sm-1 control-label" for="submit"></label>

                    <div class="col-sm-11">
                        <button id="submit" name="submit" class="btn btn-primary">check</button>
                    </div>
                </div>
            </fieldset>
        </form>
    </div>
    <?php if (isset($_SESSION["error"]) && !is_null($_SESSION["error"])) { ?>
        <div class="alert alert-danger">
            <strong>Error!</strong> <?php echo($_SESSION["error"]); ?>
        </div>
    <?php } elseif (isset($_SESSION["valid"]) && !is_null($_SESSION["valid"])) { ?>
        <div class="alert alert-warning">
            <strong>Mistake!</strong> Your syntax is right but the answer is wrong.<br>
            <?php echo $_SESSION["valid"]; ?>
        </div>
    <?php } elseif (isset($_SESSION["correct"]) && !is_null($_SESSION["correct"])) { ?>
        <div class="alert alert-success">
            <strong>Well done!</strong> Correct sql query.
        </div>
    <?php
    }
    ?>

    <div class="FormText">
        <h2>your result</h2>
        <?php
        if (isset($_SESSION["userquery"])) {
            if($task->getTaskType() == "CREATE"){
                $prefix = "CREATE_";
                $prePrefix = $prefix."USER" . $_SESSION["id"] . "_";
                $table = $task->getTableNameFromCreate($_SESSION["userquery"]);

                $dropTable = "DROP TABLE ".$prefix.$table;
                $db->setQuery($dropTable);
                @$db->execute();

                $createTable = str_replace($table,$prefix.$table,strtoupper($_SESSION["userquery"]));
                $db->setQuery($createTable);
                $db->execute();

                $db->setQuery($task->getTableSchema($prePrefix,$prefix.$table));
                $db->execute();
                echo $db->printTable("task","Schema");

                $db->setQuery($task->getTableIndex($prefix.$table));
                $db->execute();
                echo $db->printTable("task","Index");

                $db->setQuery($task->getTablePrimary($prefix.$table));
                $db->execute();
                echo $db->printTable("task","Primary-Key");

                $dropTable = "DROP TABLE ".$prefix.$table;
                $db->setQuery($dropTable);
                @$db->execute();
            } else {
                $db->setQuery($_SESSION["userquery"]);
                $db->execute();
                if ($task->getTaskType() == "SELECT") {
                    echo "<div class=\"js-masonry\" data-masonry-options='{ \"columnWidth\": 2, \"itemSelector\": \".task\" }'>";
                    echo $db->printTable("task");
                    echo "</div>";
                } elseif(@$task->getTaskType() == "DROP" && !@is_null($_SESSION["correct"])){
                    echo "<div>Database droped!</div>";
                } else {
                    foreach ($task->getTableNames() as $table) {
                        $userTab = str_replace(ADMIN_TAB_PREFIX, $_userPrefix, $table);
                        $db->setQuery("SELECT * FROM {$userTab}");
                        $db->execute();
                        echo "<div class=\"js-masonry\" data-masonry-options='{ \"columnWidth\": 2, \"itemSelector\": \".task\" }'>";
                        echo $db->printTable("task");
                        echo "</div>";
                    }
                }
            }
            $task->resetTask();
        }
        ?>
    </div>
    <a name="end"></a>

<?php

} else {
    // overview about tasks
    $db->setQuery("SELECT ID,TASKNAME,TASKTEXT FROM SYS_TASK ORDER BY ID");
    $db->execute();
    $select = array();
    echo <<<TABHEAD
            <table class="table table-striped" >
            <thead>
            <tr>
                <th>#</th>
                <th>Title</th>
                <th>Description</th>
            </tr>
            </thead>
            <tbody>
TABHEAD;
    while ($db->Fetch()) {
        echo("<tr>");
        echo("<td>" . $db->row['ID'] . "</td>");
        echo("<td><a href=viewTask.php?id=" . $db->row['ID'] . ">" . $db->row['TASKNAME'] . "</a></td>");
        echo("<td>" . substr($db->row['TASKTEXT'], 0, 50) . "...</td>");
        echo("</tr>");
    }
    echo("</tbody></table >");
    $db->closeConnection();

}

// unset variables from Session
$fH->unsetSession($_SESSION, array("error", "valid", "correct", "userquery"));

?>

</div>
<!-- /container -->


<!-- Bootstrap core JavaScript
================================================== -->
<script src="js/jquery.js"></script>
<script src="js/bootstrap.js"></script>

<!-- CodeMirror
================================================== -->
<script src="js/codemirror.js"></script>
<script src="js/sql.js"></script>
<script>
    window.onload = function () {
        window.editor = CodeMirror.fromTextArea(document.getElementById('sql'), {
            mode: 'text/x-mysql',
            indentWithTabs: true,
            smartIndent: true,
            lineNumbers: true,
            matchBrackets: true
        });
    };
</script>
<!-- automatic arragement
================================================== -->
<script src="js/autoarrangement.js"></script>
</body>
</html>