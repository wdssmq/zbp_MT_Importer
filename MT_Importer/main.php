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
  <div class="SubMenu"><a href="main.php"><span class="m-left m-now">首页</span></a><?php require 'about.php';?></div>
  <div id="divMain2">
    <p>1、通过 FTP 上传您的 MT 导出文件 <code>MovableType-*.txt</code> 到<code>zb_users/plugin/MT_Importer/input</code>文件夹内然后点击导入</p>
    <p>2、相同Title的文章仅当ID也相同且勾选覆盖选项时才会作覆盖更新；</p>
    <p>3、建议单个文件内文章数不超过300，单次导入总文章数不超过3000； </p>
    <form class="import-upload-form" action="main.php" method="post">
      <input type="hidden" value="import" name="act" />
      <p style="margin:0 0.5em">
        <input class="check-box" type="checkbox" id="over" name="over" value="true" /> <label for="over">覆盖同ID文章</label><br />
        <input class="check-box" type="checkbox" id="asnew" name="asnew" value="true" /> <label for="asnew">作为新文章追加</label> <input placeholder="别名前缀" type="text" name="prefix" value=""> <label for="prefix">别名前缀将与文章ID组成别名，供原站点写301及改版规则；</label>
      </p>
      <p class="submit">
        <input type="submit" value="导入" class="button"> <a href="main.php">刷新</a>
      </p>
    </form>
<?php
if (GetVars('act', 'POST') == 'import') {
  set_time_limit(0);
  $dir        = ZBP_PATH . 'zb_users/plugin/MT_Importer/input/';
  $arrfiles   = GetFilesInDir($dir, "txt");
  $countfiles = count($arrfiles);
  $bolDo      = true;
  $mt_import  = new MT_Import();
  if (GetVars('over', 'POST') == 'true') {
    $mt_import->over = true;
  } elseif (GetVars('asnew', 'POST') == 'true') {
    $mt_import->asnew  = true;
    $mt_import->prefix = trim(GetVars('prefix', 'POST'));
    if ($mt_import->prefix == "") {
      echo "请输入前缀，确定不使用前缀请输入none";
      $bolDo = false;
    }
  }
  if ($bolDo) {
    if ($countfiles < 13) {
      foreach ($arrfiles as $filename => $filepath) {
        echo MT_checkBOM($filepath, $filename);
        $mt_import->file = $filepath;
        $mt_import->process_posts();
      }
    }
    $mt_import->mt_Done();
    $zbp->SetHint('good');
  }
}
?>
  </div><br /><br />

</div>
<script type="text/javascript">
  $("input.check-box").click(function(){
    var bolFlag = $(this).prop("checked");
    $("input.check-box").each(
      function(){$(this).prop("checked",false);}
    );
    $(this).prop("checked",bolFlag);
  });
  $(".submit input").click(function(){
    var obj = {};
    var key = $(".check-box:checked").attr("name");
    if (key)
      //eval("obj."+key+"=true");
      obj[key] = true;
    obj.prefix = $("input[name=prefix]").val();
    zbp.cookie.set("mt_form", JSON.stringify(obj));
  });
  (function(){
    var strJSON = zbp.cookie.get("mt_form");
    if (strJSON){
      eval("var obj=" + strJSON);
      for (var key in obj){
        $("input[name="+key+"]").val(obj[key]);
        $("input[type=checkbox][name="+key+"]").prop("checked",true);
      }
    }
  })();
</script>
<?php
require $blogpath . 'zb_system/admin/admin_footer.php';
RunTime();
?>
