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

$activeDev = 'd.auto_id NOT IN (SELECT device_id FROM device_status)';
$activeManu = "d.manufacturer NOT IN (SELECT manufacturer FROM manufacturer_status) UNION (SELECT manufacturer FROM manufacturer_status WHERE status = 'active')";
$activeType = "d.device_type NOT IN (SELECT device_type FROM device_type_status) UNION (SELECT device_type FROM device_type_status WHERE status = 'active')";

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
          $sql = "SELECT DISTINCT manufacturer FROM devices_try d WHERE $activeDev AND $activeManu ORDER BY manufacturer";
          $dblink = get_db("equipment");
          $result = $dblink->query($sql);
          while ($data = $result->fetch_array(MYSQLI_ASSOC)){
               $value = str_replace(" ","_",$data['manufacturer']);
               echo '<option value="'.$value.'">'.$data['manufacturer'].'</option>';
          }
          echo '<option value="all">All Manufacturers</option>';
          echo '</select>';
     } else {
          echo '<label>Device:</label>';
          echo '<select class="form-control" name="device">';
          $sql = "SELECT DISTINCT device_type FROM devices_try d WHERE $activeDev AND $activeType ORDER BY device_type";
          $dblink = get_db("equipment");
          $result = $dblink->query($sql);
          while ($data = $result->fetch_array(MYSQLI_ASSOC)){
               $value = str_replace(" ","_",$data['device_type']);
               echo '<option value="'.$value.'">'.$data['device_type'].'</option>';
          }
          echo '<option value="all">All Devices</option>';
          echo '</select>';

          echo '<label>Manufacturer:</label>';
          echo '<select class="form-control" name="manufacturer">';
          $sql = "SELECT DISTINCT manufacturer FROM devices_try d WHERE $activeDev AND $activeManu ORDER BY manufacturer";
          $result = $dblink->query($sql);
          while ($data = $result->fetch_array(MYSQLI_ASSOC)){
               $value = str_replace(" ","_",$data['manufacturer']);
               echo '<option value="'.$value.'">'.$data['manufacturer'].'</option>';
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
     
     $dblink = get_db("equipment");
     $type = $_GET['type'];
     $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
     $limit = 100;
     $offset = ($page - 1) * $limit;

     if ($type == "serialNum") {
          $serial = $dblink->real_escape_string($_GET['serial_number']);
          $sql = "SELECT d.*, 'Active' AS status FROM devices_try d WHERE d.serial_number='$serial' AND $activeDev";
     } elseif ($type == "all") {
          $status = $_GET['status'];
          if ($status == 'active') {
               $sql = "SELECT d.*, 'Active' AS status FROM devices_try d 
               WHERE d.auto_id NOT IN (SELECT device_id FROM device_status) 
               AND d.manufacturer NOT IN (SELECT manufacturer FROM manufacturer_status) 
               AND d.device_type NOT IN (SELECT device_type FROM device_type_status)";
          } elseif ($status == 'inactive') {
               $sql = "SELECT d.*, 'Inactive' AS status FROM devices_try d 
               WHERE d.auto_id IN (SELECT device_id FROM device_status) 
               OR d.manufacturer IN (SELECT manufacturer FROM manufacturer_status WHERE status = 'inactive') 
               OR d.device_type IN (SELECT device_type FROM device_type_status WHERE status = 'inactive')";
          } else {
               $sql = "SELECT d.*, IF(ds.device_id IS NULL,'Active','Inactive') AS status FROM devices_try d 
               LEFT JOIN device_status ds ON d.auto_id = ds.device_id";
          }
     } elseif ($type == "manufacturer") {
          $man = str_replace("_"," ",$_GET['manufacturer']);
          $mCond = ($man === 'all') ? '1' : "d.manufacturer='$man'";
          $sql = "SELECT d.*, 'Active' AS status FROM devices_try d 
               WHERE $mCond AND d.auto_id NOT IN (SELECT device_id FROM device_status) 
               AND d.manufacturer NOT IN (SELECT manufacturer FROM manufacturer_status) 
               AND d.device_type NOT IN (SELECT device_type FROM device_type_status) 
               OR d.manufacturer IN (SELECT manufacturer FROM manufacturer_status WHERE $mCond AND status = 'active')";
     } else {
          $dev = str_replace("_"," ",$_GET['device']);
          $man = str_replace("_"," ",$_GET['manufacturer']);
          $dCond = ($dev === 'all') ? '1' : "d.device_type='$dev'";
          $mCond = ($man === 'all') ? '1' : "d.manufacturer='$man'";
          $sql = "SELECT d.*, 'Active' AS status FROM devices_try d 
               WHERE $dCond AND $mCond 
               AND d.auto_id NOT IN (SELECT device_id FROM device_status) 
               AND d.manufacturer NOT IN (SELECT manufacturer FROM manufacturer_status) 
               AND d.device_type NOT IN (SELECT device_type FROM device_type_status) 
               OR d.manufacturer IN (SELECT manufacturer FROM manufacturer_status WHERE $mCond AND status = 'active') 
               OR d.device_type IN (SELECT device_type FROM device_type_status WHERE $dCond AND status = 'active')";
     }

     // Get total count
     $count_sql = "SELECT COUNT(*) as total FROM ($sql) as count_query";
     $count_result = $dblink->query($count_sql);
     $total_rows = $count_result->fetch_assoc()['total'];
     $total_pages = ceil($total_rows / $limit);

     // Apply LIMIT for pagination
     $sql .= " LIMIT $limit OFFSET $offset";
     $result = $dblink->query($sql);

     while ($data = $result->fetch_array(MYSQLI_ASSOC)) {
          echo '<tr>';
          echo '<td>'.$data['device_type'].'</td><td>'.$data['manufacturer'].'</td><td>'.$data['serial_number'].'</td>';
          echo '<td><a class="btn btn-success" href="view.php?eid='.$data['auto_id'].'">View</a></td>';
          echo '</tr>';
     }
     echo '</tbody></table>';

     //page links
     echo '<div class="text-center" style="margin-top:15px;">';

     $prev = max(1, $page - 1);
     $next = min($total_pages, $page + 1);

     $original_get = $_GET; 

     $_GET['page'] = $prev;
     $prev_link = '?' . http_build_query($original_get);

     $_GET['page'] = $next;
     $next_link = '?' . http_build_query($original_get);

     echo '<a href="'.$prev_link.'" class="btn btn-sm btn-outline-secondary" style="margin-right:5px;">&laquo; Prev</a>';
     echo '<span style="font-size:14px;">Page <strong>'.$page.'</strong> of '.$total_pages.'</span>';
     echo '<a href="'.$next_link.'" class="btn btn-sm btn-outline-secondary" style="margin-left:5px;">Next &raquo;</a>';

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
