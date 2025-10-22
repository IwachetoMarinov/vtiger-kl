<?php
class Metals_RelatedList_View extends Vtiger_RelatedList_View {

    public function process(Vtiger_Request $request) {
        $relatedModuleName = $request->get('relatedModule');
        $viewer = $this->getViewer($request);

        // For Documents tab, use standard vtiger behavior
        if ($relatedModuleName === 'Documents') {
            parent::process($request);
            return;
        }

        // Otherwise fallback to normal related list view
        parent::process($request);
    }
}
