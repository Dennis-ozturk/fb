<?php 
include_once 'db/db.php';
session_start();
include_once('src/user.inc.php');
$logout = new User();
$logout->exit();