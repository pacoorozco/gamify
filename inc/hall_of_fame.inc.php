<?php
/*
 * @author Paco Orozco, paco.orozco -at- upcnet.es
 * @version $Id: hall_of_fame.inc.php 32 2014-04-04 11:40:31Z paco $
 *
 */

/* Check if this is a valid include */
defined('IN_SCRIPT') or die('Invalid attempt');
?>

<table class="table table-hover" >
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
         $top3 = 3;
        $top10 = 10;
        $toprest = $top10 - $top3;
        while ($row = $result->fetch_assoc()) {
            if ($row['username'] == $_SESSION['member']['username']) {
                $currentuser = "class='info'";
                $currentranking = $position;
            } else {

                $currentuser = "";
            }
            $html_code[] = '<tr ' . $currentuser . '>';
            if ($position<=$top3) 
            {
                 $html_code[] = '<td class="text-center" style=" vertical-align: middle;"><span class="badge alert-warning"><h'.($position+2).'>&nbsp;'.$position.'&nbsp;</h'.($position+2).'></span></td>';
            }
            else
            {
            $html_code[] = '<td class="text-center"  style=" vertical-align: middle;">' . $position . '</td>';
            }
            $html_code[] = '<td style=" vertical-align: middle;">';
            $html_code[] = '<a href="member.php?a=viewuser&item=' . $row['id'] . '">' . $row['username'] . '</a>';
            $html_code[] = '</td>';
            $html_code[] = '<td style=" vertical-align: middle;">' . $row['points'] . '</td>';
            $html_code[] = '<td style=" vertical-align: middle;">' . $levels[$row['level_id']] . '</td>';
            $badges = ($row['badges'] > 0) ? '<span class="badge">' . $row['badges'] . '</span>' : '';
            $html_code[] = '<td style=" vertical-align: middle;" class="text-center">' . $badges . '</td>';
            $html_code[] = '</tr>';
            $ranking[$position] = implode("", $html_code);
            unset($html_code);
            $position += 1;
        }
        // echo implode(PHP_EOL, $html_code);
       

        if ($currentranking <= $top3) {
            for ($i = 1; $i <= $top10; $i++) {
                echo $ranking[$i];
            }
        } else {
            for ($i = 1; $i <= $top3; $i++) {
                echo $ranking[$i];
            }


            echo "<tr><td colspan=5><center><b>...</td></tr>";
            if ($currentranking + $toprest < $position) {
                $init = $currentranking - 1;
                $end = $currentranking+$toprest-2;
            } else {
                $init = $position-$toprest;
                $end = $position;
            }
            for ($i = $init; $i <= $end; $i++) {
                echo $ranking[$i];
            }
        }
       
      
        ?>
    </tbody>
</table>
