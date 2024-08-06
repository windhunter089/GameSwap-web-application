<?php
include('common.php');
global $db;

/**
 *This file contains all the PHP functions that we will utilize thoroughout the course of GameSwap Project.
 * To invoke functions from this file, simply add: include('lib/gameswap_functions.php') at the top of your files.
 */

/**
 * Function to set the mysqli database object.
 * Having some issue with getting $db variable (from common.php) to work on the functions.
 * Hence, creating a new function to create the mysqli database object.
 */
function gameswap_get_mysqli_object(){
    define('DB_HOST', "localhost");
    define('DB_PORT', "3306");
    define('DB_USER', "gatechUser");
    define('DB_PASS', "gatech123");
    define('DB_SCHEMA', "cs6400_sp22_team064");
    $mysqli_db_object = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_SCHEMA, DB_PORT);

    return $mysqli_db_object;
}

/**
 * Function to get the current logged-in user's ID (email).
 * The logged-in user's email is stored in the session variable after successful login.
 *
 */

function gameswap_get_current_user_id(){
    return $_SESSION['email'];
}

/**
 * Function to check if the email session variable is set. If not, set the header to login.php
 * to allow user to log in.
 */

function gameswap_validate_user_login(){
    if (!isset($_SESSION['email'])) {
        header('Location: login.php');
        exit();
    }
}

/**
 * Function to return all the necessary details about an item provided it's item id.
 *
 * @param int $item_id Item Id for the item.
 * @return array Associative array that contains all the details about the item as a key => index value.
 */
function gameswap_get_item_details_from_item_id($item_id){

    $item_detail = array('item_found' => "No");

    //Query the item table, collect all the details and return an array.
    $item_query = "SELECT * FROM item WHERE item_no = '$item_id'";
    $item_res = mysqli_query(gameswap_get_mysqli_object(), $item_query);
    $item_res_count = mysqli_num_rows($item_res);

    if (!empty($item_res) && ($item_res_count > 0)) {
        $item_detail['item_found'] = "Yes";
        $item_row = mysqli_fetch_array($item_res, MYSQLI_ASSOC);
        //Now, add all these fields to the returning array.
        $item_detail['item_email'] = $item_row['email'];
        $item_detail['item_id'] = $item_id;
        $item_detail['item_type'] = $item_row['TYPE'];
        $item_detail['item_title'] = $item_row['title'];
        $item_detail['item_game_platform'] = $item_row['game_platform'];
        $item_detail['item_media'] = $item_row['media'];
        $item_detail['item_computer_platform'] = $item_row['computer_platform'];
        $item_detail['item_piece_count'] = $item_row['piece'];
        $item_detail['item_condition'] = $item_row['condition'];
        $item_detail['item_description'] = $item_row['description'];

    }

    //return the array.
    return $item_detail;

}


/**
 * Function to return all the necessary details about an user provided the user's email id.
 *
 * @param string $user_email Email Id for the user.
 * @param boolean $get_city_state_lat_lon Boolean variable to denote whether we need to query postalcode table to get city,state,lat and long or not. Set to false as default (doesn't query postalcode table)
 * @return array Returns an associative array that contains all the details about the user as a key => index value.
 */
function gameswap_get_user_details($user_email, $get_city_state_lat_lon = FALSE){

    $user_detail = array('user_found' => "No");

    //Query the item table, collect all the details and return an array.
    $user_query = "SELECT * FROM `user` WHERE email = '$user_email'";
    $user_res = mysqli_query(gameswap_get_mysqli_object(), $user_query);
    $user_res_count = mysqli_num_rows($user_res);

    if (!empty($user_res) && ($user_res_count > 0)) {
        $user_detail['user_found'] = "Yes";
        $user_row = mysqli_fetch_array($user_res, MYSQLI_ASSOC);
        //Now, add all these fields to the returning array.
        $user_detail['user_first_name'] = $user_row['first_name'];
        $user_detail['user_last_name'] = $user_row['last_name'];
        $user_detail['user_nickname'] = $user_row['nickname'];
        $user_postal_code = $user_row['postal_code'];
        $user_detail['user_postalcode'] = $user_postal_code;
        $user_detail['user_password'] = $user_row['password'];

        //Now, if the $get_city_state_lat_lon boolean is TRUE, we need to get city and state value as well.
        if($get_city_state_lat_lon){
            //Query the postalcode table using the user's postalcode.
            $postalcode_query = "SELECT * FROM postalcode WHERE postal_code = '$user_postal_code'";
            $postalcode_res = mysqli_query(gameswap_get_mysqli_object(), $postalcode_query);
            $postalcode_res_count = mysqli_num_rows($postalcode_res);
            if(!empty($postalcode_res) && ($postalcode_res_count > 0)) {
                $postalcode_row = mysqli_fetch_array($postalcode_res, MYSQLI_ASSOC);
                $user_detail['user_city'] = $postalcode_row['city'];
                $user_detail['user_state'] = $postalcode_row['state'];
                $user_detail['user_latitude'] = $postalcode_row['latitude'];
                $user_detail['user_longitude'] = $postalcode_row['longitude'];
            }

        }
    }

    //Return the array.
    return $user_detail;

}


