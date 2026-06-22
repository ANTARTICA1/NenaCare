<?php
require_once 'config.php';
require_once 'classes/AuthManager.php';

$auth = new AuthManager($db);
$auth->logout();
