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
 * @package    Home
 * @author     Paco Orozco <paco_@_pacoorozco.info> 
 * @license    http://www.gnu.org/licenses/gpl-2.0.html (GPL v2)
 * @link       https://github.com/pacoorozco/gamify
 */

require_once realpath(dirname(__FILE__) . '/../resources/lib/Bootstrap.class.inc');
\Pakus\Application\Bootstrap::init(APP_BOOTSTRAP_FULL);

require_once TEMPLATES_PATH . '/tpl_header.inc';

if (userIsLoggedIn()) {
    ?>

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
            printHTMLRankingTable(
                $db->getAll(
                    "SELECT t1.uuid, t1.id, t1.username, t2.points, t1.level_id, "
                    . "(SELECT COUNT(id) FROM members_badges AS t3 "
                    . "WHERE t1.id = t3.id_member AND t3.status = 'completed') AS badges "
                    . "FROM vmembers AS t1, vtop_month AS t2 "
                    . "WHERE t1.id = t2.id AND t1.role = 'member' "
                    . "ORDER BY points DESC, badges DESC, username ASC"
                )
            );
            ?>
            </div>

            <!-- top -->
            <div class="table-responsive tab-pane fade active" id="top">
            <?php
            printHTMLRankingTable(
                $db->getAll(
                    "SELECT t1.uuid, t1.id, t1.username, t2.points, t1.level_id, "
                    . "(SELECT COUNT(id) FROM members_badges AS t3 "
                    . "WHERE t1.id = t3.id_member AND t3.status = 'completed') AS badges "
                    . "FROM vmembers AS t1, vtop AS t2 "
                    . "WHERE t1.id = t2.id AND t1.role = 'member' "
                    . "ORDER BY points DESC, badges DESC, username ASC"
                )
            );
            ?>
            </div>
        </div>
    </div>
    </div>
    <?php
} else {
    ?>
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
                        <div style="margin-bottom: 25px" class="input-group">
                            <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
                            <input type="text" name="username" class="form-control" placeholder="<?= $usertext; ?>" required>
                        </div>
                        <div style="margin-bottom: 25px" class="input-group">
                            <span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
                            <input type="password" name="password" class="form-control" placeholder="contrasenya" required>
                        </div>
                        <div style="margin-bottom: 25px" class="input-group">
                            <span class="input-group-addon"><i class="glyphicon glyphicon-envelope"></i></span>
                            <input type="text" name="email" id="email" class="form-control" placeholder="adreça correu electrònic" required>
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

require_once TEMPLATES_PATH . '/tpl_footer.inc';
exit();

/*** FUNCTIONS ***/
function printHTMLRankingTable($users = array())
{
    global $db;
    
    $htmlCode = array();
    $top3 = 3;
    $top10 = 10;
    
    // Get all levels from table and create an array
    $levels = $db->getAssoc(
        "SELECT id, name FROM levels"
    );
    ?>
    <table class="table table-hover" >
    <thead>
        <tr>
            <th class="text-center">Posició</th>
            <th>Usuari</th>
            <th>Experiència</th>
            <th>Nivell</th>
            <th class="text-center">Insígnies</th>
        </tr>
    </thead>
    <tbody>
    <?php
    $position = 1;
    $toprest = $top10 - $top3;
        
    foreach ($users as $row) {
        if ($row['username'] == $_SESSION['member']['username']) {
            $currentuser = "class='info'";
            $currentranking = $position;
        } else {
            $currentuser = "";
        }
        $htmlCode[] = '<tr ' . $currentuser . '>';
        if ($position <= $top3) {
            $htmlCode[] = '<td class="text-center" style=" vertical-align: middle;">';
            $htmlCode[] = '<span class="badge alert-warning">';
            $htmlCode[] = sprintf(
                '<h%d>&nbsp; %d &nbsp;</h%d>',
                ($position + 2),
                $position,
                ($position + 2)
            );
            $htmlCode[] = '</span>';
            $htmlCode[] = '</td>';
        } else {
            $htmlCode[] = '<td class="text-center"  style=" vertical-align: middle;">' . $position . '</td>';
        }
        $htmlCode[] = '<td style=" vertical-align: middle;">';
        $htmlCode[] = '<a href="member.php?a=viewuser&item=' . $row['uuid'] . '">' . $row['username'] . '</a>';
        $htmlCode[] = '</td>';
        $htmlCode[] = '<td style=" vertical-align: middle;">' . $row['points'] . '</td>';
        $htmlCode[] = '<td style=" vertical-align: middle;">' . $levels[$row['level_id']] . '</td>';
        $badges = ($row['badges'] > 0) ? '<span class="badge">' . $row['badges'] . '</span>' : '';
        $htmlCode[] = '<td style=" vertical-align: middle;" class="text-center">' . $badges . '</td>';
        $htmlCode[] = '</tr>';
            
        $ranking[$position] = implode(PHP_EOL, $htmlCode);
        unset($htmlCode);
        $position++;
    }

    if ($currentranking <= $top3) {
        for ($i = 1; $i <= $top10; $i++) {
            echo $ranking[$i];
        }
    } else {
        for ($i = 1; $i <= $top3; $i++) {
            echo $ranking[$i];
        }

        echo '<tr><td colspan="5" class="text-center">...</td></tr>';
        
        if ($currentranking + $toprest < $position) {
            $init = $currentranking - 1;
            $end = $currentranking+$toprest-2;
        } else {
            $init = $position-$toprest;
            $end = $position;
        }
        for ($i = $init; $i <= $end; $i++) {
            echo $ranking[$i];
        }
    }
    ?>
        </tbody>
    </table>
    <?php
}
