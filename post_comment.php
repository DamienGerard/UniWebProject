<?php include 'session_login.php';?>

<?php

use Opis\JsonSchema\{
    Validator, ValidationResult, ValidationError, Schema
};

require 'vendor/autoload.php';
$comment_data = json_decode(file_get_contents('php://input'));
$comment_schema = Schema::fromJsonString(file_get_contents('comment_schema.json'));
$comment_validator = new Validator();

/** @var ValidationResult $result */
$result = $comment_validator->schemaValidation($comment_data, $comment_schema);

if($result->isValid()){
    $text = $comment_data->text;
    $thisViewId = $comment_data->view_id;
    $content_type = $comment_data->content_type;
    $user_id = $comment_data->user_id;

    if($text != ''){
        $query = "INSERT INTO webprojectdatabase.comment(comment.comment_id, comment.text, comment.date_time) VALUES(:comment_id, :text, :date_time)";
        $values = array(':comment_id'=>$thisViewId, ':text'=>$text, ':date_time'=>date('Y-m-d H:i:s'));

        $pdo->beginTransaction();

        try {
            $res = $pdo->prepare($query);
            $res->execute($values);
        }catch (PDOException $e){
            throw new Exception('Database query error');
        }   
        
        $query = "INSERT INTO webprojectdatabase.notification(notification.event_id, notification.event_type, notification.cause_id, notification.cause_type, notification.date, notification.user_id, notification.seen) VALUES(:event_id, :event_type, :cause_id, :cause_type, :date, :user_id, :seen)";
        $values = array(':event_id'=>$thisViewId, ':event_type'=>'comment_'.$content_type, ':cause_id'=>$account->getId(), ':cause_type'=>'user', ':date'=>date("Y-m-d"), ':user_id'=>$user_id, ':seen'=>0);  

        try {
            $res = $pdo->prepare($query);
            $res->execute($values);
        }catch (PDOException $e){
            throw new Exception('Database query error');
        }   
        
        $pdo->commit();
        echo json_encode(array("status"=>"success"));
    }
}








