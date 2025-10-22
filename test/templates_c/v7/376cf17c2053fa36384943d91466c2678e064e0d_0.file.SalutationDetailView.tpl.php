<?php
/* Smarty version 4.5.5, created on 2025-10-22 08:56:35
  from 'C:\laragon\www\vtiger-gpm\layouts\v7\modules\Vtiger\uitypes\SalutationDetailView.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '4.5.5',
  'unifunc' => 'content_68f89c43b5f393_27958209',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '376cf17c2053fa36384943d91466c2678e064e0d' => 
    array (
      0 => 'C:\\laragon\\www\\vtiger-gpm\\layouts\\v7\\modules\\Vtiger\\uitypes\\SalutationDetailView.tpl',
      1 => 1752039682,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_68f89c43b5f393_27958209 (Smarty_Internal_Template $_smarty_tpl) {
echo $_smarty_tpl->tpl_vars['RECORD']->value->getDisplayValue('salutationtype');?>


<?php echo $_smarty_tpl->tpl_vars['FIELD_MODEL']->value->getDisplayValue($_smarty_tpl->tpl_vars['FIELD_MODEL']->value->get('fieldvalue'),$_smarty_tpl->tpl_vars['RECORD']->value->getId(),$_smarty_tpl->tpl_vars['RECORD']->value);
}
}
