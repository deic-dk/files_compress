<?php

OCP\JSON::checkLoggedIn();
OCP\App::checkAppEnabled('files_compress');

$filename = $_POST["filename"];
$dir = $_POST["dir"];
$user = \OCP\USER::getUser();
$tank_dir = \OCP\Config::getSystemValue('datadirectory', '');
$user_dir = $tank_dir . "/" . $user . "/";
$files_dir = $user_dir . "/files";
$app_dir = $user_dir . "files_compress/";
$temp_dir = $app_dir .md5($filename)."/";
$filenameWOext = pathinfo($filename, PATHINFO_FILENAME);
$extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
$archive_dir = rtrim($files_dir, "/") . "/" . ltrim($dir, "/") . "/";
$temp_file = $temp_dir . $filenameWOext;
$dest_file = $archive_dir . $filenameWOext;
$tid = -1;
$success = false;

if($extension!='zip' && $extension!= 'gz' && $extension!= 'tgz' && $extension!= 'tar' && $extension!='bz2') {
	OCP\JSON::error("Encoding not supported");
	exit;
}

if(!file_exists($app_dir)){
	\OCP\Util::writeLog('files_compress', 'Creating directory '.$app_dir, \OC_Log::WARN);
	mkdir($app_dir) || die('Could not create '.$app_dir);
}
if(file_exists($temp_dir)){
	rrmdir($temp_dir);
}
\OCP\Util::writeLog('files_compress', 'Creating directory '.$temp_dir, \OC_Log::WARN);
mkdir($temp_dir) || die('Could not create '.$temp_dir);

$tid1 = time();
$newfiles = [];

// Which mimetype to use
switch ($extension) {
	case 'zip':
		$archive = new ZipArchive();
		// check if we can open archive
		$ziparchive =  $archive_dir . $filename;
		/* open archive */
		if(!$archive->open($ziparchive)){
			$success = false;
		}
		// Notice: falling through
	case 'tar':
		$ziparchive =  $archive_dir . $filename;
		$archive = $extension=='zip'?$archive:new PharData($ziparchive);
		$success = $archive->extractTo($temp_dir);
		$tid2 = time();
		$tid = $tid2-$tid1;
		\OCP\Util::writeLog('files_compress', 'UNZIP:: '.$temp_dir." # $success :: $tid secs", \OC_Log::WARN);
		$extension=='zip' && $archive->close();
		if($success){
			/*array_map(function($src) use ($dir, $archive_dir, $temp_dir) {
				\OCP\Util::writeLog('files_compress', 'MOVING '.$src.'-->'.$archive_dir.basename($src), \OC_Log::WARN);
				rename($src, $archive_dir.basename($src));
				$newfiles[] = rtrim($dir, "/")."/".basename($src);
			}, glob($temp_dir."*"));*/
			rename($temp_dir, $dest_file);
			$newfiles[] = rtrim($dir, "/")."/".$filenameWOext;
		}
		else{
			\OCP\Util::writeLog('files_compress', 'UNZIP ERROR '.$temp_dir, \OC_Log::ERROR);
		}
		break;
	case 'tgz':
		$dest_file = $dest_file.'.tar';
		$filenameWOext = $filenameWOext.'.tar';
	case 'gz':
		$ziparchive = $archive_dir . $filename;
		$gz = gzopen($ziparchive, "rb");
		// temp destination file
		$ds = fopen($temp_file, "wb");
		if($ds){
			while(!gzeof($gz)){
				fwrite($ds, gzread($gz, 4096));
			}
			$success = true;
		}
		else{
			$success = false;
			\OCP\Util::writeLog('files_compress', 'GUNZIP:: Could not write to '.$temp_file, \OC_Log::ERROR);
		}
		gzclose($gz);
		fclose($ds);
		$tid2 = time();
		$tid = $tid2-$tid1;
		rename($temp_file, $dest_file);
		rrmdir($temp_dir);
		$newfiles[] = rtrim($dir, "/")."/".$filenameWOext;
		\OCP\Util::writeLog('files_compress', 'GUNZIP:: '.$temp_dir.' :: '. $tid.' secs', \OC_Log::WARN);
		break;
	default:
}

if($success){
	$view = \OC\Files\Filesystem::getView();
	$absPath = $view->getAbsolutePath($dir);
	list($storage, $internalPath) = \OC\Files\Filesystem::resolvePath('/' . $absPath);
	\OCP\Util::writeLog('files_compress', 'Internal path: '.$internalPath, \OC_Log::WARN);
	if($storage){
		$scanner = $storage->getScanner($internalPath);
		array_map(function($file) use ($scanner){
			return $scanner->scanFile($file);
		}, $newfiles);
	}
	$ret = [];
	foreach($newfiles as $newfile){
		$meta = \OC\Files\Filesystem::getFileInfo($newfile);
		$ret[] = \OCA\Files\Helper::formatFileInfo($meta);
	}
	OCP\JSON::success(array('data' => $ret));
}
else{
	OCP\JSON::error();
}

function rrmdir($dir) {
	if (is_dir($dir)) {
		$objects = scandir($dir);
		foreach ($objects as $object) {
			if ($object != "." && $object != "..") {
				if (is_dir($dir."/".$object) && !is_link($dir."/".$object))
					rrmdir($dir."/".$object);
					else
						unlink($dir."/".$object);
			}
		}
		rmdir($dir);
	}
}

