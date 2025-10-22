<?php
class Metals_Detail_View extends Vtiger_Detail_View
{

    public function process(Vtiger_Request $request)
    {
        $mode = $request->get('mode');

        // 1) Let vTiger handle related lists (Documents, etc.)
        if ($mode === 'showRelatedList') {
            parent::process($request);
            return;
        }

        // Load record only when rendering Summary/Details
        $viewer = $this->getViewer($request);
        $recordId = $request->get('record');
        $recordModel = Vtiger_Record_Model::getInstanceById($recordId, 'Metals');

        // Provide fields to templates
        $viewer->assign('RECORD', $recordModel);
        $viewer->assign('NAME', $recordModel->get('name'));
        $viewer->assign('FINEOZ', $recordModel->get('fineoz'));
        $viewer->assign('TYPE', $recordModel->get('metal_type'));
        $viewer->assign('CREATEDTIME', $recordModel->getDisplayValue('createdtime'));
        $viewer->assign('MODIFIEDTIME', $recordModel->getDisplayValue('modifiedtime'));
        $viewer->assign('ASSIGNEDTO', $recordModel->getDisplayValue('assigned_user_id'));

        // Disable summary tab — redirect to Details instead
        if ($mode === 'showDetailViewByMode' && $request->get('requestMode') === 'summary') {
            header('Location: index.php?module=Metals&view=Detail&record=' . $request->get('record') . '&mode=showDetailViewByMode&requestMode=full');
            exit;
        }

        // 3) Details tab (full view)
        echo $viewer->view('DetailViewFullContents.tpl', 'Metals', true);
    }

    public function getViewer(Vtiger_Request $request)
    {
        $viewer = parent::getViewer($request);
        $viewer->assign('MODULE', 'Metals');
        return $viewer;
    }
}
