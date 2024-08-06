<?php

ini_set('max_execution_time', 0);
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
    define("SEPARATOR", "\\");
else
    define("SEPARATOR", "/");

error_reporting(E_ALL);
ini_set('display_errors', 'off');
ini_set("log_errors", 'on');
ini_set("error_log", getcwd() . SEPARATOR ."error.log");
include("../lib/error.php");
include('../lib/gameswap_functions.php');

$start_time = time();

$all_table_arr = array("user" => 0,
                       "phonenumber" => 0,
                       "platform" => 0,
                       "item" => 0,
                       "swap" => 0);

/**
 * Creating this script to import demo data into our DB.
 * To run, simply go to localhost/GameSwap/demo_data/import_demo_data.php
 * MAKE SURE THAT YOU HAVE ADDED THE users.tsv, items.tsv, and swaps.tsv file in the "demo_data" folder.
 */


/**
 * Function to print error msg on fatal errors.
 * 
 */
function shutDownFunction(){
    $error = error_get_last();
    //FATAL error, E_ERROR === 1
    if($error['type'] === E_ERROR){
        //Display error msg with file and line.
        $error_msg = $error['message'];
        $error_file = $error['file'];
        $error_line = $error['line'];
        print_r("<div style='background: #f8d7da; margin-top: 10px; border: solid black 1px; padding: 20px; border-radius: 5px;'>
                            <span style='font-weight: bold'> FATAL ERRROR ENCOUNTERED ON (FILE: $error_file, LINE $error_line):</span>
                            <p> $error_msg </p><br />
                       </div>");
    }
}

register_shutdown_function('shutDownFunction');

/**
 * First, we start with "users.tsv". This file will import to two of our tables: 1. user 2. phonenumber. Mapping for these shown below:
 * Mapping (users.tsv) => (user table):
 *         email => email
 *         password => password
 *         first_name => first_name
 *         last_name => last_name
 *         nickname => nickname
 *         postal_code => postal_code
 *
 * Mapping (users.tsv) => (phonenumber table)
 *         email => email
 *         phone_number => number
 *         phone_type => number_type
 *         to_share => share_phone_number
 */

$user_start_time = time();
print_r("<br /> ----------------- START OF USER + PHONENUMBER IMPORT TEST ----------------- <br />");
//Start the import process. First, we need to locate and read the users.tsv file.
$users_file = fopen("users.tsv", "r");

if($users_file){
    $mysqli_db_object = gameswap_get_mysqli_object();
    //File found.
    print_r("<br />&nbsp;&nbsp;&nbsp;Success! File 'users.tsv' found! Starting the import process now. <br />");

    //Let's loop through each line.
    $users_row_count = 0;
    $users_inserted_count = 0;
    $phone_number_inserted_count = 0;
    while(($users_file_line = fgets($users_file)) != false){
        //We avoid the first row since it's the header.
        if($users_row_count != 0){
            //print_r("$line <br />");
            //Now, let's get each data from the line. The delimiter to use is the tab ("\t")
            $each_user_data_arr = explode("\t", $users_file_line);
            $each_user_email = trim($each_user_data_arr[0]);
            $each_user_password = trim($each_user_data_arr[1]);
            $each_user_first_name = trim($each_user_data_arr[2]);
            $each_user_last_name = trim($each_user_data_arr[3]);
            $each_user_nickname = trim($each_user_data_arr[4]);
            $each_user_postalcode = trim($each_user_data_arr[5]);
            $each_user_phone_number = trim($each_user_data_arr[6]);
            $each_user_phone_number_type = trim($each_user_data_arr[7]);
            //Sanitize data before insertion.
            $each_user_email = mysqli_real_escape_string($mysqli_db_object, $each_user_email);
            $each_user_password = mysqli_real_escape_string($mysqli_db_object, $each_user_password);
            $each_user_first_name = mysqli_real_escape_string($mysqli_db_object, $each_user_first_name);
            $each_user_last_name = mysqli_real_escape_string($mysqli_db_object, $each_user_last_name);
            $each_user_nickname = mysqli_real_escape_string($mysqli_db_object, $each_user_nickname);
            $each_user_postalcode = mysqli_real_escape_string($mysqli_db_object, $each_user_postalcode);
            $each_user_phone_number = mysqli_real_escape_string($mysqli_db_object, $each_user_phone_number);
            $each_user_phone_number_type = mysqli_real_escape_string($mysqli_db_object, $each_user_phone_number_type);

            //Convert shareable phone number field to int.
            $each_user_shareable_phone_number = utf8_encode(trim($each_user_data_arr[8]));
            $each_user_shareable_phone_number = intval($each_user_shareable_phone_number);

            //Now that we have everything we need, let's create INSERT this line into the tables (user and phonenumber) as per the mapping above.

            $create_user_query = "INSERT INTO `user` (email, password, first_name, last_name, nickname, postal_code)
                                  VALUES('$each_user_email', '$each_user_password', '$each_user_first_name', '$each_user_last_name', '$each_user_nickname', '$each_user_postalcode')";

            $create_user_res = mysqli_query($mysqli_db_object, $create_user_query);

            if(!$create_user_res){
                echo ("<br />&nbsp;&nbsp;&nbsp;ERROR INSERTING USER FROM users.tsv -----> ROW $users_row_count. DataLine: $each_user_data_arr <br />");
                echo ("<br />MYSQL ERROR: " . mysqli_error($mysqli_db_object));
                die();
            }else{
                //Insert successful.
                $users_inserted_count++;
            }

            /*
            if ($create_user_res  == False) {
                print_r("<br />&nbsp;&nbsp;&nbsp;ERROR INSERTING USER FROM users.tsv -----> ROW $users_row_count. DataLine: $each_user_data_arr <br />&nbsp;&nbsp;Killing Script !");
                die();
            }else{
                //Insert successful.
                $users_inserted_count++;
            }
            */

            //Now, we also insert into phonenumber table. (ONLY IF THE PHONE NUMBER EXISTS).
            if($each_user_phone_number != ""){

                //Format phone number into XXX-XXX-XXXX format before storing.
                $each_user_phone_number_first_three = substr($each_user_phone_number, 0 ,3);
                $each_user_phone_number_second_three = substr($each_user_phone_number, 3 ,3);
                $each_user_phone_number_last_four = substr($each_user_phone_number, 6, 4);
                $each_user_phone_number_formatted = "$each_user_phone_number_first_three-$each_user_phone_number_second_three-$each_user_phone_number_last_four";
                $create_phonenumber_query = "INSERT INTO phonenumber (email, number, number_type, share_phone_number) 
                                         VALUES('$each_user_email', '$each_user_phone_number_formatted', '$each_user_phone_number_type', $each_user_shareable_phone_number)";

                $create_phonenumber_res = mysqli_query($mysqli_db_object, $create_phonenumber_query);

                if($create_phonenumber_res == false){
                    print_r("<br />&nbsp;&nbsp;&nbsp;ERROR INSERTING Phonenumber FROM users.tsv -----> ROW $users_row_count. DataLine: $each_user_data_arr <br />&nbsp;&nbsp;Killing Script !");
                    die();
                }else{
                    //Insert successful.
                    $phone_number_inserted_count++;
                }

            }
        }
        $users_row_count++;
    }

        //This means we have finished inserting all the lines into the table.
        print_r("<br />&nbsp;&nbsp;&nbsp;Success! Inserted $users_inserted_count records into the user table.");
        print_r("<br />&nbsp;&nbsp;&nbsp;Success! Inserted $phone_number_inserted_count records into the phonenumber table.");
        $all_table_arr['user'] = $users_inserted_count;
        $all_table_arr['phonenumber'] = $phone_number_inserted_count;

}else{
    //File not found. Display error message.
    print_r("<br />&nbsp;&nbsp;&nbsp;ERROR! FILE 'users.tsv' NOT FOUND!! Killing Script!<br />");
    die();
}

$user_end_time = time();
$user_import_duration = $user_end_time - $user_start_time;
$user_import_duration_formatted = date('H:i:s', $user_import_duration);
print_r("<br /><br /> ----------------- END OF USER + PHONENUMBER IMPORT [ Duration (hr:min:sec) : $user_import_duration_formatted ] ----------------- <br /><br />");

/**
 * Next, let's insert data into our platform table.
 * This will be manual data insertion, and not from a file.
 */

$platform_import_time_start = time();
print_r("<br /><br /> ----------------- START OF PALTFORM IMPORT ----------------- <br />");

$mysqli_db_object = gameswap_get_mysqli_object();

$platform_query = "INSERT INTO platform (platform_name) VALUES ('Nintendo'), ('PlayStation'), ('Xbox')";
$platform_res = mysqli_query($mysqli_db_object, $platform_query);

if($platform_res == false){
    print_r("<br />&nbsp;&nbsp;&nbsp;ERROR INSERTING INTO platform table <br />&nbsp;&nbsp;Killing Script !");
    die();
}else{
    print_r("<br />&nbsp;&nbsp;&nbsp;Success! Inserted 3 records into the platform table.");
    $all_table_arr['platform'] = 3;
}
$platform_import_time_end = time();
$platform_import_time_duration = $platform_import_time_end - $platform_import_time_start;
$platform_import_time_duration_formatted = date('H:i:s', $platform_import_time_duration);
print_r("<br /><br /> ----------------- END OF PALTFORM IMPORT [ Duration (hr:min:sec) : $platform_import_time_duration_formatted ] ----------------- <br /><br />");

/**
 * Next, we import data from items.tsv file into our item table.
 *
 * Mapping (items.tsv) => (item table):
 *         email => email
 *         item_no => item_no
 *         type => type
 *         title => title
 *         platform => game_platform (WILL BE DETERMINED BY IF CONDITION)
 *         media    => media
 *         platform => computer_platform (WILL BE DETERMINED BY IF CONDITION)
 *         piece_count => piece
 *         condition => condition
 *         description => description
 *
 */

$items_import_start_time = time();
print_r("<br /><br /> ----------------- START OF ITEMS IMPORT ----------------- <br />");
//Start the import process. First, we need to locate and read the users.tsv file.
$items_file = fopen("items.tsv", "r");

if($items_file){
    $mysqli_db_object = gameswap_get_mysqli_object();
    //File found.
    print_r("<br />&nbsp;&nbsp;&nbsp;Success! File 'items.tsv' found! Starting the import process now. <br />");
    //$create_item_query = "INSERT INTO item (email, item_no, `type`, title, game_platform, media, computer_platform, piece, `condition`, description)
    //                                VALUES";

    //Let's loop through each line and insert data.
    $items_row_count = 0;
    $items_inserted_count = 0;

    //Special Rows:
    $special_rows_arr = array(1838, 2530, 2564, 4520, 4733, 5992, 6297, 7383);

    while(($items_file_line = fgets($items_file)) != false){
        //Explode by tab delimiter to separate each data into array.
        //We avoid the first row since it's the header.
        if($items_row_count != 0){
            //Now, let's get each data from the line. The delimiter to use is the tab ("\t")
            $each_item_data_arr = explode("\t", $items_file_line);
            //if($items_row_count == 5922 || $items_row_count == 5906){
            //    print_r("<br />");
            //    print_r($each_item_data_arr);
            //}
            //print_r("<br />");
            //print_r($each_item_data_arr);
            //Collect data and trim.
            $each_item_no = trim($each_item_data_arr[0]);
            $each_item_title = trim($each_item_data_arr[1]);
            //There seem to be some special entries with double quotes in the title. Let's handle that.
            if($each_item_data_arr[2] == "\""){
                //print_r("<br /> ************* UNIQUE ITEM **************** <br />");
                //This is one of such item.
                //We'll include this item by removing its double quotes.
                $each_item_title .= $each_item_data_arr[2];
                //Now, trim double quotes.
                //$each_item_title = trim($each_item_title, "\"");
                $each_item_condition = trim($each_item_data_arr[3]);
                $each_item_description = trim($each_item_data_arr[4]);
                $each_item_email = trim($each_item_data_arr[5]);
                $each_item_type = trim($each_item_data_arr[6]);
                $each_item_piece_count = trim($each_item_data_arr[7]);
                $each_item_platform = trim($each_item_data_arr[8]);
                $each_item_media = trim($each_item_data_arr[9]);
            }else{
                $each_item_condition = trim($each_item_data_arr[2]);
                $each_item_description = trim($each_item_data_arr[3]);
                //Special case: Row: 1838, 2530, 2564, 4520, 4733, 5992, 6297, 7383
                if(in_array($items_row_count, $special_rows_arr)){
                    $each_item_description .= $each_item_data_arr[4];
                    //Trim double quotes.
                    //$each_item_description = trim($each_item_description, "\"");
                    $each_item_email = trim($each_item_data_arr[5]);
                    $each_item_type = trim($each_item_data_arr[6]);
                    $each_item_piece_count = trim($each_item_data_arr[7]);
                    $each_item_platform = trim($each_item_data_arr[8]);
                    $each_item_media = trim($each_item_data_arr[9]);
                }else{
                    $each_item_email = trim($each_item_data_arr[4]);
                    $each_item_type = trim($each_item_data_arr[5]);
                    $each_item_piece_count = trim($each_item_data_arr[6]);
                    $each_item_platform = trim($each_item_data_arr[7]);
                    $each_item_media = trim($each_item_data_arr[8]);
                }

            }

            $each_item_game_platform = "";
            $each_item_computer_platform = "";

            //Sanitize data before insertion.
            $each_item_no = mysqli_real_escape_string($mysqli_db_object, $each_item_no);
            $each_item_title = mysqli_real_escape_string($mysqli_db_object, $each_item_title);
            $each_item_condition = mysqli_real_escape_string($mysqli_db_object, $each_item_condition);
            $each_item_description = mysqli_real_escape_string($mysqli_db_object, $each_item_description);
            $each_item_email = mysqli_real_escape_string($mysqli_db_object, $each_item_email);
            $each_item_type = mysqli_real_escape_string($mysqli_db_object, $each_item_type);
            $each_item_piece_count = mysqli_real_escape_string($mysqli_db_object, $each_item_piece_count);
            $each_item_platform = mysqli_real_escape_string($mysqli_db_object, $each_item_platform);
            $each_item_media = mysqli_real_escape_string($mysqli_db_object, $each_item_media);


            //Now, our insertion into the item table depends on whether the value of platform field from the 'items.tsv' file should go to "computer_platform" field or "game_platform" field in our item table.
            //If item type = "Computer Game" --> populate it in computer_platform
            //If item type = "Video Game" --> populate it in game_platform
            if($each_item_type == "Video Game"){

                $each_item_game_platform = $each_item_platform;


            }elseif($each_item_type == "Computer Game"){
                $each_item_computer_platform = $each_item_platform;
            }
            //Now, let's insert the data from this line into our item table.


            $create_item_query = "INSERT INTO item (email, item_no, `type`, title, game_platform, media, computer_platform, piece, `condition`, description)
                                    VALUES('$each_item_email', '$each_item_no', '$each_item_type', '$each_item_title'";


            //Game Platform
            if($each_item_game_platform == ""){
                $create_item_query .= ", NULL";
            }else{
                $create_item_query .= ", '$each_item_game_platform'";
            }

            //Media
            if($each_item_media == ""){
                $create_item_query .= ", NULL";
            }else{
                $create_item_query .= ", '$each_item_media'";
            }

            //Computer Platform
            if($each_item_computer_platform == ""){
                $create_item_query .= ", NULL";
            }else{
                $create_item_query .= ", '$each_item_computer_platform'";
            }

            //Piece Count
            if($each_item_piece_count == ""){
                $create_item_query .= ", NULL";
            }else{
                $create_item_query .= ", '$each_item_piece_count'";
            }

            //Item Condition.
            if($each_item_condition == ""){
                $create_item_query .= ", NULL";
            }else{
                $create_item_query .= ", '$each_item_condition'";
            }

            //Description
            if($each_item_description == ""){
                $create_item_query .= ", NULL";
            }else{
                $create_item_query .= ", '$each_item_description'";
            }

            $create_item_query .=")";

            $create_item_res = mysqli_query($mysqli_db_object, $create_item_query);

            if($create_item_res == False){
                array_push($error_msg, "INSERT ERROR: items Row no: " . $items_row_count."<br>". __FILE__ ." line:". __LINE__ );
                print_r("<br />&nbsp;&nbsp;&nbsp;ERROR INSERTING Item FROM items.tsv -----> ROW $items_row_count. DataLine: $each_item_data_arr <br />&nbsp;&nbsp;Killing Script !");
                die();
            }else{
                //Insert successful.
                $items_inserted_count++;
            }

        }
        $items_row_count++;
    }
    //This means we finished inserting all the records into the item table.
    print_r("<br />&nbsp;&nbsp;&nbsp;Success! Inserted $items_inserted_count records into the item table.");
    $all_table_arr['item'] = $items_inserted_count;

}else{
    //Error opening file. Killing script!
    print_r("<br />&nbsp;&nbsp;&nbsp;ERROR! FILE 'items.tsv' NOT FOUND!! Killing Script!<br />");
    die();

}
$items_import_end_time = time();
$items_import_time_duration = $items_import_end_time - $items_import_start_time;
$items_import_time_duration_formatted = date('H:i:s', $items_import_time_duration);
print_r("<br /><br /> ----------------- END OF ITEMS IMPORT [ Duration (hr:min:sec) : $items_import_time_duration_formatted ] ----------------- <br /><br />");
/**
 * Now, we import data from swaps.tsv file into our swap table.
 *
 * Mapping (swaps.tsv) => (swap table):
 *         NONE => counterparty_email (WE NEED TO QUERY THE item TABLE TO GET IT using desired_item_id)
 *         NONE => proposer_email (WE NEED TO QUERY THE item TABLE TO GET IT using proposer_item_id)
 *         item_desired => desired_item_id
 *         item_proposed => proposer_item_id
 *         date_reviewed => accepted_rejected_date
 *         date_proposed => proposal_date
 *         accepted => swap_status (WE NEED TO CALCULATE IT BASED ON THE accepted value: If 1 --> Accepted, If 0 --> Rejected, If "" --> Pending
 *         proposer_rate => swap_proposer_rating
 *         counterparty_rate => swap_counterparty_rating
 *
 */

$swaps_import_start_time = time();
print_r("<br /><br /> ----------------- START OF SWAPS IMPORT ----------------- <br />");
//Start the import process. First, we need to locate and read the users.tsv file.
$swaps_file = fopen("swaps.tsv", "r");

if($swaps_file) {
    $mysqli_db_object = gameswap_get_mysqli_object();

    //File found.
    print_r("<br />&nbsp;&nbsp;&nbsp;Success! FILE 'swaps.tsv' found! Starting the import process now. <br />");

    //Let's loop through each line and insert data.
    $swaps_row_count = 0;
    $swaps_inserted_count = 0;

    while(($swaps_file_line = fgets($swaps_file)) != false){
        //We avoid the first row since it's the header.
        if($swaps_row_count != 0){
            //Now, let's get each data from the line. The delimiter to use is the tab ("\t")
            $each_swap_data_arr = explode("\t", $swaps_file_line);
            //Collect data.
            //print_r("<br />");
            //print_r($each_swap_data_arr);
            $each_swap_proposed_item_id = trim($each_swap_data_arr[0]);
            //Get the proposed item owner's email.
            //$each_swap_proposed_item_email = gameswap_get_user_id_from_item_id($each_swap_proposed_item_id);
            $each_swap_proposed_item_email_query = "SELECT item.email AS ITEM_OWNER_ID FROM item WHERE item_no ='$each_swap_proposed_item_id'";
            $each_swap_proposed_item_email_res = mysqli_query($mysqli_db_object, $each_swap_proposed_item_email_query);
            $each_swap_proposed_item_email_res_count = mysqli_num_rows($each_swap_proposed_item_email_res);
            if(!empty($each_swap_proposed_item_email_res) && ($each_swap_proposed_item_email_res_count > 0)) {
                $proposed_item_row = mysqli_fetch_array($each_swap_proposed_item_email_res, MYSQLI_ASSOC);
                $each_swap_proposed_item_email = $proposed_item_row['ITEM_OWNER_ID'];
            }
            $each_swap_desired_item_id =  trim($each_swap_data_arr[1]);
            //Get the desired item owner's email.
            //$each_swap_desired_item_email = gameswap_get_user_id_from_item_id($each_swap_desired_item_id);
            $each_swap_desired_item_email_query = "SELECT item.email AS ITEM_OWNER_ID FROM item WHERE item_no ='$each_swap_desired_item_id'";
            $each_swap_desired_item_email_res = mysqli_query($mysqli_db_object, $each_swap_desired_item_email_query);
            $each_swap_desired_item_email_res_count = mysqli_num_rows($each_swap_desired_item_email_res);
            if(!empty($each_swap_desired_item_email_res) && ($each_swap_desired_item_email_res_count > 0)) {
                $desired_item_row = mysqli_fetch_array($each_swap_desired_item_email_res, MYSQLI_ASSOC);
                $each_swap_desired_item_email = $desired_item_row['ITEM_OWNER_ID'];
            }
            $each_swap_proposed_date =  trim($each_swap_data_arr[2]);
            $each_swap_reviewed_date =  trim($each_swap_data_arr[3]);
            $each_swap_accepted_flag =  trim($each_swap_data_arr[4]);
            $each_swap_status = "Pending";
            /**
             * Now, the value for "swap_status" field in our swap table depends on the value of "$each_swap_accepted_flag".
             * If it is 1, swap_status = "Accepted"
             * If it is 0, swap_status = "Rejected"
             * If it is "", swap_status = "Pending"
             */

            switch ($each_swap_accepted_flag){
                case 1:
                    //Accepted.
                    $each_swap_status = "Accepted";
                    break;

                case 0:
                    //Rejected
                    $each_swap_status = "Rejected";
                    break;

                default:
                    //Pending
                    $each_swap_status = "Pending";
            }

            $each_swap_proposer_rating =  trim($each_swap_data_arr[5]);
            $each_swap_counterparty_rating =  trim($each_swap_data_arr[6]);

            //Now we have everything we need.

            //Sanitize the data.
            $each_swap_proposed_item_id = mysqli_real_escape_string($mysqli_db_object, $each_swap_proposed_item_id);
            $each_swap_proposed_item_email = mysqli_real_escape_string($mysqli_db_object, $each_swap_proposed_item_email);
            $each_swap_desired_item_id =  mysqli_real_escape_string($mysqli_db_object, $each_swap_desired_item_id);
            $each_swap_desired_item_email = mysqli_real_escape_string($mysqli_db_object, $each_swap_desired_item_email);
            $each_swap_proposed_date =  mysqli_real_escape_string($mysqli_db_object, $each_swap_proposed_date);
            $each_swap_reviewed_date =  mysqli_real_escape_string($mysqli_db_object, $each_swap_reviewed_date);
            $each_swap_accepted_flag =  mysqli_real_escape_string($mysqli_db_object, $each_swap_accepted_flag);
            $each_swap_proposer_rating =  mysqli_real_escape_string($mysqli_db_object, $each_swap_proposer_rating);
            $each_swap_counterparty_rating =  mysqli_real_escape_string($mysqli_db_object, $each_swap_counterparty_rating);

            //Ready for insertion.

            $swap_insert_query = "INSERT INTO swap (counterparty_email, proposer_email, desired_item_id, proposer_item_id, accepted_rejected_date, proposal_date, swap_status, swap_proposer_rating, swap_counterparty_rating)
                                    VALUES ('$each_swap_desired_item_email', '$each_swap_proposed_item_email', '$each_swap_desired_item_id', '$each_swap_proposed_item_id'";

            //Handle empty values as NULLs.

            //Accepted-Rejected Date
            if($each_swap_reviewed_date == ""){
                $swap_insert_query .= ", NULL";
            }else{
                $swap_insert_query .= ", '$each_swap_reviewed_date'";
            }

            //Proposed Date -- Cannot be null.
            $swap_insert_query .= ", '$each_swap_proposed_date'";

            //Swap Status -- Cannot be null.
            $swap_insert_query .= ", '$each_swap_status'";

            //Swap Proposer Rating
            if($each_swap_proposer_rating == ""){
                $swap_insert_query .= ", NULL";
            }else{
                $swap_insert_query .= ", '$each_swap_proposer_rating'";
            }

            //Swap Counterparty Rating
            if($each_swap_counterparty_rating == ""){
                $swap_insert_query .= ", NULL";
            }else{
                $swap_insert_query .= ", '$each_swap_counterparty_rating'";
            }

            $swap_insert_query .= ")";

            //We have the query ready.
            //print_r($swap_insert_query);
            $swap_insert_res = mysqli_query($mysqli_db_object, $swap_insert_query);

            if($swap_insert_res == FALSE){
                //Error.
                array_push($error_msg, "INSERT ERROR: swaps Row no: " . $swaps_row_count."<br>". __FILE__ ." line:". __LINE__ );
                print_r("<br />&nbsp;&nbsp;&nbsp;ERROR INSERTING swap FROM swaps.tsv -----> ROW $swaps_row_count. DataLine: $each_swap_data_arr <br />&nbsp;&nbsp;Killing Script !");
                die();
            }else{
                //Insertion successful
                $swaps_inserted_count++;
            }
        }
        $swaps_row_count++;
    }
    //This means we finished inserting all the records into the swap table.
    print_r("<br />&nbsp;&nbsp;&nbsp;Success! Inserted $swaps_inserted_count records into the swap table.");
    $all_table_arr['swap'] = $swaps_inserted_count;
}else{
    //Error opening file. Killing script!
    print_r("<br />&nbsp;&nbsp;&nbsp;ERROR! FILE 'swaps.tsv' NOT FOUND!! Killing Script!<br />");
    die();

}
$swaps_import_end_time = time();
$swaps_import_time_duration = $swaps_import_end_time - $swaps_import_start_time;
$swaps_import_time_duration_formatted = date('H:i:s', $swaps_import_time_duration);
print_r("<br /><br /> ----------------- END OF SWAPS IMPORT [ Duration (hr:min:sec) : $swaps_import_time_duration_formatted ] ----------------- <br /><br />");

$end_time = time();
$time_elapsed = $end_time - $start_time;
$time_elapsed_formatted = date('H:i:s', $time_elapsed);

/**
 * Display Import Totals for each table
 */
$import_summary = "<br /><hr />
                   &nbsp;&nbsp;&nbsp; Data IMPORT COMPLETE <br />";

foreach($all_table_arr as $table_name => $insertion_count){
    $import_summary .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; $table_name ==> $insertion_count Records Added! <br />";
}

$import_summary .= "Total Processing Time: $time_elapsed_formatted <br />";

print_r($import_summary);
