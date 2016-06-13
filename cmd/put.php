<?php

/**
 * @author      OA Wu <comdan66@gmail.com>
 * @copyright   Copyright (c) 2016 OA Wu Design
 */

include_once 'libs/functions.php';

define ('PROTOCOL', "http://");
define ('FCPATH', implode (DIRECTORY_SEPARATOR, explode (DIRECTORY_SEPARATOR, dirname (str_replace (pathinfo (__FILE__, PATHINFO_BASENAME), '', __FILE__)))) . '/');
define ('NAME', ($temps = array_filter (explode (DIRECTORY_SEPARATOR, FCPATH))) ? end ($temps) : '');
if (!NAME) {
  echo "\n" . str_repeat ('=', 80) . "\n";
  echo ' ' . color ('◎', 'R') . ' ' . color ('錯誤囉！', 'r') . color ('請確認常數 NAME 是否正確，請洽詢設計者', 'p') . ' ' . color ('OA Wu', 'W') . '(http://www.ioa.tw)' . color ('！', 'p') . '  ' . color ('◎', 'R');
  echo "\n" . str_repeat ('=', 80) . "\n\n";
  exit ();
}

$file = array_shift ($argv);
$argv = params ($argv, array (array ('-b', '-bucket'), array ('-a', '-access'), array ('-s', '-secret')));
if (!(isset ($argv['-b'][0]) && ($bucket = trim ($argv['-b'][0], '/')) && isset ($argv['-a'][0]) && ($access = $argv['-a'][0]) && isset ($argv['-s'][0]) && ($secret = $argv['-s'][0]))) {
  echo "\n" . str_repeat ('=', 80) . "\n";
  echo ' ' . color ('◎', 'R') . ' ' . color ('錯誤囉！', 'r') . color ('請確認參數是否正確，分別需要', 'p') . ' ' . color ('-b', 'W') . '、' . color ('-a', 'W') . '、' . color ('-s', 'W') . ' ' . color (' 的參數！', 'p') . ' ' . color ('◎', 'R');
  echo "\n" . str_repeat ('=', 80) . "\n\n";
  exit ();
}

echo "\n" . str_repeat ('=', 80) . "\n";
echo ' ' . color ('◎ 執行開始 ◎', 'P') . "\n";
echo str_repeat ('-', 80) . "\n";

// // ========================================================================
// // ========================================================================
// // ========================================================================

echo ' ➜ ' . color ('初始化 S3 工具', 'g');

include_once 'libs/s3.php';
S3::init ($access, $secret);
echo ' - ' . color ('初始化成功！', 'C') . "\n";
echo str_repeat ('-', 80) . "\n";

// // ========================================================================
// // ========================================================================
// // ========================================================================

echo ' ➜ ' . color ('列出 S3 上所有檔案', 'g');
try {
  $s3_files = array_filter (S3::getBucket ($bucket), function ($s3_file) {
    return preg_match ('/^' . NAME . '\//', $s3_file['name']);
  });
  echo color ('(' . ($c = count ($s3_files)) . ')', 'g') . ' - 100% - ' . color ('取得檔案成功！', 'C') . "\n";
  echo str_repeat ('-', 80) . "\n";
} catch (Exception $e) {
  echo ' - ' . color ('取得檔案失敗！', 'R') . "\n";
  exit ();
}

// // ========================================================================
// // ========================================================================
// // ========================================================================

$i = 0;
$c = 5;
$local_files = array ();
echo ' ➜ ' . color ('列出即將上傳所有檔案', 'g');

$files = array ();
merge_array_recursive (directory_list ('..'), $files, '..');
$files = array_filter ($files, function ($file) { return in_array (pathinfo ($file, PATHINFO_EXTENSION), array ('html', 'txt')); });
$files = array_map (function ($file) { return array ('path' => $file, 'md5' => md5_file ($file), 'uri' => preg_replace ('/^(\.\.\/)/', '', $file)); }, $files);
echo "\r ➜ " . color ('列出即將上傳所有檔案', 'g') . color ('(' . count ($local_files = array_merge ($local_files, $files)) . ')', 'g') . ' - ' . sprintf ('% 3d%% ', (100 / $c) * ++$i);