/**
 * Function to get just the full name and nickname of a user from the user's id (email id).
 *
 * @param string $user_email Email address (ID) of the user
 * @param boolean $display_nickname Boolean to determine if the nickname is displayed or not.
 *                                  Default is false. If set to true,it returns in this format: {FirstName} {LastName} ({Nickname})
 *
 * @return string String that represents the Name of the student and nickname(optional).
 */

function gameswap_get_user_name_from_id($user_email, bool $display_nickname = FALSE){

    $user_name = "";

    //Query the user table, collect all the details and return an array.
    $user_name_query = "SELECT * FROM `user` WHERE email = '$user_email'";
    $user_name_res = mysqli_query(gameswap_get_mysqli_object(), $user_name_query);
    $user_name_res_count = mysqli_num_rows($user_name_res);

    if (!empty($user_name_res) && ($user_name_res_count > 0)) {
        $user_name_row = mysqli_fetch_array($user_name_res, MYSQLI_ASSOC);
        $user_first_name = $user_name_row['first_name'];
        $user_last_name = $user_name_row['last_name'];
        $user_name = "$user_first_name $user_last_name";
        //Add nickname if $display_nickname is set to TRUE.
        if($display_nickname) {
            $user_nickname = $user_name_row['nickname'];
            if($user_name !=""){
                $user_name .= "($user_nickname)";
            }

        }
    }

    return $user_name;

}



/**
 * Function to gather the phone number details provided the user's id (email address)
 *
 * @param string $user_email Email address (Id) of the user
 * @return array Returns an associative array that contains all the phonenumber details about the user as a key => index value.
 */

function gameswap_get_user_phone_details($user_email){

    //Setting default value.
    $user_phone_detail = array('user_has_phone_num' => "No");

    //Query the phonenumber table, collect all the details and return an array.
    $user_phone_query = "SELECT * FROM phonenumber WHERE email = '$user_email'";
    $user_phone_res = mysqli_query(gameswap_get_mysqli_object(), $user_phone_query);
    $user_phone_res_count = mysqli_num_rows($user_phone_res);

    if (!empty($user_phone_res) && ($user_phone_res_count > 0)) {
        //Found a row.
        $user_phone_row = mysqli_fetch_array($user_phone_res, MYSQLI_ASSOC);
        $user_phone_detail['user_has_phone_num'] = "Yes";
        $user_phone_detail['phone_number'] = $user_phone_row['number'];
        $user_phone_detail['phone_number_type'] = $user_phone_row['number_type'];
        $user_phone_detail['phone_number_is_shareable'] = $user_phone_row['share_phone_number'];
    }

    return $user_phone_detail;

}




/**
 * Function to calculate the distance between two users provided their ID (email address)
 *
 * @param string $user_id_1 Email address of User 1.
 * @param string $user_id_2 Email address of User 2.
 * @return float Distance between the users in miles.
 */

