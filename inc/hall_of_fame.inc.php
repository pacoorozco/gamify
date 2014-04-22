<?php
/*
 * @author Paco Orozco, paco.orozco -at- upcnet.es
 * @version $Id: hall_of_fame.inc.php 32 2014-04-04 11:40:31Z paco $
 *
 */

/* Check if this is a valid include */
defined('IN_SCRIPT') or die('Invalid attempt');

?>

<table class="table table-hover">
    <thead>
        <tr>
            <th class="text-center">Posició</th>
            <th>Usuari</th>
            <th>Experiència</th>
            <th>Nivell</th>
            <th class="text-center">Insígnies</th>
        </tr>  
    </thead>
    <tbody>
        <?php
	// Per incrementar la velocitat, guardem tot el codi en una variable i fem nomes un echo.
	$html_code = array();
        $position = 1;
        while ($row = $result->fetch_assoc()) {
            $html_code[] = '<tr>';
            $html_code[] = '<td class="text-center">'. $position .'</td>';
            $html_code[] = '<td>';
            $html_code[] = '<a href="member.php?a=viewuser&item='. $row['id'] .'">'. $row['username'] .'</a>';
            $html_code[] = '</td>';
            $html_code[] = '<td>'. $row['points'] .'</td>';
            $html_code[] = '<td>'. $levels[$row['level_id']] .'</td>';
            $badges = ($row['badges'] > 0) ? '<span class="badge">' . $row['badges'] . '</span>' : '';
            $html_code[] = '<td class="text-center">'. $badges .'</td>';
            $html_code[] = '</tr>';
            $position += 1;
        }
        echo implode(PHP_EOL, $html_code);
	unset($html_code);
        ?>
    </tbody>
</table>
