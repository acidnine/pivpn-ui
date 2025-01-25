<?php
require_once('auth.php');
header('Content-Type: application/json; charset=utf-8');

$db = new SQLite3('pivpn.sqlite', SQLITE3_OPEN_CREATE | SQLITE3_OPEN_READWRITE);
$db->enableExceptions(true);
$stmt = $db->prepare('SELECT `id`,`login`,`lastlogin` FROM `users`');
$result = $stmt->execute();
$users = array();
while($row = $result->fetchArray(SQLITE3_ASSOC)){
    $users[$row['id']] = $row;
}
echo json_encode($users);