function gameswap_get_distance_between_users_by_user_id($user_id_1, $user_id_2){

    $distance_in_miles = 0.00;

    $user_distance_query = "SELECT *,
                                 ROUND((6371 * 0.621371 * (2 * ATAN((SQRT((POWER((SIN(((RADIANS(T2.USER2_LAT)) - (RADIANS(T1.USER1_LAT))) / 2)), 2) +  (COS((RADIANS(T1.USER1_LAT)))) * (COS((RADIANS(T2.USER2_LAT)))) * POWER((SIN(((RADIANS(T2.USER2_LON)) - (RADIANS(T1.USER1_LON))) / 2)), 2)))), (SQRT(1 - (POWER((SIN(((RADIANS(T2.USER2_LAT)) - (RADIANS(T1.USER1_LAT))) / 2)), 2) +  (COS((RADIANS(T1.USER1_LAT)))) * (COS((RADIANS(T2.USER2_LAT)))) * POWER((SIN(((RADIANS(T2.USER2_LON)) - (RADIANS(T1.USER1_LON))) / 2)), 2))))))),2) AS DISTANCE_CALC_MILES
                                 FROM 
                                     (SELECT U1.email AS USER1_EMAIL, U1.nickname AS USER1_NICKNAME, U1.postal_code AS USER1_POSTAL_CODE, P1.latitude AS USER1_LAT, P1.longitude USER1_LON, P1.City AS USER1_CITY, P1.State AS USER1_STATE FROM user U1 JOIN postalcode P1 ON U1.postal_code = P1.postal_code) T1 
                                     JOIN 
                                     (SELECT U2.email AS USER2_EMAIL, U2.nickname AS USER2_NICKNAME, U2.postal_code AS USER2_POSTAL_CODE, P2.latitude AS USER2_LAT, P2.longitude USER2_LON, P2.City AS USER2_CITY, P2.state AS USER2_STATE FROM user U2 JOIN postalcode P2 ON U2.postal_code = P2.postal_code) T2 
                                     ON T1.USER1_EMAIL <> T2.USER2_email WHERE USER1_EMAIL = '$user_id_1' AND USER2_EMAIL = '$user_id_2'";


    $user_distance_res = mysqli_query(gameswap_get_mysqli_object(), $user_distance_query);
    $user_distance_count = mysqli_num_rows($user_distance_res);
    if(!empty($user_distance_res) && ($user_distance_count > 0)) {
        $user_distance_row = mysqli_fetch_array($user_distance_res, MYSQLI_ASSOC);
        $distance_in_miles = $user_distance_row['DISTANCE_CALC_MILES'];
    }
    return $distance_in_miles;
}


/**
 * Function to calculate the rating for a user provided their ID (email address)
 *
 * @param string $user_id Email address for the user.
 * @return float Rating for the User.
 */

function gameswap_get_user_rating_by_id($user_id){

    $user_rating = "N/A"; //Setting a default value of N/A to return.

    $user_rating_query = "SELECT ROUND(COALESCE((((SELECT AVG(swap_proposer_rating) AS RATING_AVG FROM swap 
                            WHERE swap_status = 'Accepted' AND proposer_email = '$user_id' AND
                            swap_proposer_rating IS NOT NULL) + (SELECT AVG(swap_counterparty_rating) AS
                            RATING_AVG FROM swap WHERE swap_status = 'Accepted' AND counterparty_email =
                            '$user_id' AND swap_counterparty_rating IS NOT NULL)) /2), ((SELECT
                            AVG(swap_proposer_rating) AS RATING_AVG FROM swap WHERE swap_status =
                            'Accepted' AND proposer_email = '$user_id' AND swap_proposer_rating IS NOT NULL)),
                            ((SELECT AVG(swap_counterparty_rating) AS RATING_AVG FROM swap WHERE
                            swap_status = 'Accepted' AND counterparty_email = '$user_id' AND
                            swap_counterparty_rating IS NOT NULL)), 'None'),2) AS USER_RATING_AVG";

    $user_rating_res = mysqli_query(gameswap_get_mysqli_object(), $user_rating_query);
    $user_rating_res_count = mysqli_num_rows($user_rating_res);
    if(!empty($user_rating_res) && ($user_rating_res_count > 0)) {
        $user_rating_row = mysqli_fetch_array($user_rating_res, MYSQLI_ASSOC);
        $user_rating = $user_rating_row['USER_RATING_AVG'];
    }

    return $user_rating;
}


/**
 * Function to get the total number of unrated swaps for a user provided their ID (email address).
 *
 * @param string $user_id Email address for the user whose unrated swap count is needed.
 * @return int Total Number of Unrated swaps for the user provided.
 */

