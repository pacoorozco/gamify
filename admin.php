<?php
/*
 * @author Paco Orozco, paco.orozco -at- upcnet.es
 * @version $Id: admin.php 65 2014-04-21 18:09:54Z paco $
 *
 */

define('IN_SCRIPT',1);
require_once('inc/functions.inc.php');
require_once('inc/gamify.inc.php');

// Page only for members
if ( false === loginCheck() ) {
    // save referrer to $_SESSION['nav'] for after login redirect
    $_SESSION['nav'] = urlencode($_SERVER['SCRIPT_NAME'] . '?' . $_SERVER['QUERY_STRING']);
    header('Location: login.php');
    exit;
}

// Check if user has privileges
if ( ! userHasPrivileges($_SESSION['member']['id'], 'administrator') ) {
    // User has no privileges
    require_once('inc/header.inc.php');
    printAccessDenied();
    require_once('inc/footer.inc.php');
    exit();
}

require_once('inc/header.inc.php');

$missatges = array();

// Que hem de fer?
$action = getREQUESTVar('a');
switch ($action) {
    case 'actions':
        printActions();
        break;

    case 'giveexperience':
        $data = array();
        $data['id'] = getPOSTVar('item');
        $data['experience'] = getPOSTVar('experience');
        $data['memo'] = getPOSTVar('memo');

        addExperience($data);
        break;

    case 'givebadge':
        $data = array();
        $data['id_member'] = getPOSTVar('item');
        $data['id_badge'] = getPOSTVar('achievement');
        $data['amount'] = getPOSTVar('amount');

        action($data);
        break;

    case 'users':
        printUserManagement();
        break;

    case 'newuser':
        printNewUserForm();
        break;

    case 'createuser':
        $data = array();
        $data['username'] = getPOSTVar('username');
        $data['password'] = getPOSTVar('password');
        $data['repeatpassword'] = getPOSTVar('repeatpassword');
        $data['email'] = getPOSTVar('email');
        $data['role'] = getPOSTVar('role');

        createUser($data);
        break;

    case 'edituser':
        $userId = getREQUESTVar('item');
        printEditUserForm($userId);
        break;

    case 'saveuser':
        $data = array();
        $data['id'] = getPOSTVar('item');
        $data['password'] = getPOSTVar('password');
        $data['repeatpassword'] = getPOSTVar('repeatpassword');
        $data['email'] = getPOSTVar('email');
        $data['role'] = getPOSTVar('role');

        saveUserData($data);
        break;

    case 'deleteuser':
        $userId = getREQUESTVar('item');

        if(deleteUser($userId)) {
           $missatges[] = array('type' => "success", 'msg' => "L'usuari s'ha el&middot;liminat correctament.");
        } else {
           $missatges[] = array('type' => "error", 'msg' => "No s'ha pogut el&middot;liminar l'usuari.");
        }
        printUserManagement($missatges);
        break;

    case 'levels':
        printLevelManagement();
        break;

    case 'newlevel':
        printNewLevelForm();
        break;

    case 'createlevel':
        $data = array();
        $data['name'] = getPOSTVar('name');
        $data['experience_needed'] = getPOSTVar('experience_needed');
        $data['image'] = getPOSTVar('image');

        createLevel($data);
        break;

    case 'editlevel':
        $level_id = getREQUESTVar('item');
        printEditLevelForm($level_id);
        break;

    case 'savelevel':
        $data = array();
        $data['id'] = getPOSTVar('item');
        $data['name'] = getPOSTVar('name');
        $data['experience_needed'] = getPOSTVar('experience_needed');
        $data['image'] = getPOSTVar('image');

        saveLevelData($data);
        break;

    case 'deletelevel':
        $level_id = getREQUESTVar('item');

        if(deleteLevel($level_id)) {
           $missatges[] = array('type' => "success", 'msg' => "El n&iacute;vell s'ha el&middot;liminat correctament.");
        } else {
           $missatges[] = array('type' => "error", 'msg' => "No s'ha pogut el&middot;liminar el n&iacute;vell.");
        }
        printLevelManagement($missatges);
        break;

    case 'badges':
        printBadgeManagement();
        break;

    case 'newbadge':
        printNewBadgeForm();
        break;

    case 'createbadge':
        $data = array();
        $data['name'] = getPOSTVar('name');
        $data['image'] = getPOSTVar('image');
        $data['description'] = getPOSTVar('description');
        $data['amount_needed'] = getPOSTVar('amount_needed');

        createBadge($data);
        break;

    case 'editbadge':
        $badge_id = getREQUESTVar('item');
        printEditBadgeForm($badge_id);
        break;

    case 'savebadge':
        $data = array();
        $data['id'] = getPOSTVar('item');
        $data['name'] = getPOSTVar('name');
        $data['image'] = getPOSTVar('image');
        $data['description'] = getPOSTVar('description');
        $data['amount_needed'] = getPOSTVar('amount_needed');

        saveBadgeData($data);
        break;

    case 'deletebadge':
        $badge_id = getREQUESTVar('item');

        if(deleteBadge($badge_id)) {
           $missatges[] = array('type' => "success", 'msg' => "La insígnia s'ha el&middot;liminat correctament.");
        } else {
           $missatges[] = array('type' => "error", 'msg' => "No s'ha pogut el&middot;liminar la insígnia.");
        }
        printBadgeManagement($missatges);
        break;

    case 'messages':
        printSendMessage();
        break;

    case 'sendmessage':
        $missatges = array();
        $data = array();
        $subject = getPOSTVar('subject');
        $missatge = getPOSTVar('missatge');

        if ( sendMessage($subject, $missatge) ) {
           $missatges[] = array('type' => "success", 'msg' => "El missatge s'ha enviat correctament.");
        } else {
           $missatges[] = array('type' => "error", 'msg' => "No s'ha pogut enviar el missatge.");
        }
        printSendMessage($missatges);
        break;

    case 'quiz':
        printQuestionManagement();
        break;

    case 'newquiz':
        printNewQuestionForm();
        break;

    case 'createquiz':
        $data = array();
        $data['name'] = getPOSTVar('name');
        $data['image'] = getPOSTVar('image');
        $data['question'] = getPOSTVar('question');
        $data['tip'] = getPOSTVar('tip');
        $data['solution'] = getPOSTVar('solution');
        $data['type'] = getPOSTVar('type');
        $data['status'] = getPOSTVar('status');

        $data['choices'] = getPOSTVar('choices');
        $data['points'] = getPOSTVar('points');
        $data['correct'] = getPOSTVar('correct');

        $data['actions'] = getPOSTVar('actions');
        $data['when'] = getPOSTVar('when');

        createQuestion($data);
        break;

    case 'editquiz':
        $question_id = getREQUESTVar('item');
        printEditQuestionForm($question_id);
        break;

    case 'savequiz':
        $data = array();
        $data['id'] = getPOSTVar('item');
        $data['name'] = getPOSTVar('name');
        $data['image'] = getPOSTVar('image');
        $data['question'] = getPOSTVar('question');
        $data['tip'] = getPOSTVar('tip');
        $data['solution'] = getPOSTVar('solution');
        $data['type'] = getPOSTVar('type');
        $data['status'] = getPOSTVar('status');

        $data['choices'] = getPOSTVar('choices');
        $data['points'] = getPOSTVar('points');
        $data['correct'] = getPOSTVar('correct');

        $data['actions'] = getPOSTVar('actions');
        $data['when'] = getPOSTVar('when');

        saveQuestionData($data);
        break;

    case 'deletequiz':
        $question_id = getREQUESTVar('item');

        if(deleteQuestion($question_id)) {
           $missatges[] = array('type' => "success", 'msg' => "La pregunta s'ha el&middot;liminat correctament.");
        } else {
           $missatges[] = array('type' => "error", 'msg' => "No s'ha pogut el&middot;liminar la pregunta.");
        }
        printQuestionManagement($missatges);
        break;

    case 'previewquiz':
        $question_id = getREQUESTVar('item');
        printPreviewQuestion($question_id);
        break;

    default:
        printAdminDashboard();
}

require_once('inc/footer.inc.php');
exit();

/*** FUNCTIONS ***/

function printAdminHeader( $a = 'users', $msg = array() ) {
    ?>
            <h1>Administració</h1>
            <p><?php echo getHTMLMessages($msg); ?></p>

            <ul class="nav nav-tabs">
                <li<?php echo ( $a == 'actions' ) ? ' class="active"' : ''; ?>><a href="admin.php?a=actions">Accions</a></li>
                <li<?php echo ( $a == 'users' ) ? ' class="active"' : ''; ?>><a href="admin.php?a=users">Gestió d'usuaris</a></li>
                <li<?php echo ( $a == 'levels' ) ? ' class="active"' : ''; ?>><a href="admin.php?a=levels">Gestió de nivells</a></li>
                <li<?php echo ( $a == 'badges' ) ? ' class="active"' : ''; ?>><a href="admin.php?a=badges">Gestió d'insígnies</a></li>
                <li<?php echo ( $a == 'quiz' ) ? ' class="active"' : ''; ?>><a href="admin.php?a=quiz">Gestió de preguntes</a></li>
                <li<?php echo ( $a == 'messages' ) ? ' class="active"' : ''; ?>><a href="admin.php?a=messages">Enviament missatges</a></li>

            </ul>
    <?php
}

function printAdminDashboard() {
    printActions();
} // END print_admin_dashboard()

