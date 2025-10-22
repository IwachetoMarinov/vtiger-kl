<?php
/* Smarty version 4.5.5, created on 2025-10-21 12:06:37
  from 'C:\laragon\www\vtiger-gpm\layouts\v7\modules\Vtiger\partials\SidebarHeader.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '4.5.5',
  'unifunc' => 'content_68f7774d2c20a4_59795927',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '76330e7a30d20a5c82a339e1da2df3ec0be33f58' => 
    array (
      0 => 'C:\\laragon\\www\\vtiger-gpm\\layouts\\v7\\modules\\Vtiger\\partials\\SidebarHeader.tpl',
      1 => 1752039682,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
    'file:modules/Vtiger/partials/SidebarAppMenu.tpl' => 1,
  ),
),false)) {
function content_68f7774d2c20a4_59795927 (Smarty_Internal_Template $_smarty_tpl) {
$_smarty_tpl->_assignInScope('APP_IMAGE_MAP', Vtiger_MenuStructure_Model::getAppIcons());?>

<div class="col-sm-1 col-xs-2 app-indicator-icon-container app-<?php echo $_smarty_tpl->tpl_vars['SELECTED_MENU_CATEGORY']->value;?>
">
	<div class="row" title="<?php if ($_smarty_tpl->tpl_vars['MODULE']->value == 'Home' || !$_smarty_tpl->tpl_vars['MODULE']->value) {?> <?php echo vtranslate('LBL_DASHBOARD');?>
 <?php } else {
echo vtranslate("LBL_".((string)$_smarty_tpl->tpl_vars['SELECTED_MENU_CATEGORY']->value));
}?>">
		<span class="app-indicator-icon fa <?php if ($_smarty_tpl->tpl_vars['MODULE']->value == 'Home' || !$_smarty_tpl->tpl_vars['MODULE']->value) {?>fa-dashboard<?php } else {
echo $_smarty_tpl->tpl_vars['APP_IMAGE_MAP']->value[$_smarty_tpl->tpl_vars['SELECTED_MENU_CATEGORY']->value];
}?>"></span>
	</div>
</div>

<?php $_smarty_tpl->_subTemplateRender("file:modules/Vtiger/partials/SidebarAppMenu.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, false);
}
}
