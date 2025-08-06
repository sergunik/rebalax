<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="favicon.ico">
    <title>{{ config('app.name') }}</title>
    <style>
        html, body {
            margin: 0;
            padding: 0;
            height: 100%;
            width: 100%;
            overflow: hidden;
            font-family: NationalWeb, Helvetica, Arial, sans-serif;
            color: rgb(33, 37, 41);
        }

        #trianglify-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
        }

        .project-window {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: rgba(255, 255, 255, 0.9);
            padding: 30px 50px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            text-align: center;
            z-index: 2;
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .project-title {
            font-size: 2.5rem;
            color: #333;
            margin: 0;
            font-weight: 700;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
        }

        .project-subtitle {
            font-size: 1.2rem;
            color: #666;
            margin-top: 10px;
        }
    </style>
</head>
<body>
<div id="trianglify-container"></div>

<div class="project-window glass">
    <h1 class="project-title">{{ config('app.name', 'Laravel') }}</h1>
    <p class="project-subtitle">Automated portfolio experiments to discover what works</p>
</div>

<script src='https://unpkg.com/trianglify@^4/dist/trianglify.bundle.js'></script>
<script>
    const pattern = trianglify({
      seed: 'pears',
      cellSize: 55,
      width: window.innerWidth,
      height: window.innerHeight,
      colorFunction: trianglify.colorFunctions.shadows(),
    })

    document.getElementById('trianglify-container').appendChild(pattern.toCanvas())
</script>
</body>
</html>
