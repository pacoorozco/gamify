<?php
/*
 * @author Paco Orozco, paco.orozco -at- upcnet.es
 * @version $Id: header.inc.php 64 2014-04-17 12:04:17Z paco $
 *
 */

/* Check if this is a valid include */
defined('IN_SCRIPT') or die('Invalid attempt');
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="GoW - Game of Work és una plataforma de gamificació d'UPCnet">
    <meta name="author" content="Paco Orozco">
    <link rel="shortcut icon" href="images/favicon.ico">

    <title>GoW - Game of Work!</title>

    <!-- Bootstrap core CSS -->
    <link href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css" rel="stylesheet">
    <!-- Chosen plugin CSS and theme for bootstrap -->
    <link href="js/chosen/chosen.min.css" rel="stylesheet">
    <link href="js/chosen/chosen-bootstrap.css" rel="stylesheet">
    

    <!-- Custom styles for this template -->
    <link href="css/style.css" rel="stylesheet">

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="//oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="//oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>

  <body>
      <div id="wrap">
          
          <!-- Fixed navbar -->          
    <div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="index.php"><img src="images/logo-gow.png" alt="GoW!"></a>
        </div>
        <div class="navbar-collapse collapse">
            <ul class="nav navbar-nav">
                <li><a href="index.php">Inici</a></li>
                <?php
                if( true === login_check() ) {
                ?>
                <li><a href="quiz.php">Participa <span class="badge"><?php echo get_pending_quizs($_SESSION['member']['id']); ?></span></a></li>
                
                <li><a href="admin.php">Administra</a></li>
                <?php
                }
                ?>
            </ul>
            <?php
            if( true === login_check() ) {
                ?>
        <ul class="nav navbar-nav navbar-right">
        <li class="dropdown"><a href="#" class="dropdown-toggle" data-toggle="dropdown"><?php echo $_SESSION['member']['username']; ?>
                <b class="caret"></b></a>
            <ul class="dropdown-menu">
                <li>
                    <div class="navbar-content">
                        <div class="row">
                            <div class="col-md-5">
                                <img src="//lh5.googleusercontent.com/-b0-k99FZlyE/AAAAAAAAAAI/AAAAAAAAAAA/twDq00QDud4/s120-c/photo.jpg" alt="<?php echo $_SESSION['member']['username']; ?>" class="img-responsive">
                                <p class="text-center small"><a href="#">Canviar imatge</a></p>
                            </div>
                            <div class="col-md-7">
                                <span><?php echo $_SESSION['member']['username']; ?></span>
                                <p class="text-muted small"><?php echo $_SESSION['member']['email']; ?></p>
                                <div class="divider"></div>
                                <a href="member.php?a=viewuser&item=<?php echo $_SESSION['member']['id']; ?>" class="btn btn-primary btn-sm active">El meu compte</a>
                            </div>
                        </div>
                    </div>
                    <div class="navbar-footer">
                        <div class="navbar-footer-content">
                            <div class="row">
                                <div class="col-md-6"></div>
                                <div class="col-md-6">
                                    <a href="login.php?a=logout" class="btn btn-danger btn-sm pull-right">Sortir</a>
                                </div>
                            </div>                            
                        </div>
                    </div>
                </li> 
            </ul>
        </li>
        </ul>
                <?php 
            }
            ?>
        </div><!--/.navbar-collapse -->
      </div>
    </div>
          
          <!-- Begin page content -->
          <div class="container">