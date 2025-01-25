<?php
require_once('auth.php');
$install_check = shell_exec("which openvpn");
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // client name
    $name = preg_replace('/[^a-zA-Z0-9\.@_-]+/','',$_POST['name']);
    if(strlen($name) <= 0){echo json_encode(array('result'=>false));die();}
    
    // new client
    if($_POST['cmd'] == 'new'){
        if($install_check){// only openvpn
            // not using this: password_req
            $days = $_POST['days'];
            $password = $_POST['password'];
            if(empty($password)){
                $command = "pivpn -a nopass -n ".$name." -d ".$days;
            }else{
                $command = "pivpn -a -n ".$name." -p ".$password." -d ".$days;
            }
        }else{// only wireguard
            $command = "pivpn -a -n ".$name;
        }
        $output = shell_exec($command);
        if(stristr($output,"::: Done!")){
            echo json_encode(array('result'=>true));
        }else{
            echo json_encode(array('result'=>false,'output'=>$output));
        }
    }
    
    // enable client
    if($_POST['cmd'] == 'enable'){
        // pivpn -off '' -y
        $command = "pivpn -on ".$name." -y";
        $output = shell_exec($command);
        if(stristr($output, '::: Successfully enabled')){
            echo json_encode(array('result'=>true));
        }else{
            echo json_encode(array('result'=>false,'output'=>$output));
        }
    }
    
    // ( disable || delete ) client
    if($_POST['cmd'] == 'disable' || $_POST['cmd'] == 'delete'){
        // CLIENT NEEDS TO BE DISABLED BEFORE DELETING FOR SOME REASON :/
        // pivpn -off '' -y
        $command = "pivpn -off ".$name." -y";
        $output = shell_exec($command);
        // verify output from disable
        if(!stristr($output,'::: Successfully disabled') && !stristr($output,'is already disabled')){
            die(json_encode(array('result'=>false,'output'=>$output)));
        }
        if($_POST['cmd'] == 'delete'){// THIS COULD NOW BE MOVED OUTSIDE THE DISABLE CLAUSE DUE TO UI CHANGES
            // delete client
            $command = "pivpn -r ".$name." -y";
            $output = shell_exec($command);
            if(stristr($output,'::: Successfully deleted')){
                echo json_encode(array('result'=>true));
            }else{
                echo json_encode(array('result'=>false,'output'=>$output));
            }
        }else{
            die(json_encode(array('result'=>true)));
        }
    }
}
