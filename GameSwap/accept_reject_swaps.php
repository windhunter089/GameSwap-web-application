<?php
include('lib/common.php');
include('lib/bootstrap_header.php');
include('lib/gameswap_functions.php');

//Validate User Login.
gameswap_validate_user_login();

//Let's add buttons to homepage and logout at the top of the page.


//Current logged in user.
$current_user_id = gameswap_get_current_user_id();
$page_content = "<title>Accept Reject Swaps</title>
                 </head>
                 <body>";



//Set page title.
$page_title = "Accept/Reject Swaps";

/**
 * Since this form/page also acts as a self-posting form, we check to see if we have received any post
 * for accepting or rejecting a swap. If so, we need to update the swap table to accept or reject swaps.
 */
//Check to see if a swap accept/reject was submitted. This happens when we get a POST of "swap_accept_reject_submit_flag" set to Yes.
if(isset($_REQUEST['swap_accept_reject_submit_flag']) && $_REQUEST['swap_accept_reject_submit_flag'] != ""){

    /**
     * This means there was a swap acceptation/rejection postback.
     * Let's do some more checks on the POST we get. For a Swap Accept/Reject, we get these variables in the POST.
     *
     * 1. swap_accept_reject_submit_flag --> Will be set to "Yes"
     * 2. swap_accept_reject_proposer_item_id --> This is the "proposer_item_id" field for the table. Cannot be null or empty.
     * 3. swap_accept_reject_counterparty_item_id --> This is the "desired_item_id" field for the table. Cannot be null or empty.
     * 4. swap_accept_reject_proposer_user_id --> This is the "proposer_email" for the table. Cannot be null or empty.
     * 5. swap_accept_reject_counterparty_user_id --> This is the "counterparty_email" field for the table. Cannot be null or empty.
     * 6. swap_accept_reject_btn --> Shows which button was pressed; Accept or Reject. Used to determine whether to accept or reject the swap.
     */


    if(strtolower($_REQUEST['swap_accept_reject_submit_flag']) == "yes"){

        //Let's see if this is a "Accept Swap" request or a "Reject Swap" request.
        $action_type = $_REQUEST['swap_accept_reject_btn'];
        $accepted_action_types = array("Accept", "Reject");

        //Make sure 2,3,4, and 5 from above comment are not empty. If empty, display error message.
        $proposer_item_id = $_REQUEST['swap_accept_reject_proposer_item_id'];
        $counterparty_item_id = $_REQUEST['swap_accept_reject_counterparty_item_id'];
        $proposer_user_id = $_REQUEST['swap_accept_reject_proposer_user_id'];
        $counterparty_user_id = $_REQUEST['swap_accept_reject_counterparty_user_id'];

        if($proposer_item_id == "" || $counterparty_item_id == "" || $proposer_user_id == "" || $counterparty_user_id == "" || !(in_array($action_type, $accepted_action_types))){
            // This means something went wrong while posting the data. This shouldn't happen, but if it ever does,
            // we display error message page.
            $error_msg = "Error! Cannot $action_type Swap. Error with requests. Please try again later.";
            $error_btn_url = "main_menu.php";
            $error_btn_text = "Go to Home Page";
            $page_content .= gameswap_generate_error_message_content($page_title, $error_msg, $error_btn_url, $error_btn_text);
        }else{

            //This means the requests are fine. Let's proceed to update the swap table.

            //Get today's date, since this will be used to populate the "accepted_rejected" date in the table.
            $accepted_rejected_date = date("Y-m-d");

            /**
             * If "Accept" button was clicked, we need to mark "swap_status" as accepted.
             * If "Reject" button was clicked, we need to mark "swap_status" as rejected.
             */
            if(strtolower($action_type) == "accept"){
                //Accepted.
                $swap_status = "Accepted";
            }elseif(strtolower($action_type) == "reject"){
                //Rejected.
                $swap_status = "Rejected";
            }

            //Now, we are ready to update.

            $swap_accept_reject_query = "UPDATE swap SET accepted_rejected_date = '$accepted_rejected_date', swap_status = '$swap_status' 
                                            WHERE
                                            desired_item_id = '$counterparty_item_id' AND 
                                            proposer_item_id = '$proposer_item_id' AND 
                                            counterparty_email = '$counterparty_user_id' AND 
                                            proposer_email ='$proposer_user_id'";

            $swap_accept_reject_res = mysqli_query($db, $swap_accept_reject_query);

            include('lib/show_queries.php');

            if ($swap_accept_reject_res  == False) {
                $error_msg[] = "UPDATE ERROR: Swap accepted_rejected_date: " . $accepted_rejected_date .
                                " swap_status: " . $swap_status .
                                " desired_item_id: " . $counterparty_item_id .
                                " proposer_item_id: " . $proposer_item_id .
                                "<br>" . __FILE__ . " line:" . __LINE__;
            }

            //Update Successful.
            // If the swap was accepted, we need to display a modal window with the information about the other user involved in the swap.
            if($swap_status == "Accepted"){

                //First, let's gather the information (Proposer's email, first name, phone number, phone number type)
                $other_user_information = gameswap_get_user_details($proposer_user_id);
                $other_user_first_name = $other_user_information['user_first_name'];

                //Let's get the phone number for other user.
                $other_user_phone_details = gameswap_get_user_phone_details($proposer_user_id);
                //See if the user has phone number.
                $user_has_phone_num = $other_user_phone_details['user_has_phone_num'];
                $phone_number_markup = "";
                if(strtolower($user_has_phone_num) == "yes"){
                    //User has a phone number.
                    //See if it is shareable. If it is not,we display "Not Available" instead.
                    $other_user_phone_num_is_shareable = $other_user_phone_details['phone_number_is_shareable'];
                    if($other_user_phone_num_is_shareable){
                        //Shareable phone number.
                        $other_user_phone_number = $other_user_phone_details['phone_number'];
                        $phone_number_type = $other_user_phone_details['phone_number_type'];
                        $phone_number_markup = "<strong>Phone Numnber: </strong>$other_user_phone_number ($phone_number_type)";
                    }else{
                        $other_user_phone_number = "Not Available";
                        //No need tp collect phone number type.
                        $phone_number_markup = "<strong>Phone Numnber: </strong>$other_user_phone_number";
                    }

                }else{
                    //User has no phone number.
                    $phone_number_markup = "No phone number available";
                }


                //Now we have everything we need for the modal window pop-up.
                //Let's put it together in one variable.

                $swap_accepted_rejected_modal_markup = "<div id='gameswap-swap-accepted-modal-markup-container'>
                                                    <p><strong>Contact the proposer to swap items!</strong></p>
                                                    <p><strong>Email:</strong> $proposer_user_id</p>
                                                    <p><strong>Name:</strong> $other_user_first_name</p>
                                                    <p>$phone_number_markup</p>
                                               </div>";



            }elseif ($swap_status == "Rejected"){
                //Swap was Rejected.
                $swap_accepted_rejected_modal_markup = "<div id='gameswap-swap-accepted-modal-markup-container'>
                                                    <p><strong>Swap Rejected! Please click on OK to continue.</strong></p>
                                               </div>";
            }

            /**
             * Note: We are adding a hidden input element based on whether the current logged-in user has any
             * more unaccepted swaps or not. We do this because we need to redirect the user to home page
             * if there are none. However, if there are more, we will simply redirect them to
             * accept/reject swap page.
             */
            //First, check to see if the current logged in user has any unaccepted swaps.
            $current_user_unaccepted_swaps_count = gameswap_get_user_unaccepted_swap_count_by_id($current_user_id);

            //Set default redirect value to main_menu.php (Home Page)
            $redirect_val = "main_menu.php";

            if($current_user_unaccepted_swaps_count >0){

                //Set redirect value to swap accept/reject page
                $redirect_val = "accept_reject_swaps.php";
            }

            $swap_accepted_rejected_modal_markup .= "<div id='gameswap-swap-accepted-rejected-modal-hidden-container'>
                                                        <input type='hidden' id = 'gameswap-swap-accepted-rejected-redirect-val' name='gameswap-swap-accepted-rejected-redirect-val' value='$redirect_val'>
                                                     </div>";

?>


            <script>

            $(document).ready(function (){
                $('#swapAcceptedModal').modal('show');

                //Callback function for OK button in the modal.
                $('#gameswap-swap-accepted-rejected-modal-ok-btn').on('click', function(){
                    //OK button on the modal was clicked.
                    //Redirect to appropriate page.
                    //Get the hidden redirect value from the modal.
                    var modalRedirectVal = $('#gameswap-swap-accepted-rejected-redirect-val').val();
                    window.location.href = modalRedirectVal;
                });
            });


            </script>

<?php

            //Add the modal content.
            $page_content .="<div class='modal fade' id='swapAcceptedModal' tabindex='-1' role='dialog' aria-labelledby='swapAcceptedRejectedModalLabel' aria-hidden='true'>
                                  <div class='modal-dialog' role='document'>
                                    <div class='modal-content'>
                                      <div class='modal-header' id='gameswap-swap-accepted-rejected-modal-header-container'>
                                        <h5 class='modal-title' id='swapAcceptedRejectedModalLabel'>Swap Accepted</h5>
                                        <!--
                                        <button type='button' class='close' data-bs-dismiss='modal' aria-label='Close'>
                                          <span aria-hidden='true'>&times;</span>
                                        </button>
                                        -->
                                      </div>
                                      <div class='modal-body'>
                                        $swap_accepted_rejected_modal_markup
                                      </div>
                                      <div class='col-12 modal-footer'>
                                        <!--
                                            <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Close</button>
                                        -->
                                        <button type='button' class='btn btn-primary' id='gameswap-swap-accepted-rejected-modal-ok-btn'>OK</button>
                                      </div>
                                    </div>
                                  </div>
                                </div>";



        }
    }


}else{


    //There was no swap accept/reject postback. That means simply display the page content.
    $banner_container = gameswap_generate_banner_container();
    $page_content .= "<div class='container-fluid gameswap-body-container'>
                           $banner_container
                           <div class ='gameswap-inside-container'>
                              <h3 id='gameswap-accept-reject-swap-header'>Accept/Reject Swaps</h3>
                              <hr class = 'gameswap-container-hr' id='gameswap-accept-reject-swap-container-hr'/>";

    //First, we query to get all the swaps for the current user where he/she is the counterparty.
    $current_user_swaps_query = "SELECT proposer_email, counterparty_email, desired_item_id, proposer_item_id FROM swap 
                                WHERE swap_status = 'Pending' AND counterparty_email = '$current_user_id'";

    $current_user_swaps_res = mysqli_query($db, $current_user_swaps_query);
    $current_user_swaps_res_count = mysqli_num_rows($current_user_swaps_res);

    //if there are no unaccepted swaps for the cuurent user, simply display a message box.
    if($current_user_swaps_res_count == 0){
        $page_content .= "<div id ='gameswap-accept-reject-swap-no-swaps-container'>
                            <p>No Swaps to Accept/Reject for you! <i class='bi bi-emoji-smile'></i></p>
                          </div>";
    }else{
        $page_content .="<table class = 'table table-responsive align-middle mb-0 bg-white border-bottom'>
                            <thead class='bg-light'>
                                <tr>
                                    <th scope='col'>Date</th>
                                    <th scope='col'>Desired Item</th>
                                    <th scope='col'>Proposer</th>
                                    <th scope='col'>Rating</th>
                                    <th scope='col'>Distance</th>
                                    <th scope='col'>Proposed Item</th>
                                    <th scope='col'></th>
                                </tr>
                            </thead>";
        while($current_user_swaps_row = mysqli_fetch_array($current_user_swaps_res, MYSQLI_ASSOC)){

            $current_user_swaps_proposer_id = $current_user_swaps_row['proposer_email']; //$proposerUserId
            $current_user_swaps_counterparty_id = $current_user_swaps_row['counterparty_email']; // $userId
            $current_user_swaps_proposer_item_id = $current_user_swaps_row['proposer_item_id']; //$proposedItemId
            $current_user_swaps_counterparty_item_id = $current_user_swaps_row['desired_item_id']; //desiredItemId

            //Now, query to get the necessary data for this swap.

            $each_swap_get_details_query = "SELECT * FROM
                                    (SELECT swap.proposal_date, swap.desired_item_id, T2.CP_TITLE
                                    AS DESIRED_ITEM, T2.P_EMAIL AS PROPOSER_EMAIL,
                                    T2.P_NICKNAME AS PROPOSER, swap.proposer_item_id,
                                    T2.P_TITLE AS PROPOSED_ITEM FROM swap JOIN (SELECT
                                    T1.CP_TITLE, T1. P_TITLE, T1.P_EMAIL, T1.CP_EMAIL,
                                    T1.CP_INO, T1.P_INO, U1.nickname AS P_NICKNAME FROM
                                    (SELECT IT1.email AS CP_EMAIL, IT1.item_no AS CP_INO,
                                    IT1.title AS CP_TITLE, IT2.email AS P_EMAIL, IT2.item_no AS
                                    P_INO, IT2.title AS P_TITLE FROM item IT1 JOIN item IT2) T1
                                    JOIN(SELECT email, nickname FROM `user`) U1 ON T1.P_EMAIL =
                                    U1.email) T2 ON swap.proposer_email = T2.P_EMAIL AND
                                    swap.counterparty_email = T2.CP_EMAIL WHERE
                                    swap.counterparty_email = '$current_user_swaps_counterparty_id' AND swap.swap_status =
                                    'Pending' AND swap.desired_item_id = T2.CP_INO AND
                                    swap.proposer_item_id = T2.P_INO ) FT1
                                    JOIN
                                    (SELECT ROUND((6371 * 0.621371 * (2 *
                                    ATAN((SQRT((POWER((SIN(((RADIANS(T2.USER2_LAT)) -
                                    (RADIANS(T1.USER1_LAT))) / 2)), 2) +
                                    (COS((RADIANS(T1.USER1_LAT)))) *
                                    (COS((RADIANS(T2.USER2_LAT)))) *
                                    POWER((SIN(((RADIANS(T2.USER2_LON)) -
                                    (RADIANS(T1.USER1_LON))) / 2)), 2)))), (SQRT(1 -
                                    (POWER((SIN(((RADIANS(T2.USER2_LAT)) -
                                    (RADIANS(T1.USER1_LAT))) / 2)), 2) +
                                    (COS((RADIANS(T1.USER1_LAT)))) *
                                    (COS((RADIANS(T2.USER2_LAT)))) *
                                    POWER((SIN(((RADIANS(T2.USER2_LON)) -
                                    (RADIANS(T1.USER1_LON))) / 2)), 2))))))),2) AS
                                    DISTANCE_CALC_MILES FROM
                                     (SELECT U1.email AS USER1_EMAIL, U1.nickname AS
                                    USER1_NICKNAME, U1.postal_code AS USER1_POSTAL_CODE,
                                    P1.latitude AS USER1_LAT, P1.longitude USER1_LON, P1.City AS
                                    USER1_CITY, P1.State AS USER1_STATE FROM `user` U1 JOIN
                                    postalcode P1 ON U1.postal_code = P1.postal_code) T1
                                     JOIN
                                     (SELECT U2.email AS USER2_EMAIL, U2.nickname AS
                                    USER2_NICKNAME, U2.postal_code AS USER2_POSTAL_CODE,
                                    P2.latitude AS USER2_LAT, P2.longitude USER2_LON, P2.City AS
                                    USER2_CITY, P2.state AS USER2_STATE FROM `user` U2 JOIN
                                    postalcode P2 ON U2.postal_code = P2.postal_code) T2
                                     ON T1.USER1_EMAIL <> T2.USER2_email WHERE
                                    USER1_EMAIL = '$current_user_swaps_counterparty_id' AND USER2_EMAIL =
                                    '$current_user_swaps_proposer_id') FT2
                                    JOIN
                                    (SELECT COALESCE((((SELECT AVG(swap_proposer_rating) AS
                                    RATING_AVG FROM swap WHERE swap_status = 'Accepted' AND
                                    proposer_email = '$current_user_swaps_proposer_id' AND swap_proposer_rating IS
                                    NOT NULL) + (SELECT AVG(swap_counterparty_rating) AS
                                    RATING_AVG FROM swap WHERE swap_status = 'Accepted' AND
                                    counterparty_email = '$current_user_swaps_proposer_id' AND
                                    swap_counterparty_rating IS NOT NULL)) /2), ((SELECT
                                    AVG(swap_proposer_rating) AS RATING_AVG FROM swap
                                    WHERE swap_status = 'Accepted' AND proposer_email =
                                    '$current_user_swaps_proposer_id' AND swap_proposer_rating IS NOT NULL)),
                                    ((SELECT AVG(swap_counterparty_rating) AS RATING_AVG
                                    FROM swap WHERE swap_status = 'Accepted' AND
                                    counterparty_email = '$current_user_swaps_proposer_id' AND
                                    swap_counterparty_rating IS NOT NULL)), 'None') AS
                                    USER_RATING_AVG) FT3 WHERE FT1.desired_item_id =
                                    '$current_user_swaps_counterparty_item_id' AND FT1.proposer_item_id ='$current_user_swaps_proposer_item_id'";


            $each_swap_get_details_res = mysqli_query($db, $each_swap_get_details_query);
            $each_swap_get_details_res_count = mysqli_num_rows($each_swap_get_details_res);

            if(!empty($each_swap_get_details_res) && ($each_swap_get_details_res_count > 0)){
                $each_swap_row = mysqli_fetch_array($each_swap_get_details_res, MYSQLI_ASSOC);
                //Collect all the fields that we need.
                $each_swap_date = $each_swap_row['proposal_date'];
                $each_swap_desired_item_id = $each_swap_row['desired_item_id'];
                $each_swap_desired_item_title = $each_swap_row['DESIRED_ITEM'];
                $each_swap_desired_item_href = "<a href='view_item.php?item_id=$each_swap_desired_item_id'>$each_swap_desired_item_title</a>";
                $each_swap_desired_item_proposer = $each_swap_row['PROPOSER'];
                $each_swap_proposer_rating = $each_swap_row['USER_RATING_AVG'];
                $each_swap_proposer_counterparty_distance_in_miles = $each_swap_row['DISTANCE_CALC_MILES'];
                $each_swap_proposed_item_id = $each_swap_row['proposer_item_id'];
                $each_swap_proposed_item_title = $each_swap_row['PROPOSED_ITEM'];
                $each_swap_proposed_item_href = "<a href='view_item.php?item_id=$each_swap_proposed_item_id'>$each_swap_proposed_item_title</a>";

                //Now, add it to our table.

                $page_content .="<tr>
                                <form id = 'gameswap-accept-reject-swap-form' action ='' method='POST'>
                                    <td>$each_swap_date</td>
                                    <td>$each_swap_desired_item_href</td>
                                    <td>$each_swap_desired_item_proposer</td>
                                    <td>$each_swap_proposer_rating</td>
                                    <td>$each_swap_proposer_counterparty_distance_in_miles miles</td>
                                    <td>$each_swap_proposed_item_href</td>
                                    <td>
                                        <div class='gameswap-accept-reject-btn-container'>
                                            <input type='submit' name= 'swap_accept_reject_btn' value = 'Accept' class='btn btn-primary'>
                                        </div>
                                        <div class='gameswap-accept-reject-btn-container'>
                                            <input type='submit' name= 'swap_accept_reject_btn' value = 'Reject' class='btn btn-primary'>
                                        </div>
                                    </td>
                                    <input type='hidden' id='swap_accept_reject_proposer_item_id' name='swap_accept_reject_proposer_item_id' value='$each_swap_proposed_item_id'>
                                    <input type='hidden' id='swap_accept_reject_counterparty_item_id' name='swap_accept_reject_counterparty_item_id' value='$each_swap_desired_item_id'>
                                    <input type='hidden' id='swap_accept_reject_proposer_user_id' name='swap_accept_reject_proposer_user_id' value='$current_user_swaps_proposer_id'>
                                    <input type='hidden' id='swap_accept_reject_counterparty_user_id' name='swap_accept_reject_counterparty_user_id' value='$current_user_swaps_counterparty_id'>
                                    <input type='hidden' id='swap_accept_reject_submit_flag' name='swap_accept_reject_submit_flag' value='Yes'>
                                </form>    
                              </tr>";
            }

        }
        $page_content .="</table></div>";
    }
}

$page_content .="</body>";

?>

<html>
<?php
print_r($page_content);
?>
</html>
