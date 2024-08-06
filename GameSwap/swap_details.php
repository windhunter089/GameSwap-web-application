<?php
include('lib/common.php');
include('lib/bootstrap_header.php');
include('lib/gameswap_functions.php');

// validate user is logged in
gameswap_validate_user_login();
$userId = gameswap_get_current_user_id();
$banner_container = gameswap_generate_banner_container();

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $myRole = $_GET['myRole'];
    if($myRole == "CounterProposer"){
        $myRole = "CounterParty";
    }
    else{
        $myRole = "Proposer";
    }
    $desiredItemId = $_GET['desiredItemId'];
    $proposerItemId = $_GET['proposerItemId'];
    $status = $_GET['status'];

    // get swap details
    $query = "SELECT swap.proposal_date, swap.accepted_rejected_date, swap.swap_status FROM 
    swap WHERE swap.desired_item_id='$desiredItemId' AND 
    swap.proposer_item_id='$proposerItemId'";
    $result = mysqli_query($db, $query);
    include('lib/show_queries.php');
    $row = mysqli_fetch_array($result, MYSQLI_ASSOC);

    $proposal_date = $row['proposal_date'];
    $accepted_rejected_date = $row['accepted_rejected_date'];
    $swap_status = $row['swap_status'];

    // get proposed item details
    $query = "SELECT item.item_no,item.title,item.TYPE,item.`condition`,item.description FROM 
    item WHERE item.item_no='$proposerItemId'";
    $result = mysqli_query($db, $query);
    include('lib/show_queries.php');
    $row = mysqli_fetch_array($result, MYSQLI_ASSOC);

    $proposedItemTitle = $row['title'];
    $proposedItemType = $row['TYPE'];
    $proposedItemCondition = $row['condition'];
    $proposedItemDesc = $row['description'];

    // get desired item details
    $query = "SELECT item.item_no,item.title,item.TYPE,item.`condition`,item.description FROM 
    item WHERE item.item_no='$desiredItemId'";
    $result = mysqli_query($db, $query);
    include('lib/show_queries.php');
    $row = mysqli_fetch_array($result, MYSQLI_ASSOC);

    $desiredItemTitle = $row['title'];
    $desiredItemType = $row['TYPE'];
    $desiredItemCondition = $row['condition'];
    $desiredItemDesc = $row['description'];

    // get user details
    if ($myRole == "Counterparty") {
        $query = "SELECT `user`.nickname FROM `user` INNER JOIN swap ON swap.proposer_email= 
    `user`.email WHERE swap.counterparty_email = '$userId' AND 
    swap.desired_item_id='$desiredItemId' AND 
    swap.proposer_item_id='$proposerItemId'";
        $result = mysqli_query($db, $query);
        include('lib/show_queries.php');
        $row = mysqli_fetch_array($result, MYSQLI_ASSOC);

        $nick = $row['nickname'];
    } else {
        $query = "SELECT `user`.nickname FROM `user` INNER JOIN swap ON 
    swap.counterparty_email= `user`.email WHERE swap.proposer_email = '$userId' AND 
    swap.desired_item_id='$desiredItemId' AND swap.proposer_item_id='$proposerItemId'";
        $result = mysqli_query($db, $query);
        include('lib/show_queries.php');
        $row = mysqli_fetch_array($result, MYSQLI_ASSOC);

        $nick = $row['nickname'];
    }

    // get user emails
    $query = "SELECT counterparty_email, proposer_email FROM swap WHERE 
    swap.desired_item_id='$desiredItemId' AND 
    swap.proposer_item_id='$proposerItemId'";
    $result = mysqli_query($db, $query);
    include('lib/show_queries.php');
    $row = mysqli_fetch_array($result, MYSQLI_ASSOC);

    $counterparty_email = $row['counterparty_email'];
    $proposer_email = $row['proposer_email'];

    // get distance beween users
    $distance = gameswap_get_distance_between_users_by_user_id($counterparty_email, $proposer_email);

    // display other user info
    if ($status == "Accepted") {
        if ($myRole == "Counterparty") {
            $otherUserId = $proposer_email;
        } else {
            $otherUserId = $counterparty_email;
        }

        $query = "SELECT `user`.first_name, `user`.email FROM `user` WHERE 
    `user`.email='$otherUserId'";
        $result = mysqli_query($db, $query);
        include('lib/show_queries.php');
        $row = mysqli_fetch_array($result, MYSQLI_ASSOC);

        $otherUserName = $row['first_name'];
        $otherUserEmail = $row['email'];

        $query = "SELECT phonenumber.number, phonenumber.number_type FROM `phonenumber` 
    WHERE phonenumber.email='$otherUserId' AND share_phone_number=1";
        $result = mysqli_query($db, $query);
        include('lib/show_queries.php');
        $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
        $count = mysqli_num_rows($result);

        if ($count > 0) {
            $otherUserNumber = $row['number'];
            $otherUserNumberType = $row['number_type'];
        }
    }

    // get more swap details
    if ($myRole == "Counterparty") {
        $query = "SELECT swap.swap_proposer_rating FROM swap WHERE 
    swap.desired_item_id='$desiredItemId' AND 
    swap.proposer_item_id='$proposerItemId'";
        $result = mysqli_query($db, $query);
        include('lib/show_queries.php');
        $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
        $ratingLeft = $row['swap_proposer_rating'];
    } else {
        $query = "SELECT swap.swap_counterparty_rating FROM swap WHERE 
    swap.desired_item_id='$desiredItemId' AND 
    swap.proposer_item_id='$proposerItemId'";
        $result = mysqli_query($db, $query);
        include('lib/show_queries.php');
        $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
        $ratingLeft = $row['swap_counterparty_rating'];
    }
}
?>
<?php include("lib/header.php"); ?>
<title>GTOnline Profile</title>
</head>

