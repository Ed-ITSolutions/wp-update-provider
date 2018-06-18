<?php
echo("Ed-IT Solutions Plugin Builder");

$zip = new ZipArchive();
$filename = './wp-update-provider.zip';

$rootPath = dirname(dirname(__FILE__));

if($zip->open($filename, ZipArchive::CREATE) !== true){
  exit("Could not create {$filename}");
}

$files = new RecursiveIteratorIterator(
  new RecursiveDirectoryIterator($rootPath),
  RecursiveIteratorIterator::LEAVES_ONLY
);

foreach ($files as $name => $file){
  // Skip directories (they would be added automatically)
  if(!$file->isDir()){
    // Get real and relative path for current file
    $filePath = $file->getRealPath();
    $relativePath = substr($filePath, strlen($rootPath) + 1);

    $dirs = explode(DIRECTORY_SEPARATOR, $relativePath);

    if(
      $dirs[0] !== '.git'
      &&
      $dirs[0] !== '.circleci'
    ){
      $zip->addFile($filePath, $relativePath);
    }    
  }
}

$zip->close();