function printActions ( $msg = array() ) {
    global $db;

    printAdminHeader('actions');
    ?>
               <div class="panel panel-default">
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-6">
                    <h2>Donar experiència</h2>
                    <p><?php echo getHTMLMessages($msg); ?></p>
                    <form action="admin.php" method="post" class="form-horizontal" role="form">
                        <div class="form-group">
                            <label for="username" class="col-sm-2 control-label">Usuari</label>
                            <div class="col-sm-10">
                                <select data-placeholder="escull un usuari..." name="item" id="username" class="form-control chosen-select">
                                    <option value=""></option>
    <?php
    $query = "SELECT id, username FROM members WHERE role = 'member' ORDER BY username";
    $result = $db->query($query);

    // Per incrementar la velocitat, guardem tot el codi en una variable i fem nomes un echo.
    $htmlCode = array();
    while ($row = $result->fetch_assoc()) {
        $htmlCode[] = '<option value="' . $row['id'] . '">' . $row['username'] . '</option>';
    }
    echo implode(PHP_EOL, $htmlCode);
    ?>

                                </select>
                                </div>
                            </div>
                        <div class="form-group">
                            <label for="experience" class="col-sm-2 control-label">Experiència</label>
                            <div class="col-sm-10">
                                <select name="experience" id="experience" class="form-control">
                                    <option value="1">1 punt</value>
                                    <option value="5">5 punts</value>
                                    <option value="10">10 punts</value>
                                </select>
                            </div>
                        </div>

                            <div class="form-group">
                                <label for="memo" class="col-sm-2 control-label">Motiu</label>
                                <div class="col-sm-10">
                                    <input type="text" name="memo" id="memo" class="form-control" placeholder="per què rep els punts? (opcional)">
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-sm-offset-2 col-sm-10">
                                    <input type="hidden" id="a" name="a" value="giveexperience">
                                    <button type="submit" class="btn btn-default">Donar experiència</button>
                                </div>
                            </div>
                    </form>
                        </div>
                        <div class="col-md-6">
            <h2>Donar insígnies</h2>
                    <form action="admin.php" method="post" class="form-horizontal" role="form">
                        <div class="form-group">
                            <label for="username" class="col-sm-2 control-label">Usuari</label>
                            <div class="col-sm-10">
                                <select data-placeholder="escull un usuari..." name="item" id="username" class="form-control chosen-select">
                                    <option value=""></option>
                                    <?php echo implode(PHP_EOL, $htmlCode); ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="badge" class="col-sm-2 control-label">Assoliment</label>
                            <div class="col-sm-10">
                                <select data-placeholder="escull un badge..." name="achievement" id="badge" class="form-control chosen-select">
                                    <option value=""></option>
    <?php
    $query = "SELECT id, name FROM badges WHERE status = 'active' ORDER BY name";
    $result = $db->query($query);

    // Per incrementar la velocitat, guardem tot el codi en una variable i fem nomes un echo.
    unset($htmlCode);
    $htmlCode = array();
    while ($row = $result->fetch_assoc()) {
        $htmlCode[] = '<option value="' . $row['id'] . '">' . $row['name'] . '</option>';
    }
    echo implode(PHP_EOL, $htmlCode);
    ?>
                                </select>
                            </div>
                        </div>
                          <div class="form-group">
                                <div class="col-sm-offset-2 col-sm-10">
                                    <input type="hidden" id="amount" name="amount" value="1">
                                    <input type="hidden" id="a" name="a" value="givebadge">
                                    <button type="submit" class="btn btn-default">Executar acció</button>
                                </div>
                            </div>


                </div>
            </div>
    <?php
} // END print_action()

function printUserManagement ( $msg = array() ) {
    global $db;

    printAdminHeader('users', $msg);
    ?>
                        <div class="panel panel-default">
                            <div class="panel-body">
                                <p class="text-right">
                                    <a href="admin.php?a=newuser" class="btn btn-success" role="button"><span class="glyphicon glyphicon-plus"></span> Nou usuari</a>
                                </p>

                <table class="table table-hover">
                    <thead>
                    <tr>
                        <th>Usuari</th>
                        <th>Rol</th>
                        <th>Accions</th>
                    </tr>
                    </thead>
                    <tbody>
    <?php
    $query = "SELECT id, username, role FROM members ORDER BY username";
    $result = $db->query($query);

    // Per incrementar la velocitat, guardem tot el codi en una variable i fem nomes un echo.
    $htmlCode = array();
    while ($row = $result->fetch_assoc()) {
        $htmlCode[] = '<tr>';
        $htmlCode[] = '<td>';
        $htmlCode[] = '<a href="admin.php?a=edituser&item=' . $row['id'] . '">' . $row['username'] . '</a>';
        $htmlCode[] = '</td>';
        $htmlCode[] = '<td>' . $row['role'] . '</td>';
        $htmlCode[] = '<td>';
        $htmlCode[] = '<a href="admin.php?a=edituser&item='. $row['id'] .'" class="btn btn-default" role="button"><span class="glyphicon glyphicon-edit"></span> Editar</a>';
        $htmlCode[] = '<a href="admin.php?a=deleteuser&item='. $row['id'] .'" class="btn btn-danger" role="button"><span class="glyphicon glyphicon-trash"></span> El·liminar</a>';
        $htmlCode[] = '</td>';
        $htmlCode[] = '</tr>';
    }
    echo implode(PHP_EOL, $htmlCode);
    unset($htmlCode);
    ?>
                    </tbody>
                </table>
                            </div>
                        </div>
    <?php
} // END print_user_management()

function printLevelManagement( $msg = array() ) {
    global $db;

    printAdminHeader('levels', $msg);
    ?>
                        <div class="panel panel-default">
                            <div class="panel-body">
                                <p class="text-right">
                                    <a href="admin.php?a=newlevel" class="btn btn-success" role="button"><span class="glyphicon glyphicon-plus"></span> Nou nivell</a>
                                </p>
                <table class="table table-hover">
                    <thead>
                    <tr>
                        <th>Nivell</th>
                        <th>Experiència</th>
                        <th>Imatge</th>
                        <th>Accions</th>
                    </tr>
                    </thead>
                    <tbody>
    <?php
    $query = "SELECT id, name, experience_needed, image FROM levels ORDER BY experience_needed";
    $result = $db->query($query);

    // Per incrementar la velocitat, guardem tot el codi en una variable i fem nomes un echo.
    $htmlCode = array();
    while ($row = $result->fetch_assoc()) {
        $htmlCode[] = '<tr>';
        $htmlCode[] = '<td>';
        $htmlCode[] = '<a href="admin.php?a=editlevel&item=' . $row['id'] . '">' . $row['name'] . '</a>';
        $htmlCode[] = '</td>';
        $htmlCode[] = '<td>' . $row['experience_needed'] . '</td>';
        if ( empty($row['image']) ) {
            $htmlCode[] = '<td><img data-src="holder.js/64x64" alt="..."></td>';
        } else {
            $htmlCode[] = '<td><img src="images/levels/'. $row['image'] .'" alt="'. $row['name'] .'" width="64"></td>';
        }
        $htmlCode[] = '<td>';
        $htmlCode[] = '<a href="admin.php?a=editlevel&item='. $row['id'] .'" class="btn btn-default" role="button"><span class="glyphicon glyphicon-edit"></span> Editar</a>';
        $htmlCode[] = '<a href="admin.php?a=deletelevel&item='. $row['id'] .'" class="btn btn-danger" role="button"><span class="glyphicon glyphicon-trash"></span> El·liminar</a>';
        $htmlCode[] = '</td>';
        $htmlCode[] = '</tr>';
    }
    echo implode(PHP_EOL, $htmlCode);
    unset($htmlCode);
    ?>
                    </tbody>
                </table>
                            </div>
                        </div>
    <?php
} // END print_level_management()

function printBadgeManagement( $msg = array() ) {
    global $db;

    printAdminHeader('badges', $msg);
    ?>
                        <div class="panel panel-default">
                            <div class="panel-body">
                                <p class="text-right">
                                    <a href="admin.php?a=newbadge" class="btn btn-success" role="button"><span class="glyphicon glyphicon-plus"></span> Nova insígnia</a>
                                </p>
                <table class="table table-hover">
                    <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Imatge</th>
                        <th>Quantitat</th>
                        <th>Accions</th>
                    </tr>
                    </thead>
                    <tbody>
    <?php
    $query = "SELECT id, name, image, amount_needed, status FROM badges ORDER BY name";
    $result = $db->query($query);

    // Per incrementar la velocitat, guardem tot el codi en una variable i fem nomes un echo.
    $htmlCode = array();
    while ($row = $result->fetch_assoc()) {
        $htmlCode[] = '<tr>';
        $htmlCode[] = '<td>';
        $htmlCode[] = '<a href="admin.php?a=editbadge&item=' . $row['id'] . '">' . $row['name'] . '</a>';
        $htmlCode[] = '</td>';
        if ( empty($row['image']) ) {
            $htmlCode[] = '<td><img src="images/default_badge_off.png" alt="'. $row['name'] .'" class="img-thumbnail" width="64"></td>';
        } else {
            $htmlCode[] = '<td><img src="images/badges/'. $row['image'] .'" alt="'. $row['name'] .'" class="img-thumbnail" width="64"></td>';
        }
        $htmlCode[] = '<td>'. $row['amount_needed'] .'</td>';
        $htmlCode[] = '<td>';
        $htmlCode[] = '<a href="admin.php?a=editbadge&item='. $row['id'] .'" class="btn btn-default" role="button"><span class="glyphicon glyphicon-edit"></span> Editar</a>';
        $htmlCode[] = '<a href="admin.php?a=deletebadge&item='. $row['id'] .'" class="btn btn-danger" role="button"><span class="glyphicon glyphicon-trash"></span> El·liminar</a>';
        $htmlCode[] = '</td>';
        $htmlCode[] = '</tr>';
    }
    echo implode(PHP_EOL, $htmlCode);
    unset($htmlCode);
    ?>
                    </tbody>
                </table>
                            </div>
                        </div>
    <?php
} // END print_badge_management()