$files = array ();
merge_array_recursive (directory_map ('../css'), $files, '../css');
$files = array_filter ($files, function ($file) { return in_array (pathinfo ($file, PATHINFO_EXTENSION), array ('css')); });
$files = array_map (function ($file) { return array ('path' => $file, 'md5' => md5_file ($file), 'uri' => preg_replace ('/^(\.\.\/)/', '', $file)); }, $files);
echo "\r ➜ " . color ('列出即將上傳所有檔案', 'g') . color ('(' . count ($local_files = array_merge ($local_files, $files)) . ')', 'g') . ' - ' . sprintf ('% 3d%% ', (100 / $c) * ++$i);

$files = array ();
merge_array_recursive (directory_map ('../js'), $files, '../js');
$files = array_filter ($files, function ($file) { return in_array (pathinfo ($file, PATHINFO_EXTENSION), array ('js')); });
$files = array_map (function ($file) { return array ('path' => $file, 'md5' => md5_file ($file), 'uri' => preg_replace ('/^(\.\.\/)/', '', $file)); }, $files);
echo "\r ➜ " . color ('列出即將上傳所有檔案', 'g') . color ('(' . count ($local_files = array_merge ($local_files, $files)) . ')', 'g') . ' - ' . sprintf ('% 3d%% ', (100 / $c) * ++$i);

$files = array ();
merge_array_recursive (directory_map ('../font'), $files, '../font');
$files = array_filter ($files, function ($file) { return in_array (pathinfo ($file, PATHINFO_EXTENSION), array ('eot', 'svg', 'ttf', 'woff')); });
$files = array_map (function ($file) { return array ('path' => $file, 'md5' => md5_file ($file), 'uri' => preg_replace ('/^(\.\.\/)/', '', $file)); }, $files);
echo "\r ➜ " . color ('列出即將上傳所有檔案', 'g') . color ('(' . count ($local_files = array_merge ($local_files, $files)) . ')', 'g') . ' - ' . sprintf ('% 3d%% ', (100 / $c) * ++$i);

$files = array ();
merge_array_recursive (directory_map ('../img'), $files, '../img');
$files = array_filter ($files, function ($file) { return in_array (pathinfo ($file, PATHINFO_EXTENSION), array ('png', 'jpg', 'jpeg', 'gif', 'svg')); });
$files = array_map (function ($file) { return array ('path' => $file, 'md5' => md5_file ($file), 'uri' => preg_replace ('/^(\.\.\/)/', '', $file)); }, $files);
echo "\r ➜ " . color ('列出即將上傳所有檔案', 'g') . color ('(' . count ($local_files = array_merge ($local_files, $files)) . ')', 'g') . ' - ' . sprintf ('% 3d%% ', (100 / $c) * ++$i);

// // ========================================================================
// // ========================================================================
// // ========================================================================

echo ' ➜ ' . color ('過濾需要上傳檔案', 'g');
$i = 0;
$c = count ($local_files);
$upload_files = array_filter ($local_files, function ($local_file) use ($s3_files, &$i, $c) {
  foreach ($s3_files as $s3_file)
    if (($s3_file['name'] == (NAME . DIRECTORY_SEPARATOR . $local_file['uri'])) && ($s3_file['hash'] == $local_file['md5']))
      return false;
  echo sprintf ("\r" . ' ➜ ' . color ('過濾需要上傳檔案', 'g') . color ('(' . ($i + 1) . ')', 'g') . " - % 3d%% ", ceil ((++$i * 100) / $c));
  return $local_file;
});
echo sprintf ("\r" . ' ➜ ' . color ('過濾需要上傳檔案', 'g') . color ('(' . count ($upload_files) . ')', 'g') . " - % 3d%% ", 100);
echo '- ' . color ('過濾需要上傳檔案成功！', 'C') . "\n";
echo str_repeat ('-', 80) . "\n";

