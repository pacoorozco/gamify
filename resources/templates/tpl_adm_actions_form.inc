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
 * @package    Admin Actions
 * @author     Paco Orozco <paco_@_pacoorozco.info> 
 * @license    http://www.gnu.org/licenses/gpl-2.0.html (GPL v2)
 * @link       https://github.com/pacoorozco/gamify
 */
?>
<div class = "panel panel-default">
    <div class = "panel-body">
        <div class = "row">
            <div class = "col-md-6">
                <h2>Donar experiència</h2>
                <p><?= getHTMLMessages($msg); ?></p>
                <form action="admin.php" method="post" class="form-horizontal" role="form">
                    <div class="form-group">
                        <label for="username" class="col-sm-2 control-label">Usuari</label>
                        <div class="col-sm-10">
                            <select data-placeholder="escull un usuari..." name="item" id="username" class="form-control chosen-select">
                                <option value=""></option>
                                <?= implode(PHP_EOL, $htmlUsersCode); ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="experience" class="col-sm-2 control-label">Experiència</label>
                        <div class="col-sm-10">
                            <select name="experience" id="experience" class="form-control">
                                <option value="1">1 punt</value>
                                <option value="5">5 punts</value>
                                <option value="10">10 punts</value>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="memo" class="col-sm-2 control-label">Motiu</label>
                        <div class="col-sm-10">
                            <input type="text" name="memo" id="memo" class="form-control" placeholder="per què rep els punts? (opcional)">
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-sm-offset-2 col-sm-10">
                            <input type="hidden" id="a" name="a" value="giveexperience">
                            <button type="submit" class="btn btn-default">Donar experiència</button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="col-md-6">
                <h2>Donar insígnies</h2>
                <form action="admin.php" method="post" class="form-horizontal" role="form">
                    <div class="form-group">
                        <label for="username" class="col-sm-2 control-label">Usuari</label>
                        <div class="col-sm-10">
                            <select data-placeholder="escull un usuari..." name="item" id="username" class="form-control chosen-select">
                                <option value=""></option>
                                <?= implode(PHP_EOL, $htmlUsersCode); ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="badge" class="col-sm-2 control-label">Assoliment</label>
                        <div class="col-sm-10">
                            <select data-placeholder="escull un badge..." name="achievement" id="badge" class="form-control chosen-select">
                                <option value=""></option>
                                <?= implode(PHP_EOL, $htmlBadgesCode); ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-offset-2 col-sm-10">
                            <input type="hidden" id="amount" name="amount" value="1">
                            <input type="hidden" id="a" name="a" value="givebadge">
                            <button type="submit" class="btn btn-default">Executar acció</button>
                        </div>
                    </div>

            </div>
        </div>
    </div>
</div>