/*** USERS ***/
function printNewUserForm( $data = array(), $msg = array() ) {
    global $CONFIG;
    ?>
                        <h1>Nou usuari</h1>
                        <p><?php echo getHTMLMessages($msg); ?></p>
                        <form action="admin.php" method="post" class="form-horizontal" role="form">
                            <div class="form-group">
                                <label for="username" class="col-sm-2 control-label">Usuari</label>
                                <div class="col-sm-10">
                                    <input type="text" name="username" id="username" class="form-control" placeholder="Usuari" value="<?php if (isset($data['username'])) echo $data['username']; ?>" required>
                                </div>
                            </div>
                            <?php
                            if ($CONFIG['authentication']['type'] == 'LOCAL') {
                            ?>
                            <div class="form-group">
                                <label for="password" class="col-sm-2 control-label">Contrasenya</label>
                                <div class="col-sm-10">
                                    <input type="password" name="password" id="password" class="form-control" placeholder="Contrasenya" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="repeatpassword" class="col-sm-2 control-label">Verificar contrasenya</label>
                                <div class="col-sm-10">
                                    <input type="password" name="repeatpassword" id="repeatpassword" class="form-control" placeholder="Contrasenya" required>
                                </div>
                            </div>
                            <?php
                            }
                            ?>
                            <div class="form-group">
                                <label for="email" class="col-sm-2 control-label">Adreça de correu</label>
                                <div class="col-sm-10">
                                    <input type="email" name="email" id="email" class="form-control" placeholder="nom.cognom@domini.cat" value="<?php if (isset($data['email'])) echo $data['email']; ?>" required>
                                </div>
                            </div>
                            <div class="form-group">

                                <label for="role" class="col-sm-2 control-label">Rol</label>
                                <div class="col-sm-10">
                                    <select name="role" id="role" class="form-control">
                                        <option value="member">member</option>
                                        <option value="administrator">administrator</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-sm-offset-2 col-sm-10">
                                    <input type="hidden" id="a" name="a" value="createuser">
                                    <button type="submit" class="btn btn-success">Crear usuari</button>
                                    <a href="admin.php?a=users" class="btn btn-default" role="button">Cancel·lar</a>
                                </div>
                            </div>
                        </form>

    <?php
} // END print_newuser_form()

function createUser( $data = array() ) {
    global $db, $CONFIG;

    $missatges = array();

    // Validate supplied data
    if ( empty($data['username']) ) {
        $missatges[] = array('type' => "error", 'msg' => "El nom d'usuari no &eacute;s v&agrave;lid.");
    }

    if ( getUserExists($data['username']) ) {
        $missatges[] = array('type' => "error", 'msg' => "El nom d'usuari ja existeix.");
    }

    if ( ! filter_var($data['email'], FILTER_VALIDATE_EMAIL) ) {
        $missatges[] = array('type' => "error", 'msg' => "L'adre&ccedil;a de correu no &eacute;s correcta.");
    }

    if ($CONFIG['authentication']['type'] == 'LOCAL') {
        if ( empty($data['password']) ) {
            $missatges[] = array('type' => "error", 'msg' => "El camp contrasenya no pot estar buida.");
        }

        if ( $data['password'] != $data['repeatpassword'] ) {
            $missatges[] = array('type' => "error", 'msg' => "La contrasenya i la verficaci&oacute; no coincideixen.");
        }
    } else {
        // set default password to NULL
        $data['password'] = '';
    }

    if ( ($data['role'] != 'member') && ($data['role'] != 'administrator') ) {
        $missatges[] = array('type' => "error", 'msg' => "El valor del rol &eacute;s incorrecte.");
    }

    if ( ! empty($missatges) ) {
        printNewUserForm($data, $missatges);
        return false;
    }

    // User data is correct, now we can insert it to DB
    $query = sprintf( "INSERT INTO members SET uuid='%s', username='%s', password='%s', email='%s', role='%s'",
            $db->real_escape_string(getNewUUID()),
            $db->real_escape_string($data['username']),
            $db->real_escape_string(md5($data['password'])),
            $db->real_escape_string($data['email']),
            $db->real_escape_string($data['role'])
            );
    $db->query($query);

    // Get new user_id or 0 on error.
    $user_id = $db->insert_id;

    if ( $user_id == 0 ) {
        $missatges[] = array('type' => "error", 'msg' => "No s'ha pogut crear l'usuari.");
        printNewUserForm($data, $missatges);
    } else {
        $missatges[] = array('type' => "success", 'msg' => "L'usuari '<strong>". getUserName($user_id) ."</strong>' s'ha creat correctament.");
        printUserManagement($missatges);
    }
} // END create_user()

function printEditUserForm($user_id, $msg = array()) {
    global $db, $CONFIG;

    $missatges = array();

    // user_id must be integer
    $user_id = intval($user_id);

    // Get user data from DB
    $query = sprintf( "SELECT * FROM members WHERE id = '%d' LIMIT 1", $user_id );
    $result = $db->query($query);

    if ($result->num_rows == 0) {
        // L'usuari que ens han passat no existeix, per tant tornem a mostrar la llista.
        $missatges[] = array('type' => "error", 'msg' => "No he trobat informaci&oacute; per aquest usuari.");
        printUserManagement($missatges);
        return false;
    }
    $row = $result->fetch_assoc();
    ?>
                        <h1>Editar usuari</h1>
                        <p><?php echo getHTMLMessages($msg); ?></p>
                        <form action="admin.php" method="post" class="form-horizontal" role="form">
                            <div class="form-group">
                                <label for="username" class="col-sm-2 control-label">Usuari</label>
                                <div class="col-sm-10">
                                    <p class="form-control-static"><?php echo $row['username']; ?></p>
                                </div>
                            </div>
                            <?php
                            if ($CONFIG['authentication']['type'] == 'LOCAL') {
                            ?>
                            <div class="form-group">
                                <label for="password" class="col-sm-2 control-label">Contrasenya</label>
                                <div class="col-sm-10">
                                    <input type="password" name="password" id="password" class="form-control" placeholder="Contrasenya">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="repeatpassword" class="col-sm-2 control-label">Verificar contrasenya</label>
                                <div class="col-sm-10">
                                    <input type="password" name="repeatpassword" id="repeatpassword" class="form-control" placeholder="Contrasenya">
                                </div>
                            </div>
                            <?php
                            }
                            ?>
                            <div class="form-group">
                                <label for="email" class="col-sm-2 control-label">Adreça de correu</label>
                                <div class="col-sm-10">
                                    <input type="email" name="email" id="email" class="form-control" placeholder="nom.cognom@domini.cat" value="<?php echo $row['email']; ?>" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="role" class="col-sm-2 control-label">Rol</label>
                                <div class="col-sm-10">
                                    <select name="role" id="role" class="form-control">
                            <?php
                            $available_roles = array('member', 'administrator');
                            foreach ($available_roles as $opt_key) {
                                if ( $opt_key == $row['role'] ) {
                                    echo '<option value="' . $opt_key . '" selected="selected">' . $opt_key . '</option>';
                                } else {
                                    echo '<option value="' . $opt_key . '">' . $opt_key . '</option>';
                                }
                            }
                            ?>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-sm-offset-2 col-sm-10">
                                    <input type="hidden" id="item" name="item" value="<?php echo $user_id; ?>">
                                    <input type="hidden" id="a" name="a" value="saveuser">
                                    <button type="submit" class="btn btn-success"><span class="glyphicon glyphicon-save"></span> Actualitzar dades</button>
                                    <a href="<?php echo $_SERVER['PHP_SELF']; ?>?a=users" class="btn btn-danger" role="button"><span class="glyphicon glyphicon-retweet"></span> Tornar</a>
                                </div>
                            </div>
                        </form>

    <?php
} // END print_edituser_form()

function saveUserData( $data = array() ) {
    global $db, $CONFIG;

    $missatges = array();

    // Validate supplied data
    $data['id'] = intval($data['id']);
    if ( ! getUserExists($data['id']) ) {
        $missatges[] = array('type' => "error", 'msg' => "<strong>ATENCI&Oacute;</strong>: L'usuari suministrat per actualitzar no existeix.");
        printUserManagement($missatges);
        return false;
    }

    if ( ! filter_var($data['email'], FILTER_VALIDATE_EMAIL) ) {
        $missatges[] = array('type' => "error", 'msg' => "L'adre&ccedil;a de correu no &eacute;s correcta.");
    }

    if ($CONFIG['authentication']['type'] == 'LOCAL') {
        if ( $data['password'] != $data['repeatpassword'] ) {
            $missatges[] = array('type' => "error", 'msg' => "La contrasenya i la verficaci&oacute; no coincideixen.");
        }
    }

    if ( ($data['role'] != 'member') && ($data['role'] != 'administrator') ) {
        $missatges[] = array('type' => "error", 'msg' => "El valor del rol &eacute;s incorrecte.");
    }

    if ( ! empty($missatges) ) {
        printEditUserForm($data['id'], $missatges);
        return false;
    }

    // User data is correct, now we can insert it to DB
    if ( empty($data['password']) ) {
        $query = sprintf("UPDATE members SET email='%s', role='%s' WHERE id = '%d' LIMIT 1", $db->real_escape_string($data['email']), $db->real_escape_string($data['role']), $data['id'] );
    } else {
        $query = sprintf("UPDATE members SET password='%s', email='%s', role='%s' WHERE id = '%d' LIMIT 1", $db->real_escape_string(md5($data['password'])), $db->real_escape_string($data['email']), $db->real_escape_string($data['role']), $data['id'] );
    }

    if ( $db->query($query) ) {
        $missatges[] = array('type' => "success", 'msg' => "Dades d'usuari '<strong>". getUserName($data['id']) ."</strong>' actualitzades.");
        printUserManagement($missatges);

    } else {
        $missatges[] = array('type' => "error", 'msg' => "No s'ha pogut actualitzar les dades de l'usuari.");
        printEditUserForm($data, $missatges);
    }
} // END save_user_data()

