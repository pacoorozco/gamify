<?php
/*
 * @author Paco Orozco, paco.orozco -at- upcnet.es
 * @version $Id$
 *
 */

define('IN_SCRIPT', 1);
require_once('inc/functions.inc.php');
require_once('inc/gamify.inc.php');

// Page only for members
if ( false === login_check() ) {
    // save referrer to $_SESSION['nav'] for after login redirect
    $_SESSION['nav'] = urlencode($_SERVER['SCRIPT_NAME'] . '?' . $_SERVER['QUERY_STRING']);
    header('Location: login.php');
    exit;
}

// Que hem de fer?
$action = pakus_REQUEST('a');

// There are some actions that doesn't need header / footer
switch ($action) {
    case 'search':
        // Que hem de buscar?
        $searchterm = pakus_GET('q');         
        echo get_search_results($searchterm);
        exit();
        break;
    
    case 'upload':
        echo upload_profile_picture();
        exit();
        break;
}

require_once('inc/header.inc.php'); 

switch ($action) {
    case 'viewuser':
    default:
        $user_id = pakus_REQUEST('item');
        print_profile($user_id);
}

    
require_once('inc/footer.inc.php');
exit();

/*** FUNCTIONS ***/
function get_search_results( $searchterm ) {
	global $db;

	$html_code = array();
        $html_code[] = '<ul class="list-unstyled list-group">';
        
        // Nomes farem cerques si busquen mes de tres caracters, aixo evita que sobrecarreguem la BDD
        if ( ! isset($searchterm[3]) ) {
            $html_code[] = '<li class="list-group-item list-group-item-info">Tecleja m&eacute;s de 3 car&agrave;cters per fer la cerca</li>';
        } else {       
            $query = sprintf("SELECT id, username FROM vmembers WHERE username LIKE '%%%s%%'", $db->real_escape_string($searchterm));
            $result = $db->query($query);
        
            if ( 0 == $result->num_rows  ) {
                // No s'ha trobat res
                $html_code[] = '<li class="list-group-item list-group-item-danger">No he trobat cap resultat</li>';
                
            } else {
                // Hem trobat informacio
                while ( $row = $result->fetch_assoc() ) {
                    $html_code[] = '<li><a href="member.php?a=viewuser&item=' . $row['id'] . '" title="Veure ' . $row['username'] . '" class="list-group-item"><span class="glyphicon glyphicon-user"></span> ' . $row['username'] . "</a></li>";
                }
            }
        }
        $html_code[] = '</ul>';
	return implode($html_code, PHP_EOL);
} // END get_search_results()

