<?php 
include_once 'db/db.php';
session_start();
include_once('classes/user.inc.php');
$logout = new User();
$logout->exit();