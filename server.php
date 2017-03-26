<!DOCTYPE HTML>
<head>
  <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
  <?php
    
    if(isset($_POST['x'],$_POST['y'],$_POST['direction'],$_POST['time'],$_POST['condition'],$_POST['volume'],$_POST['uid'])){

      $db = mysqli_connect('127.0.0.3', 'db388648_1', 'MMMIinfo', 'db388648_1');

      if ($db->connect_error) {
        die('Failed to connect: '.$db->connect_error);
      }

      $table;

      //Determine into which table to write
      if($_POST['condition'] == "a.") {
        $table = "ConditionA";
      } elseif ($_POST['condition'] == "b.") {
        $table = "ConditionB";
      }

      //uid, id, condition, xrot, yrot, direction, time, volume, timestamp
      $query = "INSERT INTO " . $table . " VALUES (
      \"" . $_POST['uid'] . "\",
      NULL,
      \"" . $_POST['condition'] . "\",
      " . $_POST['x'] . ", 
      " . $_POST['y'] . ",
      \"" . $_POST['direction'] . "\",
      " . $_POST['time'] . ",
      \"" . $_POST['volume'] . "\",
      NULL
      )";

      $result = $db->query($query);

      if (!$result) {
        die('INSERT failed: '.$db->error);
      } 

      $db->close();
    } 
  ?>
</body>
</html>