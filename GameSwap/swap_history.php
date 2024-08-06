
<?php
include('lib/common.php');
include('lib/bootstrap_header.php');
include('lib/gameswap_functions.php');

//Validate User log in
gameswap_validate_user_login();

//Get current logged in user's id.
$current_user_id = gameswap_get_current_user_id();

$current_user_name = gameswap_get_user_name_from_id($current_user_id, TRUE);

// get banner
$banner_container = gameswap_generate_banner_container();

// ERROR: demonstrating SQL error handlng, to fix
//First query to get swap summary for the current user.

$query = "(SELECT 'Proposer' AS MY_ROLE, (PA.P_ACCEPTED +
                    PR.P_REJECTED) AS TOTAL, PA.P_ACCEPTED AS ACCEPTED,
                    PR.P_REJECTED AS REJECTED,
                    ((PR.P_REJECTED/(PA.P_ACCEPTED + PR.P_REJECTED)) * 100) AS
                    `Rejected PCNT` FROM
                     (SELECT COUNT(*) AS P_ACCEPTED FROM swap WHERE
                    proposer_email = '$current_user_id' AND swap_status = 'Accepted') PA JOIN
                     (SELECT COUNT(*) AS P_REJECTED FROM swap WHERE
                    proposer_email = '$current_user_id' AND swap_status = 'Rejected') PR)
                    UNION
                    (SELECT 'CounterParty' AS MY_ROLE, (CPA.CP_ACCEPTED +
                    CPR.CP_REJECTED) AS TOTAL, CPA.CP_ACCEPTED AS
                    ACCEPTED, CPR.CP_REJECTED AS REJECTED,
                    ((CPR.CP_REJECTED/(CPA.CP_ACCEPTED + CPR.CP_REJECTED)) *
                    100) AS `Rejected PCNT` FROM
                    (SELECT COUNT(*) AS CP_ACCEPTED FROM swap WHERE
                    counterparty_email = '$current_user_id' AND swap_status = 'Accepted') CPA JOIN
                     (SELECT COUNT(*) AS CP_REJECTED FROM swap WHERE
                    counterparty_email = '$current_user_id' AND swap_status = 'Rejected') CPR)";

$result = mysqli_query($db, $query);
//print_r( mysqli_num_rows( $result) );
include('lib/show_queries.php');

if ( !is_bool($result) && (mysqli_num_rows($result) > 0) ) {
    $proposerRow = mysqli_fetch_array($result, MYSQLI_ASSOC);

    $counterPartyRow = mysqli_fetch_array($result, MYSQLI_ASSOC);

} else {
    array_push($error_msg,  "Query ERROR: Failed to get Swap History...<br>" . __FILE__ ." line:". __LINE__ );
}
?>
<?php include("lib/header.php"); ?>


<title>Swap history</title>
</head>
<body>
<div><?php print $banner_container ?></div>
<table class="swapHistoryTable">

    <tr>
        <th scope="col">My role</th>
        <th scope="col">Total</th>
        <th scope="col">Accepted</th>
        <th scope="col">Rejected</th>
        <th scope="col">Rejected%</th>
    </tr>

    <tr>
        <td> <?php if ($proposerRow['MY_ROLE'] == 'Proposer') { print 'Proposer';} else {print 'Counterparty';} ?> </td>
        <td> <?php print $proposerRow['TOTAL'];?> </td>
        <td> <?php print $proposerRow['ACCEPTED'];?> </td>
        <td> <?php print $proposerRow['REJECTED'];?> </td>
        <td> <?php print $proposerRow['Rejected PCNT'];?> </td>
    </tr>
    <tr>
        <td> <?php if ($counterPartyRow['MY_ROLE'] == 'Proposer') { print 'Proposer';} else {print 'Counterparty';} ?> </td>
        <td> <?php print $counterPartyRow['TOTAL'];?> </td>
        <td> <?php print $counterPartyRow['ACCEPTED'];?> </td>
        <td> <?php print $counterPartyRow['REJECTED'];?> </td>
        <td> <?php print $counterPartyRow['Rejected PCNT'];?> </td>
    </tr>
</table>

<!--Now, a table will be created for listing of all displayed swaps for the current user.-->
<table class="swapHistoryTable">

    <tr>
        <th scope="col">Proposed Date</th>
        <th scope="col">Accepted/Rejected Date</th>
        <th scope="col">Swap status</th>
        <th scope="col">My role</th>
        <th scope="col">Proposed Item</th>
        <th scope="col">Desired Item</th>
        <th scope="col">Other User</th>
        <th scope="col">Rating</th>
        <th scope="col"></th>

    </tr>
