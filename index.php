<?php
header('Content-Type: text/html; charset=utf-8');
define("__BAAS_API__", TRUE);
require './config/config.php';
require './libs/application.php';
require './libs/controller.php';
require './libs/model.php';
session_start();
$app = new Application();
?>