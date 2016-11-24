<?php
require '../../../zb_system/function/c_system_base.php';
require '../../../zb_system/function/c_system_admin.php';
$zbp->Load();
$action='root';
if (!$zbp->CheckRights($action)) {$zbp->ShowError(6);die();}
if (!$zbp->CheckPlugin('MT_Importer')) {$zbp->ShowError(48);die();}

$blogtitle='MovableType数据格式导入';
require $blogpath . 'zb_system/admin/admin_header.php';
require $blogpath . 'zb_system/admin/admin_top.php';
require_once 'function.php';
// InstallPlugin_MovableType_Importer();
?>
<div id="divMain">
  <div class="divHeader"><?php echo $blogtitle;?></div>
  <div class="SubMenu"><?php require 'about.php';?></div>
  <div id="divMain2">
    <h3>导入 Moveable Type</h3>
    <p>1、通过 FTP 上传您的 MT 导出文件 <code>MovableType.txt</code> 到<code>zb_users/plugin/MT_Importer/input</code>文件夹内然后点击导入</p>
    <p>2、相同Title的文章只会导入一次，文章过多时请尝试分段导入； </p>
    <form class="import-upload-form" action="main.php" method="post">
      <input type="hidden" value="import" name="act">
      <p class="submit">
        <input type="submit" value="导入" class="button"> <a href="main.php">刷新</a>
      </p>
    </form>
    <?php
    if ($_POST && $_POST['act'] == 'import') {
      $dir = ZBP_PATH . 'zb_users/plugin/MT_Importer/input/';
      $arrfiles = GetFilesInDir($dir, "txt");
      $countfiles = count($arrfiles);
      foreach ($arrfiles as $filename => $filepath) {
        $mt_import = new MT_Import();
        $mt_import->file=$filepath;
        $mt_import->process_posts();
      }
      $zbp->SetHint('good');
      //Redirect('./main.php');
    }
    ?>
  </div><br /><br />

</div>
<?php
require $blogpath . 'zb_system/admin/admin_footer.php';
RunTime();
?>