<?php
require_once __DIR__ . '/config.php';

class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        try {
            $this->connection = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            die("Ошибка подключения к базе данных: " . $e->getMessage());
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    // Подготовленный запрос с параметрами
    public function query($sql, $params = []) {
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
    
    // Получение одной строки
    public function fetchOne($sql, $params = []) {
        return $this->query($sql, $params)->fetch();
    }
    
    // Получение всех строк
    public function fetchAll($sql, $params = []) {
        return $this->query($sql, $params)->fetchAll();
    }
    
    // Вставка данных
    public function insert($table, $data) {
        $fields = array_keys($data);
        $placeholders = ':' . implode(', :', $fields);
        $sql = "INSERT INTO $table (" . implode(', ', $fields) . ") VALUES ($placeholders)";
        $this->query($sql, $data);
        return $this->connection->lastInsertId();
    }
    
    // Обновление данных
    public function update($table, $data, $where, $whereParams = []) {
        $set = [];
        foreach ($data as $key => $value) {
            $set[] = "$key = :$key";
        }
        $sql = "UPDATE $table SET " . implode(', ', $set) . " WHERE $where";
        $params = array_merge($data, $whereParams);
        return $this->query($sql, $params)->rowCount();
    }
    
    // Удаление данных
    public function delete($table, $where, $params = []) {
        $sql = "DELETE FROM $table WHERE $where";
        return $this->query($sql, $params)->rowCount();
    }
    
    // Подготовка запроса (для совместимости)
    public function prepare($sql) {
        return $this->connection->prepare($sql);
    }
}

function db() {
    return Database::getInstance();
}