function deleteUser($user_id) {
    global $db;

    // user_id must be an integer
    $user_id = intval($user_id);
    if ( ! getUserExists($user_id) ) return false;

    $query = sprintf( "DELETE FROM members WHERE id = '%d' LIMIT 1", $user_id );
    $db->query($query);

    return ( ! getUserExists($user_id) );
} // END delete_user()

/*** LEVELS ***/

function printNewLevelForm( $data = array(), $msg = array() ) {
    ?>
                        <h1>Nou nivell</h1>
                        <p><?php echo getHTMLMessages($msg); ?></p>
                        <form action="admin.php" method="post" class="form-horizontal" role="form">
                            <div class="form-group">
                                <label for="levelname" class="col-sm-2 control-label">Nom</label>
                                <div class="col-sm-10">
                                    <input type="text" name="name" id="levelname" class="form-control" placeholder="Nom del nivell" value="<?php echo $data['name']; ?>" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="levelsrc" class="col-sm-2 control-label">Imatge</label>
                                <div class="col-sm-10">
                                    <input type="text" name="image" id="levelsrc" class="form-control" placeholder="Imatge del nivell">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="experience" class="col-sm-2 control-label">Experiència necessària</label>
                                <div class="col-sm-10">
                                    <input type="text" name="experience_needed" id="experience" class="form-control" placeholder="Experiència necessària per aconseguir-ho" value="<?php echo $data['experience_needed']; ?>" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-sm-offset-2 col-sm-10">
                                    <input type="hidden" id="a" name="a" value="createlevel">
                                    <button type="submit" class="btn btn-success">Crear nivell</button>
                                    <a href="admin.php?a=levels" class="btn btn-default" role="button">Cancel·lar</a>
                                </div>
                            </div>
                        </form>

    <?php
} // END print_newlevel_form()

function createLevel( $data = array() ) {
    global $db;

    $missatges = array();

    // Validate supplied data
    if ( empty($data['name']) ) {
        $missatges[] = array('type' => "error", 'msg' => "El nom del n&iacute;vell és obligatori.");
    }

    $data['experience_needed'] = intval($data['experience_needed']);
    if ( empty($data['experience_needed']) ) {
        $missatges[] = array('type' => "error", 'msg' => "El camp experiència és obligatori.");
    }

    $query = sprintf( "SELECT name FROM levels WHERE name = '%s' OR experience_needed = '%d'", $data['name'], $data['experience_needed'] );
    $result = $db->query($query);

    if ($result->num_rows != 0) {
        // A level exists with the same name or with de same experience_needed.
        $missatges[] = array('type' => "error", 'msg' => "Ja existeix un nivell amb el mateix nom o la mateixa experiència.");
    }

    if ( ! empty($missatges) ) {
        printNewLevelForm($data, $missatges);
        return false;
    }

    $query = sprintf("INSERT INTO levels SET name='%s', experience_needed='%d', image='%s'", $db->real_escape_string($data['name']), $data['experience_needed'], $db->real_escape_string($data['image']) );
    $db->query($query);

    // Get new level_id or 0 on error.
    $level_id = $db->insert_id;

    if ( $level_id == 0 ) {
        $missatges[] = array('type' => "error", 'msg' => "No s'ha pogut crear el nivell.");
        printNewLevelForm($data, $missatges);
    } else {
        $missatges[] = array('type' => "success", 'msg' => "El nivell '<strong>". $data['name'] ."</strong>' s'ha creat correctament.");
        printLevelManagement($missatges);
    }
} // END create_level()

function printEditLevelForm($level_id, $msg = array()) {
    global $db;

    $missatges = array();

    // level_id must be integer
    $level_id = intval($level_id);

    // Get user data from DB
    $query = sprintf( "SELECT * FROM levels WHERE id = '%d' LIMIT 1", $level_id );
    $result = $db->query($query);

    if ($result->num_rows == 0) {
        // No existeix.
        $missatges[] = array('type' => "error", 'msg' => "No he trobat informaci&oacute; per aquest nivell.");
        printLevelManagement($missatges);
        return false;
    }
    $data = $result->fetch_assoc();
    ?>
                        <h1>Editar nivell</h1>
                        <p><?php echo getHTMLMessages($msg); ?></p>
                        <form action="admin.php" method="post" class="form-horizontal" role="form">
                           <div class="form-group">
                                <label for="levelname" class="col-sm-2 control-label">Nom</label>
                                <div class="col-sm-10">
                                    <input type="text" name="name" id="levelname" class="form-control" placeholder="Nom del nivell" value="<?php echo $data['name']; ?>" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="levelsrc" class="col-sm-2 control-label">Imatge</label>
                                <div class="col-sm-10">
                                    <input type="text" name="image" id="levelsrc" class="form-control" placeholder="Imatge del nivell" value="<?php echo $data['image']; ?>">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="experience" class="col-sm-2 control-label">Experiència necessària</label>
                                <div class="col-sm-10">
                                    <input type="text" name="experience_needed" id="experience" class="form-control" placeholder="Experiència necessària per aconseguir-ho" value="<?php echo $data['experience_needed']; ?>" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-sm-offset-2 col-sm-10">
                                    <input type="hidden" id="item" name="item" value="<?php echo $data['id']; ?>">
                                    <input type="hidden" id="a" name="a" value="savelevel">
                                    <button type="submit" class="btn btn-success"><span class="glyphicon glyphicon-save"></span> Actualitzar dades</button>
                                    <a href="admin.php?a=levels" class="btn btn-danger" role="button"><span class="glyphicon glyphicon-retweet"></span> Tornar</a>
                                </div>
                            </div>
                        </form>

    <?php
} // END print_editlevel_form()

function saveLevelData( $data = array() ) {
    global $db;

    $missatges = array();

    // Validate supplied data
    $data['id'] = intval($data['id']);

    $query = sprintf( "SELECT name FROM levels WHERE id = '%d'", $data['id'] );
    $result = $db->query($query);

    if ($result->num_rows == 0) {
        // A level doesn't exists .
        $missatges[] = array('type' => "error", 'msg' => "<strong>ATENCI&Oacute;</strong>: El nivell suministrat per actualitzar no existeix.");
        printLevelManagement($missatges);
        return false;
    }

    if ( empty($data['name']) ) {
        $missatges[] = array('type' => "error", 'msg' => "El camp nom és obligatori.");
    }

    $data['experience_needed'] = intval($data['experience_needed']);
    if ( empty($data['experience_needed']) ) {
        $missatges[] = array('type' => "error", 'msg' => "El camp experiència és obligatori.");
    }

    $query = sprintf( "SELECT id FROM levels WHERE ( name = '%s' OR experience_needed = '%d') AND id != '%d'", $data['name'], $data['experience_needed'], $data['id'] );
    $result = $db->query($query);

    if ($result->num_rows != 0) {
        // A level exists with the same name or with de same experience_needed.
        $missatges[] = array('type' => "error", 'msg' => "Ja existeix un nivell amb el mateix nom o la mateixa experiència.");
    }

    if ( ! empty($missatges) ) {
        printEditLevelForm($data['id'], $missatges);
        return false;
    }

    $query = sprintf("UPDATE levels SET name='%s', experience_needed='%s', image='%s' WHERE id = '%d' LIMIT 1",
                      $db->real_escape_string($data['name']), $data['experience_needed'], $db->real_escape_string($data['image']), $data['id'] );
    $db->query($query);

    if ( $db->query($query) ) {
        $missatges[] = array('type' => "success", 'msg' => "Dades del nivell '<strong>". $data['name'] ."</strong>' actualitzades.");
        printLevelManagement($missatges);
    } else {
        $missatges[] = array('type' => "error", 'msg' => "No s'ha pogut actualitzar les dades del nivell.");
        printEditLevelForm($data, $missatges);
    }
} // END save_level_data()

function deleteLevel($level_id) {
    global $db;

    // level_id must be an integer
    $level_id = intval($level_id);
    $query = sprintf( "SELECT id FROM levels WHERE id = '%d'", $level_id );
    $result = $db->query($query);
    if ($result->num_rows == 0) return false;

    $query = sprintf( "DELETE FROM levels WHERE id = '%d' LIMIT 1", $level_id );
    $db->query($query);

    $query = sprintf( "SELECT id FROM levels WHERE id = '%d'", $level_id );
    $result = $db->query($query);
    return ($result->num_rows == 0);
} // END delete_level()

/*** BADGES ***/

