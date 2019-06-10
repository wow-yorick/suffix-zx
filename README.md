# suffix-zx
家居服务电商网站

#站点样例
http://suffix-zx.5zyx.com/

# test
http://drupalchina.cn/node/6300
<<<<<<< HEAD

windows 安装drush
https://www.drupal.org/node/594744
drush 数据备份 https://drushcommands.com/drush-8x/core/archive-dump/

=======
```
windows 安装drush
https://www.drupal.org/node/594744
drush 数据备份 https://drushcommands.com/drush-8x/core/archive-dump/
```
```
>>>>>>> c7ad5f47474f46f326e1fc3961821e9d336aeb19
传值到page当中
function yourtheme_preprocess_page(&$variables){
$block = \Drupal\block\Entity\Block::load('your_block_id');
$variables['block_output'] = \Drupal::entityTypeManager()
  ->getViewBuilder('block')
  ->view($block);
}
<<<<<<< HEAD
模板中引用 {{ block_output }}
=======
模板中引用 {{ block_output }}
```
>>>>>>> c7ad5f47474f46f326e1fc3961821e9d336aeb19
