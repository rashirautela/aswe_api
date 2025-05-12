<?php
header('Content-Type: application/json; charset=utf-8');

//–– 1) DB connection helper ––
function get_db(){
    $db = new mysqli('localhost','web_user','*k8WZ!kK.zlgdo(0)','equipment');
    if($db->connect_error) die(json_encode(['error'=>"DB Connect Error: ".$db->connect_error]));
    return $db;
}
$db = get_db();

//–– 2) Read DataTables parameters ––
$draw   = isset($_POST['draw'])   ? intval($_POST['draw'])   : 0;
$start  = isset($_POST['start'])  ? intval($_POST['start'])  : 0;
$length = isset($_POST['length']) ? intval($_POST['length']) : 10;

//–– 3) Read your custom filters ––
$type     = $_POST['type'] ?? '';
$postData = $_POST['postData'] ?? [];

//–– 4) Define active/inactive conditions ––
$activeDev  = 'd.auto_id NOT IN (SELECT device_id FROM device_status)';
$activeManu = "( d.manufacturer NOT IN (SELECT manufacturer FROM manufacturer_status)
                 OR
                 d.manufacturer IN (SELECT manufacturer FROM manufacturer_status WHERE status = 'active') )";
$activeType = "( d.device_type NOT IN (SELECT device_type FROM device_type_status)
                 OR
                 d.device_type IN (SELECT device_type FROM device_type_status WHERE status = 'active') )";

//–– 5) Build WHERE clauses based on search type ––
$whereClauses = [];

switch($type){
  case 'serialNum':
    $sn = $db->real_escape_string($postData['serial_number'] ?? '');
    $whereClauses[] = "d.serial_number = '$sn'";
    $whereClauses[] = $activeDev;
    break;

  case 'all':
    $status = $postData['status'] ?? 'all';
    if($status === 'active'){
      $whereClauses[] = "$activeDev AND $activeManu AND $activeType";
    }
    elseif($status === 'inactive'){
      $whereClauses[] = "( d.auto_id IN (SELECT device_id FROM device_status)
                           OR d.manufacturer IN (SELECT manufacturer FROM manufacturer_status WHERE status='inactive')
                           OR d.device_type IN (SELECT device_type FROM device_type_status WHERE status='inactive') )";
    }
    // else 'all' => no where clause
    break;

  case 'manufacturer':
    $man = str_replace('_',' ',$postData['manufacturer'] ?? '');
    if($man !== 'all'){
      $man = $db->real_escape_string($man);
      $whereClauses[] = "d.manufacturer = '$man'";
    }
    $whereClauses[] = $activeDev;
    $whereClauses[] = $activeManu;
    break;

  default:
    // by device type (and manufacturer)
    $dev = str_replace('_',' ',$postData['device'] ?? '');
    if($dev !== 'all'){
      $dev = $db->real_escape_string($dev);
      $whereClauses[] = "d.device_type = '$dev'";
    }
    $man = str_replace('_',' ',$postData['manufacturer'] ?? '');
    if($man !== 'all'){
      $man = $db->real_escape_string($man);
      $whereClauses[] = "d.manufacturer = '$man'";
    }
    $whereClauses[] = $activeDev;
    $whereClauses[] = $activeManu;
    $whereClauses[] = $activeType;
}

// Assemble WHERE string
$where = '';
if(count($whereClauses) > 0){
  $where = 'WHERE ' . implode(' AND ', $whereClauses);
}

//–– 6) Get total record counts ––
$totalResult    = $db->query("SELECT COUNT(*) AS cnt FROM devices_try d");
$totalRecords   = $totalResult->fetch_assoc()['cnt'];
$filteredResult = $db->query("SELECT COUNT(*) AS cnt FROM devices_try d $where");
$filteredCount  = $filteredResult->fetch_assoc()['cnt'];

//–– 7) Fetch the actual page of data ––
$limitOffset = "LIMIT $start, $length";
$sql = "
  SELECT d.auto_id,
         d.device_type,
         d.manufacturer,
         d.serial_number
  FROM devices_try d
  $where
  $limitOffset
";
$res = $db->query($sql) or die(json_encode(['error'=>$db->error]));

$data = [];
while($row = $res->fetch_assoc()){
  $data[] = [
    'device_type'   => $row['device_type'],
    'manufacturer'  => $row['manufacturer'],
    'serial_number' => $row['serial_number'],
    'action'        => '<a class="btn btn-success" href="view.php?eid='.$row['auto_id'].'">View</a>',
  ];
}

//–– 8) Return JSON in DataTables format ––
echo json_encode([
  'draw'            => $draw,
  'recordsTotal'    => intval($totalRecords),
  'recordsFiltered' => intval($filteredCount),
  'data'            => $data
]);
