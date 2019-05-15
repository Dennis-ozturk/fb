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
        //får en ny array som innehåller den aktuella kolumnen,i detta fall ´Field´
    }

//Create record
    public function post($data)
    {
        // Setup query. INSERT INTO authors (id, lastname, firstname) VALUES (:id, :lastname, :firstname)
        //id, :lastname, :firstname
        $fields = [];
        foreach($this->fields as $field) {
            if ($field !== $this->table_id) {
                $fields[] = $field;
            }
        }

        $sql = "INSERT INTO $this->table (" . implode(',', $fields) . ") " .
            'VALUES (:'  . implode(', :', $fields) . ')';

        // Prepare query.
        $statement = $this->db->prepare($sql);

        // Bind values.
        foreach ($this->getFields() as $field) {
            if ($field['Field'] === $this->table_id) {
                continue;
            }
            // Different filter and pdo type depending on whether the field is string or number.
            // Not fool proof, but a beginning.
            $filter = FILTER_SANITIZE_NUMBER_INT;//id
            $pdo_type = PDO::PARAM_INT;

            // If the field type starts with one of the array items, then it's probably a string.
            if (in_array(substr($field['Type'], 0, 4), ['varc', 'char', 'text'])) {
                $filter = FILTER_SANITIZE_STRING;
                $pdo_type = PDO::PARAM_STR;
            }
    
            $statement->bindValue($field['Field'], filter_var($data[$field['Field']], $filter), $pdo_type);
                                                              //$data->id //binder ihop placeholdern med aktuell definierad variabel i detta fall till? $data                                                              
        }
        // Execute query and return result.
        return $statement->execute();
    }

//Get record
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
        return $statement->fetchAll();
    }

    public function getFields()
    {
        return $this->db->query("SHOW COLUMNS FROM $this->table;")->fetchAll();
    }

//Update record
    public function put($data)
    { 
    $id = null; 
    //    //returnera false vid fel data. Kan inte uppdatera data
    //    //om data är satt så är data = id

    if (isset($data[$this->table_id])) { //en array
        $id = $data[$this->table_id];
    } else {
        return false;
    }

    $arr_fields = [];
    $sql = "UPDATE $this->table SET ";
    foreach ($data as $field_name => $field_value) {
        if ($field_name != $this->table_id) {
            $arr_fields[] = $field_name . " = '" . $field_value . "' ";
        }
    }
    $sql .= implode(', ', $arr_fields);
    $sql .= " WHERE $this->table_id = :table_id ";

    //echo $sql;
    //die;
    $statement = $this->db->prepare($sql);

    // Bind values.
    $statement->bindValue('table_id', $id, PDO::PARAM_STR);
    //$statement->bindValue($field['Field'], filter_var($data[$field['Field']], $filter), $pdo_type);
    //$data->id //binder ihop placeholdern med aktuell definierad variabel i detta fall till? $data                                                              
    //vad innehåller pdo_t
    // Execute query and return result.
    return $statement->execute();
    }

// Delete Post
    //public function delete() {
    //    // Create query
    //    //DELETE FROM authors WHERE id=35; 
    //$sql = "DELETE FROM ". $this->table . "WHERE id = $this->table_id";
    //    //var_dump($sql);
    //echo $sql; 
    //die;  
    //    // Prepare statement
    //$statement = $this->db->prepare($sql);
    //    
    //    //Execute query and return result.
    //return $statement->execute();
    //}         
}