function printNewBadgeForm( $data = array(), $msg = array() ) {
    ?>
                        <h1>Nova insígnia</h1>
                        <p><?php echo getHTMLMessages($msg); ?></p>
                        <form action="admin.php" method="post" class="form-horizontal" role="form">
                            <div class="form-group">
                                <label for="achievementname" class="col-sm-2 control-label">Nom de la insígnia</label>
                                <div class="col-sm-10">
                                    <input type="text" name="name" id="achievementname" class="form-control" placeholder="Nom de la insígnia" value="<?php echo $data['name']; ?>" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="badgesrc" class="col-sm-2 control-label">Imatge</label>
                                <div class="col-sm-10">
                                    <input type="text" name="image" id="badgesrc" class="form-control" placeholder="Imatge de la insígnia" value="<?php echo $data['image']; ?>">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="description" class="col-sm-2 control-label">Descripció</label>
                                <div class="col-sm-10">
                                    <textarea name="description" id="description" class="form-control" rows="3" placeholder="Descripció de la insígnia"><?php echo $data['description']; ?></textarea>
                                </div>
                            </div>
                           <div class="form-group">
                                <label for="amount" class="col-sm-2 control-label">Quantitat necessària</label>
                                <div class="col-sm-10">
                                    <input type="text" name="amount_needed" id="amount" class="form-control" placeholder="Número de vegades per aconseguir-la" value="<?php echo $data['amount_needed']; ?>">
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-sm-offset-2 col-sm-10">
                                    <input type="hidden" id="a" name="a" value="createbadge">
                                    <button type="submit" class="btn btn-success">Crear insígnia</button>
                                    <a href="admin.php?a=badges" class="btn btn-default" role="button">Cancel·lar</a>
                                </div>
                            </div>
                        </form>

    <?php
} // END print_newbadge_form()

function createBadge( $data = array() ) {
    global $db;

    $missatges = array();

    // Validate supplied data
    if ( empty($data['name']) ) {
        $missatges[] = array('type' => "error", 'msg' => "El nom de la insígnia és obligatori.");
    }

    $data['amount_needed'] = intval($data['amount_needed']);
    if ( empty($data['amount_needed']) ) {
        $missatges[] = array('type' => "error", 'msg' => "El camp experiència és obligatori.");
    }

    if ( ! empty($missatges) ) {
        printNewBadgeForm($data, $missatges);
        return false;
    }

    $query = sprintf("INSERT INTO badges SET name='%s', image='%s', description='%s', amount_needed='%d'",
                      $db->real_escape_string($data['name']), $db->real_escape_string($data['image']), $db->real_escape_string($data['description']), $data['amount_needed'] );
    $db->query($query);

    // Get new achieve_id or 0 on error.
    $badge_id = $db->insert_id;

    if ( $badge_id == 0 ) {
        $missatges[] = array('type' => "error", 'msg' => "No s'ha pogut crear la insígnia.");
        printNewLevelForm($data, $missatges);
    } else {
        $missatges[] = array('type' => "success", 'msg' => "La insígnia '<strong>". $data['name'] ."</strong>' s'ha creat correctament.");
        printBadgeManagement($missatges);
    }
} // END create_badge()

function printEditBadgeForm($badge_id, $msg = array()) {
    global $db;

    $missatges = array();

    // level_id must be integer
    $badge_id = intval($badge_id);

    // Get user data from DB
    $query = sprintf( "SELECT * FROM badges WHERE id = '%d' LIMIT 1", $badge_id );
    $result = $db->query($query);

    if ($result->num_rows == 0) {
        // No existeix.
        $missatges[] = array('type' => "error", 'msg' => "No he trobat informaci&oacute; per aquesta insígnia.");
        printBadgeManagement($missatges);
        return false;
    }
    $data = $result->fetch_assoc();
    ?>
                        <h1>Editar insígnia</h1>
                        <p><?php echo getHTMLMessages($msg); ?></p>
                        <form action="admin.php" method="post" class="form-horizontal" role="form">
                            <div class="form-group">
                                <label for="achievementname" class="col-sm-2 control-label">Nom de la insígnia</label>
                                <div class="col-sm-10">
                                    <input type="text" name="name" id="achievementname" class="form-control" placeholder="Nom de la insígnia" value="<?php echo $data['name']; ?>" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="badgesrc" class="col-sm-2 control-label">Imatge</label>
                                <div class="col-sm-10">
                                    <input type="text" name="image" id="badgesrc" class="form-control" placeholder="Imatge de la insígnia" value="<?php echo $data['image']; ?>">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="description" class="col-sm-2 control-label">Descripció</label>
                                <div class="col-sm-10">
                                    <textarea name="description" id="description" class="form-control" rows="3" placeholder="Descripció de la insígnia"><?php echo $data['description']; ?></textarea>
                                </div>
                            </div>
                           <div class="form-group">
                                <label for="amount" class="col-sm-2 control-label">Quantitat necessària</label>
                                <div class="col-sm-10">
                                    <input type="text" name="amount_needed" id="amount" class="form-control" placeholder="Número de vegades per aconseguir-la" value="<?php echo $data['amount_needed']; ?>">
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-sm-offset-2 col-sm-10">
                                    <input type="hidden" id="item" name="item" value="<?php echo $badge_id; ?>">
                                    <input type="hidden" id="a" name="a" value="savebadge">
                                    <button type="submit" class="btn btn-success"><span class="glyphicon glyphicon-save"></span> Actualitzar dades</button>
                                    <a href="admin.php?a=badges" class="btn btn-danger" role="button"><span class="glyphicon glyphicon-retweet"></span> Tornar</a>
                                </div>
                            </div>
                        </form>

    <?php
} // END print_editbadge_form()

function saveBadgeData( $data = array() ) {
    global $db;

    $missatges = array();

    // Validate supplied data
    $data['id'] = intval($data['id']);

    $query = sprintf( "SELECT name FROM badges WHERE id = '%d'", $data['id'] );
    $result = $db->query($query);

    if ($result->num_rows == 0) {
        // A badge doesn't exists .
        $missatges[] = array('type' => "error", 'msg' => "<strong>ATENCI&Oacute;</strong>: La insígnia suministrada per actualitzar no existeix.");
        printBadgeManagement($missatges);
        return false;
    }

    if ( empty($data['name']) ) {
        $missatges[] = array('type' => "error", 'msg' => "El nom de la insígnia és obligatori.");
    }

    $data['amount_needed'] = intval($data['amount_needed']);
    if ( empty($data['amount_needed']) ) {
        $missatges[] = array('type' => "error", 'msg' => "El camp quantitat és obligatori.");
    }

    $query = sprintf( "SELECT id FROM badges WHERE name = '%s' AND id != '%d' LIMIT 1", $data['name'], $data['id'] );
    $result = $db->query($query);

    if ($result->num_rows != 0) {
        // A badge exists with the same name or with de same experience_needed.
        $missatges[] = array('type' => "error", 'msg' => "Ja existeix una insígnia amb el mateix nom.");
    }

    if ( ! empty($missatges) ) {
        printEditBadgeForm($data['badge_id'], $missatges);
        return false;
    }

    $query = sprintf("UPDATE badges SET name='%s', image='%s', description='%s', amount_needed='%d' WHERE id = '%d' LIMIT 1", $db->real_escape_string($data['name']), $db->real_escape_string($data['image']), $db->real_escape_string($data['description']), $data['amount_needed'], $data['id'] );

    if ( $db->query($query) ) {
        $missatges[] = array('type' => "success", 'msg' => "Dades de la insígia '<strong>". $data['name'] ."</strong>' actualitzades.");
        printBadgeManagement($missatges);
    } else {
        $missatges[] = array('type' => "error", 'msg' => "No s'ha pogut actualitzar les dades de la insígnia.");
        printEditBadgeForm($data, $missatges);
    }
} // END save_badge_data()

function deleteBadge($badge_id) {
    global $db;

    // badge_id must be an integer
    $badge_id = intval($badge_id);
    $query = sprintf( "SELECT id FROM badges WHERE id = '%d'", $badge_id );
    $result = $db->query($query);
    if ($result->num_rows == 0) return false;

    $query = sprintf( "DELETE FROM badges WHERE id = '%d' LIMIT 1", $badge_id );
    $db->query($query);

    $query = sprintf( "SELECT id FROM badges WHERE id = '%d'", $badge_id );
    $result = $db->query($query);
    return ($result->num_rows == 0);
} // END delete_badge()

function addExperience ( $data = array() ) {
    global $db;

    $missatges = array();

    // validate data
    $data['id'] = intval($data['id']);
    $data['experience'] = intval($data['experience']);

    if ( false === getUserExists($data['id']) ) {
        // L'usuari que ens han passat no existeix, per tant tornem a mostrar la llista.
        $missatges[] = array('type' => "error", 'msg' => "No he trobat informaci&oacute; per aquest usuari.");
    }

    if ( empty($data['experience']) ) {
        $missatges[] = array('type' => "error", 'msg' => "El camp experiència és obligatori.");
    }

    if ( empty($data['memo']) ) {
        $data['memo'] = "alguna ra&oacute; desconeguda";
    }

    if ( ! empty($missatges) ) {
        printActions($missatges);
        return false;
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
        printActions($data, $missatges);
        return false;
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
        notifyLevelToUser($data);
        $missatges[] = array('type' => "info", 'msg' => "L'usuari '<strong>". $data['username'] ."</strong>' ha aconseguit el nivell '<strong>". $data['name'] ."</strong>'.");
    }

    $missatges[] = array('type' => "success", 'msg' => "Dades de l'usuari '<strong>". $data['username'] ."</strong>' actualitzades.");
    printActions($missatges);
    return true;
} // END add_experience()

