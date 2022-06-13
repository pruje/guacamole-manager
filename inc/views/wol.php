<?php
// remove URL prefix
$name = substr(htmlspecialchars($_SERVER['REQUEST_URI']), 5);

$connection = Connection::getByName($name);
if (!$connection) {
    echo 'Connection not found';
    die();
}

$connection-> wakeUp(7);
$connection-> wakeUp();

echo "WOL sent";
