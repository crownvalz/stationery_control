<?php 

$sName = "localhost";
$uName = "root";
$dbpassord = "";
$dbname = "stationery_db";

try {
    $conn = new PDO("mysql:host=$sName;dbname=$dbname", $uName, $dbpassord);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}catch(PDOException $e){
  echo "Connection failed : ". $e->getMessage();
}