<?php
/**
 * 一个用于将 Typecho 内容导出为 MT 数据格式的主题
 *
 * @package MoveType_TE
 * @author 沉冰浮水
 * @version 1.0.0
 * @link https://www.wdssmq.com
 */
?>
<?php header("Content-Type: text/plain");?>
<?php while ($this->next()) : ?>
  AUTHOR: <?php $this->author(); ?><?php echo "\n"; ?>
  TITLE: <?php $this->title() ?><?php echo "\n"; ?>
  ID: <?php $this->cid() ?><?php echo "\n"; ?>
  STATUS: publish
  ISPAGE: 0
  ISTOP: 0
  ALLOW COMMENTS: 1
  CATEGORY: <?php echo $this->category; ?><?php echo "\n"; ?>
  DATE: <?php $this->date('Y-m-d H:i:s'); ?><?php echo "\n"; ?>
  BASENAME: <?php $this->slug() ?><?php echo "\n"; ?>
  -----
  BODY:
  <?php $this->content(); ?><?php echo "\n"; ?>
  -----
  EXCERPT:
  <?php MoveType_excerp($this->content); ?><?php echo "\n"; ?>
  -----
  KEYWORDS:
  <?php $this->tags(',', false, $this->category); ?><?php echo "\n"; ?>
  -----
  --------
<?php endwhile; ?>
