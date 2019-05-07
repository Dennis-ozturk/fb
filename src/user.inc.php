<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require('../vendor/autoload.php');

class User
{
    private $db;
    public function __construct()
    {
        $this->db = new DB();
        $this->db = $this->db->connect();
    }

    public function checkUserExists($fields)
    {
        //Checks if user already exists with email
        $stmt = $this->db->prepare("SELECT email FROM users WHERE email = :email");
        $stmt->bindValue(':email', $fields[':email'], PDO::PARAM_STR);
        $stmt->execute();
        if ($stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "Email already in use";
        } else {
            $this->registerUser($fields);
        }
    }

    public function registerUser($fields)
    {
        //Simple register
        $stmt = $this->db->prepare("INSERT INTO users(email, password) VALUES (:email, :password) ");
        foreach ($fields as $key => $value) {
            if ($key === ':password') {
                $hash = hash('sha256', $value);
                $stmt->bindValue($key, $hash, PDO::PARAM_STR);
            } else {
                $stmt->bindValue($key, $value, PDO::PARAM_STR);
            }
        }
        if ($stmt->execute()) {
            echo ("Registered");
            header('location: /');
        } else {
            echo ("Something went wrong");
        }
    }

    public function sendMail()
    {

        $mail = new PHPMailer();

        $mail->isSMTP();
        $mail->Host = 'smtp.mailtrap.io';
        $mail->SMTPAuth = true;
        $mail->Username = '2eb57a82b41b8e';
        $mail->Password = 'dfd10316b4ad32';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 2525;

        $mail->setFrom('dennis.dada@hotmail.se', 'Dennis Ozturk');
        $mail->addReplyTo('dennis.dada@hotmail.se', 'Dennis Ozturk');

        $mail->addAddress('dennis.dada@hotmail.se', 'Dennis Ozturk');

        $mail->Subject = 'PHPMailer SMTP test';

        $mail->AltBody = "Line 1\r\nLine 2\r\nLine3";
        $mail->Body = "Line 1\r\nLine 2\r\nLine3";

        if (!$mail->send()) {
            echo ("Message could not be sent");
            echo ("Mailer error" . $mail->ErrorInfo);
        } else {
            echo ("Message sent");
        }
    }

    public function getAllUsers($email, $password)
    {
        //Get all users from db and set session to email
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email AND password = :password");
        $hash = hash('sha256', $password);
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->bindValue(':password', $hash, PDO::PARAM_STR);
        if ($stmt->execute() && $stmt->fetchColumn()) {
            $_SESSION['user'] = $email;
            header('location: /');
        }
    }

    public function exit()
    {
        unset($_SESSION["user"]);
        session_destroy();
        header("Location: /");
    }
}
