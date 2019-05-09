<?php

class API
{
    protected $table;
    protected $table_id;
    protected $fields;

    private $db;

    public function __construct()
    {
        include 'db/db.php';
        $this->db = new DB();
        $this->db = $this->db->connect();

        $this->fields = array_column($this->getFields(), 'Field');
    }

    public function post($data)
    {
        die;
        // Setup query.
        $sql = "INSERT INTO $this->table (" . implode(',', $this->fields) . ") " .
            'VALUES (:' . implode(', :', $this->fields) . ')';

        // Prepare query.
        $statement = $this->db->prepare($sql);

        // Bind values.
        foreach ($this->getFields() as $field) {
            // Different filter and pdo type depending on wether the field is string or number.
            // Not fool proof, but a beginning.
            $filter = FILTER_SANITIZE_NUMBER_INT;
            $pdo_type = PDO::PARAM_INT;

            // If the field type starts with one of the array items, then it's probably a string.
            if (in_array(substr($field['Type'], 0, 4), ['varc', 'char', 'text'])) {
                $filter = FILTER_SANITIZE_STRING;
                $pdo_type = PDO::PARAM_STR;
            }

            $statement->bindValue($field['Field'], filter_var($data->{$field['Field']}, $filter), $pdo_type);
        }

        // Execute query and return result.
        return $statement->execute();
    }

    public function get($id = null)
    {
        // Setup query.
        $sql = "SELECT * FROM $this->table";
        $parameters = null;

        if ($id !== null) {
            // If caller has provided id, then let's just look for that one product.
            $sql .= " WHERE $this->table_id = :table_id ";
            $parameters = ['table_id' => $id];
        }

        $statement = $this->db->prepare($sql);
        $statement->execute($parameters);

        // Return all posts.
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getFields()
    {
        return $this->db->query("SHOW COLUMNS FROM $this->table;")->fetchAll();
    }

    //den här funktionen tar bort alla rader eftersom att alla id:n är ett id.
    public function delete($id = null)
    {
        $sql = "DELETE FROM $this->table";
        $parameters = null;

        if ($id !== null) {
            // If caller has provided id, then let's just look for that one product.
            $sql .= " WHERE $this->table_id = :table_id ";
            $parameters = ['table_id' => $id];
        }
        
        $statement = $this->db->prepare($sql);
        return $statement->execute($parameters);
    }

    public function getId($data)
    {
        return $this->db->query("SELECT id FROM $this->table WHERE id = $data")->fetchColumn();
    }

    public function update($args, $body_data)
    {
        $body_data = json_decode(file_get_contents('php://input'), true);
        
        if (!empty($args) && !empty($body_data)) {
            $getid = $this->getId($args);

            $fields = $this->fields;
            unset($fields[0]);
            
            $sql = "UPDATE $this->table SET";

            foreach ($body_data as $column => $data) {
                $sql .= " $column = '$data' ";
            }
            
            $sql .= "WHERE $this->table_id = $args";
            
            print_r($sql);

            $statement = $this->db->prepare($sql);

            die;
            foreach ($this->getFields() as $field) {
                if ($field['Field'] == $this->table_id) {
                    continue;
                }

                $filter = FILTER_SANITIZE_STRING;

                $filter = FILTER_SANITIZE_NUMBER_INT;
                $pdo_type = PDO::PARAM_INT;

                if (in_array(substr($field['Type'], 0, 4), ['varc', 'char', 'text'])) {
                    $pdo_type = PDO::PARAM_STR;
                }
                $statement->bindValue($field['Field'], filter_var($body_data[$field['Field']]));
            }

            return $statement->execute();
        } else {
            echo "data is null";
            echo "<br>";
            return false;
        }
    }
}
