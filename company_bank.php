<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

/* KEEP THIS */
require_once __DIR__ . '/vendor/autoload.php';

/* THIS IS THE KEY LINE */
define('VTIGER_ENTRY_POINT', true);

/* VTIGER BOOTSTRAP */
require_once 'includes/main/WebUI.php';

/* DB NOW EXISTS */
global $db;
$db = PearDatabase::getInstance();

echo "<h1>company_bank.php running</h1>";

$companyTabId = 54; // GPMCompany
$bankTabId    = 55; // BankAccount


/* ---------- CLEAN OLD RELATIONS ---------- */
$db->pquery(
    'DELETE FROM vtiger_relatedlists
     WHERE tabid IN (?, ?) OR related_tabid IN (?, ?)',
    [$companyTabId, $bankTabId, $companyTabId, $bankTabId]
);

echo "<p>✔ Old related lists removed</p>";

/* ---------- COMPANY → BANKACCOUNT ---------- */
$db->pquery("
    INSERT INTO vtiger_relatedlists (
        tabid,
        related_tabid,
        name,
        sequence,
        label,
        presence,
        actions,
        relationfieldid,
        relationtype
    ) VALUES (
        54, 55,
        'get_related_list',
        1,
        'Bank Accounts',
        0,
        'ADD,SELECT',
        0,
        ''
    )
");

echo "<p>✔ Company → BankAccount registered</p>";

/* ---------- BANKACCOUNT → COMPANY ---------- */
$db->pquery("
    INSERT INTO vtiger_relatedlists (
        tabid,
        related_tabid,
        name,
        sequence,
        label,
        presence,
        actions,
        relationfieldid,
        relationtype
    ) VALUES (
        55, 54,
        'get_related_list',
        1,
        'Companies',
        0,
        'SELECT',
        0,
        ''
    )
");

echo "<p>✔ BankAccount → Company registered</p>";
