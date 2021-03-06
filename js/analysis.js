//generate user ID
var uid = hexCode();
var measuringLoop;

function hexCode(){
  //create Code
  var letters = '0123456789ABCDEF'.split('');
  var code = '';
  for (var i = 0; i < 6; i++ ) {
    code += letters[Math.round(Math.random() * 15)];
  }

  return code;
}

function getCondition(){
  var pathArray = window.location.pathname;
  var result = pathArray.charAt(pathArray.length-6) + pathArray.charAt(pathArray.length-5);
  return result.toString();
}

function getVolume(videoTime,videoDuration) {
  //1s until video starts -> 10s until trigger sound starts
  var trainingDuration = 10;
  if(videoTime >= trainingDuration) {
    // gradual volume change
    // return ((videoTime-trainingDuration) / (videoDuration-trainingDuration)).toFixed(2);

    // stepwise volume change every 4s
    var volumetmp = 1 + Math.floor((videoTime-trainingDuration)/4);

    //check experiment termination condition
    //stop measuring after 20s (4s * 5volume_categories)
    // if (volumetmp == 5 && Math.floor(((videoTime + 0.333)-trainingDuration)/4) > 5) {
    //   clearInterval(measuringLoop);
    // }

    if (volumetmp == 5 && (1 + Math.floor(((videoTime + 0.333)-trainingDuration)/4)) > 5) {
      clearInterval(measuringLoop);
    }
    console.log("Video time: " + videoTime + "Volume: " + volumetmp);

    return volumetmp;
  } else {
    return 0;
  }
}

//playback button listener
function togglePlayback () {
  var el = document.getElementById('videoSphere')
  var material = Object.assign({}, el.getAttribute('material'))
  material.pause = !material.pause
  el.setAttribute('material', material)
}

AFRAME.registerComponent('measurements', {
  init: function () {
    //video starts 1s start button hover
    setTimeout(function(){

      //remove uid after training stage
      $('#uid').remove();

      var videoEntity = document.querySelector('#video');
      videoEntity.play();

      $("video")[0].muted = false;

      var oldY = 0;
      var startTime = Date.now();

      //create targetObject
      $('a-scene').append('<a-entity id="target" template="src: #targetBox"></a-entity>');

      //set targetObject to 180 degree behind camera focus point
      var alpha = (180 + $('#camera').attr('rotation').y) * Math.PI / 180.0;
      var x = Math.sin(alpha) * (-3);

      // var z = Math.cos(alpha) * (-3);
      $('#target').attr('position', x + " 0 0");

      //measure every 300ms
      measuringLoop = setInterval(function(){
        var camera = $('#camera').attr('rotation');
        var x = camera.x;
        var newY = camera.y;
        var direction;
        var volume = getVolume($('#video')[0].currentTime, $('#video')[0].duration);
        //to occur the exact time
        var elapsedTime = Date.now() - startTime;
        //var videoTime = (elapsedTime / 1000).toFixed(3);
        var videoTime = $('#video')[0].currentTime;
        var condition = getCondition();

        //to which direction did the camera turn since measured last time
        if (newY > oldY) {
          direction = "l"; //left
        } else if (newY < oldY) {
          direction = "r"; //right
        } else {
          direction = "s"; //same
        }

        oldY = newY;
        // console.log('x: ' + x + ' y: ' + newY + ' direction: ' + direction + ' videoTime: ' + videoTime + ' condition: ' + condition + ' uid: ' + uid);
        console.log("time: " + videoTime + " v: " + volume);

        //send measurements to server.php
        $.ajax({
          url: './server.php',
          type: "POST",
          data: {x: x, y: newY, direction: direction, time: videoTime, condition: condition, volume: volume, uid: uid},
        });

        //terminate measuring process
        if($('#video')[0].currentTime >= $('#video')[0].duration) {
          clearInterval(measuringLoop);
        }

      }, 333);

    }, 1000);
  }
});

//to be called on target init
AFRAME.registerComponent('hover-listener', {
  init: function () {
    this.el.addEventListener('raycaster-intersected', function(evt) {
      if (this.hoveron !== true) {
        this.emit('hoveron');
        this.hoveron = true;

        //stop to measure when target object found by participant
        clearInterval(measuringLoop);
        $('video').get(0).pause();
      }
    }, true);
  }
});

AFRAME.registerComponent('init-hover', {
  init: function () {
    //print uid to screen
    $('a-scene').append('<a-entity id="uid" text="value:' + uid + '" position="0.6 2.5 -1" scale="1.5 1.5 1.5"></a-entity>');

    //init buffering
    $('video').get(0).load();
    $('video').get(0).pause();

    this.el.addEventListener('raycaster-intersected', function(evt) {
      if (this.inithoveron !== true) {
        this.emit('inithoveron');
        this.inithoveron = true;

        //start scene if buffered
        if ($('video').get(0).readyState == 4) {
          $('a-scene').append('<a-entity id="measure" measurements></a-entity>');
          $('#initLink').remove();
          $('#startText').remove();
        } else {
          $('#startText').get(0).setAttribute('text', 'value:still loading...;color:black;');
          $('#startText').get(0).setAttribute('position','2.45 1.04 -1.83');
        }
      }
    }, true);
    this.el.addEventListener('raycaster-intersected-cleared', function(evt) {
      this.emit('inithoveroff');
      this.inithoveron = false;
      $('#startText').get(0).setAttribute('text', 'value:start;color:black;');
      $('#startText').get(0).setAttribute('position','2.8 1.04 -1.83');
    }, true);
  }
});
