<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once 'config.inc.php';
require_once 'include/utils/utils.php';
require_once 'include/events/include.inc';

global $adb;

echo "Registering handler...\n";

$em = new VTEventsManager($adb);

$em->registerHandler(
    'vtiger.entity.beforesave',
    'modules/Potentials/handlers/AutoLinkContact.php',
    'Potentials_AutoLinkContact_Handler'
);

echo "Handler registered successfully\n";