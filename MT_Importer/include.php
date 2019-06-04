<?php
//注册插件
RegisterPlugin("MT_Importer", "ActivePlugin_MT_Importer");

function ActivePlugin_MT_Importer()
{
  Add_Filter_Plugin('Filter_Plugin_Admin_TopMenu', 'MT_Importer_TopMenu');
}
function MT_Importer_TopMenu(&$m)
{
  global $zbp;
  $m[] = MakeTopMenu("root", 'AutoMT', $zbp->host . "zb_users/plugin/MT_Importer/task.php", "_blank", "MT-SyncIn");
}
function InstallPlugin_MT_Importer()
{
  if (!file_exists(ZBP_PATH . 'zb_users/plugin/MT_Importer/input/')) {
    @mkdir(ZBP_PATH . 'zb_users/plugin/MT_Importer/input/', 0755);
  }
  if (!file_exists(ZBP_PATH . 'zb_users/plugin/MT_Importer/cache/')) {
    @mkdir(ZBP_PATH . 'zb_users/plugin/MT_Importer/cache/', 0755);
  }
  $filepath = ZBP_PATH . 'zb_users/plugin/MT_Importer/cache/input.txt';
  if (is_file($filepath)) {
    @unlink($filepath);
  }
}
function UninstallPlugin_MT_Importer()
{ }
