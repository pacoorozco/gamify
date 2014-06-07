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

$hiddenFields = array();

if ('edit' == $form) {
    $title = 'Editar insígnia';
    $hiddenFields['item'] = $data['id'];
    $hiddenFields['a'] = 'savebadge';
} else {
    $title = 'Nova insígnia';
    $hiddenFields['a'] = 'createbadge';
}

// Prepare $data before HTML, if a value doesn't exist give a defaults one
$required = array('name', 'image', 'description', 'amount_needed');
$data = getVarDefaults($data, $required);
?>
<h1><?= $title; ?></h1>
<p><?= getHTMLMessages($msg); ?></p>
<form action="admin.php" method="post" class="form-horizontal" role="form">
    <div class="form-group">
        <label for="achievementname" class="col-sm-2 control-label">Nom de la insígnia</label>
        <div class="col-sm-10">
            <input type="text" name="name" id="achievementname" class="form-control" placeholder="Nom de la insígnia" value="<?= $data['name']; ?>" required>
        </div>
    </div>
    <div class="form-group">
        <label for="badgesrc" class="col-sm-2 control-label">Imatge</label>
        <div class="col-sm-10">
            <input type="text" name="image" id="badgesrc" class="form-control" placeholder="Imatge de la insígnia" value="<?= $data['image']; ?>" required>
        </div>
    </div>
    <div class="form-group">
        <label for="description" class="col-sm-2 control-label">Descripció</label>
        <div class="col-sm-10">
            <textarea name="description" id="description" class="form-control" rows="3" placeholder="Descripció de la insígnia" required><?= $data['description']; ?></textarea>
        </div>
    </div>
    <div class="form-group">
        <label for="amount" class="col-sm-2 control-label">Quantitat necessària</label>
        <div class="col-sm-10">
            <input type="text" name="amount_needed" id="amount" class="form-control" placeholder="Número de vegades per aconseguir-la" value="<?= $data['amount_needed']; ?>" required="">
        </div>
    </div>
    <div class="form-group">
        <div class="col-sm-offset-2 col-sm-10">
    <?php
    foreach ($hiddenFields as $name => $value) {
        echo '<input type="hidden" id="' . $name . '" name="'
            . $name . '" value="'. $value . '">' . PHP_EOL;
    }
    ?>
            <button type="submit" class="btn btn-success"><span class="glyphicon glyphicon-save"></span> Guardar dades</button>
            <a href="admin.php?a=badges" class="btn btn-danger" role="button"><span class="glyphicon glyphicon-retweet"></span> Tornar</a>
        </div>
    </div>
</form>
                        