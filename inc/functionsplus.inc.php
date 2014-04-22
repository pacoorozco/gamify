<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function add_experience ( $data = array() ) {
    global $db;
    
    $missatges = array();
    $data_html = array();
    // validate data
    $data['id'] = intval($data['id']);
    $data['experience'] = intval($data['experience']);
    
    if ( false === user_exists($data['id']) ) {
        // L'usuari que ens han passat no existeix, per tant tornem a mostrar la llista.
        $missatges[] = array('type' => "error", 'msg' => "No he trobat informaci&oacute; per aquest usuari.");
    }
    
    if ( empty($data['experience']) ) {
        $missatges[] = array('type' => "error", 'msg' => "El camp experiència és obligatori.");
    }
    
    if ( empty($data['memo']) ) {
        $data['memo'] = "t'han donat aquests punts sense cap ra&oacute;.";
    }
    
    if ( ! empty($missatges) ) {
         
         $data_html[]= get_html_messages($missatges) ;
        return implode(PHP_EOL, $data_html);
    } 
      
    // get the current level, before adding points
    $query = sprintf("SELECT level_id FROM vmembers WHERE id = '%d' LIMIT 1", $data['id']);
    $result = $db->query($query);
    $row = $result->fetch_assoc();
    $old_level = $row['level_id'];
    
    // adds experience to user
    $query = sprintf("INSERT INTO points SET id_member='%d', points='%d', memo='%s'", $data['id'], $data['experience'], $db->real_escape_string($data['memo']));
    $result = $db->query($query);
    
    if ( !$result ) {
        
        $missatges[] = array('type' => "error", 'msg' => "No s'ha pogut actualitzar les dades de l'usuari '<strong>". $data['username'] ."</strong>'.");
        $data_html[]= get_html_messages($missatges);
        return implode(PHP_EOL, $data_html);
    }
    
    // get the current level, after adding points
    $query = sprintf("SELECT id, username, email, total_points FROM vmembers WHERE id = '%d' LIMIT 1", $data['id']);
    $result = $db->query($query);
    $data = $result->fetch_assoc();
    
    $query = sprintf("SELECT id, name, image FROM levels WHERE experience_needed = (SELECT MAX(experience_needed) FROM levels WHERE experience_needed <= '%d') LIMIT 1", $data['total_points']);
    $result = $db->query($query);
    $row = $result->fetch_assoc();
    $data['level_id'] = $row['id'];
    $data['name'] = $row['name'];
    $data['image'] = $row['image'];
          
    if ( $old_level != $data['level_id'] ) {
        $query = sprintf( "UPDATE members SET level_id='%d' WHERE id = '%d' LIMIT 1", $data['level_id'], $data['id'] );
        $result = $db->query($query);
        // Send a mail to user in order to tell him/her, his/her new level
        
        notify_level_2_user($data);
        $missatges[] = array('type' => "info", 'msg' => "L'usuari '<strong>". $data['username'] ."</strong>' ha aconseguit el nivell '<strong>". $data['name'] ."</strong>'.");
       $data_html[]= get_html_messages($missatges);
        
    } 
    
 //   $missatges[] = array('type' => "success", 'msg' => "Dades de l'usuari '<strong>". $data['username'] ."</strong>' actualitzades.");
 
    return implode(PHP_EOL, $data_html);
} // END add_experience()

function notify_level_2_user( $data = array() ) {
    global $CONFIG;
       
    $level_name = $data['name'];
    $level_image = sprintf("%s/images/levels/%s", $CONFIG['site']['base_url'], $data['image']);
    $user_profile = sprintf("%s/member.php?a=viewuser&item=%s", 
                             $CONFIG['site']['base_url'], $data[id_member]); 
    
    $subject = 'Has pujat de nivell a GoW!';
    $mail_body = <<<LEVEL_MAIL
<div style="text-align:center;">
<h2>Enhorabona, acabes de pujar de nivell.</h2>
<img src="$level_image">
<h3>Ets un $level_name</h3>
<p style="padding-bottom: 10px;">Pots veure el teu perfil <a href="$user_profile">aquí</a>.</p>
</div>            
LEVEL_MAIL;
      
    // Send the message
   // return send_message($subject, $mail_body, $data['email']);
} // END notify_badge_2_user()