function print_profile($user_id) {
    global $db;
    
    // user_id must be integer
    $user_id = intval($user_id);
    
    // check if user to view profile is admin
    $admin = user_has_privileges($user_id, 'administrator');
    
    $query = sprintf("SELECT t1.id, t1.username, t1.total_points, t1.month_points, t1.last_access, t2.name AS level_name, t2.image AS level_image,t2.experience_needed FROM vmembers AS t1, levels AS t2 WHERE t1.level_id = t2.id AND t1.id = '%d' LIMIT 1", $user_id);    
    $result = $db->query($query);
    $row = $result->fetch_assoc();
    
    $query = sprintf("SELECT profile_image FROM members WHERE id='%d' LIMIT 1", $user_id);
    $result = $db->query($query);
    $row2 = $result->fetch_assoc();
    $row['profile_image'] = $row2['profile_image'];
    
    if (false === $admin) {
        $query = sprintf( "SELECT * FROM levels WHERE experience_needed >= '%d' LIMIT 1", $row['total_points']);    
        $result = $db->query($query);
        $row2 = $result->fetch_assoc();
    
        $levelper= round($row['total_points'] / $row2['experience_needed'] * 100);
    }
     
    ?>        
        <div class="row" style="margin-top:50px;">
            <div class="col-md-7">
                <div class="row">
                    <div class="col-md-4">
                        <?php
                        if (empty($row['profile_image'])) {
                            $row['profile_image'] = 'images/profile_default.png';
                        }
                        ?>
                        <img src="<?= $row['profile_image']; ?>" class="img-thumbnail" id="profileImage">
                        <?php 
                        if ($user_id == $_SESSION['member']['id']) {
                            // L'usuari por editar la seva imatge.
                        ?>
                        <p class="text-center"><a href="#" id="uploadFile" title="Upload"><span class="glyphicon glyphicon-open"></span> Canviar imatge</a></p>
                        <p id="messageBox"></p>
                        <script>
                            var uploadURL = "<?= $_SERVER['PHP_SELF']; ?>?a=upload";
                            head(function() {
                                $(document).ready(function() {
                                    $('a#uploadFile').file();
                                    $('input#uploadFile').file().choose(function(e, input) {
                                        input.upload(uploadURL, function(res) {
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
                        <p class="small">Darrera connexió el <?php echo strftime('%A, %d de %B', $row['last_access']); ?></p>
                    </div>
                </div>
                <?php 
                if (false === $admin) {
                ?>
                <h3>Activitat <small>darrers 10 events</small></h3>
                <?php
                $query = sprintf("SELECT * FROM points WHERE id_member='%d' ORDER BY date DESC LIMIT 10", $user_id);
                $result = $db->query($query);
                $html_code = array();
                while ( $row3 = $result->fetch_assoc() ) {
                        $html_code[] = sprintf ("<p>%s va rebre <strong>%d punts</strong> d'experiència per <em>%s</em></p>",
                                time_elapsed_string($row3['date']),
                                $row3['points'],
                                $row3['memo']
                                );
                }
                echo implode(PHP_EOL, $html_code);
                }
                ?>
            </div>
                
            <div class="col-md-offset-1 col-md-4">
                <h3>Experiència</h3>
                <div class="media">
                    <img src="images/levels/<?php echo $row['level_image']; ?>" width="100" alt="<?php echo $row['level_name']; ?>" class="img-thumbnail media-object pull-left">
                    <div class="media-body">
                        <p class="lead media-heading"><?php echo $row['level_name']; ?></p>
                        <?php
                        if (false === $admin) {
                        ?>
                        <p>Nivell següent</p>
                        <div class="progress">
                            <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="<?php echo $row['total_points']; ?>" aria-valuemin="0" aria-valuemax="<?php echo $row2['experience_needed']; ?>" style="width: <?php echo $levelper; ?>%">
                            <span><?php echo $row['total_points'] . '/' . $row2['experience_needed']; ?></span>
                            </div>
                        </div>
                        <?php
                        }
                        ?>
                    </div>
                </div>

                <?php
                if (false === $admin) {
                $query = sprintf("SELECT COUNT(*) AS completed FROM members_badges WHERE id_member='%d' AND status='completed'", $user_id);
                $result = $db->query($query);
                $row = $result->fetch_assoc();
                $badges = $row['completed'];
                
                $query = sprintf("SELECT t1.image, t1.name, t1.description, t2.status FROM badges AS t1, members_badges AS t2 WHERE t2.id_member='%d' AND t1.id=t2.id_badges", $user_id);
                $result = $db->query($query);
                echo '<h3>Insígnies ('. $badges .')</h3>';
                $html_code = array();
                while ($row = $result->fetch_assoc()) {
                    $title = sprintf("%s\n%s", $row['name'], $row['description']);
                    $html_code[] = '<a href="#" title="' . $title . '">';
                    $image = ('completed' == $row['status']) ? 'images/badges/' . $row['image'] : 'images/default_badge_off.png';
                    $html_code[] = '<img src="' . $image .'" alt="'. $row['name'] . '" class="img-thumbnail" width="80">';
                    $html_code[] = '</a>';
                }
                echo implode(PHP_EOL, $html_code);
                }
                ?>
            </div>
        </div>
    <?php

} // END print_profile()

function upload_profile_picture() {
    global $db;
    
    list($returnedValue, $returnedMessage) = uploadFile('uploadFile', 'uploads');
    
    if (false === $returnedValue) return 'ERROR';

    // Deletes previous profile picture file
    $query = sprintf("SELECT profile_image FROM members WHERE id='%d'", $_SESSION['member']['id']);
    $result = $db->query($query);
    $row = $result->fetch_assoc();
    if (file_exists($row['profile_image'])) unlink($row['profile_image']);
        
    // ACTION: Si es la primera vegada que puja una imatge... guanya un badge
    if (empty($row['profile_image'])) {
        silent_action($_SESSION['member']['id'], 19);
    }
    // END ACTION
        
    $query = sprintf("UPDATE members SET profile_image='%s' WHERE id='%d'", 
            $returnedMessage, 
            $_SESSION['member']['id']
            );
    $db->query($query);
  
    return $returnedMessage;  
}
