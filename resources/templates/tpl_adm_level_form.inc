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
 * @package    Admin Edit Badge
 * @author     Paco Orozco <paco_@_pacoorozco.info> 
 * @license    http://www.gnu.org/licenses/gpl-2.0.html (GPL v2)
 * @link       https://github.com/pacoorozco/gamify
 */
?>
<h1><?= $title; ?></h1>
<p><?= getHTMLMessages($msg); ?></p>
<form action="admin.php" method="post" class="form-horizontal" role="form">
    <div class="form-group">
        <label for="levelname" class="col-sm-2 control-label">Nom</label>
        <div class="col-sm-10">
            <input type="text" name="name" id="levelname" class="form-control" placeholder="Nom del nivell" value="<?= $data['name']; ?>" required>
        </div>
    </div>
    <div class="form-group">
        <label for="levelsrc" class="col-sm-2 control-label">Imatge</label>
        <div class="col-sm-10">
            <input type="text" name="image" id="levelsrc" class="form-control" placeholder="Imatge del nivell" value="<?= $data['image']; ?>" required>
        </div>
    </div>
    <div class="form-group">
        <label for="experience" class="col-sm-2 control-label">Experiència necessària</label>
        <div class="col-sm-10">
            <input type="text" name="experience_needed" id="experience" class="form-control" placeholder="Experiència necessària per aconseguir-ho" value="<?= $data['experience_needed']; ?>" required>
        </div>
    </div>
    <div class="form-group">
        <div class="col-sm-offset-2 col-sm-10">
            <?= implode(PHP_EOL, $htmlHiddenFields); ?>
            <button type="submit" class="btn btn-success"><span class="glyphicon glyphicon-save"></span> Guardar dades</button>
            <a href="admin.php?a=levels" class="btn btn-danger" role="button"><span class="glyphicon glyphicon-retweet"></span> Tornar</a>
        </div>
    </div>
</form>
