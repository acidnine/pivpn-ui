<?php
require_once('auth.php');
header('Content-Type: application/json; charset=utf-8');

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    
    $message = '';
    // OPEN OR CREATE THE DATABASE FILE
    $db = new SQLite3('pivpn.sqlite', SQLITE3_OPEN_CREATE | SQLITE3_OPEN_READWRITE);
    $db->enableExceptions(true);
    if($_POST['cmd'] == 'username'){
        // VERIFY USERNAME LENGTH
        if(strlen($_POST['val']) <= 0){
            die(json_encode(array('result'=>false,'message'=>'Username cannot be empty.')));
        }
        // VERIFY USERNAME FITS THE REGEX
        if(preg_match('/[^a-zA-Z0-9.@_-]/',$_POST['val']) != 0){
            die(json_encode(array('result'=>false,'message'=>'Invalid username.')));
        }
        // VERIFY THE USERNAME DOESNT ALREADY EXIST
        $prestmt = $db->prepare('SELECT COUNT(*) AS `total` FROM `users` WHERE `login`=:login');
        $prestmt->bindValue(':login', $_POST['val']);
        $preresult = $prestmt->execute();
        $ret = $preresult->fetchArray(SQLITE3_ASSOC);
        if($ret['total'] > 0){
            die(json_encode(array('result'=>false,'message'=>'User already exists.')));
        }
        $stmt = $db->prepare('UPDATE `users` SET `login`=:login WHERE `id`=:id');
        $stmt->bindValue(':login', $_POST['val']);
        $message = 'Updated username.';
    }elseif($_POST['cmd'] == 'password'){
        if(strlen($_POST['val']) < 8){
            die(json_encode(array('result'=>false,'message'=>'Password is too short!')));
        }
        $stmt = $db->prepare('UPDATE `users` SET `password`=:password WHERE `id`=:id');
        $stmt->bindValue(':password', password_hash($_POST['val'], PASSWORD_DEFAULT));
        $message = 'Updated password.';
    }
    $stmt->bindValue(':id', $_POST['id']);
    $result = $stmt->execute();
    if($result){
        if($_POST['cmd'] == 'username'){
            $_SESSION['username'] = $_POST['val'];
        }
        echo json_encode(array('result'=>true,'message'=>$message));
    }else{
        echo json_encode(array('result'=>false,'message'=>'No data received.'));
    }
    
}
