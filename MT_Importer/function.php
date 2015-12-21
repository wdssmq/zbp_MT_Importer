<?php

class MT_Import{
  var $file;

  function fopen($filename, $mode='r') {
		return fopen($filename, $mode);
	}

	function feof($fp) {
		return feof($fp);
	}

	function fgets($fp, $len=8192) {
		return fgets($fp, $len);
	}

	function fclose($fp) {
		return fclose($fp);
 	}

  function mt_Build() {
    global $zbp;
    $comm_list = $zbp->GetCommentList();
    $post_list = $zbp->GetPostList();
    $tag_list = $zbp->GetTagList();
    $member_list = $zbp->GetMemberList();
    $catagory_list = $zbp->GetCategoryList();
    //关联评论用户ID
    foreach ($comm_list as $o) {
      foreach ($member_list as $m) {
        if (($o->Name) == ($m->Name)) {
          $comm = new Comment;
          $comm->LoadInfoByID($o->ID);
          $comm->AuthorID = $m->ID;
          $comm->Save();
        }
      }
    }
    /* //更新章统计
    foreach ($post_list as $p) {
      $post = new Post;
      $post->LoadInfoByID($p->ID);
      $post->Save();
    }
    //更新标签统计
    foreach ($tag_list as $t) {
      $tag = new Tag;
      $tag->LoadInfoByID($t->ID);
      $tag->Save();
    }
    //更新分类统计
    foreach ($catagory_list as $c) {
      $cata = new Category;
      $cata->LoadInfoByID($c->ID);
      $cata->Save();
    }
    //更新用户统计
    foreach ($member_list as $m) {
      $mem = new Member;
      $mem->LoadInfoByID($m->ID);
      $mem->Save();
    } */
    //更新全局统计
    $zbp->BuildTemplate();
    $all_artiles = GetValueInArrayByCurrent($zbp->db->Query('SELECT COUNT(log_ID) AS num FROM ' . $GLOBALS['table']['Post'] . ' WHERE log_Type=0') , 'num');
    $all_pages = GetValueInArrayByCurrent($zbp->db->Query('SELECT COUNT(log_ID) AS num FROM ' . $GLOBALS['table']['Post'] . ' WHERE log_Type=1') , 'num');
    $all_categorys = GetValueInArrayByCurrent($zbp->db->Query('SELECT COUNT(cate_ID) AS num FROM ' . $GLOBALS['table']['Category']) , 'num');
    $all_comments = GetValueInArrayByCurrent($zbp->db->Query('SELECT COUNT(comm_ID) AS num FROM ' . $GLOBALS['table']['Comment']) , 'num');
    $all_views = GetValueInArrayByCurrent($zbp->db->Query('SELECT SUM(log_ViewNums) AS num FROM ' . $GLOBALS['table']['Post']) , 'num');
    $all_tags = GetValueInArrayByCurrent($zbp->db->Query('SELECT COUNT(tag_ID) as num FROM ' . $GLOBALS['table']['Tag']) , 'num');
    $all_members = GetValueInArrayByCurrent($zbp->db->Query('SELECT COUNT(mem_ID) AS num FROM ' . $GLOBALS['table']['Member']) , 'num');
    $s = $zbp->db->sql->Count($zbp->table['Post'], array(
      array(
        'COUNT',
        '*',
        'num'
      )
    ) , array(
      array(
        '=',
        'log_Type',
        0
      ) ,
      array(
        '=',
        'log_IsTop',
        0
      ) ,
      array(
        '=',
        'log_Status',
        0
      )
    ));
    $num = GetValueInArrayByCurrent($zbp->db->Query($s) , 'num');
    $zbp->LoadConfigs();
    $zbp->LoadCache();
    $zbp->cache->normal_article_nums = $num;
    $zbp->SaveCache();
    $zbp->AddBuildModule('statistics', array(
      $all_artiles,
      $all_pages,
      $all_categorys,
      $all_tags,
      $all_views,
      $all_comments
    ));
    $zbp->BuildModule();
  }

  function get_AuthorID($name) {
    global $zbp;
    $zbp->LoadCategorys();
    $o = $zbp->GetMemberByName($name);
    if ($o->ID == 0) {
      $o->Name = $name;
      $o->Save();
    }
    return $o->ID;
  }

  function get_CateID($name) {
    global $zbp;
    $zbp->LoadCategorys();
    $o = $zbp->GetCategoryByName($name);
    if ($o->ID == 0) {
      $o->Name = $name;
      $o->Save();
    }
    return $o->ID;
  }

