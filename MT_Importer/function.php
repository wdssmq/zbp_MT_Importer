<?php
function MT_checkBOM($path,$filename)
{
  $contents = RemoveBOM(file_get_contents($path));
  if (file_put_contents($path, $contents)) {
    return ("开始导入 $filename <br />");
  } else {
    return "";
  }
}
class MT_Import
{
  function fopen($filename, $mode = 'r')
  {
    return fopen($filename, $mode);
  }

  function feof($fp)
  {
    return feof($fp);
  }

  function fgets($fp, $len = 10000)
  {
    return fgets($fp, $len);
  }

  function fclose($fp)
  {
    return fclose($fp);
  }

  public $file;

  public $override = false;

  public $qianzhi = false;

  //更新文章数；
  function mt_Build(&$article, $m = +1)
  {
    global $zbp;
    if ($article->Tag)
      CountTagArrayString($article->Tag, $m, $article->ID);
    if ($article->Type == 0)
      CountMemberArray(array($article->AuthorID ), array($m, 0, 0, 0));
    elseif ($article->Type == 1)
      CountMemberArray(array($article->AuthorID ), array(0, $m, 0, 0));
    CountCategoryArray(array(
      $article->CateID
    ), $m);
  }
  
  function mt_Done(){
    global $zbp;
    $zbp->AddBuildModule('catalog');
    $zbp->AddBuildModule('previous');
    $zbp->AddBuildModule('calendar');
    $zbp->AddBuildModule('comments');
    $zbp->AddBuildModule('archives');
    $zbp->AddBuildModule('tags');
    $zbp->AddBuildModule('authors');
    $zbp->BuildModule();
    $zbp->SaveCache();
  }

  //获取作者ID
  function get_AuthorID($name)
  {
    global $zbp;
    $o = $zbp->GetMemberByName($name);
    if ($o->ID == 0) {
      $o->Name = $name;
      $o->Save();
      $zbp->LoadMembers();
    }
    return $o->ID;
  }

  //获取分类ID
  function get_CateID($name)
  {
    global $zbp;
    $o = $zbp->GetCategoryByName($name);
    if ($o->ID == 0) {
      $o->Name = $name;
      $o->Save();
      $zbp->LoadCategorys();
    }
    return $o->ID;
  }

  private $PostsArray = array();
  private $Check_PA = false;
  function get_PostArray()
  {
    global $zbp;
    $postlist = $zbp->GetPostList('log_Title');
    foreach ($postlist as $key => $value) {
      $this->PostsArray[] = $value->Title;
    }
    $this->Check_PA = true;
  }

  function save_post(&$post, &$comments)
  {
    global $zbp;
    $exist = " <b style=\"color:red\">已存在</b>；<br/>";
    if (!$this->Check_PA)
      $this->get_PostArray();
    if (in_array($post->Title, $this->PostsArray)) {
      echo ($post->ID . "、" . $post->Title . $exist);
      if (!$this->qianzhi)
        return;
    } else {
      $this->PostsArray[] = $post->Title;
    }

    if ($post->ID !== 0) {
      $sql   = $zbp->db->sql->Select($zbp->table['Post'], array(
        'log_ID'
      ), array(
        array(
          '=',
          'log_ID',
          $post->ID
        )
      ));
      $array = $zbp->db->Query($sql);
      if (count($array) > 0) {
        $article = new Post();
        $article->LoadInfoByID($post->ID);
        echo ($post->ID . "、" .$article->Title . $exist);
        if (!$this->override)
          return;
        // echo ($post->ID . "、" . "强制写入");
        $this->mt_Build($article, -1);
        DelArticle_Comments($article->ID);
      } else {
        $keyvalue = array(
          'log_ID' => $post->ID,
          'log_Intro' => '',
          'log_Content' => '',
          'log_Meta' => ''
        );
        $sql      = $zbp->db->sql->Insert($GLOBALS['table']['Post'], $keyvalue);
        $zbp->db->Insert($sql);
      }
    }
    $post->CateID   = $this->get_CateID($post->CateID);
    $post->AuthorID = $this->get_AuthorID($post->AuthorID);
    $post->Tag      = PostArticle_CheckTagAndConvertIDtoString($post->Tag);

    // $post->Content = str_replace('src="image/face/', 'src="{#ZC_BLOG_HOST#}zb_users/emotion/face/', $post->Content);
    // $post->Content = str_replace('src="upload', 'src="{#ZC_BLOG_HOST#}zb_users/upload', $post->Content);
    // $post->Content = str_replace('href="upload', 'href="{#ZC_BLOG_HOST#}zb_users/upload', $post->Content);
    // $post->Content = str_replace('value="upload', 'value="{#ZC_BLOG_HOST#}zb_users/upload', $post->Content);
    $post->Content = str_replace('&lt;br/&gt;', '<br/>', $post->Content);
    $post->Content = str_replace('<#ZC_BLOG_HOST#>', '{#ZC_BLOG_HOST#}', $post->Content);

    // $post->Intro = str_replace('src="image/face/', 'src="{#ZC_BLOG_HOST#}zb_users/emotion/face/', $post->Intro);
    // $post->Intro = str_replace('src="upload', 'src="{#ZC_BLOG_HOST#}zb_users/upload', $post->Intro);
    // $post->Intro = str_replace('href="upload', 'href="{#ZC_BLOG_HOST#}zb_users/upload', $post->Intro);
    // $post->Intro = str_replace('value="upload', 'value="{#ZC_BLOG_HOST#}zb_users/upload', $post->Intro);
    $post->Intro = str_replace('&lt;br/&gt;', '<br/>', $post->Intro);
    $post->Intro = str_replace('<#ZC_BLOG_HOST#>', '{#ZC_BLOG_HOST#}', $post->Intro);
    $post->Save();
    $this->mt_Build($post);
    echo ($post->ID . "、" . $post->Title . " <b>写入成功</b><br/>");

    //$zbp->LoadMembers();

    foreach ($comments as $comment) {
      $comment->AuthorID = $zbp->GetMemberByName($comment->Name)->ID;
      $comment->LogID    = $post->ID;
      $comment->Save();
      CountPostArray(array(
        $comment->LogID
      ), +1);
      CountCommentNums(+1, 0);
    }
  }

