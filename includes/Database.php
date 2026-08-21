<?php
// includes/Database.php

class Database {
    private static $instance = null;
    private $pdo;
    
    private function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->pdo;
    }
    
    public function query($sql, $params = []) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
    
    public function fetch($sql, $params = []) {
        $result = $this->query($sql, $params)->fetch();
        return $result ?: null;
    }
    
    public function fetchAll($sql, $params = []) {
        return $this->query($sql, $params)->fetchAll();
    }
    
    public function insert($table, $data) {
        $keys = array_keys($data);
        $placeholders = implode(', ', array_fill(0, count($keys), '?'));
        $sql = "INSERT INTO $table (" . implode(', ', $keys) . ") VALUES ($placeholders)";
        $this->query($sql, array_values($data));
        return (int) $this->pdo->lastInsertId();
    }
    
    public function update($table, $data, $where, $whereParams = []) {
        $set = implode(' = ?, ', array_keys($data)) . ' = ?';
        $params = array_merge(array_values($data), $whereParams);
        $sql = "UPDATE $table SET $set WHERE $where";
        return $this->query($sql, $params)->rowCount();
    }
    
    public function delete($table, $where, $params = []) {
        $sql = "DELETE FROM $table WHERE $where";
        return $this->query($sql, $params)->rowCount();
    }
    
    public function beginTransaction() {
        $this->pdo->beginTransaction();
    }
    
    public function commit() {
        $this->pdo->commit();
    }
    
    public function rollback() {
        $this->pdo->rollback();
    }
}