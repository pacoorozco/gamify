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
 * @package    Register
 * @author     Paco Orozco <paco_@_pacoorozco.info> 
 * @license    http://www.gnu.org/licenses/gpl-2.0.html (GPL v2)
 * @link       https://github.com/pacoorozco/gamify
 */
?>
<div id="signupbox" style="margin-top:50px;" class="mainbox col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2">
    <div class="panel panel-info">
        <div class="panel-heading">
            <div class="panel-title">Registre</div>
            <div style="float:right; position: relative; top:-10px">
                <a href="<?= $_SERVER['PHP_SELF']; ?>">Ja tens usuari? Accedeix!</a>
            </div>
        </div>
        <div class="panel-body">
            <form action="<?= $_SERVER['PHP_SELF']; ?>" method="post" class="form-horizontal" role="form">
                <p><?= getHTMLMessages($missatges); ?></p>
                <div style="margin-bottom: 25px" class="input-group">
                    <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
                    <input type="text" name="username" class="form-control" placeholder="<?= $usertext; ?>" required>
                </div>
                <div style="margin-bottom: 25px" class="input-group">
                    <span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
                    <input type="password" name="password" class="form-control" placeholder="contrasenya" autocomplete="off" required>
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
