<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Add Equipment</title>
<link href="../assets/css/bootstrap.css" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/templatemo-style.css">
</head>
<body id="top" data-spy="scroll" data-target=".navbar-collapse" data-offset="50">

<section class="navbar custom-navbar navbar-fixed-top" role="navigation">
  <div class="container">
    <div class="navbar-header">
      <button class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
        <span class="icon icon-bar"></span>
        <span class="icon icon-bar"></span>
        <span class="icon icon-bar"></span>
      </button>
      <a href="#" class="navbar-brand">Modify Manufacturer</a>
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

echo '<h2>Modify Manufacturer</h2>';

// Step 1: Show list of all manufacturers
if (!isset($_POST['selected_manufacturer']) && !isset($_POST['updated_name'])) {
  echo '<form method="post">';
  echo '<div class="form-group">';
  echo '<label>Select Manufacturer to Modify:</label>';
  echo '<select name="selected_manufacturer" class="form-control" required>';

  $rs = $db->query("SELECT DISTINCT manufacturer FROM devices_try   UNION (SELECT manufacturer FROM manufacturer_status WHERE status = 'active') ORDER BY manufacturer");
  while($row = $rs->fetch_assoc()) {
    $man = htmlspecialchars($row['manufacturer']);
    echo '<option value="'.$man.'">'.$man.'</option>';
  }

  echo '</select></div>';
  echo '<button type="submit" class="btn btn-primary">Select</button>';
  echo '</form>';
}

// Step 2: Show form to edit selected manufacturer
else if (isset($_POST['selected_manufacturer'])) {
  $original = $db->real_escape_string($_POST['selected_manufacturer']);

  // Get current status
  $stmt = $db->prepare("SELECT status FROM manufacturer_status WHERE manufacturer = ?");
  $stmt->bind_param('s', $original);
  $stmt->execute();
  $result = $stmt->get_result()->fetch_assoc();
  $currentStatus = $result ? $result['status'] : 'active';

  echo '<form method="post">';
  echo '<input type="hidden" name="original_name" value="'.htmlspecialchars($original).'">';
  
  echo '<div class="form-group">';
  echo '<label>New Manufacturer Name (A-Z only):</label>';
  echo '<input type="text" name="updated_name" class="form-control" value="'.htmlspecialchars($original).'" required>';
  echo '</div>';

  echo '<div class="form-group">';
  echo '<label>Status:</label>';
  echo '<select name="status" class="form-control">';
  echo '<option value="active"'.($currentStatus==='active' ? ' selected':'').'>Active</option>';
  echo '<option value="inactive"'.($currentStatus==='inactive' ? ' selected':'').'>Inactive</option>';
  echo '</select></div>';

  echo '<button type="submit" class="btn btn-success">Update Manufacturer</button>';
  echo ' <a href="modify_manufacturer.php" class="btn btn-default">Cancel</a>';
  echo '</form>';
}

// Step 3: Handle update
else if (isset($_POST['updated_name']) && isset($_POST['original_name'])) {
  $original = $db->real_escape_string($_POST['original_name']);
  $updated = trim($_POST['updated_name']);
  $status  = ($_POST['status'] === 'inactive') ? 'inactive' : 'active';

  // Validate name: only letters and spaces
  if (!preg_match("/^[a-zA-Z ]+$/", $updated)) {
    echo '<div class="alert alert-danger">Manufacturer name must only contain alphabetic characters and spaces.</div>';
    echo '<a href="modify_manufacturer.php" class="btn btn-default">Back</a>';
    exit;
  }

  // Check for uniqueness (excluding original)
  $stmt = $db->prepare("SELECT 1 FROM devices_try  WHERE manufacturer = ? AND manufacturer != ?");
  $stmt->bind_param('ss', $updated, $original);
  $stmt->execute();
  if ($stmt->get_result()->fetch_row()) {
    echo '<div class="alert alert-danger">That manufacturer name is already in use.</div>';
    echo '<a href="modify_manufacturer.php" class="btn btn-default">Back</a>';
    exit;
  }

  // Update devices
  $upd = $db->prepare("UPDATE devices_try  SET manufacturer = ? WHERE manufacturer = ?");
  $upd->bind_param('ss', $updated, $original);
  $upd->execute();

  // Update or insert into manufacturer_status
  $check = $db->prepare("SELECT 1 FROM manufacturer_status WHERE manufacturer = ?");
  $check->bind_param('s', $updated);
  $check->execute();

  if ($check->get_result()->fetch_row()) {
    $upds = $db->prepare("UPDATE manufacturer_status SET status = ? WHERE manufacturer = ?");
    $upds->bind_param('ss', $status, $updated);
    $upds->execute();
  } else {
    $ins = $db->prepare("INSERT INTO manufacturer_status (manufacturer, status) VALUES (?, ?)");
    $ins->bind_param('ss', $updated, $status);
    $ins->execute();
  }

  echo '<div class="alert alert-success">Manufacturer updated successfully.</div>';
  echo '<a href="modify_manufacturer.php" class="btn btn-default">Modify Another</a>';
}
?>

</body>
</html>
