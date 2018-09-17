<?php
require_once 'vendor/autoload.php';

$s=new Switcher;
// print_r($s->loadConfigFile());
var_dump($s->callFailedNotify());
// print_r($s->getFailCount());
// print_r($s->getSipID());
$s->display();
// $s->output();
echo '<br>';
// $m = new MemcachedAPI();
// // print_r($m->set('asdftest',5));
// print_r($m->get('failcount'));
?>