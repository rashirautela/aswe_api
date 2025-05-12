<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Advanced Software Engineering</title>
<link href="../assets/css/bootstrap.css" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/font-awesome.min.css">
<link rel="stylesheet" href="../assets/css/owl.carousel.css">
<link rel="stylesheet" href="../assets/css/owl.theme.default.min.css">
<link rel="stylesheet" href="../assets/css/dataTables.dataTables.css">
<link rel="stylesheet" href="../assets/css/templatemo-style.css">
<script src="../assets/js/jquery-3.7.1.js"></script>
<script src="../assets/js/dataTables.js"></script>
</head>
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
               <a href="#" class="navbar-brand">Search Equipment Database</a>
          </div>
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

<section id="feature">
     <div class="container">
<?php
include "data.php";

if(!isset($_GET['type'])){
     echo '<a class="btn btn-primary" href="search.php?type=device">Search by Device Type </a>   ';
     echo '<a class="btn btn-primary" href="search.php?type=manufacturer">Search by Manufacturer </a>   ';
     echo '<a class="btn btn-primary" href="search.php?type=serialNum">Search by Serial Number </a>   ';
     echo '<a class="btn btn-primary" href="search.php?type=all">View All </a>   ';
} else {
     echo '<form method="get" action="">';
     echo '<div class="form-group">';
     $type = $_GET['type'];

     if ($type == "serialNum") {
          echo '<label for="serialNumber">Enter Serial Number:</label>';
          echo '<input type="text" class="form-control" name="serial_number" required>';
     } elseif ($type == "all") {
          echo '<label>Status:</label>';
          echo '<select name="status" class="form-control">';
          echo '<option value="active">Only Active</option>';
          echo '<option value="inactive">Only Inactive</option>';
          echo '<option value="all">All</option>';
          echo '</select>';
     } elseif ($type == "manufacturer") {
          echo '<label>Manufacturer:</label>';
          echo '<select class="form-control" name="manufacturer">';
          $result = make_api_call("GET", "fetch_active_manufacturers");
          foreach($result as $manufacturer){
               $value = str_replace(" ","_",$manufacturer);
               echo '<option value="'.htmlspecialchars($value).'">'.htmlspecialchars($manufacturer).'</option>';

          }
          echo '<option value="all">All Manufacturers</option>';
          echo '</select>';
     } else {
          echo '<label>Device:</label>';
          echo '<select class="form-control" name="device">';
          $result = make_api_call("GET", "fetch_active_device_types");
          foreach($result as $device_type){
               $value = str_replace(" ","_",$device_type);
               echo '<option value="'.htmlspecialchars($value).'">'.htmlspecialchars($device_type).'</option>';

          }
          echo '<option value="all">All Devices</option>';
          echo '</select>';

          echo '<label>Manufacturer:</label>';
          echo '<select class="form-control" name="manufacturer">';
          $result = make_api_call("GET", "fetch_active_manufacturers");
          foreach($result as $manufacturer){
               $value = str_replace(" ","_",$manufacturer);
               echo '<option value="'.htmlspecialchars($value).'">'.htmlspecialchars($manufacturer).'</option>';
          }
          echo '<option value="all">All Manufacturers</option>';
          echo '</select>';
     }

     echo '</div>';
     echo '<input type="hidden" name="type" value="'.$type.'">';
     echo '<button type="submit" class="btn btn-success" name="submit" value="search">Search</button>';
     echo '</form>';
}

if (isset($_GET['submit']) && $_GET['submit'] == "search") {
     echo '<table id="equipmentTable" class="table table-striped table-bordered display nowrap" style="width:100%">';
     echo '<thead><tr><th>Device Type</th><th>Manufacturer</th><th>Serial Number</th><th>Action</th></tr></thead><tbody>';
     
     $type = $_GET['type'];

     if ($type == "serialNum") {
          $serial_number = $_GET['serial_number'];
          $payload = http_build_query(['serial_number'=>$serial_number]);
          $result = make_api_call("POST", "search_serial_number", $payload);
     } elseif ($type == "all") {
          $status = $_GET['status'];
          $payload = http_build_query(['status'=>$status]);
          $result = make_api_call("POST", "search_all", $payload);
     } elseif ($type == "manufacturer") {
          $manufacturer = $_GET['manufacturer'];
          $payload = http_build_query(['manufacturer'=>$manufacturer]);
          $result = make_api_call("POST", "search_manufacturer", $payload);
     } else {
          $device_type = $_GET['device_type'];
          $manufacturer = $_GET['manufacturer'];
          $payload = http_build_query(['device_type'=>$device_type, 'manufacturer'=>$manufacturer]);
          $result = make_api_call("POST", "search_device_type", $payload);
     }

     foreach($result as $data){
          echo '<tr>';
          echo '<td>'.$result['device_type'].'</td><td>'.$result['manufacturer'].'</td><td>'.$result['serial_number'].'</td>';
          echo '<td><a class="btn btn-success" href="view.php?eid='.$result['auto_id'].'">View</a></td>';
          echo '</tr>';
     }
     echo '</tbody></table>';
     echo '</div>';
}
?>
     </div>
</section>

<script>
$(document).ready(function () {
     $('#equipmentTable').DataTable({
          paging: false,
          searching: false,
          info: false,
          ordering: false
     });
});
</script>
</body>
</html>
