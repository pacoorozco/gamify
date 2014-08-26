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
 * @package    Login
 * @author     Paco Orozco <paco_@_pacoorozco.info> 
 * @license    http://www.gnu.org/licenses/gpl-2.0.html (GPL v2)
 * @link       https://github.com/pacoorozco/gamify
 */
?>

<div id="loginbox" style="margin-top:50px;" class="mainbox col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2">
    <div class="panel panel-info">
        <div class="panel-heading">
            <div class="panel-title">Accedir</div>
            <div style="float:right; position: relative; top:-10px"><a href="http://www.upcnet.es/CanviContrasenyaUPC" target="_blank">Has oblidat la contrasenya?</a></div>
        </div>

        <div style="padding-top:30px" class="panel-body" >

            <p><?= getHTMLMessages($missatges); ?></p>

            <form action="<?= $_SERVER['PHP_SELF']; ?>" method="post" class="form-horizontal" role="form">
                <div style="margin-bottom: 25px" class="input-group">
                    <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
                    <input type="text" name="username" class="form-control" value="<?= $username; ?>" placeholder="<?= $usertext; ?>" required>
                </div>

                <div style="margin-bottom: 25px" class="input-group">
                    <span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
                    <input type="password" name="password" class="form-control" placeholder="contrasenya" autocomplete="off" required>
                </div>

                <div style="margin-top:10px" class="form-group">
                    <div class="col-md-12">
                        <input type="hidden" id="a" name="a" value="login">
                        <input type="hidden" name="nav" value="<?= $nav; ?>">
                        <button type="submit" class="btn btn-success"><span class="glyphicon glyphicon-log-in"></span> <?= $logintext; ?></button>
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-md-12 control">
                        <div style="border-top: 1px solid#888; padding-top:15px;">
                            No has accedit mai?
                            <a href="<?= $_SERVER['PHP_SELF']; ?>?a=register">
                                Registra't ara!
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>