<?php
include('lib/common.php');

// Code borrowed from Sample Phase 3 Submission
if ($showQueries) {
    array_push($query_msg, "showQueries currently turned ON, to disable change to 'false' in lib/common.php");
}

//Note: known issue with _POST always empty using PHPStorm built-in web server: Use *AMP server instead
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $enteredEmail = mysqli_real_escape_string($db, $_POST['email']);
    $enteredPassword = mysqli_real_escape_string($db, $_POST['password']);

    if (empty($enteredEmail)) {
        array_push($error_msg, "Please enter an email address.");
    }

    if (empty($enteredPassword)) {
        array_push($error_msg, "Please enter a password.");
    }

    if (!empty($enteredEmail) && !empty($enteredPassword)) {

        $query = "SELECT password FROM User WHERE email='$enteredEmail'";

        $result = mysqli_query($db, $query);
        include('lib/show_queries.php');
        $count = mysqli_num_rows($result);

        if (!empty($result) && ($count > 0)) {
            $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
            $storedPassword = $row['password'];

            $options = [
                'cost' => 8,
            ];
            //convert the plaintext passwords to their respective hashses
            // 'michael123' = $2y$08$kr5P80A7RyA0FDPUa8cB2eaf0EqbUay0nYspuajgHRRXM9SgzNgZO
            $storedHash = password_hash($storedPassword, PASSWORD_DEFAULT, $options);   //may not want this if $storedPassword are stored as hashes (don't rehash a hash)
            $enteredHash = password_hash($enteredPassword, PASSWORD_DEFAULT, $options);

            if ($showQueries) {
                array_push($query_msg, "Plaintext entered password: " . $enteredPassword);
                //Note: because of salt, the entered and stored password hashes will appear different each time
                array_push($query_msg, "Entered Hash:" . $enteredHash);
                array_push($query_msg, "Stored Hash:  " . $storedHash . NEWLINE);  //note: change to storedHash if tables store the plaintext password value
                //unsafe, but left as a learning tool uncomment if you want to log passwords with hash values
                //error_log('email: '. $enteredEmail  . ' password: '. $enteredPassword . ' hash:'. $enteredHash);
            }

            //depends on if you are storing the hash $storedHash or plaintext $storedPassword
            if (password_verify($enteredPassword, $storedHash)) {
                array_push($query_msg, "Password is Valid! ");
                $_SESSION['email'] = $enteredEmail;
                array_push($query_msg, "logging in... ");
                header(REFRESH_TIME . 'url=main_menu.php');        //to view the password hashes and login success/failure

            } else {
                array_push($error_msg, "Login failed: " . $enteredEmail . NEWLINE);
                array_push($error_msg, "To demo enter: " . NEWLINE . "michael@bluthco.com" . NEWLINE . "michael123");
            }

        } else {
            array_push($error_msg, "The username entered does not exist: " . $enteredEmail);
        }
    }
}
?>
<?php
include("lib/header.php");
?>
<title>Game Swap</title>
</head>
<body>
        <div id="main_container">
            <div id="header">
                <div class="logo">
                    <img src="img/logo.png" style="background-color:black;" alt="" title="Game Swap Logo"/>
                </div>
            </div>

            <div class="center_content">
                <div class="text_box">

                    <form action="login.php" method="post" enctype="multipart/form-data">
                        <div class="title">GameSwap Login</div>
                        <div class="login_form_row">
                            <label class="login_label">Email:</label>
                            <input type="text" name="email" class="login_input"/>
                        </div>
                        <div class="login_form_row">
                            <label class="login_label">Password:</label>
                            <input type="password" name="password" class="login_input"/>
                        </div>
                        <button type="submit" class="login">Login</button>
                        <form/>
                </div>

                <div class="register-link">
                    Not Registered? <a href="register.php">Register here</a>
                </div>

                <?php include("lib/error.php"); ?>

                <div class="clear"></div>
            </div>
        </div>
</body>
</html>