function gameswap_get_user_unrated_swap_count_by_id($user_id){

    $user_unrated_swap_count = 0; //setting default value of 0.

    $user_unrated_swap_query = "SELECT COUNT(*) AS UNRATED_SWAP FROM swap WHERE swap_status='Accepted'
                            AND ((proposer_email = '$user_id' AND swap_proposer_rating IS
                            NULL) OR (counterparty_email = '$user_id' AND
                            swap_counterparty_rating IS NULL))";
    $user_unrated_swap_res = mysqli_query(gameswap_get_mysqli_object(), $user_unrated_swap_query);
    $user_unrated_swap_res_count = mysqli_num_rows($user_unrated_swap_res);
    if(!empty($user_unrated_swap_res) && ($user_unrated_swap_res_count > 0)){
        $user_unrated_swap_row = mysqli_fetch_array($user_unrated_swap_res, MYSQLI_ASSOC);
        $user_unrated_swap_count = $user_unrated_swap_row['UNRATED_SWAP'];
    }

    return $user_unrated_swap_count;
}

/**
 * Function to get the total number of unaccepted swaps for a user provided their ID (email address).
 *
 * @param string $user_id Email address for the user whose unaccepted swap count is needed.
 * @return int Total Number of Unaccepted swaps for the user provided. 0 is the default value returned.
 */

function gameswap_get_user_unaccepted_swap_count_by_id($user_id){

    $user_unaccepted_swap_count = 0; //setting default value of 0.

    $user_unaccepted_swap_query = "SELECT COUNT(*) AS UNACCEPTED_SWAP_COUNT FROM swap 
                                    WHERE swap_status = 'Pending' AND counterparty_email = '$user_id'";
    $user_unaccepted_swap_res = mysqli_query(gameswap_get_mysqli_object(), $user_unaccepted_swap_query);
    $user_unaccepted_swap_res_count = mysqli_num_rows($user_unaccepted_swap_res);
    if(!empty($user_unaccepted_swap_res) && ($user_unaccepted_swap_res_count > 0)){
        $user_unaccepted_swap_row = mysqli_fetch_array($user_unaccepted_swap_res, MYSQLI_ASSOC);
        $user_unaccepted_swap_count = $user_unaccepted_swap_row['UNACCEPTED_SWAP_COUNT'];
    }

    return $user_unaccepted_swap_count;
}

/**
 * Function to see if a item is available or not.
 * Gets the count of all swaps where the item is either Proposer item or CP Item, and the swap
 * has the status of either "Pending" or "Accepted". This count if >0 means that the item is not available.
 * Depending on that this function returns true or false.
 *
 * @param int $item_id Item Id of the item.
 * @return boolean Boolean value (TRUE or FALSE) that dentoes whether the item is available or not.
 */
function gameswap_check_item_availablity($item_id){

    $item_availability_query = "SELECT COUNT(*) AS ITEM_ACTIVE_COUNT FROM swap WHERE (swap_status IN
                                ('Accepted', 'Pending')) AND (proposer_item_id = '$item_id' OR
                                desired_item_id = '$item_id')";

    $item_availability_res = mysqli_query(gameswap_get_mysqli_object(), $item_availability_query);
    $item_availability_res_count = mysqli_num_rows($item_availability_res);
    if(!empty($item_availability_res) && ($item_availability_res_count > 0)){
        $item_availability_row = mysqli_fetch_array($item_availability_res, MYSQLI_ASSOC);
        $item_active_in_count = $item_availability_row['ITEM_ACTIVE_COUNT'];
        //Now, if the count is greater than 0, return FALSE.
        if($item_active_in_count >0){
            return FALSE;
        }else{
            return TRUE;
        }
    }
}


/**
 * Function to get the user id (email) from the item id.
 *
 * @param int $item_id Item Id for the item.
 * @return string The id (email id) of the item's owner user.
 */

