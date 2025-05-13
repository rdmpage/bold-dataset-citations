<?php

error_reporting(E_ALL);

global $config;

// Date timezone
date_default_timezone_set('UTC');

$config['database_engine'] = 'sqlite';
$config['pdo'] = 'sqlite:ldf.db';

//$config['database_engine'] = 'postgres';

?>

