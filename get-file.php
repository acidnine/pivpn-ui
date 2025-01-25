<?php
require_once('auth.php');
$user = trim(shell_exec('whoami'));
$install_check = shell_exec("which openvpn");
if($_SERVER['REQUEST_METHOD'] === 'POST'){// JUST USE $_REQUEST ??
    $file = $_POST['file'];
}else{
    $file = $_GET['file'];
}
if($install_check){// openvpn
    // config files are in: /home/rpi/ovpns/
    $filepath = '/home/'.$user.'/ovpns/'.$file.'.conf';
    $dlname = 'openvpn-'.$file.'.conf';
}else{// wireguard
    // config files are in: /home/rpi/configs/*.conf
    $filepath = '/home/'.$user.'/configs/'.$file.'.conf';
    $dlname = 'wireguard-'.$file.'.conf';
}
if(file_exists($filepath) && is_readable($filepath)){
    if($_SERVER['REQUEST_METHOD'] === 'POST'){
        // WE ONLY USE POST FOR QR CODE
        header('Content-Type: application/json; charset=utf-8');
        $config = file_get_contents($filepath);
        echo json_encode(array('qr'=>$config));
    }else{
        // OFFER FILE DOWNLOAD
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $dlname . '"');
        readfile($filepath);
    }
    die();
}
header("HTTP/1.0 404 Not Found");
//} else {
//    // ADD LOGGING
//    // connect to database
//    // $file.' does not exist or cannot be read'
//    echo $errorMessage;
//}
