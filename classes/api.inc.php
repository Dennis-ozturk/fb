<?php
include 'db/db.php';

class API
{
    protected $table;
    protected $table_id;
    protected $fields;
    protected $auth;

    private $db;

    public function __construct()
    {
        $this->db = new DB();
        $this->db = $this->db->connect();

        $this->fields = array_column($this->getFields(), 'Field');
    }

    public function auth($api){
        $stmt = $this->db->prepare('SELECT api FROM users WHERE api = :api');
        $stmt->bindValue(':api', $api, PDO::PARAM_STR);

        if($stmt->execute()){
            return true;
        }else{
            return false;
        }
    }

    public function create($data)
    {
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
}
