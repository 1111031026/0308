<!DOCTYPE html>
<html lang="zh-TW">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SDGs Vanta BIRDS Background</title>
  <style>
    body { margin: 0; overflow: hidden; }
    #vanta-bg { width: 100%; height: 100vh; position: absolute; top: 0; left: 0; z-index: -1; }
    .content {
      position: relative;
      z-index: 1;
      text-align: center;
      padding: 50px;
      background: rgba(255, 255, 255, 0.85);
      margin: 100px auto;
      max-width: 600px;
      border-radius: 10px;
    }
  </style>
</head>
<body>
  <div id="vanta-bg"></div>
  <div class="content">
    <h1>永續發展教育平台</h1>
    <p>探索 SDGs，保護我們的地球！</p>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/three@0.128.0/build/three.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/vanta@latest/dist/vanta.birds.min.js"></script>
  <script>
    VANTA.BIRDS({
      el: "#vanta-bg",
      mouseControls: true,
      touchControls: true,
      gyroControls: false,
      minHeight: 200.00,
      minWidth: 200.00,
      scale: 1.00,
      scaleMobile: 1.00,
      backgroundColor: 0x7192f,
      color1: 0x4a00ff,
      color2: 0x95d0e6,
      birdSize: 1.5,
      quantity: 3,
      speedLimit: 5
    });
  </script>
</body>
</html>