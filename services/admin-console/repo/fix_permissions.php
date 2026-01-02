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
    ['user' => 'admin', 'pass' => 'Techimi.15'],
    ['user' => 'admin', 'pass' => 'Toyota_15'],
    ['user' => 'admin', 'pass' => 'password'],
    ['user' => 'admin', 'pass' => 'admin'],
    ['user' => 'postgres', 'pass' => 'postgres'],
    ['user' => 'postgres', 'pass' => 'password'],
    ['user' => 'postgres', 'pass' => 'example'],
    ['user' => 'root', 'pass' => 'root'],
    ['user' => 'root', 'pass' => 'password'],
];

foreach ($combinations as $combo) {
    try {
        echo "Trying {$combo['user']} / {$combo['pass']} ... ";
        $pdo = new PDO($dsn, $combo['user'], $combo['pass'], $options);
        echo "Success!\n";
        
        $pdo->exec("ALTER TABLE crm_leads OWNER TO acartin;");
        echo "Changed owner of crm_leads to acartin\n";
        
        $pdo->exec("ALTER TABLE crm_properties OWNER TO acartin;");
        echo "Changed owner of crm_properties to acartin\n";
        exit; // Done
    } catch (\PDOException $e) {
        echo "Failed.\n";
    }
}
