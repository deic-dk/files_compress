<?php

function readDirectory($path) {
	if ($handle = opendir($path)) {
		$tree = [];
	    while (false !== ($entry = readdir($handle))) {
	    	if(!($entry==="." || $entry==="..")){
	    		if(is_dir($path.$entry)){
	    			$array['dir'] = true;
	    			$array['name'] = $entry;
	    			$array['entry'] = readDirectory($path.$entry."/");
	    			array_push($tree, $array);
	    		}else{
	    			$array['dir'] = false;
	    			$array['name'] = $entry;
	    			array_push($tree, $array);
	    		}
	    		unset($array);
	    	}
	    }
	    closedir($handle);
	    return $tree;
	}
} 

OCP\JSON::checkLoggedIn();

if(OCP\App::isEnabled('files_compress')){
	$filename	= $_POST["filename"];
	$dir	= $_POST["dir"];
	$user 	= \OCP\USER::getUser();
	$tank_dir="/tank/data/owncloud/";

	$filenameWOext= strtolower(pathinfo($filename, PATHINFO_FILENAME));
	$mime = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

	$archive_dir = $tank_dir.$user."/files".$dir."/";
	$extract_dir = $archive_dir.$filenameWOext."/";

	$success = FALSE;
	if($mime==='zip' || $mime==='gz' || $mime==='tgz' || $mime==='tar' || $mime==='bz2'){
		$phar = new PharData($archive_dir.$filename);
		if ($phar->extractTo($extract_dir, null, true)) {
			$tree = readDirectory($extract_dir);
		    $success = TRUE;
		}
	}elseif($mime==='rar'){
		$rar_file = rar_open($archive_dir.$filename);
		if($rar_file){				 
			$list = rar_list($rar_file);
			foreach($list as $file) {
			    $entry = rar_entry_get($rar_file, $file);
			    $entry->extract($extract_dir);
			}
			rar_close($rar_file);

			$success = TRUE;
		}
	}

	if ($success) {
		OCP\JSON::success(array("data" => array('filename' => $filename, 'archivedir' => $archive_dir, 'dir' => $dir, 'user' => $user, 'mime' => $mime, 'workingdir' => getcwd())));
		//OCP\JSON::success(array("data" => array('tree' => $tree)));
	} else {
		OCP\JSON::error(array("data" => array('filename' => $filename, 'archivedir' => $archive_dir, 'dir' => $dir, 'user' => $user, 'mime' => $mime, 'workingdir' => getcwd())));
	}
}