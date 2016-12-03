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
InstallPlugin_MovableType_Importer();
?>
<div id="divMain">
  <div class="divHeader"><?php echo $blogtitle;?></div>
  <div class="SubMenu"><?php require 'about.php';?></div>
  <div id="divMain2">
    <p>1、通过 FTP 上传您的 MT 导出文件 <code>MovableType-*.txt</code> 到<code>zb_users/plugin/MT_Importer/input</code>文件夹内然后点击导入</p>
    <p>2、相同Title的文章只会导入一次，Title不同的已存在ID可选择覆盖； </p>
    <p>3、建议单个文件内文章数不超过300，单次导入总文章数不超过3000； </p>
    <form class="import-upload-form" action="main.php" method="post">
      <input type="hidden" value="import" name="act">
      <p style="margin:0 0.5em"><input type="checkbox" name="override" value="true" /> <label for="override">覆盖同ID文章</label></p>
      <p class="submit">
      <input type="submit" value="导入" class="button"> <a href="main.php">刷新</a>
      </p>
    </form>
    <?php
    // CountMemberArray(array(1,2386), array( null, null, 0, 0 ));

    if(GetVars('act','POST')=='import'){
      $dir = ZBP_PATH . 'zb_users/plugin/MT_Importer/input/';
      $arrfiles = GetFilesInDir($dir, "txt");
      $countfiles = count($arrfiles);
      $mt_import = new MT_Import();
      if(GetVars('override','POST')=='true'){
        $mt_import->override = true;
      }
      if ($countfiles < 13){
        foreach ($arrfiles as $filename => $filepath) {
          echo MT_checkBOM($filepath,$filename);
          $mt_import->file=$filepath;
          $mt_import->process_posts();
        }
      }
      $zbp->SetHint('good');
      $zbp->BuildModule();
    }
    ?>
  </div><br /><br />

</div>
<?php
require $blogpath . 'zb_system/admin/admin_footer.php';
RunTime();
?>