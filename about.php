<?php

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
                <a class="navbar-brand" href="viewTask.php">SQL - Validierung</a>
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

    <div class="panel panel-primary">
        <div class="panel-heading">Softwareprojekt</div>
        <div class="panel-body">
            <ul class="list-group">
                <li class="list-group-item">Projektname: SQL-Validierung</li>
                <li class="list-group-item">Projektteam: Jens Wiemann, Christian Steusloff</li>
                <li class="list-group-item">Projektverantwortlicher: Prof. Dr. rer. nat. habil. Gunter Saake</li>
                <li class="list-group-item">Projektbetreuer: M.Sc. David Broneske</li>
                <li class="list-group-item">Projektzeitraum: 11.11.13 - 24.03.14</li>
            </ul>
            <img src="images/logo.png">
        </div>
    </div>

</div>
<!-- /container -->
<!-- Bootstrap core JavaScript
================================================== -->
<script src="js/jquery.js"></script>
<script src="js/bootstrap.js"></script>

</body>
</html>