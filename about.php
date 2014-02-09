<?php
/**
 * Project: sqlValidator
 * User: Christian Steusloff
 * Date: 05.02.14
 * Time: 21:03
 */
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
                    <li><a href="createTask.php">create Task</a></li>
                    <li><a href="viewTask.php">view Task</a></li>
                </ul>
                <ul class="nav navbar-nav navbar-right">
                    <li class="active"><a href="about.php">about</a></li>
                </ul>
            </div>
        </div>
    </div>

    <?php
    require("lib/define.inc.php");
    require("lib/sqlConnection.class.php");
    require("lib/oracleConnection.class.php");
    require("lib/sqlValidator.class.php");

    $master_con = new oracleConnection();
    $slave_con = new oracleConnection();

//    $master_con->setQuery("SELECT * From tab");
//    $master_con->execute();

    $master_con->sqlquery = "SELECT cname,cid FROM MASTER_COCKTAIL WHERE gid between 5 AND 12";
    $master_con->Query();
    echo $master_con->printTable();

    echo("<br>");

    $slave_con->Query("SELECT cname,gid FROM MASTER_COCKTAIL WHERE gid >= 5 AND gid < 12");
    echo $slave_con->printTable();

    echo("<br>");

    $valid = new sqlValidator();
    $valid->setSqlConnection($master_con,$slave_con);
    if($valid->validate()){
        echo("correct");
    } else {
        echo $valid->getMistake();
    }







    ?>



</div> <!-- /container -->
<!-- Bootstrap core JavaScript
================================================== -->
<script src="js/jquery.js"></script>
<script src="js/bootstrap.js"></script>

</body>
</html>