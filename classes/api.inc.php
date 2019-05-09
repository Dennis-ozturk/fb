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
        $sql = "INSERT INTO $this->table (" . implode(',', $this->fields) . ") " .
            'VALUES (:' . implode(', :', $this->fields) . ')';

        $statement = $this->db->prepare($sql);

        foreach ($this->getFields() as $field) {
            $filter = FILTER_SANITIZE_NUMBER_INT;
            $pdo_type = PDO::PARAM_INT;

            if (in_array(substr($field['Type'], 0, 4), ['varc', 'char', 'text'])) {
                $filter = FILTER_SANITIZE_STRING;
                $pdo_type = PDO::PARAM_STR;
            }

            $statement->bindValue($field['Field'], filter_var($data->{$field['Field']}, $filter), $pdo_type);
        }

        return $statement->execute();
    }

    public function get($id = null)
    {
        $sql = "SELECT * FROM $this->table";
        $parameters = null;

        if ($id !== null) {
            $sql .= " WHERE $this->table_id = :table_id";
            $parameters = ['table_id' => $id];
        }

        $statement = $this->db->prepare($sql);
        $statement->execute($parameters);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getFields()
    {
        return $this->db->query("SHOW COLUMNS FROM $this->table;")->fetchAll();
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

    public function put($args, $body_data)
    {
        $body_data = json_decode(file_get_contents('php://input'), true);
        
        if (!empty($args) && !empty($body_data)) {
            $getid = $this->getId($args);
            $fields = $this->fields;
            unset($fields[0]);
            
            // SQL QUERY START
            $sql = "UPDATE $this->table SET";
            $key_value = [];
           
            foreach ($body_data as $column => $data) {
                $columndata = " $column = '$data'";
                array_push($key_value, $columndata);
            }
            // SQL QUERY CONTINUE
            $sql .= implode(', ', $key_value) ." WHERE $this->table_id = $args";
            
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

                $statement->bindValue($field['Field'], filter_var($body_data[$field['Field']]), $filter, $pdo_type);
            }
            return $statement->execute();
        } else {
            echo "data is null";
            echo "<br>";
            return false;
        }
    }
}