function action( $data = array() ) {
    global $db;

    $missatges = array();

    // validate data
    $data['id_member'] = intval($data['id_member']);
    $data['id_badge'] = intval($data['id_badge']);
    $data['amount'] = intval($data['amount']);

    // Get user data from DB
    $query = sprintf( "SELECT username, email FROM vmembers WHERE id = '%d' LIMIT 1", $data['id_member'] );
    $result = $db->query($query);

    if ($result->num_rows == 0) {
        // L'usuari que ens han passat no existeix.
        $missatges[] = array('type' => "error", 'msg' => "No he trobat informaci&oacute; per aquest usuari.");
        printActions($missatges);
        return false;
    } else {
        $row = $result->fetch_assoc();
        $data['username'] = $row['username'];
        $data['email'] = $row['email'];
    }

    // Get badge data from DB
    $query = sprintf( "SELECT name, amount_needed, image FROM badges WHERE id = '%d' AND status = 'active' LIMIT 1", $data['id_badge'] );
    $result = $db->query($query);

    if ($result->num_rows == 0) {
        // La insígnia que ens han passat no existeix.
        $missatges[] = array('type' => "error", 'msg' => "No he trobat informaci&oacute; per aquesta insígnia.");
        printActions($missatges);
        return false;
    } else {
        $row = $result->fetch_assoc();
        $data['name'] = $row['name'];
        $data['image'] = $row['image'];
        $data['amount_needed'] = $row['amount_needed'];
    }

    if ( empty($data['amount']) ) {
        $missatges[] = array('type' => "error", 'msg' => "El camp quantitat és obligatori.");
    }

    if ( ! empty($missatges) ) {
        printActions($missatges);
        return false;
    }

    $status = 'active';
    $query = sprintf("SELECT * FROM members_badges WHERE id_member = '%d' AND id_badges = '%d' LIMIT 1", $data['id_member'], $data['id_badge']);
    $result = $db->query($query);

    if ( $result->num_rows == 0 ) {
        // this action has not been initiated to this user
        if ( $data['amount'] >= $data['amount_needed'] ) {
            $status = 'completed';
        }
        $query = sprintf("INSERT INTO members_badges SET id_member='%d', id_badges='%d', amount='%d', last_time='%d', status='%s'",
                          $data['id_member'], $data['id_badge'], $data['amount'], time(), $status );

        if ( $db->query($query) ) {
            $missatges[] = array('type' => "success", 'msg' => "Dades de l'usuari '<strong>". $data['username'] ."</strong>' actualitzades.");
            if ( 'completed' == $status ) {
                // send a mail to user in order to tell him/her, his/her new badge
                doSilentAddExperience( $data['id_member'], 5, 'desbloquejar la ins&iacute;gnia: '. $data['name'] );
                notifyBadgeToUser($data);
                $missatges[] = array('type' => "info", 'msg' => "L'usuari '<strong>". $data['username'] ."</strong>' ha aconseguit la insíngia '<strong>". $data['name'] ."</strong>'.");
            }
            printActions($missatges);
            return true;
        } else {
            $missatges[] = array('type' => "error", 'msg' => "No s'ha pogut actualitzar les dades de l'usuari '<strong>". $data['username'] ."</strong>.");
            printActions($missatges);
            return false;
        }
    }

    $row = $result->fetch_assoc();
    $data['id'] = $row['id'];

    // checking if badge is not completed yet
    if ( 'active' == $row['status'] ) {
        // update amount in order to complete this badge.
        $data['amount'] += $row['amount'];

        // TODO - check if needed period of time is passed

        // check if badge has completed
        if ( $data['amount'] >= $data['amount_needed'] ) {
            // complete badge
            $status = 'completed';

            $query = sprintf( "UPDATE members_badges SET amount='%d', status='%s', last_time='%d' WHERE id = '%d' LIMIT 1",
                               $data['amount'], $status, time(), $data['id'] );

            if ( $db->query($query) ) {
                // send a mail to user in order to tell him/her, his/her new achievement
                notifyBadgeToUser($data);
                $missatges[] = array('type' => "success", 'msg' => "Dades de l'usuari '<strong>". $data['username'] ."</strong>' actualitzades.");
                $missatges[] = array('type' => "info", 'msg' => "L'usuari '<strong>". $data['username'] ."</strong>' ha aconseguit la insíngia '<strong>". $data['name'] ."</strong>'.");
                printActions($missatges);
                return true;
            } else {
                $missatges[] = array('type' => "error", 'msg' => "No s'ha pogut actualitzar les dades de l'usuari '<strong>". $data['username'] ."</strong>'.");
                printActions($missatges);
                return false;
            }
        } else {
            // update amount of this badges
            $query = sprintf( "UPDATE members_badges SET amount='%d', last_time='%d' WHERE id = '%d' LIMIT 1",
                               $data['amount'], time(), $data['id'] );

            if ( $db->query($query) ) {
                $missatges[] = array('type' => "success", 'msg' => "Dades de l'usuari '<strong>". $data['username'] ."</strong>' actualitzades.");
                printActions($missatges);
                return true;
            } else {
                $missatges[] = array('type' => "error", 'msg' => "No s'ha pogut actualitzar les dades de l'usuari '<strong>". $data['username'] ."</strong>'.");
                printActions($missatges);
                return false;
            }
        }
    } else {
        $missatges[] = array('type' => "info", 'msg' => "L'usuari '<strong>". $data['username'] ."</strong>' ja tenia l'insígnia <strong>". $data['name'] ."</strong>.");
        printActions($missatges);
        return false;
    }
}

function printSendMessage( $msg = array() ) {
    global $db;

    printAdminHeader('messages');

    $query = "SELECT email FROM vmembers WHERE role = 'member'";
    $result = $db->query($query);

    $bcc_mail = array();
    while ( $row = $result->fetch_assoc() ) {
        if ( !empty($row['email']) ) {
            $bcc_mail[] = $row['email'];
        }
    }

    ?>
               <div class="panel panel-default">
                <div class="panel-body">
                    <h2>Enviar mail als usuaris</h2>
                    <p><?php echo getHTMLMessages($msg); ?></p>
                    <form action="admin.php" method="post" class="form-horizontal" role="form">
                        <div class="form-group">
                            <label for="subject" class="col-sm-2 control-label">Títol</label>
                            <div class="col-sm-10">
                                <input type="text" name="subject" id="subject" class="form-control" placeholder="Assumpte del missatge" required>
                            </div>
                        </div>

                            <div class="form-group">
                                <label for="missatge" class="col-sm-2 control-label">Missatge</label>
                                <div class="col-sm-10">
                                    <textarea name="missatge" id="missatge" class="form-control tinymce" rows="3" placeholder="Cos del missatge"></textarea>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="bcc" class="col-sm-2 control-label">Destinataris</label>
                                <div class="col-sm-10">
                                    <textarea id="bcc" class="form-control" rows="3" style="display:none;" disabled><?php echo implode(',', $bcc_mail); ?></textarea>
                                    <a id="bcc_btn" href="#" class="btn btn-default" onClick="$('#bcc_btn').hide(); $('#bcc').show()"><span class="glyphicon glyphicon-eye-open"></span> Mostrar destinataris</a>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-sm-offset-2 col-sm-10">
                                    <input type="hidden" id="a" name="a" value="sendmessage">
                                    <button type="submit" class="btn btn-success"><span class="glyphicon glyphicon-envelope"></span> Enviar missatge</button>
                                </div>
                            </div>
                    </form>
                </div>
            </div>
    <?php
} // END print_send_message()

function printQuestionManagement( $msg = array() ) {
    global $db;

    printAdminHeader('quiz', $msg);
    ?>
                        <div class="panel panel-default">
                            <div class="panel-body">
                                <p class="text-right">
                                    <a href="<?php echo $_SERVER['PHP_SELF']; ?>?a=newquiz" class="btn btn-success" role="button"><span class="glyphicon glyphicon-plus"></span> Nova pregunta</a>
                                </p>
                <table class="table table-hover" id="questions">
                    <thead>
                    <tr>
                        <th>Pregunta</th>
                        <th>Estat</th>
                        <th><abbr title="Respostes"><span class="glyphicon glyphicon-comment"></span></abbr></th>
                        <th><span class="glyphicon glyphicon-tags"></span></th>
                        <th>Accions</th>
                    </tr>
                    </thead>
                    <tbody>
    <?php
    $query = "SELECT id, uuid, name, status FROM questions ORDER BY name";
    $result = $db->query($query);

    // Per incrementar la velocitat, guardem tot el codi en una variable i fem nomes un echo.
    $htmlCode = array();
    while ($row = $result->fetch_assoc()) {
        $htmlCode[] = '<tr>';
        $htmlCode[] = '<td>';
        $htmlCode[] = '<a href="'. $_SERVER['PHP_SELF'] .'?a=editquiz&item=' . $row['id'] . '">' . $row['name'] . '</a> <a href="quiz.php?a=answerqz&item=' . $row['uuid'] . '" title="Enllaç p&uacute;blic" target="_blank"><span class="glyphicon glyphicon-link"></span></a>';
        $htmlCode[] = '</td>';
        $htmlCode[] = '<td>';

        if ( 'inactive' == $row['status'] ) {
            $htmlCode[] = '<span class="label label-danger">inactiva</span>';
        }
        if ( 'draft' == $row['status'] ) {
            $htmlCode[] = '<span class="label label-info">esborrany</span>';
        }
        if ( 'hidden' == $row['status'] ) {
            $htmlCode[] = '<span class="label label-warning">oculta</span>';
        }
        if ( 'active' == $row['status'] ) {
            $htmlCode[] = '<span class="label label-success">publicada</span>';
        }

        $htmlCode[] = '</td>';
        $htmlCode[] = '<td>' . getQuestionResponses($row['uuid']) . '</td>';
        $htmlCode[] = '<td></td>';
        $htmlCode[] = '<td>';
        $htmlCode[] = '<a href="'. $_SERVER['PHP_SELF'] .'?a=editquiz&item='. $row['id'] .'" class="btn btn-default" role="button"><span class="glyphicon glyphicon-edit"></span> Editar</a>';
        $htmlCode[] = '<a href="'. $_SERVER['PHP_SELF'] .'?a=previewquiz&item='. $row['id'] .'" class="btn btn-default" role="button"><span class="glyphicon glyphicon-eye-open"></span> Veure</a>';
        $htmlCode[] = '<a href="'. $_SERVER['PHP_SELF'] .'?a=deletequiz&item='. $row['id'] .'" class="btn btn-danger" role="button"><span class="glyphicon glyphicon-trash"></span> El·liminar</a>';
        $htmlCode[] = '</td>';
        $htmlCode[] = '</tr>';
    }
    echo implode(PHP_EOL, $htmlCode);
    unset($htmlCode);
    ?>
                    </tbody>
                </table>
                            </div>
                        </div>
    <?php
    echo getHTMLDataTable('#questions');
} // END print_quiz_management()

