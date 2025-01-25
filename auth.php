<?php
session_start();
if(!isset($_SESSION['AUTH'])){
    die(header('Location: login.php'));
}
