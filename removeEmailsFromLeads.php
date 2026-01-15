<?php
/**
 * Remove Emails related list from Leads module
 * Safe to run once
 */
require_once __DIR__ . '/vendor/autoload.php';
require_once('vtlib/Vtiger/Module.php');

$module = Vtiger_Module::getInstance('Leads');
$emailModule = Vtiger_Module::getInstance('Emails');

if ($module && $emailModule) {
    $module->unsetRelatedList($emailModule, 'Emails', 'get_emails');
    echo "✅ Emails related list successfully removed from Leads module.";
} else {
    echo "❌ Failed: Leads or Emails module not found.";
}
