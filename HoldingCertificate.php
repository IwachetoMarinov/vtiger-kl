<?php

$Vtiger_Utils_Log = true;

require_once __DIR__ . '/vendor/autoload.php';
include_once 'vtlib/Vtiger/Menu.php';
include_once 'vtlib/Vtiger/Module.php';
include_once 'include/utils/utils.php';

/**
 * CREATE OR UPDATE MODULE
 */
$module = Vtiger_Module::getInstance('HoldingCertificate');

if (!$module) {
    $module = new Vtiger_Module();
    $module->name = 'HoldingCertificate';
    $module->label = 'Holding Certificate';
    $module->parent = 'Tools';

    $module->save();
    $module->initTables();       // Creates vtiger_holdingcertificate + cf table
    $module->initWebservice();   // Adds Webservice entry
}

/**
 * BLOCKS
 */
$infoBlock = Vtiger_Block::getInstance('Certificate Details', $module);
if (!$infoBlock) {
    $infoBlock = new Vtiger_Block();
    $infoBlock->label = 'Certificate Details';
    $module->addBlock($infoBlock);
}

$descriptionBlock = Vtiger_Block::getInstance('LBL_DESCRIPTION_INFORMATION', $module);
if (!$descriptionBlock) {
    $descriptionBlock = new Vtiger_Block();
    $descriptionBlock->label = 'LBL_DESCRIPTION_INFORMATION';
    $module->addBlock($descriptionBlock);
}

/**
 * FIELDS
 */

/* ------- GUID (Entity Label) ------- */
$guidField = Vtiger_Field::getInstance('guid', $module);
if (!$guidField) {
    $guidField = new Vtiger_Field();
    $guidField->name = 'guid';
    $guidField->label = 'GUID';
    $guidField->table = $module->basetable;
    $guidField->column = 'guid';
    $guidField->columntype = 'VARCHAR(128)';
    $guidField->uitype = 1;
    $guidField->typeofdata = 'V~O';

    $infoBlock->addField($guidField);

    // Set as display label (entity name column)
    $module->setEntityIdentifier($guidField);
}

/* ------- Related To (Contacts) ------- */
$relatedToField = Vtiger_Field::getInstance('contact_id', $module);
if (!$relatedToField) {
    $relatedToField = new Vtiger_Field();
    $relatedToField->name = 'contact_id';
    $relatedToField->label = 'Related To';
    $relatedToField->table = $module->basetable;
    $relatedToField->column = 'contact_id';
    $relatedToField->columntype = 'INT(11)';
    $relatedToField->uitype = 10;
    $relatedToField->typeofdata = 'V~M';

    $infoBlock->addField($relatedToField);
    $relatedToField->setRelatedModules(['Contacts']);
}

/* ------- Certificate (Document) ------- */
$certificateField = Vtiger_Field::getInstance('notes_id', $module);
if (!$certificateField) {
    $certificateField = new Vtiger_Field();
    $certificateField->name = 'notes_id';
    $certificateField->label = 'Certificate';
    $certificateField->table = $module->basetable;
    $certificateField->column = 'notes_id';
    $certificateField->columntype = 'INT(11)';
    $certificateField->uitype = 10;
    $certificateField->typeofdata = 'V~O';

    $infoBlock->addField($certificateField);
    $certificateField->setRelatedModules(['Documents']);
}

/* ------- Certificate Hash ------- */
$hashField = Vtiger_Field::getInstance('certificate_hash', $module);
if (!$hashField) {
    $hashField = new Vtiger_Field();
    $hashField->name = 'certificate_hash';
    $hashField->label = 'Certificate Hash (SHA256)';
    $hashField->table = $module->basetable;
    $hashField->column = 'certificate_hash';
    $hashField->columntype = 'VARCHAR(128)';
    $hashField->uitype = 1;
    $hashField->typeofdata = 'V~O';

    $infoBlock->addField($hashField);
}

/* ------- Verification URL ------- */
$urlField = Vtiger_Field::getInstance('verify_url', $module);
if (!$urlField) {
    $urlField = new Vtiger_Field();
    $urlField->name = 'verify_url';
    $urlField->label = 'Verify URL';
    $urlField->table = $module->basetable;
    $urlField->column = 'verify_url';
    $urlField->columntype = 'VARCHAR(256)';
    $urlField->uitype = 17; // URL
    $urlField->typeofdata = 'V~O';

    $infoBlock->addField($urlField);
}

