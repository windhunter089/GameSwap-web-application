<?php
//Search Item Page
//Writen by Emma Gonzales


include('lib/common.php'); 
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
?>

<?php
include("lib/header.php");
?>

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
    </div>
        

<!-- CONTENT --> 
    <div class="center_content">
        <!-- "SEARCH  --> 
       <div class="Title" style="color:black; font-size: 30px; text-align: center;">Search for Items</div> 
          
               
        <!--- Searching by keyword -- 
        <div class ="searchContent">
            <form style="margin:15px;">
            <label for="search" style="color: black;" >By Keyword: </label>
            <input type="text" id="search" name="search">
            </form>
        </div>
        -->
        
<?php $keyword = "A"; ?>
      

<form action="search_results.php" method="post">
    
  <!-- Keyword Search --> 
  <input type="radio" id="Keyword" name="search_method" value="byKeyword">
  <label for="byKeyword" style="color:black;">By keyword: </label>
  <input type="text" id="keywordTosearch" name="keyword"><br>
      
  <!-- In My Postal Code --> 
  <input type="radio" id="myPostalCode" name="search_method" value="inMyPostalCode">
  <label for="myPostalCode" style="color:black;">In my postal code</label><br>
    
  <!-- Within X Miles of Me --> 
  <input type="radio" id="XMiles" name="search_method" value="withinXMilesOfMe">
  <label for="javascript" style="color:black;">Within  <input type="number" id="xmiles" name="miles"> miles of me</label><br>
    
  <!-- In Postal Code --> 
  <input type="radio" id="javascript" name="search_method" value="inXPostalCode">
  <label for="javascript" style="color:black;">In postal code: </label>
  <input type="text" id=XpostalCode name="postalCode">
    
    <br>
    <button type="submit">Search!</button>
    
</form>
        
        
         <?php include("lib/error.php"); ?>
        
        <div class="clear"></div>
    </div>
    
    
    
</body>
</html>