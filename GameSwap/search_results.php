<?php
//Search Item Page
//Writen by Emma Gonzales


include('lib/common.php');
include('lib/bootstrap_header.php');
include('lib/gameswap_functions.php');
/*
//Make sure user is logged in
if (!isset($_SESSION['email'])) {
	header('Location: login.php');
	exit();
}


//query
$query = "SELECT first_name, last_name " .
		 "FROM User " .
		 "WHERE User.email = '{$_SESSION['email']}'";

//query result
$result = mysqli_query($db, $query);
    include('lib/show_queries.php');



*/
$userId = "user3@gatech.edu";
//fake userId until we get the session set
?>

<?php
include("lib/header.php");
?>

<?php  ?> 

<!--------------- HTML ----------------> 
<title>Game Swap</title>

<!-- Logo and Header --> 
</head>
<body>
    <div id="main_container">
        <div id="header">
                        <div class="logo">
                <img src="img/logo.png" style="background-color:black;" alt="" title="Game Swap Logo"/>
            </div>
        </div>
    
        

<!-- CONTENT --> 
    <div class="center_content">
        <!-- "SEARCH  --> 
       <div class="Title" style="color:black; font-size: 30px; text-align: center;">Search Results</div> 
          
    <!--- Variable for postal code validation --->
    <!-- automatically set to "true" ---> 
    <?php $validPostalCode = true; ?>
        
    
    
               
 
     <!--------- If "BY KEYWORD" Is selected ----------->
    <?php if($_POST["search_method"] == "byKeyword"){ ?>
                 
        <?php $enteredKeyword = $_POST["keyword"]; ?>
        
        <!--- Display Search Method ----> 
        <div style="color:black;"> Search results: Keyword "<?php print $enteredKeyword; ?>" </div>
        
      
        <!--- Keyword Query --> 
        <?php $searchQuery = "SELECT UT.item_no, UT.TYPE, UT.title, UT.condition, UT.description, DT.DISTANCE_CALC_MILES FROM
        (SELECT `user`.email, item.item_no, item.TYPE, item.title, item.condition, item.description FROM `user` JOIN item ON `user`.email = item.email WHERE (item.title LIKE '%$enteredKeyword%' OR item.description LIKE '%$enteredKeyword%') AND `user`.email <> '$userId') UT
        JOIN
        (SELECT T1.USER1_EMAIL, T2.USER2_EMAIL, ROUND((6371 * 0.621371 * (2 * ATAN((SQRT((POWER((SIN(((RADIANS(T2.USER2_LAT)) - (RADIANS(T1.USER1_LAT))) / 2)), 2) + (COS((RADIANS(T1.USER1_LAT)))) * (COS((RADIANS(T2.USER2_LAT)))) * POWER((SIN(((RADIANS(T2.USER2_LON)) - (RADIANS(T1.USER1_LON))) / 2)), 2)))), (SQRT(1 - (POWER((SIN(((RADIANS(T2.USER2_LAT)) - (RADIANS(T1.USER1_LAT))) / 2)), 2) + (COS((RADIANS(T1.USER1_LAT)))) * (COS((RADIANS(T2.USER2_LAT)))) * POWER((SIN(((RADIANS(T2.USER2_LON)) - (RADIANS(T1.USER1_LON))) / 2)), 2))))))),2) AS DISTANCE_CALC_MILES
        FROM
        (SELECT U1.email AS USER1_EMAIL, U1.nickname AS USER1_NICKNAME, U1.postal_code AS USER1_POSTAL_CODE, P1.latitude AS USER1_LAT, P1.longitude USER1_LON, P1.City AS USER1_CITY, P1.State AS USER1_STATE FROM `user` U1
        JOIN postalcode P1 ON U1.postal_code = P1.postal_code) T1
        JOIN
        (SELECT U2.email AS USER2_EMAIL, U2.nickname AS USER2_NICKNAME, U2.postal_code AS USER2_POSTAL_CODE, P2.latitude AS USER2_LAT, P2.longitude USER2_LON, P2.City AS USER2_CITY, P2.state AS USER2_STATE FROM `user` U2 JOIN postalcode P2 ON U2.postal_code = P2.postal_code) T2
        ON T1.USER1_EMAIL <> T2.USER2_email) DT
        ON UT.email = DT.USER2_EMAIL WHERE DT.USER1_EMAIL ='$userId' ORDER BY DISTANCE_CALC_MILES, item_no;" ?> 
        <br>
        
        
       
        
        
        <br> 
        

        
    <?php } ?>

 
        
        
        
     <!--------- If "In MY POSTAL CODE" Is selected ----------->
    <?php if($_POST["search_method"] == "inMyPostalCode"){ ?>
         
        <!--- Display Search Method ----> 
        <div style="color:black;"> Search results wihtin your postal code: </div>
        
        <?php $searchQuery = "SELECT UT.item_no, UT.TYPE, UT.title, UT.condition, UT.description, DT.DISTANCE_CALC_MILES FROM
        (SELECT `user`.email, item.item_no, item.TYPE, item.title, item.condition, item.description FROM `user` JOIN item ON `user`.email = item.email WHERE `user`.email <> '$userId') UT
        JOIN
        (SELECT T1.USER1_EMAIL, T2.USER2_EMAIL, T1.USER1_POSTAL_CODE, T2.USER2_POSTAL_CODE, ROUND((6371 * 0.621371 * (2 * ATAN((SQRT((POWER((SIN(((RADIANS(T2.USER2_LAT)) - (RADIANS(T1.USER1_LAT))) / 2)), 2) + (COS((RADIANS(T1.USER1_LAT)))) * (COS((RADIANS(T2.USER2_LAT)))) * POWER((SIN(((RADIANS(T2.USER2_LON)) - (RADIANS(T1.USER1_LON))) / 2)), 2)))), (SQRT(1 - (POWER((SIN(((RADIANS(T2.USER2_LAT)) - (RADIANS(T1.USER1_LAT))) / 2)), 2) + (COS((RADIANS(T1.USER1_LAT)))) * (COS((RADIANS(T2.USER2_LAT)))) * POWER((SIN(((RADIANS(T2.USER2_LON)) - (RADIANS(T1.USER1_LON))) / 2)), 2))))))),2) AS DISTANCE_CALC_MILES
        FROM
        (SELECT U1.email AS USER1_EMAIL, U1.nickname AS USER1_NICKNAME, U1.postal_code AS USER1_POSTAL_CODE, P1.latitude AS USER1_LAT, P1.longitude USER1_LON, P1.City AS USER1_CITY, P1.State AS USER1_STATE FROM `user` U1 JOIN postalcode P1 ON U1.postal_code = P1.postal_code) T1
        JOIN
        (SELECT U2.email AS USER2_EMAIL, U2.nickname AS USER2_NICKNAME, U2.postal_code AS USER2_POSTAL_CODE, P2.latitude AS USER2_LAT, P2.longitude USER2_LON, P2.City AS USER2_CITY, P2.state AS USER2_STATE FROM `user` U2 JOIN postalcode P2 ON U2.postal_code = P2.postal_code) T2
        ON T1.USER1_EMAIL <> T2.USER2_email WHERE T1.USER1_POSTAL_CODE = T2.USER2_POSTAL_CODE) DT
        ON UT.email = DT.USER2_EMAIL WHERE DT.USER1_EMAIL ='$userId'
        ORDER BY DISTANCE_CALC_MILES, item_no;" ?>
        
         
        
    <?php } ?>

        
     <!--------- If "WITHIN X MILES OF ME" Is selected ----------->
    <?php if($_POST["search_method"] == "withinXMilesOfMe"){ ?>
                
        <?php $userSearchMiles = $_POST["miles"]; ?>
        
        <!--- Display Search Method ----> 
        <div style="color:black;"> Search results within <?php print $userSearchMiles; ?> miles of you:</div>
        
        
        
        <?php $searchQuery = "SELECT UT.item_no, UT.TYPE, UT.title, UT.condition, UT.description, DT.DISTANCE_CALC_MILES FROM (SELECT `user`.email, item.item_no, item.TYPE, item.title, item.condition, item.description FROM `user` JOIN item ON `user`.email = item.email WHERE `user`.email <> '$userId') UT
        JOIN
        (SELECT T1.USER1_EMAIL, T2.USER2_EMAIL, ROUND((6371 * 0.621371 * (2 * ATAN((SQRT((POWER((SIN(((RADIANS(T2.USER2_LAT)) - (RADIANS(T1.USER1_LAT))) / 2)), 2) + (COS((RADIANS(T1.USER1_LAT)))) * (COS((RADIANS(T2.USER2_LAT)))) * POWER((SIN(((RADIANS(T2.USER2_LON)) - (RADIANS(T1.USER1_LON))) / 2)), 2)))), (SQRT(1 - (POWER((SIN(((RADIANS(T2.USER2_LAT)) - (RADIANS(T1.USER1_LAT))) / 2)), 2) + (COS((RADIANS(T1.USER1_LAT)))) * (COS((RADIANS(T2.USER2_LAT)))) * POWER((SIN(((RADIANS(T2.USER2_LON)) - (RADIANS(T1.USER1_LON))) / 2)), 2))))))),2) AS DISTANCE_CALC_MILES
        FROM
        (SELECT U1.email AS USER1_EMAIL, U1.nickname AS USER1_NICKNAME, U1.postal_code AS USER1_POSTAL_CODE, P1.latitude AS USER1_LAT, P1.longitude USER1_LON, P1.City AS USER1_CITY, P1.State AS USER1_STATE FROM `user` U1 JOIN postalcode P1 ON U1.postal_code = P1.postal_code) T1
        JOIN
        (SELECT U2.email AS USER2_EMAIL, U2.nickname AS USER2_NICKNAME, U2.postal_code AS USER2_POSTAL_CODE, P2.latitude AS USER2_LAT, P2.longitude USER2_LON, P2.City AS USER2_CITY, P2.state AS USER2_STATE FROM `user` U2 JOIN postalcode P2 ON U2.postal_code = P2.postal_code) T2
        ON T1.USER1_EMAIL <> T2.USER2_email) DT
        ON UT.email = DT.USER2_EMAIL WHERE DT.USER1_EMAIL ='$userId' AND DT.DISTANCE_CALC_MILES <= '$userSearchMiles' ORDER BY DISTANCE_CALC_MILES, item_no;"; ?>
        
    <?php } ?>
        
        
         <!--------- If IN POSTAL CODE Is selected ----------->
        <?php if($_POST["search_method"] == "inXPostalCode"){ ?>
                
        <!-- put entered postasl code into variable -->
        <?php $enteredPostalCode = $_POST["postalCode"] ?>
        
        <?php
        //postal code query 
        $postalCodeQuery = "SELECT postal_code FROM `postalcode` WHERE postal_code='$enteredPostalCode'";
        //postal code results
        $postalCodeResult = mysqli_query($db, $postalCodeQuery);
                                                             
        //# of results of query
        //**important for validating postal code
        $postalCount = mysqli_num_rows( $postalCodeResult );
        //Postal Code Validation 
        //If there are no results for the postal code, Postal code is INVALID                                             
        if($postalCount == 0){
            
            $validPostalCode = false;
        }                                                             
        ?>
        
        <!------- Display Search Method: "In X postal code" ----> 
        <div style="color:black;"> Search results for items in <?php print $enteredPostalCode; ?> postal code: </div>
        
        
        <!---- Search in X postal code SQL query -----> 
        <?php 
                                                         
         $searchQuery = "SELECT UT.item_no, UT.TYPE, UT.title, UT.condition, UT.description, DT.DISTANCE_CALC_MILES FROM
        (SELECT `user`.email, item.item_no, item.TYPE, item.title, item.condition, item.description FROM `user` JOIN item ON `user`.email = item.email WHERE `user`.email <> '$userId') UT
        JOIN
        (SELECT T1.USER1_EMAIL, T2.USER2_EMAIL, T1.USER1_POSTAL_CODE, T2.USER2_POSTAL_CODE, ROUND((6371 * 0.621371 * (2 * ATAN((SQRT((POWER((SIN(((RADIANS(T2.USER2_LAT)) - (RADIANS(T1.USER1_LAT))) / 2)), 2) + (COS((RADIANS(T1.USER1_LAT)))) * (COS((RADIANS(T2.USER2_LAT)))) * POWER((SIN(((RADIANS(T2.USER2_LON)) - (RADIANS(T1.USER1_LON))) / 2)), 2)))), (SQRT(1 -(POWER((SIN(((RADIANS(T2.USER2_LAT)) - (RADIANS(T1.USER1_LAT))) / 2)), 2) + (COS((RADIANS(T1.USER1_LAT)))) * (COS((RADIANS(T2.USER2_LAT)))) * POWER((SIN(((RADIANS(T2.USER2_LON)) - (RADIANS(T1.USER1_LON))) / 2)), 2))))))),2) AS DISTANCE_CALC_MILES
        FROM
        (SELECT U1.email AS USER1_EMAIL, U1.nickname AS USER1_NICKNAME, U1.postal_code AS USER1_POSTAL_CODE, P1.latitude AS USER1_LAT, P1.longitude USER1_LON, P1.City AS USER1_CITY, P1.State AS USER1_STATE FROM `user` U1 JOIN postalcode P1 ON U1.postal_code = P1.postal_code) T1
        JOIN
        (SELECT U2.email AS USER2_EMAIL, U2.nickname AS USER2_NICKNAME, U2.postal_code AS USER2_POSTAL_CODE, P2.latitude AS USER2_LAT, P2.longitude USER2_LON, P2.City AS USER2_CITY, P2.state AS USER2_STATE FROM `user` U2 JOIN postalcode P2 ON U2.postal_code = P2.postal_code) T2
        ON T1.USER1_EMAIL <> T2.USER2_email WHERE T2.USER2_POSTAL_CODE = '$enteredPostalCode') DT
        ON UT.email = DT.USER2_EMAIL WHERE DT.USER1_EMAIL ='$userId'
        ORDER BY DISTANCE_CALC_MILES, item_no;";
                                                    
                                                         
        ?>
        
    <?php } ?> <!--- end of In X postal code search --> 
        
        
        
        
        
        
        
     <!--------------------------------------------------------------------------->
     <!--------------------------------- SEARCH RESULTS -------------------------->
     <!--------------------------------------------------------------------------->
        
    <!--- Check to see if entered postal code is valid -->
    <!-- important only for "in X Postal Code searches --> 
    <?php if( ($_POST["search_method"] == "inXPostalCode") && ($validPostalCode == false) ){ 
        
        //If postal code is invalid, display "Invalid Postal Code
        $searchItemsTable = "<div id ='gameswap-my-items-no-items-container'>
                            <p> Invalid Postal Code.<i class='bi bi-clipboard-x'></i></p>
                            <br>                     
                            </div>
                             <div>
                             <a href='search_item.php' style='color:black;'> Return to Search Page </a> </div>";
    
    }
        
    //otherwise, continue with search results
    else{
        
         //Get search resultrs from query
         $searchResult = mysqli_query($db, $searchQuery);
        
        // get # of rows
        //This is important for knowing when there are NO results
         $itemCount = mysqli_num_rows( $searchResult );
        
        
        
        //If there are NO results found
        //Display "Sorry, no results found" and "return to search" link
        if($itemCount == 0){
            
            $searchItemsTable = "<div id ='gameswap-my-items-no-items-container'>
                            <p> Sorry, no results found! <i class='bi bi-clipboard-x'></i></p>
                            <br>                     
                            </div>
                             <div>
                             <a href='search_item.php' style='color:black;'> Return to Search Page </a> </div>"
                ;
            
        }
        
        //otherwise, display the results
        else{
         
         //display column headers
         $searchItemsTable = "<table class = 'table table-responsive-sm align-middle mb-0 bg-white border-bottom'> 
         <thread class = 'bg-light'>
             <tr>
                 <th scope='col'>Item #</th>
                 <th scope='col'>Game Type</th>
                 <th scope='col'>Title</th>
                 <th scope='col'>Condition</th>
                 <th scope='col'>Description</th>
                 <th scope='col'>Distance</th>
                 <th scope='col'></th>
             </tr>";
            
         //Places the data from the query into table
         while($row = mysqli_fetch_array($searchResult, MYSQLI_ASSOC)) {
                 $itemNo = $row['item_no'];
                 $gameType = $row['TYPE'];
                 $title = $row['title'];
                 $condition = $row['condition'];
                 $description = $row['description'];
                 $distance = $row['DISTANCE_CALC_MILES'];



            //We need to truncate item description if it exceeds 100 characters.
            $item_desc_char_count = mb_strlen($description);
            //If greater than 100, add "..." after 99 characters.
            if($item_desc_char_count > 100){
                $description = substr($description, 0, 99);
                $description .= "...";
            }

            //link item page for details column 
            $itemHref = "<a href = '/cs6400-2022-01-Team064/GameSwap/view_item.php?item_id=$itemNo'>Detail</a>";

             //display row data
             $searchItemsTable = $searchItemsTable . "
                 <tr>
                     <td>$itemNo</th>
                     <td>$gameType</th>";
             
             //If keyword matches title, give it a blue background
             if( ($_POST["search_method"] == "byKeyword") && (str_contains (strtolower($title) , strtolower($enteredKeyword) ) ) ){
                  $searchItemsTable = $searchItemsTable . "<td style='background: skyblue'>$title</th>";
             }
             else{
                
                 $searchItemsTable = $searchItemsTable . "<td>$title</th>";
             }
                     
            //display "condition" of item
            $searchItemsTable = $searchItemsTable . "
                     <td>$condition</th>";
             
             
            //If keyword matches desciption, give it a blue background
             if( ($_POST["search_method"] == "byKeyword") && (str_contains( strtolower($description) , strtolower($enteredKeyword) ) ) ){
                  $searchItemsTable = $searchItemsTable . "<td style='background: skyblue'>$description</th>";
             }
             else{
                $searchItemsTable = $searchItemsTable . "<td>$description</th>";
             }
            
             
            //display rest of table rows
            $searchItemsTable = $searchItemsTable . "
                     <td>$distance</th>
                     <td>$itemHref</td> 
                 </tr>
                 ";

            }//end while loop
            
        }// end of ELSE statement displaying results
            
        
         // Add html end of table
         $searchItemsTable = $searchItemsTable . "</table>";
             
    }//end of search results else statement
        

    //end of php ?> 
     <br>
     
         
     
     <!---------------------------------------------------->
     <!------- Print Search Results Table -----------------> 
     <div  style="color:black;"> <?php print $searchItemsTable ?> </div>
               
     <?php include("lib/error.php"); ?>
        
      <div class="clear"></div>      
        
    </div>
        
    </div>
    
    
    
</body>
</html>