function printEditQuestionForm( $question_id, $msg = array() ) {
    global $db;

    $missatges = array();

    // question_id must be integer
    $question_id = intval($question_id);

    // get question data from DB
    $query = sprintf( "SELECT * FROM questions WHERE id='%d' LIMIT 1", $question_id );
    $result = $db->query($query);

    if ($result->num_rows == 0) {
        // No existeix.
        $missatges[] = array('type' => "error", 'msg' => "No he trobat informaci&oacute; per aquesta pregunta.");
        printQuestionManagement($missatges);
        return false;
    }
    $data = $result->fetch_assoc();

    // get all question_choices data from DB
    $query = sprintf( "SELECT * FROM questions_choices WHERE question_id='%d'", $question_id );
    $result = $db->query($query);

    $data['choices'] = array();
    $data['points'] = array();
    $data['correct'] = array();
    while ( $row = $result->fetch_assoc() ) {
        $data['choices'][] = $row['choice'];
        $data['points'][] = $row['points'];
        $data['correct'][] = $row['correct'];
    }

    // get all question_actions data from DB
    $query = sprintf( "SELECT * FROM questions_badges WHERE question_id='%d'", $question_id );
    $result = $db->query($query);

    $data['actions'] = array();
    $data['when'] = array();
    while ( $row = $result->fetch_assoc() ) {
        $data['actions'][] = $row['badge_id'];
        $data['when'][] = $row['type'];
    }
    ?>
                        <h1>Editar pregunta</h1>
                        <p><?php echo getHTMLMessages($msg); ?></p>
                        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" class="form-horizontal" role="form">

                            <?php printQuestionContentForm($data); ?>

                            <div class="form-group">
                                <div class="col-sm-offset-2 col-sm-10">
                                    <input type="hidden" id="item" name="item" value="<?php echo $data['id']; ?>">
                                    <input type="hidden" id="a" name="a" value="savequiz">
                                    <button type="submit" class="btn btn-success"><span class="glyphicon glyphicon-save"></span> Actualitzar dades</button>
                                    <a href="<?php echo $_SERVER['PHP_SELF']; ?>?a=quiz" class="btn btn-danger" role="button"><span class="glyphicon glyphicon-retweet"></span> Tornar</a>
                                </div>
                            </div>
                        </form>
    <?php
} // END print_editquiz_form()

function printNewQuestionForm( $data = array(), $msg = array() ) {
    global $db;
    ?>
                        <h1>Nova pregunta</h1>
                        <p><?php echo getHTMLMessages($msg); ?></p>
                        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" class="form-horizontal" role="form">

                            <?php printQuestionContentForm($data); ?>

                            <div class="form-group">
                                <div class="col-sm-offset-2 col-sm-10">
                                    <input type="hidden" id="a" name="a" value="createquiz">
                                    <button type="submit" class="btn btn-success"><span class="glyphicon glyphicon-save"></span> Crear pregunta</button>
                                    <a href="<?php echo $_SERVER['PHP_SELF']; ?>?a=quiz" class="btn btn-danger" role="button"><span class="glyphicon glyphicon-retweet"></span> Tornar</a>
                                </div>
                            </div>
                        </form>

    <?php
} // END print_newquiz_form()

function createQuestion( $data = array() ) {
    global $db;

    $missatges = array();

    // TODO - Validate supplied data

    // Question data is correct, now we can insert it to DB
    $query = sprintf( "INSERT INTO questions SET uuid='%s', name='%s', image='%s', question='%s', tip='%s', solution='%s', type='%s', status='%s'",
            $db->real_escape_string(getNewUUID()),
            $db->real_escape_string($data['name']),
            $db->real_escape_string($data['image']),
            $db->real_escape_string($data['question']),
            $db->real_escape_string($data['tip']),
            $db->real_escape_string($data['solution']),
            $db->real_escape_string($data['type']),
            $db->real_escape_string($data['status'])
            );

    $db->query($query);

    // Get new question_id or 0 on error.
    $question_id = $db->insert_id;

    if ( 0 == $question_id ) {
            die($query);
            $missatges[] = array('type' => "error", 'msg' => "No s'ha pogut crear la pregunta.");
            printNewQuestionForm($data, $missatges);
            return false;
    }

    // put choices into its table
    foreach ( $data['choices'] as $key => $value ) {

        // validate supplied data
        if ( empty($value) ) continue;

        $query = sprintf( "INSERT INTO questions_choices SET question_id='%d', choice='%s', correct='%s', points='%d'",
                $question_id,
                $db->real_escape_string($value),
                $data['correct'][$key],
                intval($data['points'][$key])
                );
        $db->query($query);
    }

    // put actions into its table
    foreach ( $data['actions'] as $key => $value ) {

        // validate supplied data
        $value = intval($value);
        if ( empty($value) ) continue;

        $query = sprintf( "INSERT INTO questions_badges SET question_id='%d', badge_id='%d', type='%s'",
                $question_id,
                $value,
                $data['when'][$key]
                );
        $db->query($query);
    }

    $missatges[] = array('type' => "success", 'msg' => "La pregunta s'ha creat correctament.");
    printQuestionManagement($missatges);
    return true;
} // END create_quiz()

function saveQuestionData( $data = array() ) {
    global $db;

    $missatges = array();

    // TODO - Validate supplied data
    $data['id'] = intval($data['id']);

    // delete all choices and insert it again
    $query = sprintf( "DELETE FROM questions_choices WHERE question_id='%d'", $data['id'] );
    $db->query($query);

    // put choices into its table
    foreach ( $data['choices'] as $key => $value ) {

        // validate supplied data
        if ( empty($value) ) continue;

        $query = sprintf( "INSERT INTO questions_choices SET question_id='%d', choice='%s', correct='%s', points='%d'",
                $data['id'],
                $db->real_escape_string($value),
                $data['correct'][$key],
                intval($data['points'][$key])
                );
        $db->query($query);
    }

    // delete all actions and insert it again
    $query = sprintf( "DELETE FROM questions_badges WHERE question_id='%d'", $data['id'] );
    $db->query($query);

    // put actions into its table
    foreach ( $data['actions'] as $key => $value ) {

        // validate supplied data
        $value = intval($value);
        if ( empty($value) ) continue;

        $query = sprintf( "INSERT INTO questions_badges SET question_id='%d', badge_id='%d', type='%s'",
                $data['id'],
                $value,
                $data['when'][$key]
                );
        $db->query($query);
    }

    // Question data is correct, now we can insert it to DB
    $query = sprintf( "UPDATE questions SET name='%s', image='%s', question='%s', tip='%s', solution='%s', type='%s', status='%s' WHERE id='%d' LIMIT 1",
            $db->real_escape_string($data['name']),
            $db->real_escape_string($data['image']),
            $db->real_escape_string($data['question']),
            $db->real_escape_string($data['tip']),
            $db->real_escape_string($data['solution']),
            $db->real_escape_string($data['type']),
            $db->real_escape_string($data['status']),
            $data['id']
            );

    if ( $db->query($query) ) {
        $missatges[] = array('type' => "success", 'msg' => "Dades actualitzades.");
        printQuestionManagement($missatges);

    } else {
        $missatges[] = array('type' => "error", 'msg' => "No s'ha pogut actualitzar les dades.");
        printEditQuestionForm($data, $missatges);
    }
} // END save_quiz_data()

function deleteQuestion($question_id) {
    global $db;

    // question_id must be an integer
    $question_id = intval($question_id);

    // delete all choices
    $query = sprintf( "DELETE FROM questions_choices WHERE question_id='%d'", $question_id );
    $db->query($query);

    // delete all actions
    $query = sprintf( "DELETE FROM questions_badges WHERE question_id='%d'", $question_id );
    $db->query($query);

    $query = sprintf( "DELETE FROM questions WHERE id='%d' LIMIT 1", $question_id );
    return $db->query($query);
} // END delete_quiz()

