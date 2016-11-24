<?php
//注册插件
RegisterPlugin("MovableType_Importer", "ActivePlugin_MovableType_Importer");

function ActivePlugin_MovableType_Importer()
{
}
function InstallPlugin_MovableType_Importer()
{
    if(!file_exists(ZBP_PATH . 'zb_users/plugin/MT_Importer/input/')) {
      @mkdir(ZBP_PATH . 'zb_users/plugin/MT_Importer/input/',0755);
    }
}
function UninstallPlugin_MovableType_Importer()
{
}