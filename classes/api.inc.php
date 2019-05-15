<?php

include 'db/db.php';

class API
{
    protected $table;
    protected $table_id;
    protected $fields;

    private $db;

    public function __construct()
    {
        $this->db = new DB();
        $this->db = $this->db->connect();
        $this->fields = array_column($this->getFields(), 'Field');
    }

    public function auth($api)
    {
        $stmt = $this->db->prepare('SELECT api FROM users WHERE api = :api');
        $stmt->bindValue(':api', $api[1], PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            return true;
        } else {
            return false;
        }
    }

    public function post($data)
    {
        array_shift($this->fields);

        $sql = "INSERT INTO $this->table (" . implode(', ', $this->fields) . ") " .
            'VALUES (:' . implode(', :', $this->fields) . ')';
        $statement = $this->db->prepare($sql);

        $getfield = $this->getFields();
        array_shift($getfield);

        foreach ($getfield as $field) {
            $filter = FILTER_SANITIZE_NUMBER_INT;
            $pdo_type = PDO::PARAM_INT;

            if (in_array(substr($field['Type'], 0, 4), ['varc', 'char', 'text'])) {
                $filter = FILTER_SANITIZE_STRING;
                $pdo_type = PDO::PARAM_STR;
            }

            $statement->bindValue($field['Field'], filter_var($data{$field['Field']}, $filter), $pdo_type);
        }

        return $statement->execute();
    }

    public function checkNameExist($fields)
    {
        //Checks if user already publisher with name exists
        $sql = "SELECT name FROM $this->table WHERE name = :name";
        $stmt = $this->db->prepare($sql);
    
        $stmt->bindValue(':name', $fields['name'], PDO::PARAM_STR);
        
        $stmt->execute();

        if ($stmt->fetch(PDO::FETCH_ASSOC)) {
            // var_dump($stmt);
            echo "Publisher already exist";
        } else {
            echo "Publisher created";
            $this->post($fields);
        }
    }
    
    public function get($id = null)
    {
        $parameters = null;

        $sql = "SELECT * FROM $this->table";

        if ($id !== null) {
            $sql .= " WHERE id = :id";
            $parameters = ['id' => $id];
        } else {
            echo "<br>";
        }

        $statement = $this->db->prepare($sql);
        $statement->execute($parameters);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getFields()
    {
        return $this->db->query("SHOW COLUMNS FROM $this->table")->fetchAll();
    }

    public function delete($id = null)
    {
        $sql = "DELETE FROM $this->table";
        $parameters = null;

        if ($id !== null) {
            $sql .= " WHERE $this->table_id = :table_id ";
            $parameters = ['table_id' => $id];
            $statement = $this->db->prepare($sql);
            return $statement->execute($parameters);
        }
    }

    public function getId($data)
    {
        return $this->db->query("SELECT id FROM $this->table WHERE id = $data")->fetchColumn();
    }

    public function put($id, $postman_data)
    {
        $id = explode('?', $id);
        $id = $id[0];

        if (!empty($id) && !empty($postman_data)) {
            $getid = $this->getId($id);
            $fields = $this->fields;
            array_shift($fields);
            
            // SQL QUERY START
            $sql = "UPDATE $this->table SET";
            $key_value = [];
    
            foreach ($postman_data as $key => $value) {
                $data = " $key = '$value'";
                array_push($key_value, $data);
            }
            // SQL QUERY CONTINUE
            $sql .= implode(', ', $key_value) ." WHERE $this->table_id = $id";
          
            $statement = $this->db->prepare($sql);

            foreach ($this->getFields() as $field) {
                if ($field['Field'] == $this->table_id) {
                    continue;
                }
                $filter = FILTER_SANITIZE_NUMBER_INT;
                $pdo_type = PDO::PARAM_INT;
        
                if (in_array(substr($field['Type'], 0, 4), ['varc', 'char', 'text'])) {
                    $filter = FILTER_SANITIZE_STRING;
                    $pdo_type = PDO::PARAM_STR;
                }
                $statement->bindValue($field['Field'], filter_var($postman_data[$field['Field']], $filter), $pdo_type);
            }
            return $statement->execute();
        } else {
            echo "data is null";
            echo "<br>";
            return false;
        }
    }
}
