<?php
session_start();
include_once 'db/db.php';
include_once 'classes/user.inc.php';

$user = new User();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>

<body>
    <?php

    if (!empty($_SESSION['user'])) { 
            echo $_SESSION['user'];
        ?>
        <a href="logout.php">Logout</a>
        <form action="" method="POST">
            <input type="submit" name="generate" value="Generate Api key">
        </form>
        <?php
        $row = $user->getApi($_SESSION['user']);
        echo("Api key: " . $row[0]['api']);
        ?>
    <?php
} else { ?>
        <form action="" method="POST">
            <input type="email" name="email" placeholder="email">
            <br>
            <input type="password" name="password" placeholder="password">
            <br>
            <input type="submit" value="Register" name="register">
        </form>

        <br>

        <form action="" method="POST">
            <input type="email" name="email" placeholder="email">
            <br>
            <input type="password" name="password" placeholder="password">
            <br>
            <input type="submit" value="Login" name="login">
        </form>

    <?php } ?>
    <?php

    if (isset($_POST['register'])) {

        $fields = [
            ':email' => filter_input(INPUT_POST, 'email', FILTER_SANITIZE_STRING),
            ':password' => filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING),
        ];
        $user->checkUserExists($fields);
    }

    if (isset($_POST['login'])) {
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);
        $user->getAllUsers($email, $password);
    }

    if(isset($_POST['generate'])){
        $user->checkUserApi($_SESSION['user']);
    }

    ?>

</body>

</html>