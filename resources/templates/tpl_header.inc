<?php
/**
 * This file is part of gamify project.
 * Copyright (C) 2014  Paco Orozco <paco_@_pacoorozco.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 * 
 * @category   Pakus
 * @package    Header
 * @author     Paco Orozco <paco_@_pacoorozco.info> 
 * @license    http://www.gnu.org/licenses/gpl-2.0.html (GPL v2)
 * @link       https://github.com/pacoorozco/gamify
 */
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <base href="<?= getBaseUrl(); ?>">
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
    <!-- DataTables plugin CSS and theme for bootstrap -->
    <link href="//cdn.datatables.net/plug-ins/28e7751dbec/integration/bootstrap/3/dataTables.bootstrap.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="css/style.css" rel="stylesheet">

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="//oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="//oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

    <!-- Load head.js for improved JS loading -->
    <script src="js/head.min.js"></script>
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
                <li><a href="index.php" title="Inici"><span class="glyphicon glyphicon-home"></span> Inici</a></li>
                <?php
                if ( true === userIsLoggedIn() ) {
                ?>
                <li><a href="quiz.php" title="Participa amb nosaltres"><span class="glyphicon glyphicon-question-sign"></span> Participa <span class="badge"><?= getPendingQuizs($_SESSION['member']['id']); ?></span></a></li>
                <?php
                if (userHasPrivileges($_SESSION['member']['id'])) {
                    echo '<li><a href="admin.php" title="Panell d\'administració"><span class="glyphicon glyphicon-tasks"></span> Administra</a></li>';
                }
                }
                ?>
            </ul>
            <?php
            if ( true === userIsLoggedIn() ) {
                ?>
        <ul class="nav navbar-nav navbar-right">
        <li class="dropdown"><a href="#" class="dropdown-toggle" data-toggle="dropdown"><span class="glyphicon glyphicon-user"></span> <?php echo $_SESSION['member']['username']; ?>
                <b class="caret"></b></a>
            <ul class="dropdown-menu">
                <li>
                    <div class="navbar-content">
                        <div class="row">
                            <div class="col-md-5">
                                <?php
                                $image = (empty($_SESSION['member']['profile_image']))
                                        ? 'images/default_profile_pic.png'
                                        : $_SESSION['member']['profile_image'];
                                ?>
                                <img src="<?= $image; ?>" alt="<?= $_SESSION['member']['username']; ?>" class="img-thumbnail img-responsive">
                                <p class="small"></p>
                            </div>
                            <div class="col-md-7">
                                <span><?= $_SESSION['member']['username']; ?>
                                    <a href="https://bit.ly/1rwwkFN"><span class="glyphicon glyphicon-heart"></span></a>
                                </span>
                                <p class="text-muted small"><?php echo $_SESSION['member']['email']; ?></p>
                                <div class="divider"></div>
                                <a href="member.php" title="El meu compte" class="btn btn-primary btn-sm"><span class="glyphicon glyphicon-user"></span> El meu compte</a>
                            </div>
                        </div>
                    </div>
                    <div class="navbar-footer">
                        <div class="navbar-footer-content">
                            <div class="row">
                                <div class="col-md-6"></div>
                                <div class="col-md-6">
                                    <a href="login.php?a=logout" title="Sortir" class="btn btn-danger btn-sm pull-right"><span class="glyphicon glyphicon-log-out"></span> Sortir</a>
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
