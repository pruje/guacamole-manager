<?php
if (!isset($_GET['c']) || $_GET['c'] == '') {
    http_response_code(404);
    echo '404 not found';
    die();
}

$connection = Connection::getByName(htmlspecialchars($_GET['c']));
if (!$connection) {
    http_response_code(404);
    echo 'Connection not found';
    die();
}

// redirect
header("Location: ".GUACAMOLE_URL."/guacamole/#/client/".base64_encode($connection->id."\0c\0".DATABASE));
