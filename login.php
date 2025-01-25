<?php
session_start();

// IF USER IS ALREADY LOGGED IN, REDIRECT OFF THE LOGIN PAGE
if(isset($_SESSION['AUTH']) && $_SESSION['AUTH'] === true){
    die(header('Location: ./'));
}

// SET DEFAULT VARIABLES -- THIS SHOULD BE CONDENSED AND DONE BETTER :/
$first_setup = FALSE;
$setup_error = FALSE;
$login_error = FALSE;
$brute_force = FALSE;

// CHECK FOR DATABASE FILE
if(!file_exists('pivpn.sqlite')){
    $first_setup = TRUE;
}
// OPEN OR CREATE THE DATABASE FILE
$db = new SQLite3('pivpn.sqlite', SQLITE3_OPEN_CREATE | SQLITE3_OPEN_READWRITE);
// CREATE THE USERS TABLE IF IT DOESN'T EXIST
$db->query('CREATE TABLE IF NOT EXISTS `users` (
    `id` INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    `login` VARCHAR,
    `password` VARCHAR,
    `lastlogin` DATETIME,
    `created` DATETIME
)');
// CREATE THE LOGIN HISTORY TABLE IF IT DOESN'T EXIST
$db->query('CREATE TABLE IF NOT EXISTS `login_history` (
    `id` INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    `login` VARCHAR,
    `result` INTEGER,
    `time` DATETIME
)');
// Errors are emitted as warnings by default, enable proper error handling.
$db->enableExceptions(true);
$stmt = $db->prepare('SELECT COUNT(*) AS `total` FROM `users`');
$result = $stmt->execute();
$ret = $result->fetchArray(SQLITE3_ASSOC);
// VERIFY WE ARE A NEW SETUP OR NOT
if(!$ret['total'] > 0){
    $first_setup = TRUE;
}
$result->finalize(); // free memory up

// FUNCTIONS
function update_log($db,$login,$result){
    // ADD TO LOG
    $stmt = $db->prepare('INSERT INTO `login_history` (`id`, `login`, `result`, `time`) VALUES (NULL, :login, :result, :time)');
    $stmt->bindValue(':login', $login);
    $stmt->bindValue(':result', $result);
    $stmt->bindValue(':time', date('Y-m-d H:i:s'));
    $stmt->execute();
}
function login_succeed($db,$login){
    update_log($db,$login,1);
    $stmt = $db->prepare('UPDATE `users` SET `lastlogin`=:time WHERE `login`=:login');
    $stmt->bindValue(':time', date('Y-m-d H:i:s'));
    $stmt->bindValue(':login', $login);
    $result = $stmt->execute();
    $_SESSION['AUTH'] = TRUE;
    $_SESSION['LOGIN'] = $login;
    die(header('Location: ./'));
}

