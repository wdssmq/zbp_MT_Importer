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

?>
<div id="divMain">
  <div class="divHeader"><?php echo $blogtitle;?>-<small><a href="javascript:;" onclick="$('#help,#divMain2').slideToggle();" title="查看/隐藏关于">关于</a></small></div>
  <div id="help" style="display:none">感谢您的支持，意见及反馈请联系：<table><tr><td>微博：</td><td><iframe src="http://follow.v.t.qq.com/index.php?c=follow&a=quick&appkey=801097474&sign=4f1fc815&v=2&name=wdssmq&style=4&t=1431568734360&f=1" scrolling="auto" marginwidth="0" marginheight="0" allowtransparency="true" frameborder="0" height="27" width="145"></iframe></td><td><a title="沉冰浮水的微博" target="_blank" href="http://t.qq.com/wdssmq">http://t.qq.com/wdssmq</a></td></tr><tr><td>QQ：</td><td><a target="_blank" href="http://wpa.qq.com/msgrd?v=3&uin=349467624&site=qq&menu=yes"><img border="0" src="http://wpa.qq.com/pa?p=2:349467624:51" alt="置百丈玄冰而崩裂，掷须臾池水而漂摇。" title="置百丈玄冰而崩裂，掷须臾池水而漂摇。"/></a></td><td>349467624</td></tr><tr><td>Q群：</td><td><a target="_blank" href="http://shang.qq.com/wpa/qunwpa?idkey=c632adb705f6fecbec9a2abad09954b513a7e0131309907715ee06c9d58024c5"><img border="0" src="http://pub.idqqimg.com/wpa/images/group.png" alt="独立博客这个圈！" title="独立博客这个圈！"></a></td><td>101578028</td></tr><tr><td>捐赠：</td><td><a href="http://www.wdssmq.com/guestbook.asp#donate" target="_blank" title="wdssmq@qq.com"><img src="https://img.alipay.com/sys/personalprod/style/mc/btn-index.png" width="120" alt="wdssmq@qq.com"></a></td><td>wdssmq#qq.com</td></tr></table></div>
  <div class="SubMenu"></div>
  <div id="divMain2">
    <h3>导入 Moveable Type 或 Typepad</h3>
    <p>1、通过 FTP 上传您的 MT 导出文件 <code>MovableType.txt</code> 到<code>zb_users/plugin/MT_Importer/input</code>文件夹内然后点击导入</p>
    <p>2、相同Title的文章只会导入一次，文章过多时请尝试分段导入； </p>
    <form class="import-upload-form" action="main.php" method="post">
      <input type="hidden" value="import" name="act">
      <p class="submit">
        <input type="submit" value="导入" class="button">
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
  </div>
</div>

<?php
require $blogpath . 'zb_system/admin/admin_footer.php';
RunTime();
?>