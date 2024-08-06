<?php
include('lib/common.php');
include('lib/bootstrap_header.php');
include('lib/gameswap_functions.php');

//Validate User log in
gameswap_validate_user_login();

//Get current logged in user's id.
$current_user_id = gameswap_get_current_user_id();

$current_user_name = gameswap_get_user_name_from_id($current_user_id, TRUE);

//First, query to get all the distinct item types and their count for the current user.
//$current_user_item_types_count_query = "SELECT type AS GAME_TYPE, COUNT(*) AS GAME_COUNT FROM item WHERE email = '$current_user_id' GROUP BY type";
$current_user_item_types_count_query = "SELECT type AS GAME_TYPE, COUNT(*) AS GAME_COUNT FROM item WHERE item_no NOT IN (
                                            SELECT desired_item_id FROM swap WHERE swap_status IN ('Accepted', 'Pending') UNION 
                                            SELECT proposer_item_id FROM swap WHERE swap_status IN ('Accepted', 'Pending') ORDER BY desired_item_id ASC)
                                        AND email = '$current_user_id' GROUP BY type";


$current_user_item_types_count_res = mysqli_query($db, $current_user_item_types_count_query);

$user_item_types_arr = array(
        "board_games" => 0,
        "card_games" => 0,
        "computer_games" => 0,
        "jigsaw_puzzles" => 0,
        "video_games" => 0,

);


$user_item_count_table = "<table class = 'table table-responsive-sm align-middle mb-0 bg-white text-center border-bottom'>
                            <thead class='bg-light'>
                                <tr>
                                    <th scope='col'>Board games</th>
                                    <th scope='col'>Card games</th>
                                    <th scope='col'>Computer games</th>
                                    <th scope='col'>Jigsaw puzzles</th>    
                                    <th scope='col'>Video games</th>
                                    <th scope='col'>Total</th>
                                </tr>
                            </thead>
                            <tr>
                   ";


while($row = mysqli_fetch_array($current_user_item_types_count_res, MYSQLI_ASSOC)){
    $row_item_type = strtolower($row['GAME_TYPE']);
    $row_item_count = $row['GAME_COUNT'];
    switch($row_item_type) {
        case 'board game':
            $user_item_types_arr["board_games"] = $row_item_count;
            break;
        case 'card game':
            $user_item_types_arr["card_games"] = $row_item_count;
            break;
        case 'computer game':
            $user_item_types_arr["computer_games"] = $row_item_count;
            break;
        case 'puzzle':
            $user_item_types_arr["jigsaw_puzzles"] = $row_item_count;
            break;
        case 'video game':
            $user_item_types_arr["video_games"] = $row_item_count;
            break;
    }

}


$total_item_count = 0;
//Now, let's create a table for each entry in the array.
foreach($user_item_types_arr as $item_type => $item_count){
    $total_item_count += $item_count;
    $user_item_count_table .= "<td>$item_count</td>";

}
//Now, add the total count in the table.
$user_item_count_table .= "<td>$total_item_count</td>
                           </tr>
                           </table>";

//If the total number of items is 0, simply display a message on the table.
if($total_item_count == 0){
    $user_items_table = "<div id ='gameswap-my-items-no-items-container'>
                            <p>No Items available at this time! <i class='bi bi-clipboard-x'></i></p>
                          </div>";
}else{
    //Now, let's create a table to list user's each available item and it's details.
    $user_items_table = "<table class = 'table table-responsive-sm align-middle mb-0 bg-white border-bottom'>
                        <thead class='bg-light'>
                            <tr>
                                <th scope='col'>Item #</th>
                                <th scope='col'>Game Type</th>
                                <th scope='col'>Title</th>
                                <th scope='col'>Condition</th>
                                <th scope='col'>Description</th>
                                <th scope='col'></th>
                            </tr>
                        </thead>
                    ";


    /**
     * Query to get all available items for the current logged in user.
     */
//$current_user_items_query = "SELECT item_no, `type`, title, `condition`, description FROM item WHERE email = '$current_user_id' ORDER BY item_no";
    $current_user_items_query = "SELECT item_no, `type`, title, `condition`, description FROM item WHERE item_no NOT IN (
                                SELECT desired_item_id FROM swap WHERE swap_status IN ('Accepted', 'Pending') UNION 
                                SELECT proposer_item_id FROM swap WHERE swap_status IN ('Accepted', 'Pending') ORDER BY desired_item_id ASC) AND email = '$current_user_id' ORDER BY item_no ASC";
    $current_user_items_res = mysqli_query($db, $current_user_items_query);

    while($item_row = mysqli_fetch_array($current_user_items_res, MYSQLI_ASSOC)){
        $item_no = $item_row['item_no'];
        $item_type = $item_row['type'];
        $item_title = $item_row['title'];
        $item_condition = $item_row['condition'];
        $item_desc = $item_row['description'];
        //We need to truncate item description if it exceeds 100 characters.
        $item_desc_char_count = mb_strlen($item_desc);
        //If greater than 100, add "..." after 99 characters.
        if($item_desc_char_count > 100){
            $item_desc = substr($item_desc, 0, 99);
            $item_desc .= "...";
        }

        $item_href = "<a href = '/cs6400-2022-01-Team064/GameSwap/view_item.php?item_id=$item_no'>Detail</a>";
        //Add each of these fields into the table.
        $user_items_table .= "<tr>
                            <th scope='row'>$item_no</th>
                            <td>$item_type</td>
                            <td>$item_title</td>
                            <td>$item_condition</td>
                            <td>$item_desc</td>
                            <td>$item_href</td>
                          </tr>
                          ";
    }

    $user_items_table .= "</table>";
}




$banner_container = gameswap_generate_banner_container();

$page_content = "<head>
                    <title>View My Items</title>
                 </head>
                 <body>
                       
                       <div class='container-fluid gameswap-body-container'>   
                            $banner_container
                           <div class='gameswap-my-items-container'>
                                <h3 class='gameswap-my-items-container-header'>Item Counts</h3>
                                <hr class='gameswap-my-items-container-hr'/>
                                <div>
                                    $user_item_count_table
                                </div>
                           </div>
                           <div class='gameswap-my-items-container'>
                               <h3 class='gameswap-my-items-container-header'>My Items</h3>
                               <hr class='gameswap-my-items-container-hr'/>
                               <div>
                                    $user_items_table
                               </div>
                           </div>
                    </div>
                 </body>";

?>

<html>
<?php
    print($page_content);
?>
</html>
