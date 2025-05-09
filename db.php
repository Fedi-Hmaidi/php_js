<?php
function connectToDatabase() {
    $env = parse_ini_file(__DIR__ . '/.env');

    $host = $env['database_hostname'];
    $port = $env['database_port'];
    $dbname = $env['database_name'];
    $user = $env['database_username'];
    $password = $env['database_password'];

    try {
        $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;";
        $pdo = new PDO($dsn, $user, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        die("Database connection failed: " . $e->getMessage());
    }
}
?>
