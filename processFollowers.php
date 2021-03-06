<?php
use Opis\JsonSchema\{
    Validator, ValidationResult, ValidationError, Schema
};

require 'vendor/autoload.php';
include 'session_login.php';

if(isset($_GET["id"])){
    $id = $_GET["id"];
}

$query = "SELECT user.f_name,user.l_name,user.profile_pic,follow.date_since,user.user_name,user.self_description FROM user INNER JOIN follow on user.user_id=follow.follower WHERE follow.followed=".$id;

$result = $pdo->query($query);

$followers = $result->fetchAll(PDO::FETCH_ASSOC);

$data = json_encode($followers);
//header('Content-Type: application/json'); 
$data1 = json_decode($data);

$schema = Schema::fromJsonString(file_get_contents('userSchema.json'));
$validator = new Validator();

/** @var ValidationResult $result */
$result = $validator->schemaValidation($data1, $schema);

if ($result->isValid()) {
    
    //echo '$data is valid', PHP_EOL;
    header('Content-Type: application/json'); 
    echo $data;
}


?>