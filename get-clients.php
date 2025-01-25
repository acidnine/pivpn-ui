<?php
require_once('auth.php');
header('Content-Type: application/json; charset=utf-8');
putenv('LANG=en_US.UTF-8');

$allclients = true;
$name = '';
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $name = preg_replace('/[^a-zA-Z0-9\.@_-]+/','',$_POST['name']);
    if($_POST['cmd'] == 'get'){
        $allclients = false;
    }
    if($_POST['cmd'] == 'qr'){
        $connected_cmd = shell_exec("pivpn -qr ".$name);
        //var_dump($connected_cmd);
        $ini = strpos($connected_cmd, '=====================================================================');
        $ini += strlen('=====================================================================');
        $len = strpos($connected_cmd, '=====================================================================', $ini) - $ini;
        $qr = trim(substr($connected_cmd, $ini, $len));// trim to remove the leading \n
        //$qr = preg_replace('#\\x1b[[][^A-Za-z]*[A-Za-z]#','',str_replace("\n","<br>",str_replace(" ","&nbsp;",$qr)));// FOR SENDING TO PUT IN A div
        $qr = preg_replace('#\\x1b[[][^A-Za-z]*[A-Za-z]#','',$qr);// REMOVE TERMINAL CODES FROM STRING // FOR SENDING JUST TEXT FOR textarea
        die(json_encode(array('qr'=>$qr),JSON_UNESCAPED_UNICODE));
    }
}

// IT WOULD MAKE IT SO MUCH EASIER IF OPENVPN HAD THIS: sudo /usr/share/doc/wireguard-tools/examples/json/wg-json
//   THEN WE COULD JUST RUN THAT, PARSE THE OUTPUT AND BE DONE :/
$clients_cmd = shell_exec("pivpn -l");
$clients_list = explode("\n",$clients_cmd);
$clients = array();
$clients_dis = array();
$x = 0;
$enabled = true;
foreach($clients_list as $client_line){
    $client_line_clean = preg_replace('#\\x1b[[][^A-Za-z]*[A-Za-z]#','',$client_line);
    if($x > 1 && strlen($client_line_clean) > 50 && !stristr($client_line_clean, ":::")){
        $client = preg_split('/ {2,}/', trim($client_line_clean));
        $clients[$client[0]] = array();
        $clients[$client[0]]['pub_key'] = $client[1];
        $clients[$client[0]]['created'] = $client[2];
    }
    if($x > 1 && stristr($client_line_clean, "[disabled]")){
        $client = preg_split('/ {2,}/', trim($client_line_clean));
        $clients_dis[] = $client[1];
    }
    if($x > 1 && strlen($client_line_clean) > 50 && stristr($client_line_clean, "::: Disabled clients :::")){
        $enabled = false;
    }
    $x += 1;
}

$connected_cmd = shell_exec("pivpn -c");
$connected_list = explode("\n",$connected_cmd);
$connected = array();
$x = 0;
foreach($connected_list as $connected_line){
    if($x > 1 && strlen($connected_line) > 50 && !stristr($connected_line, ":::")){
        $conn = preg_split('/ {2,}/', trim($connected_line));
        if(array_key_exists($conn[0],$clients)){
            $clients[$conn[0]]['remote_ip'] = $conn[1];
            $clients[$conn[0]]['virtual_ip'] = $conn[2];
            $clients[$conn[0]]['bytes_in'] = $conn[3];
            $clients[$conn[0]]['bytes_out'] = $conn[4];
            $clients[$conn[0]]['seen'] = $conn[5];
        }else{
            // something is wrong, there is a "connected" client but no matching client list
        }
    }
    $x += 1;
}

// SPLIT OUR ARRAYS INTO ENABLED/DISABLED FOR EASIER DISPLAY PARSE ITERATION
$clients_enabled = array();
$clients_disabled = array();
foreach($clients as $key => $val){
    if(in_array($key, $clients_dis, true)){
        $clients_disabled[$key] = $clients[$key];
    }else{
        $clients_enabled[$key] = $clients[$key];
    }
}

// DETERMINE HOW TO OUTPUT
if($allclients == true){
    echo json_encode(array('enabled'=>$clients_enabled,'disabled'=>$clients_disabled));
}else{
    if(array_key_exists($name,$clients_enabled)){
        $client_out = $clients_enabled[$name];
        $client_out['enabled'] = true;
    }else{
        $client_out = $clients_disabled[$name];
        $client_out['enabled'] = false;
    }
    echo json_encode(array('client'=>$client_out));
}
