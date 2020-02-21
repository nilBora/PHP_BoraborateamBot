<?php
namespace Bot\Database;

class PdoObjectAdapter
{
    protected $db;
    
    public function __construct()
    {
        $db = new \PDO(
            'mysql:host='.getenv("DB_HOST").';dbname='.getenv("DB_NAME"),
            getenv("DB_USER"),
            getenv("DB_PASSWORD")
        );
        
        $this->db = $db;
    }
    
    public function select($sql)
    {
        $results = array();
        foreach($this->db->query($sql) as $row) {
            $results[] = $row;
        }
        
        return $results;
    }
    
    public function insert($table, $values)
    {
        $valuesSql = implode(",", array_fill(0, count($values), '?'));
        $sql = "INSERT INTO ".$table." (".implode(", ", array_keys($values)).") VALUES (".$valuesSql.")";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_values($values));
    
        return $this->db->lastInsertId();
        
        //$sql = "INSERT INTO ".$table." (".implode(", ", array_keys($values)).") VALUES (:type, :data, :cdate)";
        //$stmt= $this->db->prepare($sql);
        //$stmt->execute($values);
    }
}