<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="initial-scale=1.0,user-scalable=no,maximum-scale=1,width=device-width">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <title>A-Frame Video Shader - Basic</title>
  <!-- <script src="js/build.js"></script> -->

  <script src="https://aframe.io/releases/0.5.0/aframe.min.js"></script>
  <!-- <script src="https://rawgit.com/mayognaise/aframe-video-shader/master/dist/aframe-vid-shader.min.js"></script> -->
  <script src="js/aframe-extras.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/nunjucks/2.3.0/nunjucks.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/handlebars.js/4.0.5/handlebars.min.js"></script>
  <script src="https://rawgit.com/ngokevin/aframe-template-component/master/dist/aframe-template-component.min.js"></script>
  <script src="js/aframe-particle-system-component.min.js"></script>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
  <link rel="stylesheet" type="text/css" href="css/common.css"/>
</head>
<body>
  <a-scene fog="type: linear; color: #000000; far: 30; near: 0" stats>

    <!-- assets -->
    <a-assets>
      <video muted id="video" src="assets/video/360_tiger_gradual.mp4" playsinline preload="auto"></video>

      <!-- $("#dog")[0].volume = 0.1 -->
      <audio id="dog" loop="true" src="assets/sounds/dog.mp3"></audio>

      <script id="targetBox" type="text/x-nunjucks-template">
        <a-entity template="src: templates/components/targetBox.template; type: handlebars" data-position="0 2.5 3" data-scale="0.3 0.3 0.3"></a-entity>
      </script>

      <script id="room" type="text/x-nunjucks-template">
        <a-entity template="src: templates/components/sphere.template; type: handlebars"
        data-video="#video"
        ></a-entity>
      </script>
    </a-assets>

    <!-- Kamera -->
    <a-entity position="0 2.5 0">
      <a-entity id="camera" camera look-controls wasd-controls>
        <a-cursor radius-outer="0.03" radius-inner="0.02" position="0 0 -0.5" material="color: cyan; shader: flat" max-distance="2">
          <a-animation begin="cursor-hovering" easing="ease-in" attribute="color" fill="forwards" from="cyan" to="#7ED321" dur="350"></a-animation>
        </a-cursor>
      </a-entity>
    </a-entity>

    <!-- light -->
    <!-- <a-entity light="color:#fff;intensity:1.5;type:ambient;"></a-entity> -->

    <!--Room Geometry-->
    <!-- <a-entity template="src: #room"></a-entity> -->
    <a-videosphere id="test" src="#video" material="shader: standard; fog: false;" radius="25" rotation="0 -90 0"></a-videosphere>

    <!-- play sound: $('#sound')[0].components.sound.playSound() -->
    <a-entity id="sound" sound="src: #dog"></a-entity>

    <!-- computer position 180 degree to camera -->
    <a-entity template="src: #targetBox"></a-entity>
  </a-scene>

  <div class="buttons novr">
    <a href="javascript:togglePlayback()">toggle playback</a>
  </div>

  <script src="js/common.js"></script>
  <script type="text/javascript">
    //generate user ID
    //var uuid = uuid.v1();
    var uid = hexCode();

    function hexCode(){
      //create Code
      var letters = '0123456789ABCDEF'.split('');
      var code = '';
      for (var i = 0; i < 6; i++ ) {
        code += letters[Math.round(Math.random() * 15)];
      }
      return code;
    }

    function togglePlayback () {
      var el = document.getElementById('videoSphere')
      var material = Object.assign({}, el.getAttribute('material'))
      material.pause = !material.pause
      el.setAttribute('material', material)
    }

    AFRAME.registerComponent('hover-listener', {
      init: function () {
        // a = x:0 y:-1
        // 180 + camera.rotation.y

        //video starts after 15s
        setTimeout(function(){
          var videoEntity = document.querySelector('#video');
          videoEntity.play();

          $("video")[0].muted = false;

          var oldY = 0;
          var startTime = Date.now();

          //create targetObject 180 degree behind camera focus point
          var alpha = (180 + $('#camera').attr('rotation').y) * Math.PI / 180.0;
          var x = Math.sin(alpha) * (-3);
          var z = Math.cos(alpha) * (-3);
          $('#target').attr('position', x + " 2.5 " + z);

          setInterval(function(){
            var camera = $('#camera').attr('rotation');
            var x = camera.x;
            var newY = camera.y;
            var volume = Math.floor(($('#video')[0].currentTime)/9);
            var direction;
            //to occur the exact time
            var elapsedTime = Date.now() - startTime;
            var videoTime = (elapsedTime / 1000).toFixed(3);

            if (newY > oldY) {
              direction = "l"; //left
            } else if (newY < oldY) {
              direction = "r"; //right
            } else {
              direction = "s"; //same
            }

            oldY = newY;
            console.log('x: ' + x + ' y: ' + newY + ' direction: ' + direction + ' videoTime: ' + videoTime + ' volume: ' + volume + ' uid: ' + uid);
            
            $.ajax({
              type: "POST",
              url: "agrad.php",
              data: "searchq=test",
              success: function()});
            }

            //console.log('x: ' + x + ' y: ' + newY + ' volume: ' + volume + ' direction: ' + direction);
          }, 300);

        }, 1000);

        this.el.addEventListener('raycaster-intersected', function(evt) {
          if (this.hoveron !== true) {
            this.emit('hoveron');
            this.hoveron = true;
            // $('#sound')[0].components.sound.playSound();
          }
        }, true);
        this.el.addEventListener('raycaster-intersected-cleared', function(evt) {
          this.emit('hoveroff');
          this.hoveron = false;
        }, true);
      }
    });
  </script>

  <?php
    if(isset($_POST['searchq'])){
      echo $_POST['searchq'];
    }

    /*$db = mysqli_connect('127.0.0.3', 'db388648_1', 'MMMIinfo', 'db388648_1');
    if ($db->connect_error) {
      die('Failed to connect: '.$db->connect_error);
    } else {
      echo "success <br>";
    }

    $xrotation = 10.0;
    $yrotation = 20.0;
    $direction = 'l';
    $time = 1.0;
    $volume = 1.0;
    $uid = 'a';

    $query = "INSERT INTO TestExperiments VALUES (
    NULL,
    " . $xrotation . ", 
    " . $yrotation . ",
    \"" . $direction . "\",
    " . $time . ",
    " . $volume . ", 
    NULL,
    \"" . $uid . "\")";

    echo ($query);
    $result = $db->query($query);

    if (!$result) {
      echo "failINSERT";
      die('INSERT failed: '.$db->error);
    } else {
      echo "successINSERT <br>";
    }*/

  ?>
</body>
</html>
