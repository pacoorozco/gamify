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
                    "SELECT * FROM vmembers "
                    . "ORDER BY month_points DESC, badges DESC, username ASC",
                    'month_points'
                )
            );
            ?>
            </div>

            <!-- top -->
            <div class="table-responsive tab-pane fade active" id="top">
            <?php
            printHTMLRankingTable(
                $db->getAll(
                    "SELECT * FROM vmembers "
                    . "ORDER BY total_points DESC, badges DESC, username ASC",
                    'total_points'
                )
            );
            ?>
            </div>
        </div>
    </div>
</div>
