<?php 
session_start();
    require 'vendor/autoload.php'; 
    use Dotenv\Dotenv;
    use Illuminate\Database\Capsule\Manager as Capsule;
    
    $dotenv = Dotenv::createImmutable(__DIR__);
    $dotenv->load();

    define('DBHOST', $_ENV['DBHOST']);
    define('DBNAME', $_ENV['DBNAME']);
    define('DBUSER', $_ENV['DBUSER']);
    define('DBPASS', $_ENV['DBPASS']);
    define('DBPORT', $_ENV['DBPORT']);
    define('SMTP_SERVER', $_ENV['SMTP_SERVER']);
    define('SMTP_USER', $_ENV['SMTP_USER']);
    define('SMTP_PASS', $_ENV['SMTP_PASS']);
    
    $capsule = new Capsule;
    $capsule->addConnection([
        "driver" => "mysql",
        "host" => DBHOST,
        "database" => DBNAME,
        "username" => DBUSER,
        "password" => DBPASS
    ]);
    $capsule->setAsGlobal();
    $capsule->bootEloquent();


?>