<?php
/*
 * @author Paco Orozco, paco.orozco -at- upcnet.es
 * @version $Id$
 *
 */

define('IN_SCRIPT', 1);
require_once('inc/functions.inc.php');

// Page only for members
if ( false === login_check() ) {
    header('Location: index.php');
    exit();
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
        $html_code[] = '<div id="searchResults-users">';
        
        // Nomes farem cerques si busquen mes de tres caracters, aixo evita que sobrecarreguem la BDD
        if ( ! isset($searchterm[3]) ) {
            $html_code[] = '<p>Tecleja m&eacute;s de 3 car&agrave;cters per fer la cerca</p>';
        } else {       
            $query = sprintf("SELECT id, username FROM vmembers WHERE username LIKE '%%%s%%'", $db->real_escape_string($searchterm));
            $result = $db->query($query);
        
            if ( 0 == $result->num_rows  ) {
                // No s'ha trobat res
                $html_code[] = '<div id="no_result">No hi ha trobat cap resultat.</div>';
                
            } else {
                // Hem trobat informacio
                $html_code[] = '<ul>';
                while ( $row = $result->fetch_assoc() ) {
                    $html_code[] = '<li><a href="member.php?a=viewuser&item=' . $row['id'] . '">' . $row['username'] . "</a></li>";
                }
                $html_code[] = '</ul>';
            }
        }
        $html_code[] = '</div>';
	return implode($html_code, PHP_EOL);
} // END get_search_results()

function print_profile($user_id) {
    global $db;
    
    // user_id must be integer
    $user_id = intval($user_id);
    
    $query = sprintf("SELECT t1.id, t1.username, t1.total_points, t1.week_points, t1.month_points, t2.name, t2.image,t2.experience_needed FROM vmembers AS t1, levels AS t2 WHERE t1.level_id = t2.id AND t1.id = '%d' LIMIT 1", $user_id);    
    $result = $db->query($query);
    $row = $result->fetch_assoc();
    
    $query2 = sprintf("SELECT * FROM `levels` WHERE experience_needed >='%d' ORDER BY experience_needed ASC LIMIT 1", $row['total_points']);    
    $result2 = $db->query($query2);
    $row2 = $result2->fetch_assoc();
    
    $levelper= round($row['total_points'] / $row2['experience_needed']*100);
      if ($levelper<40 ) { $levelper=40;}
    ?>          
        <h1>Detalls de '<?php echo $row['username']; ?>'</h1>
        <div class="row">
            <div class="col-md-4">
                <div class="thumbnail">
                    <img data-src="holder.js/300x200" alt="...">
                    <div class="caption">
                        <h4 class="text-center">
                        <?php
                    if ( empty($row['image']) ) {
                        echo $row['name'];
                    } else {
                        echo '<img src="images/levels/'. $row['image'] .'" alt="'. $row['name'] .'" width="64"><br />' . $row['name'];
                    }
                    ?></h3>
                     <div class="progress">
                         
                        <div class="progress-bar" role="progressbar" aria-valuenow="<?php echo $levelper ?>" aria-valuemin="20" aria-valuemax="100" style="width: <?php echo $levelper ?>%;"><?php echo $row['total_points']; ?>/<?php echo $row2['experience_needed']; ?></div>
                     
                     </div>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <dl class="dl-horizontal">
                <dt>Experiència</dt> <dd>Total: <?php echo $row['total_points']; ?> | Mensual: <?php echo $row['month_points']; ?> | Setmanal: <?php echo $row['week_points']; ?></dd>
                
                
                <?php
                    $query = sprintf("SELECT t2.name, t2.image, t1.amount AS amount_got, t2.amount_needed, t1.status FROM members_badges AS t1, badges AS t2 WHERE t1.id_badges = t2.id AND t1.id_member = '%d'", $user_id);
                    $result = $db->query($query);
                    $html_code = array();
                    $html_code[] = '<dt>Insígnies</dt> <dd><ul class="list-inline">';
                    
              
                    while ( $row = $result->fetch_assoc() ) {
                         if ( $row['status'] != 'completed' )
                         { $imgstyle="opacity:0.4"; } else { $imgstyle="";} 
                            $badgetper=round($row['amount_got'] / $row['amount_needed']*100);
                            if ($badgetper<40 ) { $badgetper=40;}

                            if ( empty($row['image']) ) {
                                $html_code[] = '<li><img data-src="holder.js/64x64" alt="..."><br />' . $row['name'] . '</li>';
                            } else {
                                $html_code[] = '<li><img style="'.$imgstyle.'" src="images/badges/'. $row['image'] .'" alt="'. $row['name'] .'" width="64"><br />' . $row['name'] . '<div class="progress"><div class="progress-bar" role="progressbar" aria-valuenow="'.$badgetper.'%" aria-valuemin="30" aria-valuemax="100" style="width:'.$badgetper.'%;">'.$row['amount_got'].'/'. $row['amount_needed'].'</div></div></li>';
                            }
                        
                    }
                    $html_code[] = '</ul></dd></dl>';
                    echo implode(PHP_EOL, $html_code);
                    unset($html_code);
                ?>
            </div>
        </div>
    <?php

} // END print_profile()

