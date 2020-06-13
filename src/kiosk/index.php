<?php

require '../Include/Config.php';
//require '../Include/Functions.php';

// This file is generated by Composer
require_once dirname(__FILE__) . '/../vendor/autoload.php';

use ChurchCRM\dto\SystemConfig;
use \ChurchCRM\Utils\MiscUtils;

// Instantiate the app
//$settings = require __DIR__ . '/settings.php';
$app = new \Slim\App();
$container = $app->getContainer();

// Set up
require __DIR__ . '/../Include/slim/error-handler.php';
$settings = require __DIR__ . '/../Include/slim/settings.php';

// routes
require __DIR__ . '/routes/kiosk.php';

$windowOpen = new DateTime(SystemConfig::getValue("sKioskVisibilityTimestamp")) > new DateTime();

if (isset($_COOKIE['kioskCookie'])) {
    $g = hash('sha256', $_COOKIE['kioskCookie']);
    $Kiosk =  \ChurchCRM\Base\KioskDeviceQuery::create()
          ->findOneByGUIDHash($g);
    if (is_null($Kiosk)) {
        setcookie(kioskCookie, '', time() - 3600);
        header('Location: '.$_SERVER['REQUEST_URI']);
    }
}

if (!isset($_COOKIE['kioskCookie'])) {
    if ($windowOpen) {
        $guid = uniqid();
        setcookie("kioskCookie", $guid, 2147483647);
        $Kiosk = new \ChurchCRM\KioskDevice();
        $Kiosk->setGUIDHash(hash('sha256', $guid));
        $Kiosk->setAccepted($false);
        $Kiosk->save();
    } else {
        header("HTTP/1.1 401 Unauthorized");
        exit;
    }
}
$app->kiosk = $Kiosk;

// Run app
$app->run();