function gameswap_get_user_id_from_item_id($item_id){

    $item_owner_email = "";

    $item_query = "SELECT item.email AS ITEM_OWNER_ID FROM item WHERE item_no ='$item_id'";
    $item_res = mysqli_query(gameswap_get_mysqli_object(), $item_query);
    $item_res_count = mysqli_num_rows($item_res);
    if(!empty($item_res) && ($item_res_count > 0)) {
        $item_row = mysqli_fetch_array($item_res, MYSQLI_ASSOC);
        $item_owner_email = $item_row['ITEM_OWNER_ID'];
    }

    return $item_owner_email;
}

/**
 * Function to get the Item Title from the item id.
 *
 * @param int $item_id Item Id for the item.
 * @return string The title of the item.
 */

function gameswap_get_item_title_from_item_id($item_id){

    $item_title = "";

    $item_query = "SELECT title AS ITEM_TITLE FROM item WHERE item_no ='$item_id'";
    $item_res = mysqli_query(gameswap_get_mysqli_object(), $item_query);
    $item_res_count = mysqli_num_rows($item_res);
    if(!empty($item_res) && ($item_res_count > 0)) {
        $item_row = mysqli_fetch_array($item_res, MYSQLI_ASSOC);
        $item_title = $item_row['ITEM_TITLE'];
    }

    return $item_title;
}

/**
 * Function to get the count of available items for a user provided their id (email id)
 *
 * @param string $user_id Email id of the user.
 * @return int Count of available items for the user.
 */
function gameswap_get_available_items_count($user_id){

    $user_available_items_count = 0;

    $user_available_item_count_query = "SELECT COUNT(*) AS AVAILABLE_ITEM_COUNT FROM item WHERE item_no NOT IN (
                                            SELECT desired_item_id FROM swap WHERE swap_status IN ('Accepted', 'Pending') UNION 
                                            SELECT proposer_item_id FROM swap WHERE swap_status IN ('Accepted', 'Pending') ORDER BY desired_item_id ASC) AND email = '$user_id' ORDER BY item_no ASC";

    $user_available_item_count_res = mysqli_query(gameswap_get_mysqli_object(), $user_available_item_count_query);
    $user_available_item_count_res_count = mysqli_num_rows($user_available_item_count_res);

    if(!empty($user_available_item_count_res) && ($user_available_item_count_res_count > 0)){
        $available_count_row = mysqli_fetch_array($user_available_item_count_res, MYSQLI_ASSOC);
        $user_available_items_count = $available_count_row['AVAILABLE_ITEM_COUNT'];
    }

    return $user_available_items_count;
}

/**
 * Function to generate the error message content for a page. (For Testing visit: "localhost/GameSwap/view_item.php" in your browser.)
 *
 * @param string $page_title The text that will be displayed at the top of the error message as the page title.
 * @param string $error_msg The text that will be displayed in the error message.
 * @param string $btn_url URL or script name that the button will link to.
 * @param string $btn_text The text that will be displayed in the button.
 * @return string Text(String) that contains the HTML markup for the error message.
 */
function gameswap_generate_error_message_content($page_title, $error_msg, $btn_url, $btn_text){

    $banner_container = gameswap_generate_banner_container();
    $error_msg_content = "<div class='container-fluid gameswap-body-container'>
                        $banner_container
                        <div class='gameswap-inside-container'>
                            <h3 class='gameswap-error-msg-container-header'>$page_title</h3>
                            <hr class='gameswap-error-msg-container-hr'/>
                            <div class='alert alert-danger gameswap-error-msg-parent-container' role='alert'>
                                <div class='gameswap-error-msg-container'>
                                    <p class ='gameswap-error-msg-content'><i class='bi bi-exclamation-triangle'></i>
                                    <strong>$error_msg</strong></p>
                                </div>
                                <div class = 'gameswap-error-msg-container gameswap-error-msg-home-btn-container align-content-center'>
                                    <a class='btn btn-primary gameswap-error-msg-home-btn' href='$btn_url' role='button'>$btn_text</a>
                                </div>
                            </div>
                          </div>
                        </div>";

    return $error_msg_content;
}


/**
 * Function to generate banner container HTML content for the user. Includes User's name, nickname, and links to home page and logout.
 *
 * @return string HTML markup for the banner container. Uses the current logged in user.
 */

