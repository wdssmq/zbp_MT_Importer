<?php
// Typecho首页实现自动摘要的方法 - Ryan快快跑
// https://kkp.disk.st/typecho-excerpt.html
function MoveType_excerp($content)
{
  preg_match_all('/<p>.*?<\/p>/im', $content, $m);
  // print_r ($m[0]);
  // 如果有一个以上的p
  if (count($m[0]) > 0) {
    // echo('--第一个P字符串长度--'.strlen($m[0][0]).'<hr>');
    // 如果第一个p字数小于200
    if (strlen($m[0][0]) < 200 &&  count($m) > 1) {
      // 则输出第一个p+第二个p (如果没有第二个p php好像会自动忽略)
      echo ($m[0][0] . $m[0][1]);
    } else {
      // 输出第一个p
      echo ($m[0][0]);
    }
  }
}
