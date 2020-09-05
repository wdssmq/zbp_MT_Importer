<?php
require '../../../zb_system/function/c_system_base.php';
require '../../../zb_system/function/c_system_admin.php';
$zbp->Load();
$action = 'root';
if (!$zbp->CheckRights($action)) {
  $zbp->ShowError(6);
  die();
}
if (!$zbp->CheckPlugin('MT_Importer')) {
  $zbp->ShowError(48);
  die();
}

$blogtitle = 'MT数据格式导入【PHP】';
require $blogpath . 'zb_system/admin/admin_header.php';
require $blogpath . 'zb_system/admin/admin_top.php';
require_once 'function.php';
InstallPlugin_MT_Importer();
$act = GetVars('act', 'GET');
$suc = GetVars('suc', 'GET');
function setNow($class)
{
  global $act;
  if ($class == "main" && $act == null)
    return "m-now";
  return $class == $act ? "m-now" : $class;
}

if ($act == 'save') {
  CheckIsRefererValid();
  $zbp->Config('MT_Importer')->SyncUrl = $_POST['SyncUrl'];
  $zbp->Config('MT_Importer')->AuthorID = $_POST['AuthorID'];
  $zbp->SaveConfig('MT_Importer');
  $zbp->SetHint('good');
  Redirect('./main.php' . $suc == null ? '' : "?act=$suc");
}

?>
<div id="divMain">
  <div class="divHeader"><?php echo $blogtitle; ?></div>
  <div class="SubMenu">
    <a href="main.php"><span class="m-left <?php echo setNow("main"); ?>">首页</span></a>
    <a href="main.php?act=sync"><span class="m-left <?php echo setNow("sync"); ?>">站点同步（导入）</span></a>
    <?php require 'about.php'; ?></div>
  <div id="divMain2">
    <?php if ($act == null || $act == 'import') { ?>
      <p>1、通过 FTP 上传您的 MT 导出文件 <code>MovableType-*.txt</code> 到<code>zb_users/plugin/MT_Importer/input</code>文件夹内然后点击导入</p>
      <p>2、相同Title的文章仅当ID也相同且勾选覆盖选项时才会作覆盖更新；</p>
      <p>3、建议单个文件内文章数不超过300，单次导入总文章数不超过3000； </p>
      <form class="import-upload-form" action="<?php echo BuildSafeURL("main.php?act=import"); ?>" method="post">
        <input type="hidden" value="import" name="act" />
        <h3 class="js-tips hidden">请选择一项</h3>
        <p style="margin:0 0.5em">
          <input class="check-box" type="checkbox" id="over" name="over" value="true" /> <label for="over">覆盖同ID文章</label><br />
          <input class="check-box" type="checkbox" id="asnew" name="asnew" value="true" /> <label for="asnew">作为新文章追加</label> <input placeholder="别名前缀" type="text" name="prefix" value=""> <label for="prefix">别名前缀将与文章ID组成别名，供原站点写301及改版规则；</label>
        </p>
        <p class="submit">
          <input type="submit" value="导入" class="button"> <a href="main.php">刷新</a>
        </p>
      </form>
      <?php
      if ($act == 'import') {
        CheckIsRefererValid();
        set_time_limit(0);
        $dir = ZBP_PATH . 'zb_users/plugin/MT_Importer/input/';
        $arrfiles = GetFilesInDir($dir, "txt");
        $countfiles = count($arrfiles);
        $bolDo = false;
        $mt_import = new MT_Import();
        if (GetVars('over', 'POST') == 'true') {
          $mt_import->over = true;
          $bolDo = true;
        } elseif (GetVars('asnew', 'POST') == 'true') {
          $mt_import->asnew = true;
          $bolDo = true;
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
    <?php

    } else { ?>
      <form action="<?php echo BuildSafeURL("main.php?act=save&suc=sync"); ?>" method="post">
        <table width="100%">
          <tr>
            <th width="97">项目</th>
            <th>内容</th>
            <th width="211">说明</th>
          </tr>
          <tr>
            <td>抓取网址</td>
            <td><input onmouseover="this.select()" style="width:97%" type="text" id="SyncUrl" name="SyncUrl" value="<?php echo $zbp->Config('MT_Importer')->SyncUrl; ?>" /></td>
            <td></td>
          </tr>
          <tr>
            <td>用户选择</td>
            <td>
              <?php //var_dump($zbp->Config('MT_Import')->AuthorID)
              ?>
              <!-- user( -->
              <select style="width:223px;" size="1" name="AuthorID" id="AuthorID" onChange="AuthorID.value=this.options[this.selectedIndex].value">
                <option value="0">同名用户</option>
                <?php echo OutputOptionItemsOfMember($zbp->Config('MT_Importer')->AuthorID); ?>
              </select>&nbsp;&nbsp;如不存在同名用户则自动创建！
              <!-- )user -->
            </td>
            <td></td>
          </tr>
          <tr>
            <td>监控地址</td>
            <td><input onclick="that = this;setTimeout(function(){that.select()},150);" onkeyup="if (this.dataset['value']) this.value = this.dataset['value'];this.select()" onmouseover="this.dataset['value'] = this.value;this.select()" style="width:97%" type="text" id="url" name="url" value="<?php echo $zbp->host . "zb_users/plugin/MT_Importer/task.php"; ?>" /></td>
            <td>只读，请复制至监控服务并设置合适的触发周期</td>
          </tr>
          <tr>
            <td colspan="3"><input type="submit" value="提交" /><small><a href="main.php?act=sync" title="刷新">刷新</a></small></td>
          </tr>
        </table>
      </form>
    <?php

    } ?>
  </div><br /><br />

</div>
<script type="text/javascript">
  $("input.check-box").click(function() {
    var bolFlag = $(this).prop("checked");
    $("input.check-box").each(
      function() {
        $(this).prop("checked", false);
      }
    );
    $(this).prop("checked", bolFlag);
  });
  $(".submit input").click(function() {
    var obj = {};
    var key = $(".check-box:checked").attr("name");
    if (key) {
      obj[key] = true;
    } else {
      $(".js-tips").show().delay(1370).fadeOut();
      return false;
    }
    obj.prefix = $("input[name=prefix]").val();
    zbp.cookie.set("mt_form", JSON.stringify(obj));
  });
  (function() {
    $(".js-tips").css({
      display: "none",
      padding: 0,
      margin: 0,
      color: "red"
    });
    const strJSON = zbp.cookie.get("mt_form");
    if (strJSON) {
      obj = JSON.parse(strJSON);
      for (let key in obj) {
        $("input[name=" + key + "]").val(obj[key]);
        $("input[type=checkbox][name=" + key + "]").prop("checked", true);
      }
    }
  })();
</script>
<?php
require $blogpath . 'zb_system/admin/admin_footer.php';
RunTime();
?>