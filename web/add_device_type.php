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
      <a href="#" class="navbar-brand">Add New Device Type</a>
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
        $hostname = "localhost";
        $username = "web_user";
        $password = "*k8WZ!kK.zlgdo(0";
        $dblink = new mysqli($hostname,$username,$password,$db);
        if ($dblink->connect_error){
            die("Connection failed: " . $dblink->connect_error);
        }
        return $dblink;
    }

    if (isset($_POST['submit'])) {
        $newType = trim($_POST['device_type']);
        $dblink = get_db("equipment");

        // Validation: Only letters allowed
        if (!preg_match('/^[a-zA-Z]+$/', $newType)) {
            echo '<div class="alert alert-danger">Error: Only letters are allowed in device type names.</div>';
        } else {
            $escapedType = $dblink->real_escape_string($newType);

            // Check if it already exists in devices_try  or device_type_status
            $sqlCheck = "
                SELECT device_type FROM devices_try  WHERE device_type = '$escapedType'
                UNION
                SELECT device_type FROM device_type_status WHERE device_type = '$escapedType'
            ";
            $result = $dblink->query($sqlCheck);

            if ($result->num_rows > 0) {
                echo '<div class="alert alert-danger">Error: This device type already exists.</div>';
            } else {
                // Insert into device_type_status with active status
                $sqlInsert = "INSERT INTO device_type_status (device_type, status) VALUES ('$escapedType', 'active')";
                if ($dblink->query($sqlInsert)) {
                    echo '<div class="alert alert-success">Success! New device type added.</div>';
                } else {
                    echo '<div class="alert alert-danger">Database error: '.$dblink->error.'</div>';
                }
            }
        }
    }
    ?>

    <form method="post" action="">
        <div class="form-group">
            <label for="device_type">Device Type Name:</label>
            <input type="text" class="form-control" name="device_type" required>
        </div>
        <button type="submit" name="submit" class="btn btn-primary">Add Device Type</button>
    </form>
</div>
</body>
</html>
