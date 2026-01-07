<?php

$host = '192.168.0.31';
$db   = 'agentic';
$dsn = "pgsql:host=$host;port=5432;dbname=$db;";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

$combinations = [
    ['user' => 'acartin', 'pass' => 'Toyota_15'],
    ['user' => 'acartin', 'pass' => 'Techimi.15'],
    ['user' => 'admin', 'pass' => 'Techimi.15'],
    ['user' => 'admin', 'pass' => 'Toyota_15'],
    ['user' => 'postgres', 'pass' => 'postgres'],
];

foreach ($combinations as $combo) {
    try {
        echo "Trying {$combo['user']} / {$combo['pass']} ... ";
        $pdo = new PDO($dsn, $combo['user'], $combo['pass'], $options);
        echo "Success!\n";
        
        $tables = $pdo->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' AND table_name LIKE 'lead_%'")->fetchAll(PDO::FETCH_COLUMN);
        
        foreach ($tables as $table) {
            try {
                $pdo->exec("ALTER TABLE $table OWNER TO acartin;");
                echo "Changed owner of $table to acartin\n";
            } catch (\PDOException $e) {
                echo "Failed to change owner of $table: " . $e->getMessage() . "\n";
            }
        }
        exit; // Done
    } catch (\PDOException $e) {
        echo "Connection error: " . $e->getMessage() . "\n";
    }
}
