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
  <div>Hello</div>
  <div class="container">
    <div class="navbar-header">
      <button class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
        <span class="icon icon-bar"></span>
        <span class="icon icon-bar"></span>
        <span class="icon icon-bar"></span>
      </button>
      <a href="#" class="navbar-brand">Add New Equipment</a>
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
        function get_db($db){
          $conn = new mysqli("localhost","web_user","*k8WZ!kK.zlgdo(0",$db);
          if($conn->connect_error){
            die("Database error: " . $conn->connect_error);
          }
          return $conn;
        }

        $db = get_db("equipment");

        // Handle form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
          $type = $db->real_escape_string(str_replace('_', ' ', $_POST['device']));
          $manu = $db->real_escape_string(str_replace('_', ' ', $_POST['manufacturer']));
          $serial = trim($_POST['serialnumber']);

          //valid structure
          if (!preg_match('/^SN-[0-9a-f]{64}$/i', $serial)) {
            echo '<div class="alert alert-danger">Serial number must match SN-xxxxx format (0-9a-f).</div>';
          } else {
            $check = $db->prepare("SELECT 1 FROM devices_try  WHERE serial_number = ?");
            $check->bind_param('s', $serial);
            $check->execute();
            $checkResult = $check->get_result();

            //duplication
            if ($checkResult->num_rows > 0) {
              echo '<div class="alert alert-danger">Serial number already exists.</div>';
            } else {
              $stmt = $db->prepare("INSERT INTO devices_try  (device_type, manufacturer, serial_number) VALUES (?, ?, ?)");
              $stmt->bind_param('sss', $type, $manu, $serial);
              if ($stmt->execute()) {
                echo '<div class="alert alert-success">Equipment added successfully!</div>';
              } else {
                echo '<div class="alert alert-danger">Error adding equipment.</div>';
              }
            }
          }
        }

        // Fetch active device types
        $types = [];
        $res = $db->query("SELECT DISTINCT device_type FROM devices_try  WHERE device_type NOT IN (SELECT device_type FROM device_type_status) UNION (SELECT device_type FROM device_type_status WHERE status = 'active')");
        while ($row = $res->fetch_assoc()) {
          $types[] = $row['device_type'];
        }

        // Fetch active manufacturers
        $mans = [];
        $res = $db->query("SELECT DISTINCT manufacturer FROM devices_try  WHERE manufacturer NOT IN (SELECT manufacturer FROM manufacturer_status) UNION (SELECT manufacturer FROM manufacturer_status WHERE status = 'active')");
        while ($row = $res->fetch_assoc()) {
          $mans[] = $row['manufacturer'];
        }

        // Form
        echo '<h2>Add New Equipment</h2>';
        echo '<form method="post">';
        echo '<div class="form-group">';
        echo '<label>Device Type:</label>';
        echo '<select class="form-control" name="device">';
        foreach($types as $type){
          $val = str_replace(' ', '_', $type);
          echo '<option value="'.$val.'">'.htmlspecialchars($type).'</option>';
        }
        echo '</select></div>';

        echo '<div class="form-group">';
        echo '<label>Manufacturer:</label>';
        echo '<select class="form-control" name="manufacturer">';
        foreach($mans as $man){
          $val = str_replace(' ', '_', $man);
          echo '<option value="'.$val.'">'.htmlspecialchars($man).'</option>';
        }
        echo '</select></div>';

        echo '<div class="form-group">';
        echo '<label>Serial Number:</label>';
        echo '<input type="text" class="form-control" name="serialnumber">';
        echo '</div>';

        echo '<button type="submit" class="btn btn-primary">Add Equipment</button>';
        echo '</form>';
        ?>
      </div>
    </div>
  </div>
</section>

</body>
</html>