/* ------- Status Picklist ------- */
$certificateStatusField = Vtiger_Field::getInstance('certificate_status', $module);
if (!$certificateStatusField) {
    $certificateStatusField = new Vtiger_Field();
    $certificateStatusField->name = 'certificate_status';
    $certificateStatusField->label = 'Status';
    $certificateStatusField->column = 'certificate_status';
    $certificateStatusField->columntype = 'VARCHAR(100)';
    $certificateStatusField->uitype = 16; // Picklist
    $certificateStatusField->typeofdata = 'V~M';

    $infoBlock->addField($certificateStatusField);
    $certificateStatusField->setPicklistValues(['Active', 'Inactive']);
}

/* ------- Assigned User ------- */
$assignedToField = Vtiger_Field::getInstance('assigned_user_id', $module);
if (!$assignedToField) {
    $assignedToField = new Vtiger_Field();
    $assignedToField->name = 'assigned_user_id';
    $assignedToField->label = 'Assigned To';
    $assignedToField->table = 'vtiger_crmentity';
    $assignedToField->column = 'smownerid';
    $assignedToField->uitype = 53;
    $assignedToField->typeofdata = 'V~M';

    $infoBlock->addField($assignedToField);
}

/* ------- Created Time ------- */
$createdTimeField = Vtiger_Field::getInstance('createdtime', $module);
if (!$createdTimeField) {
    $createdTimeField = new Vtiger_Field();
    $createdTimeField->name = 'createdtime';
    $createdTimeField->label = 'Created Time';
    $createdTimeField->table = 'vtiger_crmentity';
    $createdTimeField->column = 'createdtime';
    $createdTimeField->uitype = 70;
    $createdTimeField->typeofdata = 'T~O';
    $createdTimeField->displaytype = 2;

    $infoBlock->addField($createdTimeField);
}

/* ------- Modified Time ------- */
$modifiedTimeField = Vtiger_Field::getInstance('modifiedtime', $module);
if (!$modifiedTimeField) {
    $modifiedTimeField = new Vtiger_Field();
    $modifiedTimeField->name = 'modifiedtime';
    $modifiedTimeField->label = 'Modified Time';
    $modifiedTimeField->table = 'vtiger_crmentity';
    $modifiedTimeField->column = 'modifiedtime';
    $modifiedTimeField->uitype = 70;
    $modifiedTimeField->typeofdata = 'T~O';
    $modifiedTimeField->displaytype = 2;

    $infoBlock->addField($modifiedTimeField);
}

/* ------- Description (long text) ------- */
$descriptionField = Vtiger_Field::getInstance('description', $module);
if (!$descriptionField) {
    $descriptionField = new Vtiger_Field();
    $descriptionField->name = 'description';
    $descriptionField->label = 'Certificate Data';
    $descriptionField->table = 'vtiger_crmentity';
    $descriptionField->column = 'description';
    $descriptionField->uitype = 19; // Textarea
    $descriptionField->typeofdata = 'V~O';

    $descriptionBlock->addField($descriptionField);
}

/**
 * LIST VIEW FILTER
 */
$allFilter = Vtiger_Filter::getInstance('All', $module);

if (!$allFilter) {
    $allFilter = new Vtiger_Filter();
    $allFilter->name = 'All';
    $allFilter->isdefault = true;
    $module->addFilter($allFilter);

    $allFilter->addField($guidField, 1)
        ->addField($relatedToField, 2)
        ->addField($certificateField, 3)
        ->addField($certificateStatusField, 4)
        ->addField($assignedToField, 5)
        ->addField($createdTimeField, 6);
}

/**
 * APP REGISTRATION
 */
$adb->pquery(
    'DELETE FROM vtiger_app2tab WHERE tabid = ?',
    [getTabid('HoldingCertificate')]
);

$adb->pquery(
    'INSERT INTO vtiger_app2tab(tabid, appname, sequence, visible) VALUES(?,?,?,?)',
    [getTabid('HoldingCertificate'), 'TOOLS', 10, 1]
);

echo "Module HoldingCertificate created successfully.\n";


// $result = $adb->pquery("select * from vtiger_cron_task where module = ?", array('HoldingCertificate'));

// echo "Creating a cron Service. \n";
// if ($adb->num_rows($result)) {
//     echo "Certificate Intgrety cheking Cron already exist,\n";
// } else {
//     $sql = "INSERT INTO vtiger_cron_task (name,handler_file,frequency,laststart,lastend,status,module,sequence,description) VALUES(?,?,?,?,?,?,?,?,?)";
//     $parama = array(
//         'Holding Certificate',
//         'cron/modules/HoldingCertificate/certificateIntegrityChecker.service',
//         '600',
//         NULL,
//         NULL,
//         1,
//         'HoldingCertificate',
//         9,
//         'Recommended frequency for Certificate Integrity Checker is 1 day'
//     );
//     $adb->pquery($sql, $parama);
// }
