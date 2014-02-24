<?php
session_start();
error_reporting(E_ALL);
/**
 * Project: sqlValidator
 * User: Christian Steusloff
 * Date: 05.02.14
 * Time: 21:03
 */
?>
<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="SQLValidator">
    <meta name="author" content="Christian Steusloff, Jens Wiemann">
    <link rel="shortcut icon" href="../../assets/ico/favicon.ico">

    <title>SQL-Validator</title>

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
                <a class="navbar-brand" href="#">SQL - Validator</a>
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

    // TODO erstelle Tabellen fuer User
    //$_SESSION["username"] = "demo";
    $_SESSION["username"] = "demo";
    $_SESSION["id"] = 2;

    // special task
    if (isset($_GET["id"])) {

        $task_id = $_GET["id"];

        $task = new taskHelper($db);
        $task->loadTask($_GET["id"],$_SESSION["id"]);

        // create table for task depending on user
        // TODO Darf natürlich nicht ausgeführt werden, wenn das Formular hier gesendet wurde!!!!
        $task->resetTask();

        $last_sql = $task->getLastUserQuery();

//        $_SESSION["sql"] = "SELECT
//    c.cname as Cocktail,
//  z.zname as Zutat,
//  zc.menge as Menge
//FROM
//(
//    SELECT
//      cid,
//      cname
//    FROM
//      Cocktail
//    WHERE
//      alkoholisch = 'n'
//  ) c
//  INNER JOIN Zutat_Cocktail zc ON c.cid = zc.cid
//  INNER JOIN Zutat z ON zc.zid = z.zid";

//        echo("<pre>");
//        var_dump($_SESSION["error"]);

//        // save Object
//        $s = serialize($task);
//        $fp = fopen("task.obj","w");
//        fwrite($fp,$s);
//        fclose($fp);
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
            <?php echo($task->printTable("task")); ?>
        </div>


        <div class="FormText">
            <h2>solution output</h2>
            <?php
                if($task->getTaskType() == "CREATE" ){
                    echo("See above!");
                } elseif($task->getTaskType() == "DROP" ){
                    echo("No output!");
                } else {
                    $db->setSavePoint();

                    $querySolution = $task->getSolution();
                    $queryMaster = $qT->translate($querySolution,"MASTER_");
                    $db->setQuery($queryMaster);

                    if($db->getStatementType() == "SELECT"){
                        $db->executeNoCommit();
                        echo $db->printTable("task");
                    } else {
                        $db->executeNoCommit();
                        echo($task->printTable("task",false));
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
                                      name="sql"><?php echo $task->getLastUserQuery();?></textarea>
<!--                                --><?php //echo (isset($last_sql)) ? $last_sql : ""; ?>
                        </div>
                        <div class="col-sm-3">
                            <h4>Infos</h4>

                            <p>
                            <ol>
                                <li>" becomes '</li>
                                <li>; at the end is not necessary</li>
                                <li>Avoid unnecessary parentheses</li>
                            </ol>
                            </p>
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
        <?php if (isset($_SESSION["error"])) { ?>
            <div class="alert alert-danger">
                <strong>Error!</strong> <?= $_SESSION["error"]; ?>
            </div>
        <?php } elseif (isset($_SESSION["valid"])) { ?>
            <div class="alert alert-warning">
                <strong>Misstake!</strong> Your syntax is right but it's wrong answer.<br>
                <?= $_SESSION["valid"]; ?>
            </div>
        <?php } elseif (isset($_SESSION["correct"])) { ?>
            <div class="alert alert-success">
                <strong>Well done!</strong> Correct sql query.
            </div>
        <?php }
        ?>

        <div class="FormText">
            <h2>your result</h2>
            <p>
                <?php
                if(isset($_SESSION["userquery"])){
                    $db->setQuery($_SESSION["userquery"]);
                    $db->execute();
                    echo $db->printTable("task");
                }
                ?>
            </p>
        </div>
        <a name="end"></a>



    <?php

    } else {
        // overview about tasks
        $db->setQuery("SELECT ID,TASKNAME,TASKTEXT FROM SYS_TASK ORDER BY TASKNAME");
        $db->execute();
        $select = array();
        echo <<<TABHEAD
            <table class="table table-striped" >
            <thead>
            <tr>
                <th>#</th>
                <th>title</th>
                <th>text</th>
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
    $fH->unsetSession($_SESSION,array("error","valid","correct","userquery"));

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