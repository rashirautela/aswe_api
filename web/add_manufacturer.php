<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Add Manufacturer</title>
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
      <a href="#" class="navbar-brand">Add New Manufacturer</a>
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
echo "<h2>Add New Manufacturer</h2>";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newManufacturer = trim($_POST['manufacturer']);

    if (!preg_match('/^[A-Za-z ]+$/', $newManufacturer)) {
        echo '<div class="alert alert-danger">Manufacturer name can only contain letters and spaces.</div>';
    } else {
        $newManufacturerLower = strtolower($newManufacturer);
        
        // Check in both tables
        $stmt = $db->prepare("
            SELECT manufacturer FROM manufacturer_status WHERE manufacturer = ?
            UNION
            SELECT manufacturer FROM devices_try  WHERE manufacturer) = ?
        ");
        $stmt->bind_param('ss', $newManufacturerLower, $newManufacturerLower);
        $stmt->execute();
        $exists = $stmt->get_result()->fetch_row();

        if ($exists) {
            echo '<div class="alert alert-danger">That manufacturer already exists.</div>';
        } else {
            $status = 'active';
            $insert = $db->prepare("INSERT INTO manufacturer_status (manufacturer, status) VALUES (?, ?)");
            $insert->bind_param('ss', $newManufacturer, $status);
            if ($insert->execute()) {
                echo '<div class="alert alert-success">Manufacturer added successfully!</div>';
            } else {
                echo '<div class="alert alert-danger">Database error: ' . $db->error . '</div>';
            }
        }
    }
}
?>

<form method="post">
  <div class="form-group">
    <label>Manufacturer Name</label>
    <input type="text" name="manufacturer" class="form-control" required>
  </div>
  <button type="submit" class="btn btn-primary">Add Manufacturer</button>
  <a href="index.php" class="btn btn-default">Cancel</a>
</form>

</body>
</html>
