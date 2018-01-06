<?php
$databases['default']['default'] = array (
  'database' => 'suffix_zx',
  'username' => 'root',
  'password' => '123456',
//    'password' => 'qQ2587011$',
  //'password' => 'QIaWVDLs',
  'prefix' => '',
  'host' => 'localhost',
  //'host' => '47.97.108.218',
  'port' => '3306',
  'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
  'driver' => 'mysql',
);
$settings['hash_salt'] = 'C-MpaTxIdbkY_lPkRNjxzTf69E0KnGqrszhHvusX-FGo5ld2-0q-6XkCQDpH_izAlTYfuaw__Q';
$settings['install_profile'] = 'standard';
$config_directories['sync'] = 'sites/default/files/config_lp3XtKtQ06kgzo4RumelWHrAqYF1MVPu0r0BkaMU6FgkIfPqQXeqTDD-MPm2byuFCnQp5GBeew/sync';
$update_free_access = TRUE;
if (file_exists($app_root . '/' . $site_path . '/settings.local.php')) {
    include $app_root . '/' . $site_path . '/settings.local.php';
}
