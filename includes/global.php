<?php
//GLOBAL VARIABLE
error_reporting(E_ALL);
ini_set('display_errors', 1); 

define('ROOT_DIR',dirname(dirname(__FILE__)));

define('INCLUDES_DIR', ROOT_DIR . '/includes');
define('LIB_DIR', ROOT_DIR . '/lib');
define('JS_DIR', ROOT_DIR . '/js');

define('__APP_NAME__',  'IKS eOffice'); 


require_once(LIB_DIR.'/FirePHPCore/FirePHP.class.php');

ob_start();

date_default_timezone_set('Asia/Jakarta');

$firephp = FirePHP::getInstance(true);
//$firephp->setEnabled(false);
$firephp->setEnabled(true);


//$var = array('i'=>10, 'j'=>20);
//$firephp->log($var, 'Iterators');
//$firephp->log('Plain Message');     // or FB::
//$firephp->info('Info Message');     // or FB::
//$firephp->warn('Warn Message');     // or FB::
//$firephp->error('Error Message');   // or FB::
//$firephp->log('Message','Optional Label');
//$firephp->fb('Message', FirePHP::*);

?>