<?php
/*
 * @author Paco Orozco, paco.orozco -at- upcnet.es
 * @version $Id: index.php 47 2014-04-12 06:03:33Z paco $
 *
 */

define('IN_SCRIPT',1);
require_once('inc/functions.inc.php');

require_once('inc/header.inc.php');

if ( true === loginCheck() ) { ?>

<div class="row">

    <div class="col-md-6">
        <h1>Juguem?</h1>
        <p class="lead">Benvinguts a <strong>GoW - Game of Work!</strong></p>
        <div class="video-container">
        <iframe width="320" height="240" src="//www.youtube.com/embed/eH2A0k1um3A" frameborder="0" allowfullscreen></iframe>
        </div>
        
        <h1>Contacta'ns</h1>
        <p class="lead">Tens alguna suggerència? Has trobat un error?</p>
        <p>Escriu-nos a:</p>
        <ul>
            <li>Emilio Ampudia (emilio.ampudia@upcnet.es)</li>
            <li>Paco Orozco (paco.orozco@upcnet.es)</li>
        </ul>

    </div>

        <?php
        // Get all levels from table and create an array
        $query = "SELECT id, name FROM levels";
        $result = $db->query($query);
        $levels = array();
        while ($row = $result->fetch_assoc()) {
            $levels[$row['id']] = $row['name'];
        }
        ?>

    <div class="col-md-6">

        <h1>Cerca un usuari</h1>
        <form method="get" action="#" role="form">
            <div class="input-group custom-search-form">
                <input type="text" name="q" id="live-search" class="form-control" placeholder="Cerca usuari">
                <input type="hidden" name="a" value="search">
                <span class="input-group-btn">
                    <button class="btn btn-default" type="button">
                        <span class="glyphicon glyphicon-search"></span>
                    </button>
                </span>
             </div>
        </form>

        <h1>Hall of fame</h1>

        <ul class="nav nav-tabs">
            <li class="active"><a href="#month_top" data-toggle="tab">Darrer mes</a></li>
            <li><a href="#top" data-toggle="tab">Total</a></li>
        </ul>

        <div class="tab-content">
            <!-- month top -->
            <div class="table-responsive tab-pane fade in active" id="month_top">
            <?php

            $query = "SELECT t1.uuid, t1.id, t1.username, t2.points, t1.level_id, (SELECT COUNT(id) FROM members_badges WHERE t1.id = members_badges.id_member AND members_badges.status = 'completed') AS badges FROM vmembers AS t1, vtop_month AS t2 WHERE t1.id = t2.id  ORDER BY points DESC, badges DESC, username ASC ";
            $result = $db->query($query);

            require('inc/hall_of_fame.inc.php');



            ?>
            </div>

            <!-- top -->
            <div class="table-responsive tab-pane fade active" id="top">
            <?php

            $query = "SELECT t1.uuid, t1.id, t1.username, t2.points, t1.level_id, (SELECT COUNT(id) FROM members_badges WHERE t1.id = members_badges.id_member AND members_badges.status = 'completed') AS badges FROM vmembers AS t1, vtop AS t2 WHERE t1.id = t2.id  ORDER BY points DESC, badges DESC, username ASC ";
            $result = $db->query($query);

            require('inc/hall_of_fame.inc.php');
            ?>
            </div>

        </div>


    </div>
</div>

<?php } else { ?>

<div class="row">

    <div class="col-md-6">
        <h1>Benvinguts!</h1>
<p class="text-justify">Una de les tendències actuals és la <a href="http://es.wikipedia.org/wiki/Ludificaci%C3%B3n" target="_blank">gamificació</a>, l'ús de mecàniques de joc en entorns i aplicacions no lúdiques amb la finalitat de potenciar la motivació, la concentració, l'esforç, la fidelització i altres valors positius comuns entre els jocs.</p>

<blockquote>
  <p>El trabajo es todo lo que se está obligado a hacer; el juego es lo que se hace sin estar obligado a ello.</p>
  <footer>Mark Twain</footer>
</blockquote>

<p class="text-justify">Per aquest motiu hem creat <strong><abbr title="Game of Work">GoW!</abbr></strong>, una plataforma de gamificació a UPCnet amb la que us volem convidar a jugar tot aprenent i descobrint els desafiaments proposats.<p>

<p class="lead text-jusfity">El primer que et proposem és <a href="#" onClick="$('#loginbox').hide(); $('#signupbox').show()">registrar-te</a>. Així que no perdis més temps, comença a jugar amb nosaltres.<p>

    </div>

    <div class="col-md-6">
        <h1>Juguem?</h1>
        <div id="loginbox">
            <div class="panel panel-info">
                    <div class="panel-heading">
                        <div class="panel-title">Accedir</div>
                        <div style="float:right; position: relative; top:-10px"><a href="http://www.upcnet.es/CanviContrasenyaUPC" target="_blank">Has oblidat la contrasenya?</a></div>
                    </div>

                    <div style="padding-top:30px" class="panel-body" >

                                <?php
                                $usertext = 'usuari';
                                $logintext = 'Accedir';

                                if ('LDAP' == $CONFIG['authentication']['type']) {
                                    $usertext = 'usuari LDAP';
                                    $logintext = 'Accedir amb LDAP';
                                }
                                ?>

                        <form action="login.php" method="post" class="form-horizontal" role="form">

                            <div style="margin-bottom: 25px" class="input-group">
                                <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
                                <input type="text" name="username" class="form-control" placeholder="<?= $usertext; ?>" required>
                            </div>

                            <div style="margin-bottom: 25px" class="input-group">
                                <span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
                                <input type="password" name="password" class="form-control" placeholder="contrasenya" required>
                            </div>

                            <div style="margin-top:10px" class="form-group">
                                <div class="col-md-12">
                                    <input type="hidden" id="a" name="a" value="login">
                                    <button type="submit" class="btn btn-success"><span class="glyphicon glyphicon-log-in"></span> <?= $logintext; ?></button>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-md-12 control">
                                    <div style="border-top: 1px solid#888; padding-top:15px;">
                                        No has accedit mai?
                                        <a href="#" onClick="$('#loginbox').hide(); $('#signupbox').show()">
                                            Registra't ara!
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
            </div>
        </div>
        <div id="signupbox" style="display:none;">
            <div class="panel panel-info">
                <div class="panel-heading">
                    <div class="panel-title">Registre</div>
                    <div style="float:right; position: relative; top:-10px">
                        <a href="#" onclick="$('#signupbox').hide(); $('#loginbox').show()">Ja tens usuari? Accedeix!</a>
                    </div>
                </div>
                <div class="panel-body">
                    <form action="login.php" method="post" class="form-horizontal" role="form">

                        <div id="signupalert" style="display:none" class="alert alert-danger">
                            <p>Error:</p>
                            <span></span>
                        </div>
                        
                                <?php
                                $usertext = 'usuari';

                                if ('LDAP' == $CONFIG['authentication']['type']) {
                                    $usertext = 'usuari LDAP';
                                }
                                ?>                        

                        <div class="form-group">
                            <label for="username" class="col-md-3 control-label">Usuari</label>
                            <div class="col-md-9">
                                <input type="text" name="username" class="form-control" placeholder="<?= $usertext; ?>" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="password" class="col-md-3 control-label">Contrasenya</label>
                            <div class="col-md-9">
                                <input type="password" class="form-control" name="password" placeholder="contrasenya" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="email" class="col-md-3 control-label">Adreça</label>
                            <div class="col-md-9">
                                <input type="text" name="email" id="email" class="form-control" placeholder="adreça electrónica" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-md-offset-3 col-md-9">
                                <input type="hidden" id="a" name="a" value="do_register">
                                <button type="submit" class="btn btn-info"><span class="glyphicon glyphicon-hand-right"></span> &nbsp Registrar</button>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
         </div>
        </div>
    </div>

<?php
}

require_once('inc/footer.inc.php');
?>
