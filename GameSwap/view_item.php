<?php
include('lib/common.php');
include('lib/bootstrap_header.php');
include('lib/gameswap_functions.php');


//Validate User Login.
gameswap_validate_user_login();

$page_content = "<head>
                    <title>View Item Details</title>
                 </head>
                 <body>";

//Set page title.
$page_title = "Item Details";

//Get the item's id from the URL. For this we can either use $_GET or $_REQUEST variables.
$url_item_id = $_REQUEST['item_id'];

//Before we do anything, let's check to make sure we actually got something from the URL.
//Display Error screen if we got no Item id, or if the item id is empty

if(isset($_REQUEST['item_id']) && $_REQUEST['item_id']!= "") {

    //Item Id is set. Let's proceed to get the Item details.

    $final_items_arr = array(
        "Item #" => $url_item_id,
        "Title" => "",
        "Game Type" => "",
        "Platform" => "",
        "Media" => "",
        "Computer Platform" => "",
        "Piece Count" => "",
        "Condition" => "",
        "Description" => "",
    );


//Now, get all the details about the item using the item id.
//We have a function in gameswap_functions.php that gets all the details about an item.

    $url_item_details = gameswap_get_item_details_from_item_id($url_item_id);


//First, make sure the item was found.
    if(strtolower($url_item_details['item_found']) == "yes"){
        //Item was found.
        $item_email = $url_item_details['item_email'];
        $item_id = $url_item_details['item_id'];
        $item_type = $url_item_details['item_type'];
        if($item_type != ""){
            $final_items_arr["Game Type"] = $item_type;
        }
        $item_title = $url_item_details['item_title'];
        if($item_title != ""){
            $final_items_arr["Title"] = $item_title;
        }
        $item_platform = $url_item_details['item_game_platform'];
        if($item_title != ""){
            $final_items_arr["Platform"] = $item_platform;
        }
        $item_media = $url_item_details['item_media'];
        if($item_title != ""){
            $final_items_arr["Media"] = $item_media;
        }
        $item_computer_platform = $url_item_details['item_computer_platform'];
        if($item_title != ""){
            $final_items_arr["Computer Platform"] = $item_computer_platform;
        }
        $item_piece_count = $url_item_details['item_piece_count'];
        if($item_title != ""){
            $final_items_arr["Piece Count"] = $item_piece_count;
        }
        $item_condition = $url_item_details['item_condition'];
        if($item_title != ""){
            $final_items_arr["Condition"] = $item_condition;
        }
        $item_description = $url_item_details['item_description'];
        if($item_title != ""){
            $final_items_arr["Description"] = $item_description;
        }

        $banner_content = gameswap_generate_banner_container();
        $page_content .= "<div class='container-fluid gameswap-body-container'>
                              $banner_content
                              <div class= 'gameswap-inside-container'>
                                  <h3 id='gameswap-view-item-details-item-container-header'>Item Details</h3>
                                  <hr id='gameswap-view-item-details-item-container-hr'/>
                                    <table class='table'>
                                        <tr>
                                            <td>
                                                <div id='gameswap-view-item-details-item-container'>
                                                    <table class = 'table table-borderless' id='gameswap-view-item-details-item-table'>";

        //Now, since we need to display only those fields that are not empty, we simply loop through the $final_items_arr array and print each entry whose value is not empty.
        //The label for the field will be the keys in the $final_items_arr array.

        foreach($final_items_arr as $item_field_label => $item_field_value){
            if($item_field_value != ""){
                $page_content .= "<tr>
                                <td class='gameswap-bold-td'>$item_field_label</td>
                                <td>$item_field_value</td>
                              </tr>";
            }
        }

        $page_content .= "</table>
                      </div></td>";


        //Now, check to see if the current logged in user is the same as the item's owner user.
        // If so, we don't need to gather user details and we can simply return 0 miles as the distance.
        $current_logged_in_user_email = gameswap_get_current_user_id();
        if($item_email != $current_logged_in_user_email){
            //The item's owner user and current logged in user are different.
            //Now, get the item owner's details. Using the function "gameswap_get_user_details" from gameswap_functions.php
            $item_user_details = gameswap_get_user_details($item_email, TRUE);
            $item_user_first_name = $item_user_details['user_first_name'];
            $item_user_last_name = $item_user_details['user_last_name'];
            $item_user_full_name = "$item_user_first_name $item_user_last_name";
            $item_user_nickname = $item_user_details['user_nickname'];
            $item_user_postalcode = $item_user_details['user_postalcode'];
            $item_user_city = $item_user_details['user_city'];
            $item_user_state = strtoupper($item_user_details['user_state']);
            $item_user_latitude = $item_user_details['user_latitude'];
            $item_user_longitude = $item_user_details['user_longitude'];
            $item_user_location = "$item_user_city, $item_user_state $item_user_postalcode";

            //Now, let's get the distance between the item owner user and the current user.
            $item_user_distance_in_miles = gameswap_get_distance_between_users_by_user_id($current_logged_in_user_email, $item_email);

            //Now, let's get the item owner user's rating.
            $item_user_rating = gameswap_get_user_rating_by_id($item_email);


            $page_content .="<td><div id='gameswap-item-details-item-owner-container'>
                            <table class = 'table table-borderless' id='gameswap-view-item-details-item-table'>
                                <tr>
                                    <td class='gameswap-bold-td'>Offered by</td>
                                    <td class='gameswap-bold-td'>$item_user_nickname</td>
                                </tr>
                                <tr>
                                    <td>Location</td>
                                    <td>$item_user_location</td>
                                </tr>
                                <tr>
                                    <td>Rating</td>
                                    <td>$item_user_rating</td>
                                </tr>";

            if($item_user_distance_in_miles >= 0.00 && $item_user_distance_in_miles <= 25.00){
                $page_content .= "<tr id ='gameswap-item-details-item-owner-distance-green'>";
            }elseif($item_user_distance_in_miles > 25.00 && $item_user_distance_in_miles <= 50.00){
                $page_content .= "<tr id ='gameswap-item-details-item-owner-distance-yellow'>";
            }elseif($item_user_distance_in_miles > 50.00 && $item_user_distance_in_miles <= 100.00){
                $page_content .= "<tr id ='gameswap-item-details-item-owner-distance-orange'>";
            }elseif($item_user_distance_in_miles > 100.00){
                $page_content .= "<tr id ='gameswap-item-details-item-owner-distance-red'>";
            }


            $current_logged_in_user_details = gameswap_get_user_details($current_logged_in_user_email, FALSE);
            $current_logged_in_user_postalcode = $current_logged_in_user_details['user_postalcode'];
            //If the item owner's postal code is the same as current logged in user, don't display the distance.
            if($item_user_postalcode != $current_logged_in_user_postalcode){
                $page_content .= "<td>Distance</td>
                          <td>$item_user_distance_in_miles miles</td>
                          </tr>";

            }


            /**
             *
             * Now, we display "Propose Swap" button.
             * However, we need to check few things to ensure we can display the button:
             * 1. Current User's unrated swap count cannot exceed 2.
             * 2. Current User's unaccepted swap count cannot exceed 5.
             * 3. The item is available for swapping -- Item is not a part of active swap.
             *
             */

            //First, let's set a boolean variable "displayProposeSwapBtn" and set it to FALSE.
            $displayProposeSwapBtn = FALSE;

            //Get the count of unrated swaps for current user.
            $current_user_unrated_swap_count = gameswap_get_user_unrated_swap_count_by_id($current_logged_in_user_email);

            if($current_user_unrated_swap_count <= 2){
                //Now, let's check to see the total number of unaccepted swaps for the current user.
                $current_user_unaccepted_swap_count = gameswap_get_user_unaccepted_swap_count_by_id($current_logged_in_user_email);

                if($current_user_unaccepted_swap_count <= 5){
                    //Now, check to see if the item is available.
                    $is_item_available = gameswap_check_item_availablity($item_id);

                    if($is_item_available){
                        //Set "displayProposeSwapBtn" variable value to TRUE
                        $displayProposeSwapBtn = TRUE;
                    }
                }
            }

            //Now, if the value of "$displayProposeSwapBtn" is TRUE, we display the "Propose Swap" Button.
            if($displayProposeSwapBtn){
                $page_content .= "</div></td></tr></table>
                                <table class='table table-borderless' id ='gameswap-view-item-details-user-item-propose-swap-table'>
                                    <tr>
                                        <td>
                                            <div id='gameswap-view-item-details-user-item-propose-swap-form-container'>
                                                <form id = 'gameswap-view-item-details-user-item-propose-swap-form' action ='propose_swap.php' method='POST'>
                                                    <input type='hidden' id='counterparty_item_id' name='counterparty_item_id' value='$item_id'>
                                                    <input type='hidden' id='counterparty_user_id' name='counterparty_user_id' value='$item_email'>
                                                    <input type='hidden' id='current_user_counterparty_user_distance' name='current_user_counterparty_user_distance' value='$item_user_distance_in_miles'>
                                                    <input type='submit' value = 'Propose Swap' class='btn btn-primary'>
                                                </form>
                                            </div> 
                                        </td>
                                    </tr>
                                </table>
                                </div></div>";
            }

        }

    }elseif(strtolower($url_item_details['item_found']) == "no"){
        //Item not found in the Database. Display error message.
        $error_msg = "Error! Item does not exist in the system.";
        $error_btn_url = "main_menu.php";
        $error_btn_text = "Go to Home Page";
        $page_content .= gameswap_generate_error_message_content($page_title, $error_msg, $error_btn_url, $error_btn_text);
    }
}else{
    //Item ID is not set. Display Error Message.
    $error_msg = "Error! Item ID was not provided with the request.";
    $error_btn_url = "main_menu.php";
    $error_btn_text = "Go to Home Page";
    $page_content .= gameswap_generate_error_message_content($page_title, $error_msg, $error_btn_url, $error_btn_text);
}




$page_content .= "</body>";


?>

<html>
<?php
print($page_content);
?>
</html>
