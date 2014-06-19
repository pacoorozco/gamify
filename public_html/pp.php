<?php

require_once realpath(dirname(__FILE__) . '/../resources/lib/Bootstrap.class.inc');
\Pakus\Application\Bootstrap::init(APP_BOOTSTRAP_DATABASE);

require_once LIBRARY_PATH . '/Session2.class.inc';
$pp = new \Pakus\Application\Session2('pp');

$pp->set('userdata', 'hola');

echo "pp[" . $pp->get('userdata') ."]" . PHP_EOL;

echo "<a href=pp2.php>pp2.php</a>" . PHP_EOL;


