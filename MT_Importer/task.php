<?php
require '../../../zb_system/function/c_system_base.php';
require '../MT_Importer/function.php';
$zbp->Load();
set_time_limit(0);

$filepath = ZBP_PATH . 'zb_users/plugin/MT_Importer/cache/input.txt';

$url = $zbp->Config('MT_Import')->SyncUrl;

$preAuthorID = $zbp->Config('MT_Import')->AuthorID;

if ($url == null || $url == "")
  die();

$http = Network::Create();
$http->open('GET', $url);
$http->send();
if ($http->status !== "200")
  die();
file_put_contents($filepath,$http->responseText);

$mt_import = new MT_Import();
$mt_import->over = true;
$mt_import->preAuthorID = is_numeric($preAuthorID) ? (int)$preAuthorID : 0;
echo MT_checkBOM($filepath,"input.txt");
$mt_import->file=$filepath;
$mt_import->process_posts();
$mt_import->mt_Done();
?>