<?php
require_once('auth.php');

// OPEN OR CREATE THE DATABASE FILE
$db = new SQLite3('pivpn.sqlite', SQLITE3_OPEN_CREATE | SQLITE3_OPEN_READWRITE);
$db->enableExceptions(true);

if(!empty($_GET['limit'])){
    $limit = (int)$_GET['limit'];
}else{
    $limit = PHP_INT_MAX;
}

$stmt = $db->prepare('SELECT * FROM `users` ORDER BY `created` LIMIT :limit');
$stmt->bindValue(':limit', $limit);
$result = $stmt->execute();
$users = array();
echo '<h3>users</h3>';
echo '<pre>';
while($row = $result->fetchArray(SQLITE3_ASSOC)){
    echo json_encode($row)."\n";
}
echo '</pre>';
echo '<br>';


$stmt = $db->prepare('SELECT * FROM `login_history` ORDER BY `time` DESC LIMIT :limit');
$stmt->bindValue(':limit', $limit);
$result = $stmt->execute();
$users = array();
echo '<h3>login_history</h3>';
echo '<pre>';
while($row = $result->fetchArray(SQLITE3_ASSOC)){
    echo json_encode($row)."\n";
}
echo '</pre>';
echo '<br><br><br>';

