<?php

OCP\JSON::checkLoggedIn();

if (OCP\App::isEnabled('files_compress')){
	$filename	= $_POST["filename"];
	$dir	= $_POST["dir"];
	$user 	= \OCP\USER::getUser();
	$tank_dir="/tank/data/owncloud/";

	$filenameWOext= strtolower(pathinfo($filename, PATHINFO_FILENAME));
	$mime = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

	$archive_dir = $tank_dir.$user."/files".$dir."/";
	$extract_dir = $archive_dir.$filenameWOext."/";

	$success = FALSE;
	if ($mime==='zip' || $mime==='gz' || $mime==='tgz' || $mime==='tar' || $mime==='bz2'){
		$phar = new PharData($archive_dir.$filename);
		if ($phar->extractTo($extract_dir, null, true)) {
			$tree = OC_Files_Archive_Util::readDirectory($extract_dir);
		    $success = TRUE;
		}
	} elseif ($mime==='rar'){
		$rar_file = rar_open($archive_dir.$filename);
		if ($rar_file){				 
			$list = rar_list($rar_file);
			foreach ($list as $file) {
			    $entry = rar_entry_get($rar_file, $file);
			    $entry->extract($extract_dir);
			}
			rar_close($rar_file);

			$success = TRUE;
		}
	}

	if ($success) {
		OCP\JSON::success(array("data" => array('filename' => $filename, 'archivedir' => $archive_dir, 'dir' => $dir, 'user' => $user, 'mime' => $mime, 'workingdir' => getcwd())));
	} else {
		OCP\JSON::error(array("data" => array('filename' => $filename, 'archivedir' => $archive_dir, 'dir' => $dir, 'user' => $user, 'mime' => $mime, 'workingdir' => getcwd())));
	}
}