// // ========================================================================
// // ========================================================================
// // ========================================================================

echo sprintf ("\r" . ' ➜ ' . color ('上傳檔案', 'g') . color ('(' . ($c = count ($upload_files)) . ')', 'g') . " - % 3d%% ", $c ? ceil ((++$i * 100) / $c) : 100);
$i = 0;
if (array_filter (array_map (function ($file) use ($bucket, &$i, $c) {
  echo sprintf ("\r" . ' ➜ ' . color ('上傳檔案', 'g') . color ('(' . $c . ')', 'g') . " - % 3d%% ", ceil ((++$i * 100) / $c));
  try {
    return !S3::putFile ($file['path'], $bucket, NAME . DIRECTORY_SEPARATOR . $file['uri']);
  } catch (Exception $e) {
    return true;
  }
}, $upload_files))) {
  echo '- ' . color ('上傳發生錯誤！', 'r') . "\n";
  echo str_repeat ('=', 80) . "\n";
  return;
}
echo '- ' . color ('上傳成功！', 'C') . "\n";
echo str_repeat ('-', 80) . "\n";

// // ========================================================================
// // ========================================================================
// // ========================================================================

echo ' ➜ ' . color ('過濾需要刪除檔案', 'g');
$i = 0;
$c = count ($s3_files);
$delete_files = array_filter ($s3_files, function ($s3_file) use ($local_files, &$i, $c) {
  foreach ($local_files as $local_file) if ($s3_file['name'] == (NAME . DIRECTORY_SEPARATOR . $local_file['uri'])) return false;
  echo sprintf ("\r" . ' ➜ ' . color ('過濾需要刪除檔案', 'g') . color ('(' . ($i + 1) . ')', 'g') . " - % 3d%% ", ceil ((++$i * 100) / $c));
  return true;
});
echo sprintf ("\r" . ' ➜ ' . color ('過濾需要刪除檔案', 'g') . color ('(' . count ($delete_files) . ')', 'g') . " - % 3d%% ", 100);
echo '- ' . color ('過濾需要刪除檔案成功！', 'C') . "\n";
echo str_repeat ('-', 80) . "\n";

// // ========================================================================
// // ========================================================================
// // ========================================================================

echo sprintf ("\r" . ' ➜ ' . color ('刪除 S3 上需要刪除的檔案(' . ($c = count ($delete_files)) . ')', 'g'));
$i = 0;
echo '- ' . (array_filter (array_map (function ($file) use ($bucket, &$i, $c) {
  echo sprintf ("\r" . ' ➜ ' . color ('刪除 S3 上需要刪除的檔案(' . $c . ')', 'g') . " - % 3d%% ", ceil ((++$i * 100) / $c));
  return !S3::deleteObject ($bucket, $file['name']);
  try {
    return !S3::deleteObject ($bucket, $file['name']);
  } catch (Exception $e) {
    return true;
  }
}, $delete_files)) ? color ('刪除 S3 上需要刪除的檔案失敗！', 'r') : color ('刪除 S3 上需要刪除的檔案成功！', 'C')) . "\n";
echo str_repeat ('-', 80) . "\n";

// // ========================================================================
// // ========================================================================
// // ========================================================================

echo ' ' . color ('◎ 執行結束 ◎', 'P') . "\n";
echo str_repeat ('=', 80) . "\n";
echo "\n";

echo " " . color ('➜', 'R') . " " . color ('您的網址是', 'G') . "：" . color (PROTOCOL . $bucket . '/' . NAME . '/', 'W') . "\n\n";
echo str_repeat ('=', 80) . "\n";
echo "\n";
