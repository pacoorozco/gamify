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
 * @package    Quiz Answer Question
 * @author     Paco Orozco <paco_@_pacoorozco.info> 
 * @license    http://www.gnu.org/licenses/gpl-2.0.html (GPL v2)
 * @link       https://github.com/pacoorozco/gamify
 */
?>
<p><?= getHTMLMessages($msg); ?></p>
<div class="panel panel-default" width="70%">
    <div class="panel-heading">
        <h2><?= $question['name']; ?></h2>
    </div>
    <div class="panel-body">
        <img src="<?= $question['image']; ?>" width="120" class="img-rounded">
        <h4><?= $question['question']; ?></h4>
        <form action="<?= $_SERVER['PHP_SELF']; ?>" method="post" role="form">
            <ul class="list-group">
                <?= implode(PHP_EOL, $htmlCode); ?>
            </ul>
            <a href="//kbtic.upcnet.es/search?SearchableText=<?= $question['tip']; ?>" title="Buscar la resposta a la KBTic" class="btn btn-default" target="_blank"role="button"><span class="glyphicon glyphicon-new-window"></span> Ho buscaré a la KBTic</a>
            <a href="//www.google.es/search?q=<?= $question['tip']; ?>" title="Buscar la resposta a Google" class="btn btn-default" target="_blank" role="button"><span class="glyphicon glyphicon-new-window"></span> Ho buscaré a Google</a>
            <input type="hidden" name="item" value="<?= $questionUUID; ?>">
            <input type="hidden" name="a" value="answer">
            <button type="submit" class="btn btn-success pull-right"><span class="glyphicon glyphicon-save"></span> Guardar resposta</button>
        </form>
    </div>
</div>

