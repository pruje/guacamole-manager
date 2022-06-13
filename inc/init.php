<?php

// load config file
if (!file_exists("config.php")) {
    Logger::fatal("Config file not found! Please create it");
    exit(1);
}
require_once('config.php');

// load utils
require_once('logger.php');

// check database type (security)
if (!preg_match('/^\w+$/', DATABASE)) {
    Logger::fatal("bad database type: ".DATABASE);
    exit(1);
}

// load main database class
require_once('inc/database/database.php');

// load internal database class if exists
if (file_exists("inc/database/".DATABASE.".php")) {
    require_once("inc/database/".DATABASE.".php");
} else {
    Logger::fatal("unknown database type: ".DATABASE);
    exit(1);
}

// load classes
require_once('classes/Connection.php');
require_once('classes/ConnectionGroup.php');
require_once('classes/ConnectionTemplate.php');
require_once('classes/UserGroup.php');
