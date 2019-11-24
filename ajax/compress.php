<?php

OCP\JSON::checkLoggedIn();

OCP\App::checkAppEnabled('files_compress');

$dirname = $_POST["filename"];
$parentdir = $_POST["dir"];
$user = \OCP\USER::getUser();

$tank_dir = \OCP\Config::getSystemValue('datadirectory',1);
$user_dir = $tank_dir . "/" . $user . "/";
$files_dir = $user_dir . "/files"; 
$temp_dir = $user_dir . "files_compress/";
$ext = ".zip";
$mime = strtolower(pathinfo($dirname, PATHINFO_EXTENSION)); 
$mime_org = pathinfo($dirname, PATHINFO_EXTENSION); 
$pattern = '/\((\d+)\)/i';
$full_parent_dir = preg_replace("|/+|", "/", $files_dir . "/".  $parentdir . "/");
$full_path = $full_parent_dir . $dirname;
$tempfile = $temp_dir . $dirname . $ext;
$zipfile = $full_path . $ext;
$zipRelativeFile = rtrim($parentdir, '/') . '/' . ltrim($dirname, '/') . $ext;

$l = OC_L10N::get('files_accounting');

if(!file_exists($temp_dir)){
	mkdir($temp_dir);
}

if(file_exists($zipfile)){
	$err = $l->t('Archive file %1$s already exists', array($zipfile));
}
// zip
elseif(!zip($full_path, $tempfile)){
	$err = $l->t('Something went wrong');
}
// move archive in place
elseif(!rename($tempfile, $zipfile)){
	$err = $l->t('Zip file not found');
}
else{
	$view = \OC\Files\Filesystem::getView();
	$absPath = $view->getAbsolutePath($zipRelativeFile);
	list($storage, $internalPath) = \OC\Files\Filesystem::resolvePath('/' . $absPath);
	if($storage){
		$scanner = $storage->getScanner($internalPath);
		$scanner->scanFile($zipRelativeFile);
	}
}

if(empty($err) && !empty($internalPath) && $storage){
	\OCP\Util::writeLog('files_sharding', 'zipfile: '.$zipRelativeFile, \OC_Log::WARN);
	$meta = \OC\Files\Filesystem::getFileInfo($zipRelativeFile);
	OCP\JSON::success(array('data' => \OCA\Files\Helper::formatFileInfo($meta)));
}
else {
	OCP\JSON::error(array('message'=>$err));
}

// From https://stackoverflow.com/questions/1334613/how-to-recursively-zip-a-directory-in-php
function zip($source, $destination){
	$zip = new ZipArchive();
	if(!$zip->open($destination, ZIPARCHIVE::CREATE)){
		return false;
	}
	if(is_dir($source)){
		$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source),
			RecursiveIteratorIterator::SELF_FIRST);
		foreach ($files as $file){
			$file = str_replace('\\', '/', $file);
			// Ignore "." and ".." folders
			if(in_array(substr($file, strrpos($file, '/')+1), array('.', '..'))){
				continue;
			}
			$file = realpath($file);
			if(is_dir($file)){
				$zip->addEmptyDir(str_replace($source . '/', '', $file . '/'));
			}
			elseif(is_file($file)){
				$zip->addFromString(str_replace($source . '/', '', $file), file_get_contents($file));
			}
		}
	}
	elseif(is_file($source)){
		$zip->addFromString(basename($source), file_get_contents($source));
	}
	return $zip->close();
}

