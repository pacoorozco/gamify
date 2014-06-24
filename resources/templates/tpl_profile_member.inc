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
 * @package    Member Profile
 * @author     Paco Orozco <paco_@_pacoorozco.info> 
 * @license    http://www.gnu.org/licenses/gpl-2.0.html (GPL v2)
 * @link       https://github.com/pacoorozco/gamify
 */
?>

<div class="row" style="margin-top:50px;">
    <div class="col-md-7">
        <div class="row">
            <div class="col-md-4">
                <img src="<?= $row['profile_image']; ?>" class="img-thumbnail" id="profileImage">
                <?php
                if ($userId == $session->get('member.id')) {
                    // L'usuari por editar la seva imatge.
                    ?>
                    <p class="text-center">
                        <a href="#" id="uploadFile" title="Upload">
                            <span class="glyphicon glyphicon-open"></span> Canviar imatge
                        </a>
                    </p>
                    <p id="messageBox"></p>
                    <script>
                        var uploadURL = "<?= $_SERVER['PHP_SELF']; ?>?a=upload";
                        head(function () {
                            $(document).ready(function () {
                                $('a#uploadFile').file();
                                $('input#uploadFile').file().choose(function (e, input) {
                                    input.upload(uploadURL, function (res) {
                                        if (res=="ERROR") {
                                            $('p#messageBox').attr("class","text-danger");
                                            $('p#messageBox').html("Invalid extension !");
                                        } else {
                                             $('img#profileImage').attr("src",res);
                                            $('input#profileImageFile').val(res);
                                            $(this).remove();
                                        }
                                    }, '');
                                } );
                            } );
                        } );
                    </script>
                    <?php
                }
                ?>
            </div>
            <div class="col-md-8">
                <p class="h1"><?php echo $row['username']; ?></p>
                <p class="lead"><?php echo $row['level_name']; ?></p>
                <p class="small">
                    Darrera connexió el <?= strftime('%A %d de %B', strtotime($row['last_access'])); ?>
                </p>
            </div>
        </div>
        <h3>Activitat <small>darrers 10 events</small></h3>
        <?= implode(PHP_EOL, $htmlEventsCode); ?>
    </div>

    <div class="col-md-offset-1 col-md-4">
        <h3>Experiència</h3>
        <div class="media">
            <img src="<?= getLevelImageById($row['level_id']); ?>" width="100" alt="<?= $row['level_name']; ?>" class="img-thumbnail media-object pull-left">
            <div class="media-body">
                <p class="lead media-heading"><?= $row['level_name']; ?></p>
                <p>Nivell següent</p>
                <div class="progress">
                    <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="<?= $row['total_points']; ?>" aria-valuemin="0" aria-valuemax="<?= $row2['experience_needed']; ?>" style="width: <?= $levelper; ?>%">
                        <span><?= $row['total_points'] . '/' . $row2['experience_needed']; ?></span>
                    </div>
                </div>
            </div>
        </div>
        <h3>Insígnies (<?= $badges; ?>)</h3>'
        <?= implode(PHP_EOL, $htmlBadgesCode); ?>
    </div>
</div>