// PAGE POSTS TO SELF
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    if(preg_match('/[^a-zA-Z0-9.@_-]/',$_POST['username']) != 0){
        $login_error = TRUE;
    }elseif(strlen($_POST['username']) <= 0){
        $login_error = TRUE;
    }else{
        if($first_setup === TRUE){
            if(strlen($_POST['password']) < 8){
                $setup_error = TRUE;
                $errorMessage = 'Passwords are too short, 8 minimum.';
            }else{
                if($_POST['password'] == $_POST['password2']){
                    $stmt = $db->prepare('INSERT INTO `users` (`id`, `login`, `password`, `lastlogin`, `created`) VALUES (NULL, :login, :password, :time, :time)');
                    $stmt->bindValue(':login', $_POST['username']);
                    $stmt->bindValue(':password', password_hash($_POST['password'], PASSWORD_DEFAULT));
                    $stmt->bindValue(':time', date('Y-m-d H:i:s'));
                    $stmt->execute();
                    // SUCCESS
                    login_succeed($db,$_POST['username']);
                }else{
                    $setup_error = TRUE;
                    $errorMessage = 'Passwords do not match!';
                }
            }
        }else{
            // READ FROM THE LOGIN HISTORY LOG
            $stmt = $db->prepare('SELECT COUNT(*) AS `total` FROM `login_history` WHERE `login`=:login AND `result`=0 AND `time`>=:time');
            $stmt->bindValue(':login', $_POST['username']);
            $stmt->bindValue(':time', date('Y-m-d H:i:s', strtotime("-30 minutes")));
            $result = $stmt->execute();
            if($result){
                $ret = $result->fetchArray(SQLITE3_ASSOC);
                if($ret){
                    if($ret['total'] > 5){
                        $brute_force = TRUE;
                        $login_error = TRUE;// SO IT WILL PASS IT CORRECTLY TO update_log()
                        $errorMessage = 'User is locked, over 5 attempts in 30mins.';
                    }
                }
            }
            if(!$brute_force){
                // FIND THE USER TO COMPARE
                $stmt = $db->prepare('SELECT `id`, `login`, `password`, `lastlogin` FROM `users` WHERE `login`=:login');
                $stmt->bindValue(':login', $_POST['username']);
                $result = $stmt->execute();
                if($result){
                    $ret = $result->fetchArray(SQLITE3_ASSOC);
                    if($ret){
                        if(password_verify($_POST['password'], $ret['password'])){
                            // SUCCESSFUL LOGIN
                            login_succeed($db,$_POST['username']);
                        }else{
                            $login_error = TRUE;
                            $errorMessage = 'Bad user or password.';
                        }
                    }else{
                        $login_error = TRUE;
                        $errorMessage = 'Bad user or password.';
                    }
                    $result->finalize(); // free memory up
                }else{
                    $login_error = TRUE;
                    $errorMessage = 'Bad user or password.';
                }
            }
        }
    }
    
    update_log($db,$_POST['username'],(int)(($login_error)?0:1));// flip the login_error bit
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Login Form</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="apple-touch-icon" sizes="180x180" href="img/apple-touch-icon.png">
    <link rel="icon" type="image/x-icon" href="favicon.ico">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: self-start;
            min-height: 100vh;
            background-color: #f0f0f0; /* Light background */
        }
        .login-container {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); /* Subtle shadow */
            max-width: 400px; /* Responsive width */
            width: 90%; /* Occupy 90% of parent width on smaller screens */
        }
        .pivpn-logo {
            height: 100px;
            width: 100px;
            background: url(img/pivpnlogo_100.png);
            margin: auto;
            border-radius: 50%;
            box-sizing: border-box;
            box-shadow: 7px 7px 10px #cbced1, -7px -7px 10px white;
        }
        .pivpn-title {
            text-align: center;
            margin-top: 10px;
            font-weight: 900;
            font-size: 1.8rem;
            color: #1a3865;
            letter-spacing: 1px;
        }
        .version {
            font-size: 14px;
            color: #888;
            margin-bottom: 20px;
        }
        .pivpn-setup {
            color: #e79e40;
            font-style: italic;
            margin-top: 20px;
            margin-bottom: 20px;
        }
        .pivpn-error {
            color: #ff0000;
            font-weight: bold;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="login-container py-5 my-4 my-sm-5">
        <div class="pivpn-logo"></div>
        <h2 class="text-center mb-4">PiVPN Login</h2>
        <?php if($first_setup === TRUE){ ?>
        <div class="pivpn-setup">Create a new user to manage the system.</div>
        <?php } ?>
        <?php if($setup_error === TRUE){ ?>
        <div class="pivpn-error"><?=$errorMessage?></div>
        <?php } ?>
        <?php if($login_error === TRUE && !$brute_force){ ?>
        <div class="pivpn-error">Bad user or password!</div>
        <?php } ?>
        <?php if($brute_force === TRUE){ ?>
        <div class="pivpn-error">User is locked out!</div>
        <?php } ?>
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" placeholder="Enter username" <?php if($first_setup === TRUE){ ?>data-toggle="tooltip" data-placement="top" title="a-zA-Z0-9.@_-" <?php } ?>pattern="[a-zA-Z0-9.@_\-]{1,}">
            </div>
            <div class="mb-4">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" placeholder="Enter password">
            </div>
            <?php if($first_setup === TRUE){ ?>
            <div class="mb-4">
                <label for="password2" class="form-label">Confirm Password:</label>
                <input type="password" class="form-control" id="password2" name="password2" placeholder="Confirm Password">
            </div>
            <?php } ?>
            <div class="d-grid">
                <button type="submit" class="btn btn-primary">Login</button>
            </div>
        </form>
    </div>
    <script type="text/javascript" src="js/jquery-3.7.1.min.js"></script>
    <script type="text/javascript" src="js/bootstrap.bundle.min.js"></script>
    <script>
        $(function(){$('[data-toggle="tooltip"]').tooltip();});// enable bootstrap tooltips
    </script>
</body>
</html>
