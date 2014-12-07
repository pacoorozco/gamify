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
 * @package    Home for members
 * @author     Paco Orozco <paco_@_pacoorozco.info> 
 * @license    http://www.gnu.org/licenses/gpl-2.0.html (GPL v2)
 * @link       https://github.com/pacoorozco/gamify
 */
?>
<div class="row">

    <div class="col-md-6">
        <h1>Juguem?</h1>
        <p class="lead">Benvinguts a <strong>GoW - Game of Work!</strong></p>
        
        <ul class="nav nav-pills" role="tablist">
                <li class="active"><a href="#desafiament" role="tab" data-toggle="tab">Desafiament</a></li>
                <li><a href="#newrelic" role="tab" data-toggle="tab">Data Nerds</a></li>
                <li><a href="#gow50" role="tab" data-toggle="tab">50 preguntes GoW!</a></li>
                <li><a href="#gow" role="tab" data-toggle="tab">Qu&egrave; &eacute;s GoW!?</a></li>
        </ul>

        <p>&nbsp;</p>

        <div class="tab-content">

                <div class="tab-pane active" id="desafiament">
			<p class="lead">El primer Desafiament ha finalitzat</p>
                        <p>Tal i com us vam explicar a <a href="https://gow.upcnet.es//quiz.php?a=answerqz&item=8797c6c4-8873-488e-b42f-a7ed80295134">aquesta pregunta del desafiament</a> tots els que l'heu completat heu arribat als <b>550 punts d'experiència</b>. Felicitats.</p>
                        <p>Esperem que hagueu gaudit amb les preguntes i recordeu que <a href="https://gow.upcnet.es//quiz.php?a=answerqz&item=9c15e078-6bde-442b-94ba-3fba92e81a26">podeu dir-nos que us ha semblat</a>!</p>
                        <p><small><b>Atenció</b>: Totes les preguntes que no havieu respost amb anterioritat al desafiament, ara ja no estan al vostre abast.</small></p>
			<img src="uploads/badges/curious_pirate.png" class="img-responsive" />
                </div>

                <div class="tab-pane" id="newrelic">
			<p class="lead">Felicitats</p>
			<p>Aquests són els nostres <i>Data Nerds</i> que han aconseguit el premi especial a l'ésser els 4 més ràpids en respondre <a href="https://gow.upcnet.es/quiz.php?a=answerqz&item=280be6d8-0d4e-4cd4-843c-a5090be557e5">aquesta pregunta de GoW!</a></p>
			<img src="uploads/questions/Guanyadors_New_Relic_rs.jpeg" class="img-responsive" />
                </div>
                <div class="tab-pane" id="gow50">
                        <div class="video-container">
                                <iframe width="320" height="240" src="//www.youtube.com/embed/UpLxb4ZUg9A" allowfullscreen></iframe>
                        </div>
                </div>
                <div class="tab-pane" id="gow">
                        <div class="video-container"> 
                                <iframe width="320" height="240" src="//www.youtube.com/embed/eH2A0k1um3A" allowfullscreen></iframe>
                        </div>
                </div>
        </div>


        <h1>Novetats</h1>
        <ul>
		<li><p class="lead">El desafiament de GoW! <a href="https://gow.upcnet.es//quiz.php?a=answerqz&item=92554bf8-9315-46b8-911a-6b4b60a0327b" target="_blank">"Qu&egrave; &eacute;s?"</a></p></li>
	        <li><p class="lead">El GoW! al <a href="http://blog.upcnet.es/ludificacio-gow" target="_blank">blog d'UPCnet</a></p></li>
        	<li><p class="lead">Hem fet les <a href="http://bit.ly/gow-50" target="_blank">50 preguntes a GoW!</a>, fes clic <a  href="http://bit.ly/gow-50" target="_blank">aquí</a></p></li>
	</ul>

        <h1>Contacta'ns</h1>
        <p class="lead">Tens algun suggeriment? Has trobat un error?</p>
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
            <li><a href="#top10" data-toggle="tab">10 primers</a></li>
        </ul>

        <div class="tab-content">
            <!-- month top -->
            <div class="table-responsive tab-pane fade in active" id="month_top">
                <table class="table table-hover" >
                    <thead>
                        <tr>
                            <th class="text-center">Posició</th>
                            <th>Nom</th>
                            <th>Experiència</th>
                            <th>Nivell</th>
                            <th class="text-center">Insígnies</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?= implode(PHP_EOL, $htmlMonthTop); ?>
                    </tbody>
                </table>
            </div>

            <!-- top -->
            <div class="table-responsive tab-pane fade active" id="top">
                <table class="table table-hover" >
                    <thead>
                        <tr>
                            <th class="text-center">Posició</th>
                            <th>Nom</th>
                            <th>Experiència</th>
                            <th>Nivell</th>
                            <th class="text-center">Insígnies</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?= implode(PHP_EOL, $htmlTop); ?>
                    </tbody>
                </table>
            </div>

            <!-- top10 -->
            <div class="table-responsive tab-pane fade active" id="top10">
                <table class="table table-hover" >
                    <thead>
                        <tr>
                            <th class="text-center">Posició</th>
                            <th>Nom</th>
                            <th>Experiència</th>
                            <th>Nivell</th>
                            <th class="text-center">Insígnies</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?= implode(PHP_EOL, $htmlTop10); ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>