  function save_post(&$post, &$comments){
    global $zbp;
    $postlist = $zbp->GetPostList('log_Title');
    foreach ($postlist as $key => $value){
      if ($post->Title == $value->Title); 
        return false;
    }
    // $post->LoadInfoByID(0);
    $post->CateID = $this->get_CateID($post->CateID);
    $post->AuthorID = $this->get_AuthorID($post->AuthorID);
    $post->Type = 0; //?
    $post->Tag = PostArticle_CheckTagAndConvertIDtoString($post->Tag);

    $post->Content = str_replace('src="image/face/', 'src="{#ZC_BLOG_HOST#}zb_users/emotion/face/', $post->Content);
    $post->Content = str_replace('src="upload', 'src="{#ZC_BLOG_HOST#}zb_users/upload', $post->Content);
    $post->Content = str_replace('href="upload', 'href="{#ZC_BLOG_HOST#}zb_users/upload', $post->Content);
    $post->Content = str_replace('value="upload', 'value="{#ZC_BLOG_HOST#}zb_users/upload', $post->Content);
    $post->Content = str_replace('href="http://upload', 'value="{#ZC_BLOG_HOST#}zb_users/upload', $post->Content);
    $post->Content = str_replace('&lt;br/&gt;', '<br/>', $post->Content);
    $post->Content = str_replace('<#ZC_BLOG_HOST#>', '{#ZC_BLOG_HOST#}', $post->Content);

    $post->Intro = str_replace('src="image/face/', 'src="{#ZC_BLOG_HOST#}zb_users/emotion/face/', $post->Intro);
    $post->Intro = str_replace('src="upload', 'src="{#ZC_BLOG_HOST#}zb_users/upload', $post->Intro);
    $post->Intro = str_replace('href="upload', 'href="{#ZC_BLOG_HOST#}zb_users/upload', $post->Intro);
    $post->Intro = str_replace('value="upload', 'value="{#ZC_BLOG_HOST#}zb_users/upload', $post->Intro);
    $post->Intro = str_replace('href="http://upload', 'value="{#ZC_BLOG_HOST#}zb_users/upload', $post->Intro);
    $post->Intro = str_replace('&lt;br/&gt;', '<br/>', $post->Intro);
    $post->Intro = str_replace('<#ZC_BLOG_HOST#>', '{#ZC_BLOG_HOST#}', $post->Intro);

    $post->Save();



    foreach ( $comments as $comment ) {}
  }

  function process_posts() {
    global $zbp;
		$handle = $this->fopen($this->file, 'r');
		if ( $handle == null )
			return false;

		$context = '';
		$post = new Post;
		$comment = new Comment;
		$comments = array();

    while ($line = fgets($handle,8192)) {
      $line = trim($line);
			if ( '-----' == $line ) {
				// Finishing a multi-line field
				if ( 'comment' == $context ) {
					$comments[] = $comment;
					$comment = new Comment;
				} else if ( 'ping' == $context ) {
					//$pings[] = $ping;
					//$ping = new StdClass();
				}
				$context = '';
			} else if ( '--------' == $line ) {
				// Finishing a post.
				$context = '';
				$result = $this->save_post($post, $comments);
					//return $result;
				$post = new Post;
				$comment = new Comment;
				$comments = array();
			} else if ( 'BODY:' == $line ) {
				$context = 'body';
			} else if ( 'EXTENDED BODY:' == $line ) {
				$context = 'extended';
			} else if ( 'EXCERPT:' == $line ) {
				$context = 'excerpt';
			} else if ( 'KEYWORDS:' == $line ) {
				$context = 'keywords';
			} else if ( 'COMMENT:' == $line ) {
				$context = 'comment';
			} else if ( 0 === strpos($line, 'AUTHOR:') ) {
				$author = trim( substr($line, strlen('AUTHOR:')) );
				if ( '' == $context )
					$post->AuthorID = $author;
				else if ( 'comment' == $context )
					 $comment->Name = $author;
			} else if ( 0 === strpos($line, 'TITLE:') ) {
				$title = trim( substr($line, strlen('TITLE:')) );
				if ( '' == $context )
					$post->Title = $title;
			} else if ( 0 === strpos($line, 'BASENAME:')){
				$slug = trim( substr($line, strlen('BASENAME:')) );
				if ( !empty( $slug ) )
					$post->Alias = $slug;
			} else if ( 0 === strpos($line, 'STATUS:') ) {
				$status = trim( strtolower( substr($line, strlen('STATUS:')) ) );
				if ( empty($status) )
					$status = 1 ;
				$post->Status = $status;
			} else if ( 0 === strpos($line, 'ALLOW COMMENTS:') ) {
				$allow = trim( substr($line, strlen('ALLOW COMMENTS:')) );
				if ( $allow == 1 )
					$post->IsLock = 0;
				else
					$post->IsLock = 1;
			} else if ( 0 === strpos($line, 'CATEGORY:') ) {
				$category = trim( substr($line, strlen('CATEGORY:')) );
				if ( '' != $category )
					$post->CateID = $category;  //TD
			} else if ( 0 === strpos($line, 'PRIMARY CATEGORY:') ) {
				$category = trim( substr($line, strlen('PRIMARY CATEGORY:')) );
				if ( '' != $category )
					$post->CateID = $category;
			} else if ( 0 === strpos($line, 'DATE:') ) {
				$date = trim( substr($line, strlen('DATE:')) );
				$date = strtotime($date);
				//$date = date('Y-m-d H:i:s', $date);
				//$date_gmt = get_gmt_from_date($date);
				if ( '' == $context ) {
					$post->PostTime = $date;
          //print_r $date;
				} else if ( 'comment' == $context ) {
					$comment->PostTime = $date;
				}
      } else if ( 0 === strpos($line, 'EMAIL:') ) {
        $email = trim( substr($line, strlen('EMAIL:')) );
        if ( 'comment' == $context )
          $comment->Email = $email;
      } else if ( 0 === strpos($line, 'IP:') ) {
        $ip = trim( substr($line, strlen('IP:')) );
        if ( 'comment' == $context )
          $comment->IP = $ip;
      } else if ( 0 === strpos($line, 'URL:') ) {
        $url = trim( substr($line, strlen('URL:')) );
        if ( 'comment' == $context )
          $comment->HomePage = $url;
      } else {
				// Processing multi-line field, check context.
				if( !empty($line) )
					$line .= "\n";
				if ( 'body' == $context ) {
					$post->Content .= $line;
				} else if ( 'extended' ==  $context ) {
					// $post->extended .= $line;
				} else if ( 'excerpt' == $context ) {
					$post->Intro .= $line;
				} else if ( 'keywords' == $context ) {
					$post->Tag .= ','.$line;
				} else if ( 'comment' == $context ) {
					$comment->Content .= $line;
				}
			}
    }
    $this->fclose($handle);
  }
}
?>