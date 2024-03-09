<?php
    // routes definition: route => Page menu title
    $routes = [
        'home' => 'Home',
        'import' => 'Connections import',
        'wakeonlan' => 'Wake on LAN',
        'help' => 'Help',
    ];

    // remove URL prefix
    $route = substr(htmlspecialchars($_SERVER['REQUEST_URI']), 1);

    if ($route == '') {
        $route = 'home';
    }

    // remove subpath
    $route = preg_replace('/\/.*/', '', $route);
    // remove GET parameters
    $route = preg_replace('/\?.*/', '', $route);

    if (file_exists("views/$route.php")) {
        if (!isset($routes[$route])) {
            require_once("views/$route.php");
            die();
        }
    } else {
        http_response_code(404);
        echo '404 not found';
        die();
    }
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <link rel="icon" href="favicon.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-0evHe/X+R7YkIZDRvuzKMRqM+OrBnVFBL6DOitfPri4tjfHxaWutUpFmBp4vmVor" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/assets/css/style.css"/>
    <title>Guacamole manager</title>
  </head>
  <body>
    <nav class="navbar navbar-expand-lg bg-light">
      <div class="container-fluid">
        <a class="navbar-brand" href="#">Guacamole manager</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
          <ul class="navbar-nav">
            <?php
                foreach ($routes as $path => $title) {
                    if ($title == '')
                        continue;
                    echo '<li class="nav-item">';
                    echo '<a class="nav-link';
                    if ($path == $route)
                        echo ' active';
                    echo '" aria-current="page" href="'.$path.'">'.$title.'</a>';
                    echo '</li>';
                }
            ?>
          </ul>
        </div>
      </div>
    </nav>
    <div class="container">
      <?php require_once("views/$route.php") ?>
    </div>
  </body>
</html>
