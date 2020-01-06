<?php
require_once __DIR__.'/php/main.php';

$pageURL = Routing();
ob_start();

if(!empty($pageURL)){
    include 'master.php';
}

ob_end_flush();
