<?php
session_start();
session_unset();
session_destroy();
die(header('Location: login.php'));
