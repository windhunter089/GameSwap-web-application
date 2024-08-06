<?php
include('lib/common.php');
include('lib/bootstrap_header.php');
include('lib/gameswap_functions.php');

//Validate User Login.
gameswap_validate_user_login();

//Current logged in user.
$current_user_id = gameswap_get_current_user_id();
$page_content = "<head>
                    <title>View Item Details</title>
                 </head>
                 <body>";

//Set page title.
$page_title = "Propose a Swap";

$banner_container = gameswap_generate_banner_container();

/**
 * Since this page also acts as a self-posting form, we check the POST to see if there was a submission (Swap Proposal)
 */

//Check to see if a swap proposal was submitted. This happens when we get a POST of "swap_proposal_submission_flag" set to Yes.
if(isset($_REQUEST['swap_proposal_submission_flag']) && $_REQUEST['swap_proposal_submission_flag'] != ""){
    /**
     * Let's do some more checks on the POST we get. For a Swap Proposal, we get these variables in the POST.
     * 1. swap_proposal_submission_flag --> Will be set to "Yes"
     * 2. propose_a_swap_selected_user_item_id --> This is the "proposer_item_id" field for the table. Cannot be null or empty.
     * 3. swap_proposal_counterparty_item_id --> This is the "desired_item_id" field for the table. Cannot be null or empty.
     * 4. swap_proposal_counterparty_user_id --> This is the "counterparty_email" field for the table. Cannot be null or empty.
     * 5. swap_proposal_current_user_id --> This is the "proposer_email" for the table. This will not be null, but if it is, we can simply query to get the current logged in user's id in place of this.
     *
     */

    if(strtolower($_REQUEST['swap_proposal_submission_flag']) == "yes"){
        //Make sure 2,3,and 4 from above comment are not empty. If empty, display error message.
        $proposer_item_id = $_REQUEST['propose_a_swap_selected_user_item_id'];
        $counterparty_item_id = $_REQUEST['swap_proposal_counterparty_item_id'];
        $counterparty_user_id = $_REQUEST['swap_proposal_counterparty_user_id'];
        if($proposer_item_id == "" || $counterparty_item_id == "" || $counterparty_user_id == ""){
            // Something went wrong while posting the data. This shouldn't happen, but if it ever does,
            // we display error message page.
            $error_msg = "Error! Cannot Propose A swap. Error with requests. Please try again later.";
            $error_btn_url = "my_items.php";
            $error_btn_text = "Go to Home Page";
            $page_content .= gameswap_generate_error_message_content($page_title, $error_msg, $error_btn_url, $error_btn_text);

        }else{
            $proposer_user_id = $_REQUEST['swap_proposal_current_user_id'];
            //If this is empty, replace it with the current logged in user's user id.
            if($proposer_user_id == ""){
                $proposer_user_id = $current_user_id;
            }

            //Now, let's sanitize the inputs from the form and prepare for insertion in the swap table.
            $proposer_user_id = mysqli_real_escape_string($db, $proposer_user_id);
            $proposer_item_id = mysqli_real_escape_string($db, $proposer_item_id);
            $counterparty_user_id = mysqli_real_escape_string($db, $counterparty_user_id);
            $counterparty_item_id = mysqli_real_escape_string($db, $counterparty_item_id);
            //Each new swap will have the status of "Pending"
            $swap_status = "Pending";
            //Get today's date to set as the proposal date.
            $proposal_date = date("Y-m-d");




            //Insert into the table now.
            $swap_insertion_query = "INSERT INTO swap (proposal_date, proposer_email,
                                        proposer_item_id, counterparty_email, desired_item_id, swap_status)
                                        VALUES ('$proposal_date', '$proposer_user_id', '$proposer_item_id', '$counterparty_user_id', '$counterparty_item_id', '$swap_status')";


            $swap_insertion_res = mysqli_query($db, $swap_insertion_query);

            include('lib/show_queries.php');

            if ($swap_insertion_res  == False) {
                $error_msg[] = "INSERT ERROR: Swap proposal_date: " . $proposal_date .
                                " proposer_email: " . $proposer_user_id .
                                " proposer_item_id: " . $proposer_item_id .
                                " counterparty_email: " . $counterparty_user_id .
                                " desired_item_id: " . $counterparty_item_id .
                                "<br>" . __FILE__ . " line:" . __LINE__;
            }


            $proposed_item_title = gameswap_get_item_title_from_item_id($proposer_item_id);
            $desired_item_title = gameswap_get_item_title_from_item_id($counterparty_item_id);

            $swap_details_markup = "<div id ='gameswap-successful-swap-insertion-details-container'>
                                        <p>Your Proposed Item: $proposed_item_title</p>
                                        <p>Desired Item: $desired_item_title ($counterparty_user_id)</p>
                                    </div>";
            //Insertion complete. Now, we display confirmation message that also allows the user to return to main menu.
            $page_content .="<div class='container-fluid gameswap-body-container'>
                        $banner_container
                        <h3 class='gameswap-swap-proposal-success-msg-container-header'>Propose a Swap</h3>
                        <hr class='gameswap-swap-proposal-success-msg-container-hr'/>
                        <div class='alert alert-success' role='alert'>
                            <div class='gameswap-swap-proposal-success-msg-container'>
                               <i class='bi bi-check-circle-fill'></i>
                                <strong>Success! Swap proposal created successfully. $swap_details_markup</strong>
                            </div>
                            <div class = 'gameswap-swap-proposal-success-msg-container'>
                                <a class='btn btn-primary' href='my_items.php' role='button'>Go to Home Page</a>
                            </div>
                        </div>
                      </div>";

        }


    }

}else{
    //First, we check to see if the current logged in user has more than 2 unrated swaps.
    //If so, display an error message.
        $current_user_unrated_swaps_count = gameswap_get_user_unrated_swap_count_by_id($current_user_id);
    //print_r("UNRATED SWAP COUNT IS $current_user_unrated_swaps_count </br>");

    if($current_user_unrated_swaps_count > 2){
        //Display error message.
        $error_msg = "Error! You have more than 2 unrated swaps. You cannot propose a swap at this time.";
        $error_btn_url = "my_items.php";
        $error_btn_text = "Go to Home Page";
        $page_content .= gameswap_generate_error_message_content($page_title, $error_msg, $error_btn_url, $error_btn_text);

    }else{
        //The current user can propose a swap.

        //First, check to see if we got at least "counterparty_user_id" in the POST request.
        //If we did not, display error message.
        if(isset($_REQUEST['counterparty_item_id']) && $_REQUEST['counterparty_item_id'] != ""){

            $counterparty_item_id = $_REQUEST['counterparty_item_id'];
            //Now, let's get the other data from POST as well.
            $counterparty_user_email = $_REQUEST['counterparty_user_id'];
            //Extra Validation: Just in case the counterparty user email is not set or is empty, let's query the item table using the item id.
            if($counterparty_user_email == ""){
                $counterparty_user_email = gameswap_get_user_id_from_item_id($counterparty_item_id);
            }
            $current_user_counterparty_user_distance = $_REQUEST['current_user_counterparty_user_distance'];
            //just an extra validation: Just in case the distance between the users is not set or is empty, let's calculate it.
            if($current_user_counterparty_user_distance == ""){
                $current_user_counterparty_user_distance = gameswap_get_distance_between_users_by_user_id($current_user_id, $counterparty_user_email);
            }

            /**
             * Before we display the Confirm button, let's check to see the count of user's available items.
             * If the user has no available items, don't display the confirm button.
             */
            $current_user_available_swap_count = gameswap_get_available_items_count($current_user_id);
            //Only display the page content if the user's available item count is greater than 0.
            if($current_user_available_swap_count == 0){
                //Display error message.
                $error_msg = "You do not have any available items to propose a swap at this moment.
                              You can try proposing a swap after you have added a new item or when you have an item available.";
                $error_btn_url = "main_menu.php";
                $error_btn_text = "Go to Home Page";
                $page_content .= gameswap_generate_error_message_content($page_title, $error_msg, $error_btn_url, $error_btn_text);


            }elseif($current_user_available_swap_count >0){

                //Now, let's start displaying the contents as needed.
                $page_content .="<div class='container-fluid gameswap-body-container'>
                                $banner_container
                              <div class ='gameswap-inside-container'>
                                  <h3 id='gameswap-propose-a-swap-container-header'>Propose Swap</h3>
                                  <hr class = 'gameswap-container-hr' id='gameswap-propose-a-swap-container-hr'/>";


                //Start displaying content.
                // First, we need to display an alert at the top of the page if the distance between the
                // current user and the counterparty user >= 100.00 miles.
                if($current_user_counterparty_user_distance >= 100.00){
                    //Display alert message in red.
                    $page_content .="<div id = 'gameswap-propose-swap-distance-warning-container' class='alert alert-danger' role='alert'>
                                        <i class='bi bi-exclamation-triangle'></i>
                                        <strong>The other user is $current_user_counterparty_user_distance miles away!</strong>
                                        <i class='bi bi-exclamation-triangle'></i>
                                    </div>";
                }

                //Now, display the counterparty item's title.
                $counterparty_item_title = gameswap_get_item_title_from_item_id($counterparty_item_id);
                $page_content .="<form id = 'gameswap-propose-swap-form' action ='' method='POST'>
                            <table class = 'table table-borderless'>
                                <tr>
                                    <td>You are proposing a trade for <br /> 
                                        <span class='gameswap-lg-font'>
                                            <strong>$counterparty_item_title</strong>
                                        </span>
                                    </td>
                                    <td>
                                        <input type='submit' value = 'Confirm' class='btn btn-primary btn-lg'>
                                    </td>
                                </tr>
                            </table>
                            <table class='table table-responsive'>
                                <tr>
                                    <div id ='gameswap-propose-a-swap-proposer-item-table-container'>
                                        <table class = 'table table-responsive align-middle mb-0 bg-white border-bottom'>
                                            <thead class='bg-light'>
                                                <tr>
                                                    <th scope='col'>Item #</th>
                                                    <th scope='col'>Game Type</th>
                                                    <th scope='col'>Title</th>
                                                    <th scope='col'>Condition</th>
                                                    <th scope='col'>Description</th>
                                                    <th scope='col'></th>
                                                </tr>
                                            </thead>";


                //Now, we get all the available items for the current logged in user and display it in a table with a checkbox at the end.
                /**
                 * Query to get all available items for the current logged in user.
                 */
                //$current_user_items_query = "SELECT item_no, `type`, title, `condition`, description FROM item WHERE email = '$current_user_id' ORDER BY item_no";
                $current_user_items_query = "SELECT item_no, `type`, title, `condition`, description FROM item WHERE item_no NOT IN (
                                            SELECT desired_item_id FROM swap WHERE swap_status IN ('Accepted', 'Pending') UNION
                                            SELECT proposer_item_id FROM swap WHERE swap_status IN ('Accepted', 'Pending') ORDER BY desired_item_id ASC) AND email = '$current_user_id' ORDER BY item_no ASC";
                $current_user_items_res = mysqli_query($db, $current_user_items_query);
                while($item_row = mysqli_fetch_array($current_user_items_res, MYSQLI_ASSOC)){
                    $current_user_item_id = $item_row['item_no'];
                    $current_user_item_type = $item_row['type'];
                    $current_user_item_title = $item_row['title'];
                    $current_user_item_condition = $item_row['condition'];
                    $current_user_item_description = $item_row['description'];
                    //Let's add it to the table.
                    $page_content .="<tr>
                                <th scope='row'>$current_user_item_id</th>
                                <td>$current_user_item_type</td>
                                <td>$current_user_item_title</td>
                                <td>$current_user_item_condition</td>
                                <td>$current_user_item_description</td>
                                <td>
                                    <input type='radio' id='propose_a_swap_selected_user_item_id' name='propose_a_swap_selected_user_item_id' value = '$current_user_item_id' required>
                                    <label for='propose_a_swap_selected_user_item_id'>Select</label>
                                </td>
                            </tr>";
                }


                $page_content .="</table>
                        </div>
                        </tr>
                        </table>
                        <input type ='hidden' id='swap_proposal_submission_flag' name='swap_proposal_submission_flag' value='Yes'>
                        <input type ='hidden' id='swap_proposal_counterparty_item_id' name='swap_proposal_counterparty_item_id' value='$counterparty_item_id'>
                        <input type ='hidden' id='swap_proposal_counterparty_user_id' name='swap_proposal_counterparty_user_id' value='$counterparty_user_email'>
                        <input type ='hidden' id='swap_proposal_current_user_id' name='swap_proposal_current_user_id' value='$current_user_id'>
                        </form>";
            }




        }else{
            //Counterparty Item Id is not in the POST request. This shouldn't happen. Display Error message.
            $error_msg = "Error! Counterparty Item ID is not available in the request. Please try again.";
            $error_btn_url = "my_items.php";
            $error_btn_text = "Go to Home Page";
            $page_content .= gameswap_generate_error_message_content($page_title, $error_msg, $error_btn_url, $error_btn_text);

        }
    }
}

$page_content .="</div></div></body>";
?>

<html>
<?php
print($page_content);
?>
</html>