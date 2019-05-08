<?php
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

    public function getAllUsers($email, $password)
    {
        //Get all users from db and set session to email
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email AND password = :password");
        $hash = hash('sha256', $password);
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->bindValue(':password', $hash, PDO::PARAM_STR);
        if ($stmt->execute() && $stmt->fetchColumn()) {
            $_SESSION['user'] = $email;
            header('Location: index.php');
        }
    }

    public function checkUserApi($email)
    {
        $stmt = $this->db->prepare("SELECT api FROM users WHERE email = :email");
        $api = bin2hex(openssl_random_pseudo_bytes(16));
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->execute();

        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach($data[0] as $value){
            empty($value) || $value != $api ? $this->generateApi($email, $api) : '';
        }
    }

    public function generateNewApi($email, $api){
        $stmt = $this->db->prepare("UPDATE users SET api = :api WHERE email = :email");
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->bindValue(':api', $api, PDO::PARAM_STR);
    }

    public function generateApi($email, $api)
    {
        $stmt = $this->db->prepare("UPDATE users SET api = :api WHERE email = :email");
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->bindValue(':api', $api, PDO::PARAM_STR);

        if ($stmt->execute()) {
            echo 'success';
        }
    }

    public function getApi($email)
    {
        $stmt = $this->db->prepare("SELECT api FROM users WHERE email = :email");
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        if ($stmt->execute()) {
            if ($stmt->rowCount() > 0) {
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $data[] = $row;
                }
                if (!empty($data[0]['api'])) {
                    return $data;
                } else {
                    echo "No api key generated";
                }
            }
        }
    }

    public function exit()
    {
        unset($_SESSION["user"]);
        session_destroy();
        header("Location: index.php");
    }
}