function printQuestionContentForm( $data ) {
    global $db;

    ?>

                           <div class="form-group">
                                <label class="col-sm-2 control-label">Nom</label>
                                <div class="col-sm-10">
                                    <input type="text" name="name" class="form-control" placeholder="Títol de la pregunta" value="<?php if (isset($data['name'])) echo $data['name']; ?>" required>
                                </div>
                           </div>

                            <div class="form-group">
                                <label class="col-sm-2 control-label">Estat</label>
                                <div class="col-sm-2">
                                    <select name="status" class="form-control">
                                    <?php
                                    $available_options = array(
                                        'draft' => 'Esborrany',
                                        'active' => 'Activa',
                                        'hidden' => 'Oculta',
                                        'inactive' => 'Inactiva'
                                        );
                                    echo getHTMLSelectOptions($available_options, $data['status']);
                                    ?>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-2 control-label">Pregunta</label>
                                <div class="col-sm-10">
                                    <textarea name="question" class="form-control tinymce" rows="3" placeholder="Quina és la pregunta?"><?php if ( isset($data['question']) ) echo $data['question']; ?></textarea>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="image" class="col-sm-2 control-label">Imatge</label>
                                <div class="col-sm-10">
                                    <input type="text" name="image" class="form-control" placeholder="URL de l'imatge (opcional)" value="<?php if (isset($data['image'])) echo $data['image']; ?>">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-2 control-label">Tipus</label>
                                <div class="col-sm-2">
                                    <select name="type" class="form-control">
                                    <?php
                                    $available_options = array(
                                        'single' => 'Resposta única',
                                        'multi' => 'Resposta multiple'
                                        );
                                    echo getHTMLSelectOptions($available_options, $data['type']);
                                    ?>
                                    </select>
                                </div>
                            </div>

                            <legend>Respostes possibles</legend>
                            <div class="form-group">
                                <label class="col-sm-offset-2 col-sm-6">Text de la resposta</label>
                                <label class="col-sm-2">Punts</label>
                                <label class="col-sm-1">correcta?</label>
                            </div>

                            <?php

                            foreach ( $data['choices'] as $key => $value ) {
                                    ?>
                            <div class="clonable">
                                <div class="form-group">
                                    <div class="col-sm-offset-2 col-sm-6">
                                        <input type="text" name="choices[]" class="form-control" placeholder="Text de la resposta" value="<?php echo $value; ?>">
                                    </div>
                                    <div class="col-sm-2">
                                        <input type="text" name="points[]" class="form-control" placeholder="Punts" value="<?php echo $data['points'][$key]; ?>">
                                    </div>
                                    <div class="col-sm-1">
                                        <select name="correct[]" class="form-control">
                                        <?php
                                            $available_options = array(
                                                'yes' => 'Si',
                                                'no' => 'No'
                                        );
                                        echo getHTMLSelectOptions($available_options, $data['correct'][$key]);
                                        ?>
                                        </select>
                                    </div>
                                    <div class="col-sm-1">
                                        <span class="input-group-btn"><button type="button" class="btn btn-danger btn-trash">-</button></span>
                                    </div>
                                </div>
                           </div>
                                    <?php
                            }
                            ?>


                            <div class="clonable">
                                <div class="form-group">
                                    <div class="col-sm-offset-2 col-sm-6">
                                        <input type="text" name="choices[]" class="form-control" placeholder="Text de la resposta">
                                    </div>
                                    <div class="col-sm-2">
                                        <input type="text" name="points[]" class="form-control" placeholder="Punts">
                                    </div>
                                    <div class="col-sm-1">
                                        <select name="correct[]" class="form-control">
                                        <?php
                                            $available_options = array(
                                                'yes' => 'Si',
                                                'no' => 'No'
                                        );
                                        echo getHTMLSelectOptions($available_options, 'no');
                                        ?>
                                        </select>
                                    </div>
                                    <div class="col-sm-1">
                                        <span class="input-group-btn"><button type="button" class="btn btn-default btn-add">+</button></span>
                                    </div>
                                </div>
                           </div>

                            <div class="form-group">
                                <label class="col-sm-2 control-label">Solució explicada</label>
                                <div class="col-sm-10">
                                    <textarea name="solution" class="form-control tinymce" rows="3" placeholder="Quina és la solució detallada? (opcional)"><?php if ( isset($data['solution']) ) echo $data['solution']; ?></textarea>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="tip" class="col-sm-2 control-label">Text a cercar</label>
                                <div class="col-sm-10">
                                    <input type="text" name="tip" class="form-control" placeholder="Text d'ajuda a la cerca (opcional)" value="<?php if (isset($data['tip'])) echo $data['tip']; ?>">
                                </div>
                            </div>

                            <?php
                            $query = "SELECT id, name FROM badges WHERE status='active'";
                            $result = $db->query($query);
                            $available_actions = array();
                            while( $row = $result->fetch_assoc() ) {
                                $available_actions[$row['id']] = $row['name'];
                            }
                            ?>

                            <legend>Accions associades</legend>

                            <?php
                            foreach ($data['actions'] as $key => $value) {
                                ?>
                            <div class="clonable">
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">Afegir acció</label>
                                    <div class="col-sm-4">
                                        <select name="actions[]" class="form-control">
                                            <option value="">Sense acció</option>
                                            <?php echo getHTMLSelectOptions($available_actions, $value); ?>
                                        </select>
                                    </div>
                                    <label class="col-sm-1 control-label">Quan</label>
                                    <div class="col-sm-4">
                                        <select name="when[]" class="form-control">
                                        <?php
                                        $available_options = array(
                                            'success' => 'Resposta correcta',
                                            'fail' => 'Resposta incorrecta',
                                            'always' => 'Sempre'
                                            );
                                        echo getHTMLSelectOptions($available_options, $data['when'][$key]);
                                        ?>
                                        </select>
                                    </div>
                                    <div class="col-sm-1">
                                        <span class="input-group-btn"><button type="button" class="btn btn-danger btn-trash">-</button></span>
                                    </div>
                                </div>
                            </div>
                                <?php

                            }
                            ?>
                            <div class="clonable">
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">Afegir acció</label>
                                    <div class="col-sm-4">
                                        <select name="actions[]" class="form-control">
                                            <option value="">Sense acció</option>
                                            <?php echo getHTMLSelectOptions($available_actions); ?>
                                        </select>
                                    </div>
                                    <label class="col-sm-1 control-label">Quan</label>
                                    <div class="col-sm-4">
                                        <select name="when[]" class="form-control">
                                        <?php
                                        $available_options = array(
                                            'success' => 'Resposta correcta',
                                            'fail' => 'Resposta incorrecta',
                                            'always' => 'Sempre'
                                            );
                                        echo getHTMLSelectOptions($available_options, 'always');
                                        ?>
                                        </select>
                                    </div>
                                    <div class="col-sm-1">
                                        <span class="input-group-btn"><button type="button" class="btn btn-default btn-add">+</button></span>
                                    </div>
                                </div>
                            </div>
    <?php
} // END print_quiz_form_content()

function printPreviewQuestion($question_id) {
    global $db;

    $query = sprintf( "SELECT * FROM questions WHERE id='%s' LIMIT 1", $db->real_escape_string($question_id) );
    $result = $db->query($query);

    if ( 0 == $result->num_rows ) {
        // La pregunta que ens han passat no existeix, per tant tornem a mostrar la llista.
        $missatges[] = array('type' => "error", 'msg' => "No he trobat informaci&oacute; per aquesta pregunta.");
        printQuestionManagement();
        return false;
    }

    $question = $result->fetch_assoc();

    // get question's choices, if none, return
    $query = sprintf( "SELECT * FROM questions_choices WHERE question_id='%d'", $question_id);
    $result = $db->query($query);

    $question['choices'] = array();
    while ( $row = $result->fetch_assoc() ) {
        $question['choices'][] = $row;
    }

    if ( empty($question['image']) ) {
        $question['image'] = 'images/question_default.jpg';
    }

    ?>
    <h1>Veure pregunta
    <a href="<?php echo $_SERVER['PHP_SELF']; ?>?a=editquiz&item=<?php echo $question_id; ?>" class="btn btn-info" role="button"><span class="glyphicon glyphicon-edit"></span> Editar</a>
    <a href="<?php echo $_SERVER['PHP_SELF']; ?>?a=quiz" class="btn btn-danger" role="button"><span class="glyphicon glyphicon-retweet"></span> Tornar</a>
    </h1>

    <div class="panel panel-default" width="70%">
        <div class="panel-heading">
            <h2><?php echo $question['name']; ?></h2>
        </div>
        <div class="panel-body">
            <img src="<?php echo $question['image']; ?>" width="120" class="img-rounded">
            <h4><?php echo $question['question']; ?></h4>
                <ul class="list-group">
                    <?php
                        $htmlCode = array();
                        foreach ($question['choices'] as $choice) {
                            $htmlCode[] = '<li class="list-group-item">';
                                if ( 'yes' == $choice['correct'] ) {
                                    $htmlCode[] = '<span class="glyphicon glyphicon-ok"></span>';
                                } else {
                                    $htmlCode[] = '<span class="glyphicon glyphicon-remove"></span>';
                                }
                            $htmlCode[] = $choice['choice'];
                            $htmlCode[] = '<span class="badge pull-right">' . $choice['points'] .'</span>';
                            $htmlCode[] = '</li>';

                        }
                        echo implode(PHP_EOL, $htmlCode);
                    ?>
                </ul>
            <?php
                if ( !empty($question['solution']) ) {
                // nomes mostrem la resposta si l'usuari ha respost la pregunta
                echo '<div class="alert alert-info"><p><strong>La resposta correcta és: </strong></p><p>'. $question['solution'] .'</p></div>';
                }
            ?>
        </div>
    </div>
    <?php
} // END print_preview_quiz()