<!--    Second query to get listing of all displayed swaps for the current user.-->

    <?php

                $query = "SELECT swap.proposal_date, swap.accepted_rejected_date,
                            swap.swap_status, 'Proposer' AS MY_ROLE, swap.proposer_item_id,
                            T2.P_TITLE AS PROPOSED_ITEM, swap.desired_item_id,
                            T2.CP_TITLE AS DESIRED_ITEM, T2.CP_NICKNAME AS
                            OTHER_USER, swap.swap_proposer_rating AS RATING FROM `swap`
                            JOIN
                            (SELECT T1.CP_TITLE, T1. P_TITLE, T1.P_EMAIL, T1.CP_EMAIL,
                            T1.CP_INO, T1.P_INO, U1.nickname AS CP_NICKNAME FROM
                            (SELECT IT1.email AS CP_EMAIL, IT1.item_no AS CP_INO, IT1.title
                            AS CP_TITLE, IT2.email AS P_EMAIL, IT2.item_no AS P_INO, IT2.title
                            AS P_TITLE FROM item IT1 JOIN item IT2) T1 JOIN
                            (SELECT email, nickname FROM `user`) U1 ON T1.CP_EMAIL =
                            U1.email) T2 ON swap.proposer_email = T2.P_EMAIL AND
                            swap.counterparty_email = T2.CP_EMAIL WHERE (swap.proposer_email
                            = '$current_user_id') AND swap.swap_status IN ('Accepted', 'Rejected') AND
                            swap.desired_item_id = T2.CP_INO AND swap.proposer_item_id =
                            T2.P_INO
                            UNION
                            SELECT swap.proposal_date, swap.accepted_rejected_date,
                            swap.swap_status, 'CounterProposer' AS MY_ROLE,
                            swap.proposer_item_id, T2.P_TITLE AS PROPOSED_ITEM,
                            swap.desired_item_id, T2.CP_TITLE AS DESIRED_ITEM,
                            T2.P_NICKNAME AS OTHER_USER, swap.swap_proposer_rating AS
                            RATING FROM `swap` JOIN
                            (SELECT T1.CP_TITLE, T1. P_TITLE, T1.P_EMAIL, T1.CP_EMAIL,
                            T1.CP_INO, T1.P_INO, U1.nickname AS P_NICKNAME FROM
                            (SELECT IT1.email AS CP_EMAIL, IT1.item_no AS CP_INO, IT1.title
                            AS CP_TITLE, IT2.email AS P_EMAIL, IT2.item_no AS P_INO, IT2.title
                            AS P_TITLE FROM item IT1 JOIN item IT2) T1 JOIN
                            (SELECT email, nickname FROM `user`) U1 ON T1.P_EMAIL =
                            U1.email) T2 ON swap.proposer_email = T2.P_EMAIL AND
                            swap.counterparty_email = T2.CP_EMAIL WHERE
                            (swap.counterparty_email = '$current_user_id') AND swap.swap_status IN
                            ('Accepted', 'Rejected') AND swap.desired_item_id = T2.CP_INO AND
                            swap.proposer_item_id = T2.P_INO";

$result = mysqli_query($db, $query);
//print_r( mysqli_num_rows( $result) );
include('lib/show_queries.php');

if ( !is_bool($result) && (mysqli_num_rows($result) > 0) ){
    while($swap_listing_row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
       //print_r($swap_listing_row);
        $desired_item_id = $swap_listing_row['desired_item_id'];
        $proposerItemId = $swap_listing_row['proposer_item_id'];
        $swap_date = $swap_listing_row['proposal_date'];
        $swap_status_date = $swap_listing_row['accepted_rejected_date'];
        $swap_status = $swap_listing_row['swap_status'];
        $user_role_in_swap = $swap_listing_row['MY_ROLE'];
        $item_for_swap = $swap_listing_row['PROPOSED_ITEM'];
        $item_wanted = $swap_listing_row['DESIRED_ITEM'];
        $other_user = $swap_listing_row['OTHER_USER'];
        $rating_other_user = $swap_listing_row['RATING'];
        $item_href = "<a href=swap_details.php?myRole=$user_role_in_swap&desiredItemId=$desired_item_id&proposerItemId=$proposerItemId&status=$swap_status>Detail</a>";

        //Add each of these fields into the table.
        $swap_listing_table = "<tr>
                            <th scope='row'>$swap_date</th>
                            <td>$swap_status_date</td>
                            <td>$swap_status</td>
                            <td>$user_role_in_swap</td>
                            <td>$item_for_swap</td>
                            <td>$item_wanted</td>
                            <td>$other_user</td>
                            <td>$rating_other_user</td>
                            <td>$item_href</td>
                          </tr>";
    }
}
else {
    array_push($error_msg,  "Query ERROR: Failed to get Swap History...<br>" . __FILE__ ." line:". __LINE__ );
}
?>
    <?php print($swap_listing_table) ?>
</table>


</body>

</html>



