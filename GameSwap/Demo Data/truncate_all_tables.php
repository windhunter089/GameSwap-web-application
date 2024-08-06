<?php
include('../lib/gameswap_functions.php');

$start_time = time();
$mysqli_db_object = gameswap_get_mysqli_object();


/**
 * First, we truncate all the tables.
 * Happens in the following order: Swap >> Item >> Platform >> PhoneNumber >> User >> PostalCode.
 *
 */

if(isset($_REQUEST['avoid_postalcode'])){

        print_r("<br> ************* START: TRUNCATE ALL TABLES (Except PostalCode) ***********************<br />");

        $all_tables_arr = array("swap", "item", "platform", "phonenumber", "`user`");

        foreach($all_tables_arr as $table_name){
            $foreign_key_check_query = "SET FOREIGN_KEY_CHECKS = 0";
            $foreign_key_check_res = mysqli_query($mysqli_db_object, $foreign_key_check_query);
            $each_table_truncate_query = "TRUNCATE TABLE $table_name";
            print_r("<br />&nbsp;&nbsp;&nbsp;$each_table_truncate_query <br />");
            $each_table_truncate_res = mysqli_query($mysqli_db_object, $each_table_truncate_query);
            if($each_table_truncate_res){
                print_r("&nbsp;&nbsp;&nbsp;SUCCESS truncating $table_name.<br />");
            }else{
                print_r("&nbsp;&nbsp;&nbsp;ERROR truncating $table_name. Killing Script! <br />");
                die();
            }
        }
        $truncate_end_time = time();
        $truncate_duration = $truncate_end_time - $start_time;
        $truncate_duration_formatted = date('H:i:s', $truncate_duration);
        print_r("<br> ************* END: TRUNCATE ALL TABLES (Except PostalCode) [ Time Taken (hr:min:sec) : $truncate_duration_formatted ]  ***********************<br />");

}else{
    print_r("<br> ************* START: TRUNCATE ALL TABLES ***********************<br />");
    $all_tables_arr = array("swap", "item", "platform", "phonenumber", "`user`", "postalcode");

    foreach($all_tables_arr as $table_name){
        $foreign_key_check_query = "SET FOREIGN_KEY_CHECKS = 0";
        $foreign_key_check_res = mysqli_query($mysqli_db_object, $foreign_key_check_query);
        $each_table_truncate_query = "TRUNCATE TABLE $table_name";
        print_r("<br />&nbsp;&nbsp;&nbsp;$each_table_truncate_query <br />");
        $each_table_truncate_res = mysqli_query($mysqli_db_object, $each_table_truncate_query);
        if($each_table_truncate_res){
            print_r("&nbsp;&nbsp;&nbsp;SUCCESS truncating $table_name.<br />");
        }else{
            print_r("&nbsp;&nbsp;&nbsp;ERROR truncating $table_name. Killing Script! <br />");
            die();
        }
    }
    $truncate_end_time = time();
    $truncate_duration = $truncate_end_time - $start_time;
    $truncate_duration_formatted = date('H:i:s', $truncate_duration);
    print_r("<br> ************* END: TRUNCATE ALL TABLES [ Time Taken (hr:min:sec) : $truncate_duration_formatted ] ***********************<br />");
}



