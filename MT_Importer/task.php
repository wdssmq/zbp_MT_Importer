<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>AutoMT</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
<?php
require '../../../zb_system/function/c_system_base.php';
require '../MT_Importer/function.php';
$zbp->Load();
set_time_limit(0);

$filepath = ZBP_PATH . 'zb_users/plugin/MT_Importer/cache/input.txt';

if (is_file($filepath))
  die();

$url = $zbp->Config('MT_Import')->SyncUrl;

$preAuthorID = $zbp->Config('MT_Import')->AuthorID;

if (empty($url))
  die();

$http = Network::Create();
$http->open('GET', $url);
$http->send();
if ($http->status !== "200")
  die();
file_put_contents($filepath, $http->responseText);

$mt_import = new MT_Import();
$mt_import->over = true;
$mt_import->preAuthorID = is_numeric($preAuthorID) ? (int)$preAuthorID : 0;
echo MT_checkBOM($filepath, "input.txt");
$mt_import->file = $filepath;
$mt_import->process_posts();
$mt_import->mt_Done();
@unlink($filepath);
?>
</body>
</html>
