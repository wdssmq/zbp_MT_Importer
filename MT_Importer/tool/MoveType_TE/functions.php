<?php
// Typecho 首页实现自动摘要的方法 - Ryan 快快跑
// https://kkp.disk.st/typecho-excerpt.html
function MoveType_excerp($content)
{
  preg_match_all('/<p>.*?<\/p>/im', $content, $m);
  // print_r ($m[0]);
  // 如果有一个以上的 p
  if (count($m[0]) > 0) {
    // echo('--第一个 P 字符串长度--'.strlen($m[0][0]).'<hr>');
    // 如果第一个p字数小于200
    if (strlen($m[0][0]) < 200 &&  count($m) > 1) {
      // 则输出第一个 p+  第二个 p (如果没有第二个 p php 好像会自动忽略)
      echo ($m[0][0] . $m[0][1]);
    } else {
      // 输出第一个 p
      echo ($m[0][0]);
    }
  }
}
