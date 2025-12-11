<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

//Same as Accounts Detail View
class Contacts_DetailView_Model extends Accounts_DetailView_Model
{
    /**
     * Function to get the detail view links (links and widgets)
     * @param <array> $linkParams - parameters which will be used to calicaulate the params
     * @return <array> - array of link models in the format as below
     *                   array('linktype'=>list of link models);
     */
    public function getDetailViewLinks($linkParams)
    {

        $moduleModel = $this->getModule();
        $recordModel = $this->getRecord();
        $moduleName = $moduleModel->getName();
        $recordId = $recordModel->getId();  

        $linkModelList = parent::getDetailViewLinks($linkParams);

        if (Users_Privileges_Model::isPermitted($moduleName, 'SaleOrderView', $recordId)) {
            $basicActionLink = array(
                'linktype' => 'DETAILVIEWBASIC',
                'linklabel' => 'Create SO',
                'linkurl' => 'index.php?module=' . $moduleName . '&view=SaleOrderView&record=' . $recordId,
                'linkicon' => '',
                'linktarget' => '_blank',
            );
            $linkModelList['DETAILVIEW'][] = Vtiger_Link_Model::getInstanceFromValues($basicActionLink);
        }

        if (Users_Privileges_Model::isPermitted($moduleName, 'ViewPO', $recordId)) {
            $basicActionLink = array(
                'linktype' => 'DETAILVIEWBASIC',
                'linklabel' => 'Create PO',
                'linkurl' => 'index.php?module=' . $moduleName . '&view=ViewPO&record=' . $recordId,
                'linkicon' => '',
                'linktarget' => '_blank',
            );
            $linkModelList['DETAILVIEW'][] = Vtiger_Link_Model::getInstanceFromValues($basicActionLink);
        }

        if (Users_Privileges_Model::isPermitted($moduleName, 'ViewSTO', $recordId)) {
            $basicActionLink = array(
                'linktype' => 'DETAILVIEWBASIC',
                'linklabel' => 'Create STO',
                'linkurl' => 'index.php?module=' . $moduleName . '&view=ViewSTO&record=' . $recordId,
                'linkicon' => '',
                'linktarget' => '_blank',
            );
            $linkModelList['DETAILVIEW'][] = Vtiger_Link_Model::getInstanceFromValues($basicActionLink);
        }

        if (Users_Privileges_Model::isPermitted($moduleName, 'ViewCR', $recordId)) {
            $basicActionLink = array(
                'linktype' => 'DETAILVIEWBASIC',
                'linklabel' => 'Create CR',
                'linkurl' => 'index.php?module=' . $moduleName . '&view=ViewCR&record=' . $recordId,
                'linkicon' => '',
                'linktarget' => '_blank',
            );
            $linkModelList['DETAILVIEW'][] = Vtiger_Link_Model::getInstanceFromValues($basicActionLink);
        }

        return $linkModelList;
    }
}