<body>
<div><?php print $banner_container ?></div>
<div id="main_container">
    <div class="center_content">
        <div class="swap_details">
            <div class="swapDetailsBoxBox">
                <div class="swapDetailsBox">
                    <div class="title_name">
                        Swap Details
                    </div>
                    <ul>
                        <li>Proposal Date: <?php print $proposal_date ?></li>
                        <li>Accepted/Rejected: <?php print $accepted_rejected_date ?></li>
                        <li>Status: <?php print $swap_status ?></li>
                        <li>My role: <?php print $myRole ?></li>
                        <li>Rating left: <?php print $ratingLeft ?></li>
                    </ul>
                </div>
                <div class="swapDetailsBox">
                    <div class="title_name">
                        User Details
                    </div>
                    <ul>
                        <li>Nickname: <?php print $nick ?></li>
                        <li>Distance: <?php print $distance ?></li>
                        <li>Name: <?php print $otherUserName ?></li>
                        <li>Email: <?php print $otherUserEmail ?></li>
                        <li>Phone: <?php print $otherUserNumber; $otherUserNumberType ?></li>
                    </ul>
                </div>
                <div class="swapDetailsBox">
                    <div class="title_name">
                        Proposed Item
                    </div>
                    <ul>
                        <li>Item #: <?php print $proposerItemId ?></li>
                        <li>Title: <?php print $proposedItemTitle ?></li>
                        <li>Game Type: <?php print $proposedItemType ?></li>
                        <li>Condition: <?php print $proposedItemCondition ?></li>
                        <li>Description: <?php print $proposedItemDesc ?></li>
                    </ul>
                </div>
                <div class="swapDetailsBox">
                    <div class="title_name">
                        Desired Item
                    </div>
                    <ul>
                        <li>Item #: <?php print $desiredItemId ?></li>
                        <li>Title: <?php print $desiredItemTitle ?></li>
                        <li>Game Type: <?php print $desiredItemType ?></li>
                        <li>Condition: <?php print $desiredItemCondition ?></li>
                        <li>Description: <?php print $desiredItemDesc ?></li>
                    </ul>
                </div>
            </div>
        </div>

        <?php include("lib/error.php"); ?>

        <div class="clear"></div>
    </div>

</div>
</body>
