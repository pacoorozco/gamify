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
 * @package    Quiz List Questions
 * @author     Paco Orozco <paco_@_pacoorozco.info> 
 * @license    http://www.gnu.org/licenses/gpl-2.0.html (GPL v2)
 * @link       https://github.com/pacoorozco/gamify
 */
?>

<div class="panel panel-default" width="70%">
    <div class="panel-body">
        <div class="well">
            <h4>Estàs preparat per posar-te a prova?</h4>
            <p>Trobar&agrave;s preguntes sobre diferents temes de la nostra organització, la nostra metodologia, eines, procediments... qualsevol cosa que serà molt &uacute;til que coneguis.</p>
            <p>A més a més guanyaràs punts i insígnies per pujar de nivell, presumir amb els teus companys o simplement superar-te.</p>
        </div>
        <h4>Les teves preguntes pendents</h4>
        <?= implode(PHP_EOL, $htmlCode); ?>
    </div>
</div>