function gameswap_generate_banner_container(){


    $current_logged_in_user_id = gameswap_get_current_user_id();
    $current_logged_in_user_name = gameswap_get_user_name_from_id($current_logged_in_user_id, TRUE);
    /*
    $banner_content = "<div class='gameswap-user-banner-container'>    
                                <ul class='list-group list-group-horizontal'>
                                  <li class='list-group-item gameswap-user-banner-group-item'><i class='bi bi-person-circle'></i>Welcome $current_logged_in_user_name</li>
                                  <li class='list-group-item gameswap-user-banner-group-item'><i class='bi bi-house-fill'></i><a href='main_menu.php'>Home Page</a></li>
                                  <li class='list-group-item gameswap-user-banner-group-item'><i class='bi bi-box-arrow-in-left'></i><a href='logout.php'>Log out?</a></li>
                                </ul>
                            </div>";
    */

    $banner_content ="<nav class='navbar navbar-expand-lg navbar-light bg-light'>
                              <div class='container-fluid'>
                                <a class='navbar-brand' href='main_menu.php' id ='gameswap-navbar-brand'>Game Swap</a>
                                <div class='collapse navbar-collapse' id='navbarResponsive'>
                                  <ul class='navbar-nav ms-auto'>
                                    <li class='nav-item active'>
                                      <a class='nav-link gameswap-navbar-link' href='#'><i class='bi bi-person-circle'></i>Welcome $current_logged_in_user_name</a>
                                    </li>
                                    <li class='nav-item'>
                                      <a class='nav-link gameswap-navbar-link' href='main_menu.php'><i class='bi bi-house-fill'></i>Home</a>
                                    </li>
                                    <li class='nav-item'>
                                      <a class='nav-link gameswap-navbar-link' href='logout.php'><i class='bi bi-box-arrow-in-left'></i>Log Out?</a>
                                    </li>
                                  </ul>
                                </div>
                              </div>
                            </nav>";

    return $banner_content;
}


/**
 * Function to rate an unrated swap
 *
 * @param string $rating Rating to give swap
 * @param string $counterParty Counterparty of swap
 * @param string $proposer Proposer of swap
 * @param string $desiredItem Desired item of swap
 * @param string $proposedItem Proposed item of swap
 * @param string $role Role of user
 * @return void
 */

function gameswap_update_rate_swaps($rating, $counterParty, $proposer, $desiredItem, $proposedItem, $role)
{
    if($role == "Proposer"){
        $query = "UPDATE `swap` SET swap_proposer_rating= '$rating'
 WHERE swap.counterparty_email='$counterParty'
 AND swap.proposer_email='$proposer'
 AND swap.desired_item_id='$desiredItem'
 AND swap.proposer_item_id='$proposedItem'";
        $result = mysqli_query(gameswap_get_mysqli_object(), $query);
        include('lib/show_queries.php');
    }
    else{
        $query = "UPDATE `swap` SET swap_counterparty_rating= '$rating'
 WHERE swap.counterparty_email='$counterParty'
 AND swap.proposer_email='$proposer'
 AND swap.desired_item_id='$desiredItem'
 AND swap.proposer_item_id='$proposedItem'";
        $result = mysqli_query(gameswap_get_mysqli_object(), $query);
        include('lib/show_queries.php');
    }
}


/**
 * Function to get all unrated swaps for user
 *
 * @param string $userID User email to get unrated swaps for
 * @return mysqli_result
 */

function gameswap_get_unrated_swaps(string $userId)
{
    $query = "SELECT swap.counterparty_email, swap.proposer_email, swap.desired_item_id, 
        swap.proposer_item_id, swap.accepted_rejected_date
        FROM `swap`
        INNER JOIN `item` ON item.item_no=swap.proposer_item_id OR 
        item.item_no=swap.desired_item_id
        INNER JOIN `user` ON `user`.email=swap.counterparty_email OR 
        `user`.email=swap.proposer_email
        WHERE swap.swap_status='Accepted' 
        AND ((swap.proposer_email = '$userId' AND swap.swap_proposer_rating IS NULL) OR 
        (swap.counterparty_email = '$userId' AND swap.swap_counterparty_rating IS NULL))
        AND (`user`.email='$userId')
        AND (item.email='$userId')";
    return mysqli_query(gameswap_get_mysqli_object(), $query);

}


/**
 * Function to get nickname of a user
 *
 * @param string $userID User email to get nickname for
 * @return string
 */

