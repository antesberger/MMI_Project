<!DOCTYPE HTML>
<head>
  <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>

  <?php
    if(isset($_POST['x'],$_POST['y'],$_POST['direction'],$_POST['time'],$_POST['volume'],$_POST['uid'])){

      //update
      // $db = mysqli_connect('127.0.0.3', 'db388648_1', 'MMMIinfo', 'db388648_1');
      // if ($db->connect_error) {
      //   die('Failed to connect: '.$db->connect_error);
      // }

      // $query = "INSERT INTO TestExperiments VALUES (
      // NULL,
      // " . $_POST['x'] . ", 
      // " . $_POST['y'] . ",
      // \"" . $_POST['direction'] . "\",
      // " . $_POST['time'] . ",
      // " . $_POST['volume'] . ", 
      // NULL,
      // \"" . $_POST['uid'] . "\")";

      // $result = $db->query($query);

      // if (!$result) {
      //   die('INSERT failed: '.$db->error);
      // } 

      // $db->close();
    } else {
      //insert row
    }
  ?>
</body>
</html>