function add_badge ($id,$id_user,$points) {
    global $db;
    
    $missatges = array();
    $data_html= array();
    // Get badge data from DB
    $query = sprintf( "SELECT name, amount_needed, image FROM badges WHERE id = '%d' AND status = 'active' LIMIT 1", $db->real_escape_string($id ));
    $result = $db->query($query);   
    
    if ($result->num_rows == 0) {
        // La insígnia que ens han passat no existeix.
        $missatges[] = array('type' => "error", 'msg' => "GOW-E-NOSUCHBADGE No he trobat informaci&oacute; per aquesta insígnia.");
        $data_html[]= get_html_messages($missatges);
        return implode(PHP_EOL, $data_html);
        }
    $row = $result->fetch_assoc();
    $query = sprintf("SELECT * FROM members_badges WHERE id_member = '%d' AND id_badges = '%d' LIMIT 1", $db->real_escape_string($id_user ),$db->real_escape_string($id ));
    $result = $db->query($query);
  
    if ( !$result ) {
        //Error de BD
        $missatges[] = array('type' => "error", 'msg' => "GOW-E-DBERROR Error de Base de dades.");
        $data_html[]= get_html_messages($missatges);
         return implode(PHP_EOL, $data_html); 
        }
    if ( $result->num_rows == 0 ) {
       // L'usuari encara no te cap punt d'aquesta insignia creem el nou registre.
        $query = sprintf("INSERT INTO members_badges SET id_member='%d', id_badges='%d', amount='%d', last_time='%d', status='%s'", 
                          $db->real_escape_string($id_user ),$db->real_escape_string($id ), 0, time(), "active" );
        
        $result = $db->query($query);
         if ( !$result ) {
            //Error de BD
            $missatges[] = array('type' => "error", 'msg' => "GOW-E-DBERROR Error de Base de dades.");
            $data_html[]= get_html_messages($missatges);
            return implode(PHP_EOL, $data_html); 
            } 
         $query = sprintf("SELECT * FROM members_badges WHERE id_member = '%d' AND id_badges = '%d' LIMIT 1", $db->real_escape_string($id_user ),$db->real_escape_string($id ));
         $result = $db->query($query);
            
         }
        
        //L'usuari ja te algun punt del badget anem a actualitzar-lo-hi
        $mg = $result->fetch_assoc();
        
        //mirem si arribem a aconseguir del badge
     
           if ($mg['status']!='completed') {
//-- 
                       // el badge encara no esta aconseguit
        if ((int)$mg['amount']+(int)$points >= (int)$row['amount_needed']) {
            $status='completed'; $points=$row['amount_needed'];
            
            
        } else {$status='active';$points=(int)$points+(int)$mg['amount'];} 
          
        //actualitzem el registre amb el nou estat i els nous punts.
        $query = sprintf( "UPDATE members_badges SET amount='%d', last_time='%d',status='%s' WHERE id = '%d' LIMIT 1", 
                               $db->real_escape_string($points), time(),  $db->real_escape_string($status) , $db->real_escape_string($mg['id']));
        $result = $db->query($query);
       
         //--
         if ( !$result ) {
            //Error de BD
            $missatges[] = array('type' => "error", 'msg' => "GOW-E-DBERROR Error de Base de dades.");
            $data_html[]= get_html_messages($missatges);
            return implode(PHP_EOL, $data_html); 
            } 
            
        if ($status=='completed') {
            // NOU badget
             $missatges[] = array('type' => "info", 'msg' => "GOW-I-NEWBADGE Has aconseguit el Badge ".$row['name'].".");
             $data_html[]= get_html_messages($missatges);
             // cal enviar mail
            }
        
   
         
                    
            }
        
   
    return implode(PHP_EOL, $data_html);
}
function show_badge($id,$id_member) {
    global $db;
    $message = array();
    $html_code= array();  
    $html_code[]= query(sprintf("select badges.description,members_badges.status,badges.name,members_badges.amount,badges.image,badges.amount_needed from members_badges,badges where id_member=%d and id_badges=%d and members_badges.id_badges=badges.id",$db->real_escape_string($id_member),$db->real_escape_string($id)),&$row);

    $badgetper=round($row['amount'] / $row['amount_needed']*100);
     if ( $row['status'] != 'completed' )
                         { $imgstyle="opacity:0.4"; } else { $imgstyle="";} 
    if ($badgetper<40 ) { $badgetper=40;}
    if ( empty($row['image']) ) {
                                $html_code[] = '<li><center><img data-src="holder.js/64x64" alt="..."><br />' . $row['name'] . '</li>';
                            } else {
                                $html_code[] = '<li><center><img  data-toggle="tooltip" data-placement="top" title="'.$row['description'].'" style="'.$imgstyle.'" src="images/badges/'. $row['image'] .'" alt="'. $row['name'] .'" width="64"><br />' . $row['name'] . '<div class="progress"><div class="progress-bar" role="progressbar" aria-valuenow="'.$badgetper.'%" aria-valuemin="30" aria-valuemax="100" style="width:'.$badgetper.'%;">'.$row['amount'].'/'. $row['amount_needed'].'</div></div></li>';
                            }
   return implode(PHP_EOL, $html_code);
}

function query ($query,&$row) {
    global $db; 
    $message = array();
    $html_code= array();
    
    if (!$result = $db->query($query)) 
    {
        $message=array(); 
        $message[] = array('type' => "error", 'msg' => PROG_NAME."-F-DBNCONNECT - Error d'accés a la base de dades (".$query.").");
        $html_code[] = get_html_messages($message);
    } 
    else
    {
        $row = $result->fetch_assoc();
    }
    return implode(PHP_EOL, $html_code);
}