function gameswap_get_nick(string $userId)
{
    $nick = "SELECT `user`.nickname from `user` WHERE `user`.email='$userId'";
    $get_nick_res = mysqli_query(gameswap_get_mysqli_object(), $nick);
    $get_nick_row = mysqli_fetch_array($get_nick_res, MYSQLI_ASSOC);

    return $get_nick_row['nickname'];
}

/**
 * Function to insert a new user
 *
 * @param string $enteredEmail New user email
 * @param string $enteredPassword New user password
 * @param string $enteredFirstName New user first name
 * @param string $enteredLastName New user last name
 * @param string $enteredNick New user nickname
 * @param string $enteredPostalCode New user postalCode
 *
 * @return void
 */

function gameswap_insert_user($enteredEmail, $enteredPassword, $enteredFirstName, $enteredLastName, $enteredNick, $enteredPostalCode)
{
    $query = "INSERT INTO User (`email`, `password`, `first_name`, `last_name`,
                        `nickname`, `postal_code`) VALUES ('$enteredEmail', '$enteredPassword', '$enteredFirstName',
                        '$enteredLastName', '$enteredNick', '$enteredPostalCode')";
    $result = mysqli_query(gameswap_get_mysqli_object(), $query);
}


/**
 * Function to insert a new phone number
 *
 * @param string $enteredEmail Email of User
 * @param string $enteredPhoneNumber Phone number of user
 * @param string $enteredType Type of phone number
 * @param string $enteredShareable on for shareable and off for not shareable
 *
 * @return void
 */

function gameswap_insert_phoneNumber($enteredEmail, $enteredPhoneNumber, $enteredType, $enteredShareable)
{
    if($enteredShareable == "on"){
        $query = "INSERT INTO phonenumber(`email`, `number`, `number_type`,`share_phone_number`) 
                            VALUES ('$enteredEmail','$enteredPhoneNumber','$enteredType', 1)";
        $result = mysqli_query(gameswap_get_mysqli_object(), $query);
    }
    else{
        $query = "INSERT INTO phonenumber(`email`, `number`, `number_type`,`share_phone_number`) 
                            VALUES ('$enteredEmail','$enteredPhoneNumber','$enteredType', 0)";
        $result = mysqli_query(gameswap_get_mysqli_object(), $query);
    }
}


/**
 * Function to verify postal code information
 *
 * @param string $enteredPostalCode Entered postal code
 * @param string $enteredCity Entered city
 * @param string $enteredState Entered state
 *
 * @return array
 */

function gameswap_verify_postalCode($enteredPostalCode, $enteredCity, $enteredState)
{
    $postalCode_info = array('postalCode_found' => "Yes");
    $query = "SELECT postal_code FROM PostalCode WHERE postal_code='$enteredPostalCode'";
    $result = mysqli_query(gameswap_get_mysqli_object(), $query);
    $count = mysqli_num_rows($result);

    if($count != 0){
        $query = "SELECT postal_code FROM `postalcode`
                WHERE postal_code='$enteredPostalCode' AND city='$enteredCity' AND state='$enteredState'";
        $result = mysqli_query(gameswap_get_mysqli_object(), $query);
        $count = mysqli_num_rows($result);

        if($count == 1){
            $postalCode_info['postalCode_verify'] = "Pass";
        }
        else{
            $postalCode_info['postalCode_verify'] = "Fail";
        }
    }
    else{
        $postalCode_info['postalCode_found'] = "No";
    }

    return $postalCode_info;
}


/**
 * Function to verify phone number information
 *
 * @param string $enteredPostalCode Entered postal code
 * @param string $enteredCity Entered city
 * @param string $enteredState Entered state
 *
 * @return array
 */

function gameswap_verify_phoneNumber($enteredPhoneNumber)
{

    $phoneNumber_info = array('phoneNumber_found' => "Yes");
    $query = "SELECT number FROM phonenumber WHERE number='$enteredPhoneNumber'";
    $result = mysqli_query(gameswap_get_mysqli_object(), $query);
    $count = mysqli_num_rows($result);

    if($count == 0){
        $phoneNumber_info['phoneNumber_found'] = "No";
    }
    return $phoneNumber_info;
}