  function process_posts()
  {
    global $zbp;
    $handle = $this->fopen($this->file, 'r');
    if ($handle == null)
      return false;

    $context  = '';
    $post     = new Post;
    $comment  = new Comment;
    $comments = array();

    while ($line = fgets($handle)) {
      $line = trim($line);
      if ('-----' == $line) {
        // Finishing a multi-line field
        if ('comment' == $context) {
          $comments[] = $comment;
          $comment    = new Comment;
        }
        $context = '';
      } else if ('--------' == $line) {
        // Finishing a post.
        $context  = '';
        $result   = $this->save_post($post, $comments);
        $post     = new Post;
        $comments = array();
      } else if ('BODY:' == $line) {
        $context = 'body';
      } else if ('EXCERPT:' == $line) {
        $context = 'excerpt';
      } else if ('KEYWORDS:' == $line) {
        $context = 'keywords';
      } else if ('COMMENT:' == $line) {
        $context = 'comment';
      } else if (0 === strpos($line, 'AUTHOR:')) {
        $author = trim(substr($line, strlen('AUTHOR:')));
        if ('' == $context)
          $post->AuthorID = $author;
        else if ('comment' == $context)
          $comment->Name = $author;
      } else if (0 === strpos($line, 'ID:')) {
        $ID = trim(substr($line, strlen('ID:')));
        if ('' == $context)
          $post->ID = (int) ($ID);
      } else if (0 === strpos($line, 'TITLE:')) {
        $title = trim(substr($line, strlen('TITLE:')));
        if ('' == $context)
          $post->Title = $title;
      } else if (0 === strpos($line, 'BASENAME:')) {
        $slug = trim(substr($line, strlen('BASENAME:')));
        if (!empty($slug))
          $post->Alias = $slug;
      } else if (0 === strpos($line, 'STATUS:')) {
        $status = trim(strtolower(substr($line, strlen('STATUS:'))));
        if ($status == 'publish') {
          $status = 0;
        } else if ($status == 'draft') {
          $status = 1;
        } else if ($status == 'private') {
          $status = 2;
        }
        $post->Status = $status;
      } else if (0 === strpos($line, 'ALLOW COMMENTS:')) {
        $allow = trim(substr($line, strlen('ALLOW COMMENTS:')));
        if ($allow == 1)
          $post->IsLock = 0;
        else
          $post->IsLock = 1;
      } else if (0 === strpos($line, 'ISPAGE:')) {
        $type = trim(substr($line, strlen('ISPAGE:')));
        if ((int)$type == 0)
          $post->Type = 0;
        else
          $post->Type = 1;
      } else if (0 === strpos($line, 'ISTOP:')) {
        $IsTop = trim(substr($line, strlen('ISTOP:')));
        if ((int)$IsTop == 0)
          $post->IsTop = 0;
        else
          $post->IsTop = 1;
      } else if (0 === strpos($line, 'CATEGORY:')) {
        $category = trim(substr($line, strlen('CATEGORY:')));
        if ('' != $category)
          $post->CateID = $category;
      } else if (0 === strpos($line, 'PRIMARY CATEGORY:')) {
        $category = trim(substr($line, strlen('PRIMARY CATEGORY:')));
        if ('' != $category)
          $post->CateID = $category;
      } else if (0 === strpos($line, 'DATE:')) {
        $date = trim(substr($line, strlen('DATE:')));
        $date = strtotime($date);
        //$date = date('Y-m-d H:i:s', $date);
        //$date_gmt = get_gmt_from_date($date);
        if ('' == $context) {
          $post->PostTime = $date;
        } else if ('comment' == $context) {
          $comment->PostTime = $date;
        }
      } else if (0 === strpos($line, 'EMAIL:')) {
        $email = trim(substr($line, strlen('EMAIL:')));
        if ('comment' == $context)
          $comment->Email = $email;
      } else if (0 === strpos($line, 'IP:')) {
        $ip = trim(substr($line, strlen('IP:')));
        if ('comment' == $context)
          $comment->IP = $ip;
      } else if (0 === strpos($line, 'URL:')) {
        $url = trim(substr($line, strlen('URL:')));
        if ('comment' == $context)
          $comment->HomePage = $url;
      } else if (0 === strpos($line, 'AGENT:')) {
        $url = trim(substr($line, strlen('AGENT:')));
        if ('comment' == $context)
          $comment->Agent = $url;
      } else {
        // Processing multi-line field, check context.
        if (!empty($line))
          $line .= "\n";
        if ('body' == $context) {
          $post->Content .= $line;
        } else if ('excerpt' == $context) {
          $post->Intro .= $line;
        } else if ('keywords' == $context) {
          $post->Tag .= $line;
        } else if ('comment' == $context) {
          $comment->Content .= $line;
        }
      }
    }
    $this->fclose($handle);
  }
}
?>