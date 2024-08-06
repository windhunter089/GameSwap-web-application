<!-- MAIN MENU --> 
<!-- Written by: Emma Gonzales --> 


<?php
//include library 
include('lib/common.php');
include('lib/gameswap_functions.php');

//Make sure user is logged in
//if (!isset($_SESSION['email'])) {
//	header('Location: login.php');
//	exit();
//}

//$userId = "user2@gatech.edu";
//fake userId until we get the session set

//Get the current logged in user's id.
$userId = gameswap_get_current_user_id();


//query that gets current user's first and last name (for welcome message)
$query = "SELECT `user`.`first_name`, `user`.`last_name`
FROM `user` WHERE `user`.email = '$userId'";
//results of frist and lasta name query
$result = mysqli_query($db, $query);
$row = mysqli_fetch_array($result, MYSQLI_ASSOC);


//include show queries code
include('lib/show_queries.php');

?> <!-- end of php --> 


<!-- GET STYLE SHEET --> 
<link rel="stylesheet" type="text/css" href="css/gameswap_style.css">


<!---- HTML ------> 
<?php
include("lib/header.php");
?>

<!-- GAME SWAP LOGO --> 
<title>Game Swap</title>
</head>
<body>
    <div id="main_container">
        <div id="header">
            <div class="logo">
                <img src="img/logo.png" style="background-color:black;" alt="" title="Game Swap Logo"/>
            </div>
        </div>

        
    
    <!--------- CONTENT --------------> 
        <!-- LOGOUT BUTTON --> 
        <div class="navbutton", style="padding: 0px;">
            <a style="font-size: 10px;" href="logout.php">logout</a> 
            <br>
        </div>
          
    
        <div class="center_content">
            
            <!--- "Welcome, firstName lastName!" message --->         
             <div class="Title" style="color:black; font-size: 30px; text-align: center;"> <?php print "Welcome, " . $row['first_name'] . ' ' . $row['last_name'] . "!" ?> </div>

            <!------------- My Rating ------------------>

            <?php
            //User rating query
            $ratingsQuery = "SELECT COALESCE((((SELECT AVG(swap_proposer_rating) AS RATING_AVG FROM swap WHERE swap_status = 'Accepted' AND proposer_email = '$userId' AND swap_proposer_rating IS NOT NULL) + (SELECT AVG(swap_counterparty_rating) AS RATING_AVG FROM swap WHERE swap_status = 'Accepted' AND counterparty_email = '$userId' AND swap_counterparty_rating IS NOT NULL)) /2), ((SELECT AVG(swap_proposer_rating) AS RATING_AVG FROM swap WHERE swap_status = 'Accepted' AND proposer_email = '$userId' AND swap_proposer_rating IS NOT NULL)), ((SELECT AVG(swap_counterparty_rating) AS RATING_AVG FROM swap WHERE swap_status = 'Accepted' AND counterparty_email = '$userId' AND swap_counterparty_rating IS NOT NULL)), 'None') AS USER_RATING_AVG;";

            $ratingsResults =  mysqli_query($db, $ratingsQuery);
            $ratingsCount = mysqli_fetch_array($ratingsResults, MYSQLI_ASSOC);

            ?>

            <!--- Display User average rating --->
            <div class="numDisplay"><title>My Rating</title> <br>
                <a><?php

                    $user_rating = $ratingsCount['USER_RATING_AVG'];
                    //Since user rating can be none, we check to see that. Only round it if it is not None.
                    if($user_rating != "None"){
                        $user_rating = round($user_rating, 2);
                    }
                    //Display the user rating.
                    print $user_rating;

                    ?>
                </a>
            </div>




            <!------------ Unaccepted Swaps -------------->
            <?php

            $unacceptedSwapsQuery = "SELECT COUNT(*), (DATEDIFF(CURRENT_DATE, swap.proposal_date)) AS PENDING_DAYS FROM swap WHERE swap_status = 'Pending' AND counterparty_email = '$userId'";

            $unacceptedResults =  mysqli_query($db, $unacceptedSwapsQuery);
            $count = mysqli_fetch_array($unacceptedResults, MYSQLI_ASSOC);

            ?>

            <!-- User has more than 5 unaccepted swaps -->
            <!-- Show # in BOLD & RED, link to unaccepted swaps -->
            <?php if( ($count['COUNT(*)'] > 5) || ($count['PENDING_DAYS'] > 5) ) { ?>

                <div class="numDisplay"><title>Unaccepted Swaps</title> <br>
                    <a href="accept_reject_swaps.php" style="color: RED; font-weight: bold;">
                        <?php print $count['COUNT(*)'] ?>  </a>
                </div>

                <!-- User has more than 5 unaccepted swaps -->
                <!-- Show # in black, link to unaccepted swaps -->
            <?php } else if($count['COUNT(*)'] > 0) { ?>

                <div class="numDisplay"><title>Unaccepted Swaps</title> <br>
                    <a href="accept_reject_swaps.php"> <?php print $count['COUNT(*)'] ?>  </a>
                </div>

                <!-- User has no unaccepted swaps -->
                <!-- Not linked to any page -->
            <?php } else { ?>
                <div class="numDisplay"><title>Unaccepted Swaps</title> <br>
                    <a> <?php print "0" ?> </a>
                </div>

                <!-- NOT WORKING -->
                <!-- $unacceptedCount['COUNT(*)'] -->
            <?php } ?>



            <!------------------------ Unrated Swaps ---------------------->
            <?php

            $unratedSwapsQuery = "SELECT COUNT(*) FROM swap WHERE swap_status ='Accepted' AND ((proposer_email = '$userId' AND swap_proposer_rating IS NULL) OR (counterparty_email = '$userId' AND swap_counterparty_rating IS NULL))";

            $unratedResults =  mysqli_query($db, $unratedSwapsQuery);
            $unratedCount = mysqli_fetch_array($unratedResults, MYSQLI_ASSOC);


            ?>

            <!-- User has more than 5 unrated swaps -->
            <!-- Show # in BOLD & RED, link to unrated swaps -->
            <?php if($unratedCount['COUNT(*)'] > 2) { ?>
                <div class="numDisplay"><title>Unrated Swaps</title> <br>
                    <a href="rate_swaps.php" style="color: RED; font-weight: bold;
            " > <?php print $unratedCount['COUNT(*)'] ?> </a>
                </div>

                <!-- User has between 1-5 swaps -->
                <!-- Show # in black, link to unrated swaps -->
            <?php } else if ($unratedCount['COUNT(*)'] > 0){ ?>
                <div class="numDisplay"><title>Unrated Swaps</title>  <br>
                    <a href="rate_swaps.php"> <?php print $unratedCount['COUNT(*)'] ?> </a>
                </div>


                <!-- User has no unrated swaps -->
                <!-- Not linked to any page -->
            <?php } else { ?>
                <div class="numDisplay"><title>Unrated Swaps</title> <br>
                    <a> <?php print $unratedCount['COUNT(*)'] ?> </a>
                </div>

            <?php } ?>



            <br>
            <?php
            if($unratedCount['COUNT(*)'] > 0 or $count['COUNT(*)'] > 0){
                $updateMyInfo = "<a href=javascript: void(0)>Update My Info</a>";
                array_push($error_msg, "Must not have unrated or unaccepted swaps to Update Account Info ");


            }
            else{
                $updateMyInfo = "<a href='update_information.php'>Update My Info</a>";
            }
            ?>
            
            <!---- NAVIGATION BUTTONS ----> 
            <div class="navbutton">
                <!-- List Item --> 
                <a href="list_item.php">List Item</a> 
                <br>            
                <br>         
                 <!-- My Items--> 
                <a href="my_items.php">My Items</a>
                <br>
                 <!-- Search Items --> 
                <a href="search_item.php">Search Items</a> 
                 <br>
                <!-- Swap History --> 
                <a href="swap_history.php">Swap History</a> 
                <br>

                <!-- Update My Info -->
                <?php print $updateMyInfo ?>
                <br>    
            </div>
          
            <?php include("lib/error.php"); ?>

            <div class="clear"></div>
        </div>
    </div>
    </body>
</html>