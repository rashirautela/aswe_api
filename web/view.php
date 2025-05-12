<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Advanced Software Engineering</title>
<link href="../assets/css/bootstrap.css" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/font-awesome.min.css">
<link rel="stylesheet" href="../assets/css/owl.carousel.css">
<link rel="stylesheet" href="../assets/css/owl.theme.default.min.css">

<!-- MAIN CSS -->
<link rel="stylesheet" href="../assets/css/templatemo-style.css">
</head>
<body>
<body id="top" data-spy="scroll" data-target=".navbar-collapse" data-offset="50">
     <!-- MENU -->
     <section class="navbar custom-navbar navbar-fixed-top" role="navigation">
          <div class="container">
               <div class="navbar-header">
                    <button class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                         <span class="icon icon-bar"></span>
                         <span class="icon icon-bar"></span>
                         <span class="icon icon-bar"></span>
                    </button>

                    <!-- lOGO TEXT HERE -->
                    <a href="#" class="navbar-brand">AES Inventory Database</a>
               </div>
               <!-- MENU LINKS -->
               <div class="collapse navbar-collapse">
                    <ul class="nav navbar-nav navbar-nav-first">
                         <li><a href="index.php" class="smoothScroll">Home</a></li>
                         <li><a href="search.php" class="smoothScroll">Search Equipment</a></li>
                         <li><a href="add.php" class="smoothScroll">Add Eq</a></li>
                         <li><a href="add_manufacturer.php" class="smoothScroll">Add Manufacturer</a></li>
                         <li><a href="add_device_type.php" class="smoothScroll">Add Type</a></li>
                         
        <li><a href="modify_manufacturer.php" class="smoothScroll">Modify Manufacturer</a></li>
        <li><a href="modify_device_type.php" class="smoothScroll">Modify Device Type</a></li>
                    </ul>
               </div>
          </div>
     </section>
 <!-- HOME -->
     <section id="home">
          </div>
     </section>
     <!-- FEATURE -->
     <section id="feature">
          <div class="container">
               <div class="row">
                    <div class="col-md-12 col-sm-12">

                         <?php
                              function get_db($db){ 
                                   $hostname = "localhost";
                                   $username = "web_user";
                                   $password = "*k8WZ!kK.zlgdo(0";
                                   $dblink = new mysqli($hostname,$username,$password,$db);
                                   if (mysqli_connect_error()){
                                       die("Error connecting to the database: ".mysqli_connect_error());
                                   }
                                   return $dblink;
                              }

                              $eid = $_GET['eid'];
                              $sql = "SELECT * FROM `devices_try` WHERE `auto_id`='$eid'";
                              $dblink = get_db("equipment");
                              $result=$dblink->query($sql) or
                                   die("<h2>Something went wrong with $sql<br>".$dblink->error."</h2>");
                              $info = $result->fetch_array(MYSQLI_ASSOC);
                              echo '<h2>Device Info:</h2>';
                              echo '<p>Device ID: <b>'.$info['auto_id'].'</b></p>';
                              echo '<p>Device Type: <b>'.$info['device_type'].'</b></p>';
                              echo '<p>Device Manufacturer: <b>'.$info['manufacturer'].'</b></p>';
                              echo '<p>Device Serial Number: <b>'.$info['serial_number'].'</b></p>';
                              echo '<p><a class="btn btn-success" href="modify.php?eid='.$info['auto_id'].'">Modify</a></p>';
                              ?>
                    </div>
                    

                    

               </div>
          </div>
     </section>
</body>
</html>