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

function getVolume(videoTime,videoDuration) {
  //for 15s training phase  
  var trainingDuration = 15;
  if(videoTime >= trainingDuration) {
    return ((videoTime-trainingDuration) / (videoDuration-trainingDuration)).toFixed(2);
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

//to be called on target init
AFRAME.registerComponent('hover-listener', {
  init: function () {

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

      //measure every 300ms
      setInterval(function(){
        var camera = $('#camera').attr('rotation');
        var x = camera.x;
        var newY = camera.y;
        var direction;
        var volume = getVolume($('#video')[0].currentTime, $('#video')[0].duration);
        //to occur the exact time
        var elapsedTime = Date.now() - startTime;
        var videoTime = (elapsedTime / 1000).toFixed(3);

        //to which direction did the camera turn since measured last time
        if (newY > oldY) {
          direction = "l"; //left
        } else if (newY < oldY) {
          direction = "r"; //right
        } else {
          direction = "s"; //same
        }

        oldY = newY;
        //console.log('x: ' + x + ' y: ' + newY + ' direction: ' + direction + ' videoTime: ' + videoTime + ' volume: ' + volume + ' uid: ' + uid);

        //send measurements to server.php
        $.ajax({
          url: './server.php',
          type: "POST",
          data: {x: x, y: newY, direction: direction, time: videoTime, volume: volume, uid: uid},
        });

      }, 300);

    }, 1000);

    this.el.addEventListener('raycaster-intersected', function(evt) {
      if (this.hoveron !== true) {
        this.emit('hoveron');
        this.hoveron = true;
      }
    }, true);
    this.el.addEventListener('raycaster-intersected-cleared', function(evt) {
      this.emit('hoveroff');
      this.hoveron = false;
    }, true);
  }
});