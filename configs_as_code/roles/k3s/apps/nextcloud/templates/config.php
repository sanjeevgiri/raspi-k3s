<?php
$CONFIG = array (
  'htaccess.RewriteBase' => '/',
  'memcache.local' => '\\OC\\Memcache\\APCu',
  'apps_paths' =>
  array (
    0 =>
    array (
      'path' => '/var/www/html/apps',
      'url' => '/apps',
      'writable' => false,
    ),
    1 =>
    array (
      'path' => '/var/www/html/custom_apps',
      'url' => '/custom_apps',
      'writable' => true,
    ),
  ),
  'upgrade.disable-web' => true,
  'passwordsalt' => 'HEQ9H07aRPpZTj0g74rc8mX1nVFPWg',
  'secret' => '1TwLdXe7enFz181bknhl7Hztw2tKGQVzDf8/sOA8UUWeVofz',
  'trusted_domains' =>
  array (
    0 => 'localhost',
    1 => '192.168.86.31',
  ),
  'datadirectory' => '/var/www/html/data',
  'dbtype' => 'pgsql',
  'version' => '28.0.2.5',
  'overwrite.cli.url' => 'http://localhost',
  'dbname' => 'nextcloud',
  'dbhost' => '192.168.86.30',
  'dbport' => '',
  'dbtableprefix' => 'oc_',
  'dbuser' => 'oc_sanjeev',
  'dbpassword' => 'fp6jTNSX95MiaOcBTTXmNwSn4rLnP7',
  'installed' => true,
  'instanceid' => 'oc60e39mo1ii',
  'loglevel' => 2,
  'maintenance' => false,

  'preview_libreoffice_path' => '/usr/bin/libreoffice',
  'enable_previews' => true,
  'enabledPreviewProviders' =>
  array (
    0 => 'OC\\Preview\\TXT',
    1 => 'OC\\Preview\\MarkDown',
    2 => 'OC\\Preview\\OpenDocument',
    3 => 'OC\\Preview\\PDF',
    4 => 'OC\\Preview\\MSOffice2003',
    5 => 'OC\\Preview\\MSOfficeDoc',
    6 => 'OC\\Preview\\Image',
    7 => 'OC\\Preview\\Photoshop',
    8 => 'OC\\Preview\\TIFF',
    9 => 'OC\\Preview\\SVG',
    10 => 'OC\\Preview\\Font',
    11 => 'OC\\Preview\\MP3',
    12 => 'OC\\Preview\\Movie',
    13 => 'OC\\Preview\\MKV',
    14 => 'OC\\Preview\\MP4',
    15 => 'OC\\Preview\\AVI',
  ),
);