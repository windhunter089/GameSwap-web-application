<?php
include('lib/common.php');
include('lib/bootstrap_header.php');
include('lib/gameswap_functions.php');

// validate user is logged in
gameswap_validate_user_login();
$userId = gameswap_get_current_user_id();
$banner_container = gameswap_generate_banner_container();

$user_details = gameswap_get_user_details($userId, TRUE);
$user_phone_details = gameswap_get_user_phone_details($userId);

if($user_details['user_found'] == "Yes"){
    $firstName = $user_details['user_first_name'];
    $lastName = $user_details['user_last_name'];
    $nick = $user_details['user_nickname'];
    $postalCode = $user_details['user_postalcode'];
    $city = $user_details['user_city'];
    $state = $user_details['user_state'];
    $password = $user_details['user_password'];

    if($user_phone_details['user_has_phone_num'] == "Yes"){
        $phoneNumber = $user_phone_details['phone_number'];
        $type = $user_phone_details['phone_number_type'];
        $detail = $user_phone_details['phone_number_is_shareable'];

        if($detail == 1){
            $detailField="<input type='checkbox' name='shareable' id='shareable' checked>";
        }
        else{
            $detailField="<input type='checkbox' name='shareable' id='shareable'>";
        }


        if(strtolower($type) == "home"){
            $typeField="<select name='type' id='type'>
                            <option value=home>Home</option>
                            <option value=work>Work</option>
                            <option value=mobile>Mobile</option>
                        </select>";
            }
        else if(strtolower($type) == "work"){
            $typeField="<select name='type' id='type'>
                            <option value=work>Work</option>
                            <option value=home>Home</option>
                            <option value=mobile>Mobile</option>
                        </select>";
            }
        else{
            $typeField="<select name='type' id='type'>
                            <option value=mobile>Mobile</option>
                            <option value=home>Home</option>
                            <option value=work>Work</option>
                        </select>";
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

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

    $query = "SELECT postal_code FROM PostalCode WHERE postal_code='$enteredPostalCode'";
    $result = mysqli_query($db, $query);
    include('lib/show_queries.php');
    $count = mysqli_num_rows($result);
    // if postal code does not exist
    if($count == 0){
        array_push($error_msg, "Not a valid postal code: " . $enteredPostalCode);
    }
    else{
        $query = "SELECT postal_code FROM `postalcode`
            WHERE postal_code='$enteredPostalCode' AND city='$enteredCity' AND state='$enteredState'";
        $result = mysqli_query($db, $query);
        include('lib/show_queries.php');
        $count = mysqli_num_rows($result);
        // if postal code is not valid
        if($count == 0) {
            array_push($error_msg, "City and state do no match postal code: " . $enteredPostalCode);
        }
        else{
            if(!empty($enteredPhoneNumber)){
                $query = "SELECT number FROM phonenumber WHERE number='$enteredPhoneNumber'";
                $result = mysqli_query($db, $query);
                include('lib/show_queries.php');
                $count = mysqli_num_rows($result);

                if($count > 0 and $enteredPhoneNumber != $phoneNumber){
                    array_push($error_msg, "Phone number already registered to another user: " . $enteredPhoneNumber);
                }
                else{
                    $query = "UPDATE User SET email='$userId',password='$enteredPassword',first_name='$enteredFirstName',last_name='$enteredLastName'
          ,nickname='$enteredNick',postal_code='$enteredPostalCode' WHERE email='$userId'";
                    $result = mysqli_query($db, $query);
                    include('lib/show_queries.php');

                    if($enteredShareable == "on"){
                        $query = "UPDATE phonenumber SET email='$userId',number='$enteredPhoneNumber',number_type='$enteredType',share_phone_number=1 WHERE email='$userId'";
                        $result = mysqli_query($db, $query);
                        include('lib/show_queries.php');
                    }
                    else{
                        $query = "UPDATE phonenumber SET email='$userId',number='$enteredPhoneNumber',number_type='$enteredType',share_phone_number=0 WHERE email='$userId'";
                        $result = mysqli_query($db, $query);
                        include('lib/show_queries.php');
                    }
                }
            }
            else{
                $query = "UPDATE User SET email='$userId',password='$enteredPassword',first_name='$enteredFirstName',last_name='$enteredLastName'
          ,nickname='$enteredNick',postal_code='$enteredPostalCode' WHERE email='$userId'";
                $result = mysqli_query($db, $query);
                include('lib/show_queries.php');
                header(REFRESH_TIME . 'url=login.php');
            }
        }
    }

    $user_details = gameswap_get_user_details($userId, TRUE);
    $user_phone_details = gameswap_get_user_phone_details($userId);

    if($user_details['user_found'] == "Yes"){
        $firstName = $user_details['user_first_name'];
        $lastName = $user_details['user_last_name'];
        $nick = $user_details['user_nickname'];
        $postalCode = $user_details['user_postalcode'];
        $city = $user_details['user_city'];
        $state = $user_details['user_state'];
        $password = $user_details['user_password'];

        if($user_phone_details['user_has_phone_num'] == "Yes"){
            $phoneNumber = $user_phone_details['phone_number'];
            $type = $user_phone_details['phone_number_type'];
            $detail = $user_phone_details['phone_number_is_shareable'];

            if($detail == 1){
                $detailField="<input type='checkbox' name='shareable' id='shareable' checked>";
            }
            else{
                $detailField="<input type='checkbox' name='shareable' id='shareable'>";
            }


            if(strtolower($type) == "home"){
                $typeField="<select name='type' id='type'>
                            <option value=home>Home</option>
                            <option value=work>Work</option>
                            <option value=mobile>Mobile</option>
                        </select>";
            }
            else if(strtolower($type) == "work"){
                $typeField="<select name='type' id='type'>
                            <option value=work>Work</option>
                            <option value=home>Home</option>
                            <option value=mobile>Mobile</option>
                        </select>";
            }
            else{
                $typeField="<select name='type' id='type'>
                            <option value=mobile>Mobile</option>
                            <option value=home>Home</option>
                            <option value=work>Work</option>
                        </select>";
            }
        }
    }
}

?>
<?php include("lib/header.php"); ?>
<title>GameSwap</title>
</head>

<body>
<?php print $banner_container ?>
<div id="main_container" style="margin-top: 1em">
    <div class="center_content">
        <div class="center_left">
            <div class="title_name">
                Update my information
            </div>
            <div class="registration-fields">
                <form class="form-inline" action="update_information.php" method="POST">
                    <div class="registration-field">
                        <label for="email">Email:</label>
                        <input disabled id="email" placeholder=<?php print $userId ?> value=<?php print $userId ?>  name="email">
                        <label for="nick">Nickname:</label>
                        <input id="nick" placeholder=<?php print $nick ?> value=<?php print $nick ?> name="nick">
                    </div>
                    <div class="registration-field">
                        <label for="pwd">Password:</label>
                        <input type="password" id="pwd" placeholder="New Password" name="pswd">
                        <label for="city">City:</label>
                        <input id="city" placeholder=<?php print $city ?> value=<?php print $city ?> name="city">
                    </div>
                    <div class="registration-field">
                        <label for="firstName">First Name:</label>
                        <input id="firstName" placeholder=<?php print $firstName ?> placeholder=<?php print $firstName ?> value=<?php print $firstName ?> name="firstName">
                        <label for="state">State:</label>
                        <input id="state" placeholder=<?php print $state ?> value=<?php print $state ?> name="state">
                    </div>
                    <div class="registration-field">
                        <label for="lastName">Last Name:</label>
                        <input id="lastName" placeholder=<?php print $lastName ?> value=<?php print $lastName ?> name="lastName">
                        <label for="postalCode">Postal Code:</label>
                        <input id="postalCode" placeholder=<?php print $postalCode ?> value=<?php print $postalCode ?> name="postalCode">
                    </div>
                    <div class="registration-field">
                        <label for="phoneNumber">Phone number (optional):</label>
                        <input type="tel" id="phoneNumber" placeholder=<?php print $phoneNumber ?> value=<?php print $phoneNumber ?> name="phoneNumber">
                        <label for="type">Type</label>
                        <?php print $typeField ?>
                    </div>
                    <div class="registration-field">
                        <label for="shareable">Show phone number in swaps</label>
                        <?php print $detailField ?>
                    </div>
                    <div class="registration-button">
                        <button type="submit">Update</button>
                    </div>
                </form>
            </div>
        </div>

        <?php include("lib/error.php"); ?>

        <div class="clear"></div>
    </div>

</div>
</body>