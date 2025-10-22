<?php
/* Smarty version 4.5.5, created on 2025-10-21 12:06:39
  from 'C:\laragon\www\vtiger-gpm\layouts\v7\modules\Metals\DetailViewFullContents.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '4.5.5',
  'unifunc' => 'content_68f7774f6406e8_07896864',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'b4c2229ffe844090744f61c495178f681d135b30' => 
    array (
      0 => 'C:\\laragon\\www\\vtiger-gpm\\layouts\\v7\\modules\\Metals\\DetailViewFullContents.tpl',
      1 => 1761045823,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_68f7774f6406e8_07896864 (Smarty_Internal_Template $_smarty_tpl) {
?><div class="details row-fluid">
    <div class="recordDetails">
        <div class="summaryViewInfo">
            <div class="block">
                <div class="blockHeader">
                    <h4 class="blockLabel">Asset Details</h4>
                </div>
                <div class="blockContent">
                    <div class="details">
                        <table class="table table-bordered table-condensed">
                            <tr>
                                <td class="fieldLabel"><strong>Name</strong></td>
                                <td class="fieldValue"><?php echo $_smarty_tpl->tpl_vars['NAME']->value;?>
</td>
                                <td class="fieldLabel"><strong>FineOz</strong></td>
                                <td class="fieldValue"><?php echo $_smarty_tpl->tpl_vars['FINEOZ']->value;?>
</td>
                            </tr>
                            <tr>
                                <td class="fieldLabel"><strong>Type</strong></td>
                                <td class="fieldValue"><?php echo $_smarty_tpl->tpl_vars['TYPE']->value;?>
</td>
                                <td class="fieldLabel"><strong>Created Time</strong></td>
                                <td class="fieldValue"><?php echo $_smarty_tpl->tpl_vars['CREATEDTIME']->value;?>
</td>
                            </tr>
                            <tr>
                                <td class="fieldLabel"><strong>Modified Time</strong></td>
                                <td class="fieldValue"><?php echo $_smarty_tpl->tpl_vars['MODIFIEDTIME']->value;?>
</td>
                                <td class="fieldLabel"><strong>Assigned To</strong></td>
                                <td class="fieldValue"><?php echo $_smarty_tpl->tpl_vars['ASSIGNEDTO']->value;?>
</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php }
}
