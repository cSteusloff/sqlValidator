<?php
/**
 * Project: sqlValidator
 * User: Christian Steusloff
 * Date: 05.02.14
 * Time: 21:00
 */
session_start();

require_once("lib/define.inc.php");
require_once("lib/sqlConnection.class.php");
require_once("lib/oracleConnection.class.php");
require_once("lib/frontendHelper.class.php");
$fH = new frontendHelper();
$db = new oracleConnection();
$db->setQuery("SELECT TABLE_NAME FROM ALL_TABLES WHERE UPPER(TABLE_NAME) LIKE '".ADMIN_TAB_PREFIX."%'");
$db->execute();

?>
<!doctype html>
<html lang="de"><head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="SQLValidator">
    <meta name="author" content="Christian Steusloff, Jens Wiemann">
    <link rel="shortcut icon" href="../../assets/ico/favicon.ico">

    <title>SQL-Validator</title>

    <!-- SQL-Syntax Highlighting -->
    <link rel="stylesheet" href="css/codemirror.css">

    <!-- Bootstrap core CSS -->
    <link href="css/bootstrap.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="style.css" rel="stylesheet">

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
                    <li class="active"><a href="createTask.php">create Task</a></li>
                    <li><a href="viewTask.php">view Task</a></li>
                </ul>
                <ul class="nav navbar-nav navbar-right">
                    <li><a href="about.php">about</a></li>
                </ul>
            </div>
        </div>
    </div>


    <?php if(isset($_GET["s"]) && $_GET["s"] == 1){ ?>
        <div class="alert alert-success">
            <strong>Well done!</strong> You successfully created a new task.
        </div>
    <?php } elseif(isset($_GET["s"]) && $_GET["s"] == 0) { ?>
        <div class="alert alert-danger">
            <strong>Error!</strong> <?php echo isset($_SESSION["error"]) ? $_SESSION["error"] : "";?>
        </div>
    <?php }
    // unset Alert
    $fH->unsetSession($_SESSION,"error");
    ?>

    <form class="form-horizontal" role="form" action="createTask.inc.php" method="post">
        <fieldset>

            <!-- Form Name -->
            <legend>New Task</legend>

            <!-- Text input-->
            <div class="form-group">
                <label class="col-sm-2 control-label" for="title">task title</label>
                <div class="col-sm-10">
                    <input id="title" name="title" type="text" placeholder="task title"
                           class="input-xlarge form-control" required=""
                           value="<?php echo isset($_SESSION["title"]) ? $_SESSION["title"] : "";?>">
                </div>
            </div>

            <!-- Textarea -->
            <div class="form-group">
                <label class="col-sm-2 control-label" for="text">task text</label>
                <div class="col-sm-10">
                    <textarea id="text" class="form-control" rows="4" required="" name="text"><?php echo isset($_SESSION["text"]) ? $_SESSION["text"] : "";?></textarea>
                </div>
            </div>

            <!-- Select Multiple -->
            <div class="form-group">
                <label class="col-sm-2 control-label" for="table2">table in use</label>
                <div class="col-sm-10">
                    <select id="table" name="table[]" required="" class="form-control" multiple="multiple" size="10">
                        <?php
                        while($db->Fetch(false)){
                            $tablename = substr(strtoupper($db->row[0]),strlen(ADMIN_TAB_PREFIX));
                            var_dump($db->row[0]);
                            var_dump($_SESSION["table"]);

                            if(in_array($db->row[0],$_SESSION["table"])){
                                echo("<option value='{$db->row[0]}' selected=selected>".$tablename."</option>");
                            } else {
                                echo("<option value='{$db->row[0]}'>".$tablename."</option>");
                            }
                        }
                        ?>
                    </select>
                </div>
            </div>

            <!-- Multiple Checkboxes -->
            <!-- it's possible to change radio-Button to checkbox, the taskHelper can handle it.
                 At Moment one task for one operation, so use radio.
                 Change this: <input type="radio" name="right[0]" id="right-#" value="#">
                 to this: <input type="checkbox" name="right[#]" id="right-#" value="#">
                 -->
            <div class="form-group">
                <label class="col-sm-2 control-label" for="right">right</label>
                <div class="col-sm-10">
                    <div class="checkbox checkbox-inline"><label class="" for="right-0">
                            <input type="radio"
                                   name="right[0]"
                                   <?php echo isset($_SESSION["right"][0]) ? 'checked="checked"' : '';?>
                                   id="right-0" value="1">
                            Select
                        </label></div>
                    <div class="checkbox checkbox-inline"><label class="" for="right-1">
                            <input type="radio"
                                   name="right[0]"
                                   <?php echo isset($_SESSION["right"][1]) ? 'checked="checked"' : '';?>
                                   id="right-1" value="2">
                            Insert/Update/Delete
                        </label></div>
                    <div class="checkbox checkbox-inline"><label class="" for="right-2">
                            <input type="radio"
                                   name="right[0]"
                                   <?php echo isset($_SESSION["right"][2]) ? 'checked="checked"' : '';?>
                                   id="right-2" value="4">
                            Create/Alter
                        </label></div>
                    <div class="checkbox checkbox-inline"><label class="" for="right-3">
                            <input type="radio"
                                   name="right[0]"
                                   <?php echo isset($_SESSION["right"][3]) ? 'checked="checked"' : '';?>
                                   id="right-3" value="8">
                            Drop
                        </label></div>
                </div>
            </div>

            <!-- Textarea -->
            <div class="form-group">
                <label class="col-sm-2 control-label" for="sql">SQL query</label>
                <div class="col-sm-10">
                    <textarea id="sql" class="form-control"
                              name="sql"><?php echo isset($_SESSION["sql"]) ? $_SESSION["sql"] : "";?></textarea>
                </div>
            </div>

            <!-- Button -->
            <div class="form-group">
                <label class="col-sm-2 control-label" for="submit"></label>
                <div class="col-sm-10">
                    <button id="submit" name="submit" class="btn btn-primary">create</button>
                </div>
            </div>
        </fieldset>
        <?php
        // unset variables from Session
        $fH->unsetSession($_SESSION,array("title","text","table","right","sql"));
        session_destroy();
        ?>
    </form>

</div> <!-- /container -->


<!-- Bootstrap core JavaScript
================================================== -->
<script src="js/jquery.js"></script>
<script src="js/bootstrap.js"></script>

<!-- CodeMirror
================================================== -->
<script src="js/codemirror.js"></script>
<script src="js/sql.js"></script>
<script>
    window.onload = function() {
        window.editor = CodeMirror.fromTextArea(document.getElementById('sql'), {
            mode: 'text/x-mysql',
            indentWithTabs: true,
            smartIndent: true,
            lineNumbers: true,
            matchBrackets : true,
        });
    };
</script>
</body>
</html>