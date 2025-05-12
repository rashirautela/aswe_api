<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Advanced Software Engineering</title>
  <link href="../assets/css/bootstrap.css" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/templatemo-style.css">
</head>
<body id="top" data-spy="scroll" data-target=".navbar-collapse" data-offset="50">

<section class="navbar custom-navbar navbar-fixed-top" role="navigation">
  <div class="container">
    <div class="navbar-header">
      <button class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
        <span class="icon icon-bar"></span><span class="icon icon-bar"></span><span class="icon icon-bar"></span>
      </button>
      <a href="#" class="navbar-brand">AES Inventory Database</a>
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

<section id="feature" style="padding-top:80px;">
  <div class="container">
    <div class="row">
      <div class="col-md-8 col-md-offset-2">

<?php
function get_db(){
  $db = new mysqli('localhost','web_user','*k8WZ!kK.zlgdo(0','equipment');
  if($db->connect_error) die("DB Connect Error: ".$db->connect_error);
  return $db;
}

$db = get_db();

$eid = isset($_GET['eid']) ? intval($_GET['eid']) : 0;
if(!$eid){
  echo '<div class="alert alert-danger">Invalid device ID.</div>';
  exit;
}

$stmt = $db->prepare("SELECT * FROM devices_try  WHERE auto_id = ?");
$stmt->bind_param('i', $eid);
$stmt->execute();
$info = $stmt->get_result()->fetch_assoc();
if(!$info){
  echo '<div class="alert alert-danger">Device not found.</div>';
  exit;
}

$check = $db->prepare("SELECT 1 FROM device_status WHERE device_id = ?");
$check->bind_param('i', $eid);
$check->execute();
$inactive = $check->get_result()->fetch_row() ? true : false;
$currentStatus = $inactive ? 'inactive' : 'active';

echo '<h2>Modify Device Info</h2>';

if($_SERVER['REQUEST_METHOD']==='POST'){
  $newType   = $db->real_escape_string(str_replace('_',' ',$_POST['type']));
  $newManu   = $db->real_escape_string(str_replace('_',' ',$_POST['manufacturer']));
  $newSerial = $db->real_escape_string($_POST['serial']);
  $newStatus = ($_POST['status']==='inactive') ? 'inactive' : 'active';

  // Validate format
  if (!preg_match('/^SN-[0-9A-F]{64}$/i', $newSerial)) {
    echo '<div class="alert alert-danger">Serial number must follow the format SN-xxxxx (0–9 or A–F).</div>';
  }
  else {
    // Uniqueness check
    $stmt = $db->prepare("SELECT 1 FROM devices_try  WHERE serial_number = ? AND auto_id != ?");
    $stmt->bind_param('si', $newSerial, $eid);
    $stmt->execute();
    if ($stmt->get_result()->fetch_row()) {
      echo '<div class="alert alert-danger">Serial number must be unique.</div>';
    }
    else {
      // Update devices_try 
      $upd = $db->prepare("
        UPDATE devices_try 
        SET device_type = ?, manufacturer = ?, serial_number = ?
        WHERE auto_id = ?
      ");
      $upd->bind_param('sssi',$newType,$newManu,$newSerial,$eid);
      $upd->execute();

      // Update device_status
      if($newStatus === 'inactive'){
        $db->query("INSERT IGNORE INTO device_status(device_id) VALUES ($eid)");
      } else {
        $db->query("DELETE FROM device_status WHERE device_id = $eid");
      }

      echo '<div class="alert alert-success">Device updated successfully.</div>';

      // Refresh data
      $info['device_type']   = $newType;
      $info['manufacturer']  = $newManu;
      $info['serial_number'] = $newSerial;
      $currentStatus         = $newStatus;
    }
  }
}

// Form render
echo '<form method="post">';

// Device Type Dropdown
echo '<div class="form-group">';
echo '<label>Device Type</label>';
echo '<select name="type" class="form-control">';
$rs = $db->query("SELECT DISTINCT device_type FROM devices_try  WHERE device_type NOT IN (SELECT device_type FROM device_type_status) UNION (SELECT device_type FROM device_type_status WHERE status = 'active')");
while($row = $rs->fetch_assoc()){
  $val = str_replace(' ','_',$row['device_type']);
  $sel = ($row['device_type']==$info['device_type']) ? 'selected' : '';
  echo '<option value="'.$val.'" '.$sel.'>'.htmlspecialchars($row['device_type']).'</option>';
}
echo '</select></div>';

// Manufacturer Dropdown
echo '<div class="form-group">';
echo '<label>Manufacturer</label>';
echo '<select name="manufacturer" class="form-control">';
$rs = $db->query("SELECT DISTINCT manufacturer FROM devices_try  WHERE manufacturer NOT IN (SELECT manufacturer FROM manufacturer_status) UNION (SELECT manufacturer FROM manufacturer_status WHERE status = 'active')");
while($row = $rs->fetch_assoc()){
  $val = str_replace(' ','_',$row['manufacturer']);
  $sel = ($row['manufacturer']==$info['manufacturer']) ? 'selected' : '';
  echo '<option value="'.$val.'" '.$sel.'>'.htmlspecialchars($row['manufacturer']).'</option>';
}
echo '</select></div>';

// Serial Number Input
echo '<div class="form-group">';
echo '<label>Serial Number</label>';
echo '<input type="text" name="serial" class="form-control" value="'.htmlspecialchars($info['serial_number']).'">';
echo '</div>';

// Status Dropdown
echo '<div class="form-group">';
echo '<label>Status</label>';
echo '<select name="status" class="form-control">';
echo '<option value="active"   '.($currentStatus==='active'   ? 'selected' : '').'>Active</option>';
echo '<option value="inactive" '.($currentStatus==='inactive' ? 'selected' : '').'>Inactive</option>';
echo '</select></div>';

echo '<button type="submit" class="btn btn-success">Save Changes</button>';
echo ' <a href="view.php?eid='.$eid.'" class="btn btn-default">Cancel</a>';
echo '</form>';
?>

      </div>
    </div>
  </div>
</section>
</body>
</html>
