<?php
include('lib/common.php');
include('lib/bootstrap_header.php');
include('lib/gameswap_functions.php');

// validate user is logged in
gameswap_validate_user_login();
$userId = gameswap_get_current_user_id();
$banner_container = gameswap_generate_banner_container();
//$userId="user2@gatech.edu";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $role = $_POST['myRole'];
    $rating = $_POST['rating'];
    $proposer = $_POST['proposerEmail'];
    $counterParty = $_POST['counterPartyEmail'];
    $proposedItem = $_POST['proposedItem'];
    $desiredItem = $_POST['desiredItem'];

//    print($role . $rating . $proposer . $counterParty . $proposedItem . $desiredItem);
    gameswap_update_rate_swaps($rating, $counterParty, $proposer, $desiredItem, $proposedItem, $role);
}


$result = gameswap_get_unrated_swaps($userId);


$html = "";
while($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
        $proposerEmail = $row['proposer_email'];
        $counterPartyEmail = $row['counterparty_email'];


        $date = $row['accepted_rejected_date'];
        if($userId == $row['proposer_email']){
            $myRole = "Proposer";
            $otherUser = $row['counterparty_email'];
        }
        else{
            $myRole = "Counterparty";
            $otherUser = $row['proposer_email'];
        }

        $proposedItem = $row['proposer_item_id'];
        $item_name = gameswap_get_item_title_from_item_id($proposedItem);

        $desiredItem = $row['desired_item_id'];
        $ditem_name = gameswap_get_item_title_from_item_id($desiredItem);

        $otherUser_nick = gameswap_get_nick($otherUser);


        $htmlRow = "<tr> 
            <td>$date</td>     
            <td>$myRole</td>            
            <td>$item_name</td>            
            <td>$ditem_name</td>           
            <td>$otherUser_nick</td>           
            <td>
            <form method='post'>
                <input type='hidden' id='myRole' name='myRole' value=$myRole>
                <input type='hidden' id='proposedItem' name='proposedItem' value=$proposedItem>
                <input type='hidden' id='desiredItem' name='desiredItem' value=$desiredItem>
                <input type='hidden' id='proposerEmail' name='proposerEmail' value=$proposerEmail>
                <input type='hidden' id='counterPartyEmail' name='counterPartyEmail' value=$counterPartyEmail>
                <select name='rating' onchange='this.form.submit();'>
                    <option value=0>0</option>
                    <option value=1>1</option>
                    <option value=2>2</option>
                    <option value=3>3</option>
                    <option value=4>4</option>
                    <option value=5>5</option>
                </select>
            </form>
            </td>
         </tr>";
        $html.=$htmlRow;

}
?>
<?php include('lib/header.php');?>
<title>GTOnline Profile</title>
</head>

<body>
<div><?php print $banner_container ?></div>
<div id="main_container">
    <div class="center_content">
        <div class="center_left">
            <table class='rateSwapsTable'>
                <tr>
                    <th>Acceptance Date</th>
                    <th>My role</th>
                    <th>Proposed Item</th>
                    <th>Desired Item</th>
                    <th>Other User</th>
                    <th>Rating</th>
                </tr>
                <?php echo $html ?>
            </table>
        </div>

        <?php include("lib/error.php"); ?>

        <div class="clear"></div>
    </div>

</div>
</body>
