# suffix-zx
后装修时
# test
http://drupalchina.cn/node/6300

windows 安装drush
https://www.drupal.org/node/594744
drush 数据备份 https://drushcommands.com/drush-8x/core/archive-dump/

传值到page当中
function yourtheme_preprocess_page(&$variables){
$block = \Drupal\block\Entity\Block::load('your_block_id');
$variables['block_output'] = \Drupal::entityTypeManager()
  ->getViewBuilder('block')
  ->view($block);
}
模板中引用 {{ block_output }}