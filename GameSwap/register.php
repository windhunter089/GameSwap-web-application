<?php
include('lib/common.php');
include('lib/gameswap_functions.php');
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $enteredEmail = mysqli_real_escape_string($db, $_REQUEST['email']);
    $enteredNick = mysqli_real_escape_string($db, $_REQUEST['nick']);
    $enteredPassword = mysqli_real_escape_string($db, $_REQUEST['pswd']);
    $enteredCity = mysqli_real_escape_string($db, $_REQUEST['city']);
    $enteredFirstName = mysqli_real_escape_string($db, $_REQUEST['firstName']);
    $enteredState = mysqli_real_escape_string($db, $_REQUEST['state']);
    $enteredLastName = mysqli_real_escape_string($db, $_REQUEST['lastName']);
    $enteredPostalCode = mysqli_real_escape_string($db, $_REQUEST['postalCode']);
    $enteredPhoneNumber = mysqli_real_escape_string($db, $_REQUEST['phoneNumber']);
    $enteredType = mysqli_real_escape_string($db, $_REQUEST['type']);
    $enteredShareable = mysqli_real_escape_string($db, $_REQUEST['shareable']);

//    $query = "SELECT * FROM User WHERE email='$enteredEmail'";
//    $result = mysqli_query($db, $query);
//    include('lib/show_queries.php');
//    $count = mysqli_num_rows($result);
    $user_detail =  gameswap_get_user_details($enteredEmail);
    // if email does not exist
    if( $user_detail['user_found'] == "Yes" ){
        array_push($error_msg, "This email already exists: " . $enteredEmail);
    }
    else{
        $postalCode_info = gameswap_verify_postalCode($enteredPostalCode, $enteredCity, $enteredState);

        // if postal code does not exist
        if($postalCode_info['postalCode_found'] == "No"){
            array_push($error_msg, "Not a valid postal code: " . $enteredPostalCode);
        }
        else{
            // if postal code is not valid
            if($postalCode_info['postalCode_verify'] == "Fail") {
                array_push($error_msg, "City and state do no match postal code: " . $enteredPostalCode);
            }
            else{
                if(!empty($enteredPhoneNumber)){
                    $user_phone_verify = gameswap_verify_phoneNumber($enteredPhoneNumber);

                    if($user_phone_verify['phoneNumber_found'] == "Yes"){
                        array_push($error_msg, "Phone number already registered to another user: " . $enteredPhoneNumber);
                    }
                    else{
                        gameswap_insert_user($enteredEmail, $enteredPassword, $enteredFirstName, $enteredLastName, $enteredNick, $enteredPostalCode);
                        gameswap_insert_phoneNumber($enteredEmail, $enteredPhoneNumber, $enteredType, $enteredShareable);
                        header(REFRESH_TIME . 'url=login.php');
                    }
                }
                else{
                    gameswap_insert_user($enteredEmail, $enteredPassword, $enteredFirstName, $enteredLastName, $enteredNick, $enteredPostalCode);
                    header(REFRESH_TIME . 'url=login.php');
                }
            }
        }
    }
}
?>
<?php include("lib/header.php"); ?>
<title>GameSwap</title>
</head>

<body>
<div id="main_container">
    <div class="center_content">
        <div class="center_left">
            <div class="title_name">
                Registration
            </div>
            <div class="registration-fields">
                <form class="form-inline" action="register.php" method="POST">
                    <div class="registration-field">
                        <label for="email">Email:</label>
                        <input id="email" placeholder="Enter Email" name="email">
                        <label for="nick">Nickname:</label>
                        <input id="nick" placeholder="Enter Nickname" name="nick">
                    </div>
                    <div class="registration-field">
                        <label for="pwd">Password:</label>
                        <input type="password" id="pwd" placeholder="Enter password" name="pswd">
                        <label for="city">City:</label>
                        <input id="city" placeholder="Enter City" name="city">
                    </div>
                    <div class="registration-field">
                        <label for="firstName">First Name:</label>
                        <input id="firstName" placeholder="Enter First Name" name="firstName">
                        <label for="state">State:</label>
                        <input id="state" placeholder="Enter State" name="state">
                    </div>
                    <div class="registration-field">
                        <label for="lastName">Last Name:</label>
                        <input id="lastName" placeholder="Enter Last Name" name="lastName">
                        <label for="postalCode">Postal Code:</label>
                        <input id="postalCode" placeholder="Enter Postal Code" name="postalCode">
                    </div>
                    <div class="registration-field">
                        <label for="phoneNumber">Phone number (optional):</label>
                        <input type="tel" id="phoneNumber" placeholder="XXX-XXX-XXXX" name="phoneNumber">
                        <label for="type">Type</label>
                        <select name="type" id="type">
                            <option value=home>Home</option>
                            <option value=work>Work</option>
                            <option value=mobile>Mobile</option>
                        </select>
                    </div>
                    <div class="registration-field">
                        <label for="shareable">Show phone number in swaps</label>
                        <input type="checkbox" name="shareable" id="shareable">
                    </div>
                    <div class="registration-button">
                        <button type="submit">Register</button>
                    </div>
                </form>
            </div>
            <div class="register-link">
                <a href="login.php">Go back to login</a>
            </div>
        </div>

        <?php include("lib/error.php"); ?>

        <div class="clear"></div>
    </div>

</div>
</body>