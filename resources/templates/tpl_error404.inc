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
 * @package    Error404
 * @author     Paco Orozco <paco_@_pacoorozco.info> 
 * @license    http://www.gnu.org/licenses/gpl-2.0.html (GPL v2)
 * @link       https://github.com/pacoorozco/gamify
 */
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <base href="<?= getBaseUrl(); ?>">
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="GoW - Game of Work és una plataforma de gamificació d'UPCnet">
        <meta name="author" content="Paco Orozco">
        <link rel="shortcut icon" href="images/favicon.ico">

        <title>GoW - Game of Work! - error 404</title>

        <!-- Bootstrap core CSS -->
        <link href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css" rel="stylesheet">

    </head>

    <body>
        <div class="container">
            <div class="header">
                <ul class="nav nav-pills pull-right">
                </ul>
                <h2 class="text-muted">#error 404</h2>
            </div>

            <div class="jumbotron">
                <h1>Ooooops, tenim un problema!</h1>
                <div class="row">
                    <div class="col-md-8">
                        <h2>Sembla que a Chuck Norris no li va agradar aquesta adreça.</h2>
                        <p class="lead">De totes formes, abans de discutir amb ell la conveniència o no de la seva el·liminació, comprova que has posat correctament l'adreça.</p>
                        <p><a href="index.php" class="btn btn-primary btn-lg">
                                <span class="glyphicon glyphicon-home"></span> Porta'm a l'inici
                            </a></p>
                    </div>
                    <div class="col-md-4">
                        <img class="img-rounded" alt="Chuck Norris" src="images/chuck_norris.jpg">
                    </div>
                </div>

            </div>
        </div> <!-- /container -->
    </body>
</html>
