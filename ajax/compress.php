<?php

/*
 * files_compress, ownCloud archive handling app
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU AFFERO GENERAL PUBLIC LICENSE for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

OCP\JSON::checkLoggedIn();

if (OCP\App::isEnabled('files_compress')) {
		$filename  = $_POST["filename"];
		$dir       = $_POST["dir"];
		$user      = \OCP\USER::getUser();

		/* Directories to use */
		$tank_dir  = \OCP\Config::getSystemValue('datadirectory',1);
		$user_dir  = $tank_dir . "/" . $user . "/";
		$files_dir = $user_dir . "/files"; 
		$temp_dir  = $user_dir . "fc_tmp/";

		$ext       =  ".zip";
		$mime	   = strtolower(pathinfo($filename, PATHINFO_EXTENSION)); 
		$mime_org  = pathinfo($filename, PATHINFO_EXTENSION); 

		$pattern  = '/\((\d+)\)/i';
		preg_match($pattern, $filename,$match);

		$archive_dir    = str_replace("//", "/", $files_dir  . $dir . "/");

		$compress_entry = $archive_dir . $filename;
		
		$tempfile = $temp_dir . $filename . $ext;

		$zipfile  = $compress_entry . $ext; // archive name including path
		
		$success = FALSE;
		
		$sti = "/tank/data/tmp";
		// for debugging - remove
		$fh = fopen($sti."/compress.log", 'a');

		/* we should do our dirty work in a tempdir */
		if (!file_exists($temp_dir)) {
			mkdir($temp_dir);
		}


		$dirlist = array();
		$dirlist = scandir($compress_entry);

		// Check if archive exists and create versioning if not
		if (file_exists($zipfile)) {
			fwrite($fh, "\n\n#####################################\n\nEXISTS: $zipfile\n");


					
		}
		$checkfile = file_exists($zipfile);


		$compress_entry = $compress_entry;

		#fwrite($fh, "\n\n#####################################\n\nEXISTS: $checkfile\n");
		/* Check if file exists */
		if (is_dir($compress_entry)) {

			$dirlist = scandir($archive_dir);
			foreach ($dirlist as $key => $value) {
				
				


				$pattern  = '/\(\d+\)/i';
				preg_match($pattern, $filename,$match);
				foreach ($match as $key => $value) {
					fwrite($fh, "\n###### SCAN: $key => $value :: $zipfile ##############"); 
					fwrite($fh, "\n###### MATCH: $key => $value ##############");   
				}

				if ($filename.$ext == $value) {
					

					
					$zipexists = TRUE;
				} 
			}
			
			$cmpr_dir = $archive_dir;
		} else {
			$dirlist = array_diff(scandir($archive_dir), array('.','..'));
			foreach ($dirlist as $key => $value) {

fwrite($fh, "\n###### Check: $checkfile :: $value ##############");
				if ($checkfile == 1) {
					$f = explode('.',$value);
					foreach ($f as $ke => $va) {
				//		fwrite($fh, "\n# $ke => $va #");
					}

					$pattern  = '/_v\d+/i';
					preg_match($pattern, $value,$match);
					foreach ($match as $k => $v) {
						$f = explode(".",$value);
						foreach ($f as $ke => $va) {
							fwrite($fh, "\n# M: $ke => $va #");
						}
						//fwrite($fh, "\n###### MATCH: $k => $v + $mime_org ##############");
						//fwrite($fh, "\n###### SCAN: $key => $value :: ".$filename.$ext."  ##############");  
					}
				}

				if ($filename.$ext == $value) {
					
					$zipexists = TRUE;
					
				}

				//findFileVersion($value);
			}
			$cmpr_dir = $archive_dir;
		}

		if (!$zipexists) {

			$tid1 = time();
			$zipcmd = escapeshellcmd('zip -r '.escapeshellarg($tempfile).' '.escapeshellarg($filename));

			$cmd    = 'cd '.escapeshellarg($cmpr_dir).' && ';

			//fwrite($fh, "CMDTEST:: $cmd\n$zipcmd -- ".escapeshellarg($tempfile)."\nTID1: $tid1");


			if (exec($cmd . $zipcmd)) {
				$success = TRUE;
			}

			$tid2 = time();
			$tid = $tid2 - $tid1;

			// move everything in place
				
			$return = rename($tempfile, $zipfile);

			// cleanup by deleting all files and temp directory
			$files = glob($temp_dir . '{,.}*', GLOB_BRACE);
			foreach ($files as $file) { // iterate files
					if (is_file($file)) {
							unlink($file);
					} // delete file
			}
			
			if (file_exists($temp_dir)) {
					rmdir($temp_dir);
			}
			
			// success is determined by .zip file in proper place
			$success = file_exists($zipfile);
			fwrite($fh, "\nZIP:: $zipfile\nTID: $tid secs. \n###########################\nSUCCESS :: $success ++ $zipexists\n");
		} else {
			$success = FALSE;
			$explain = array('message'=>'Archive file exists!');
		}
		if ($success == TRUE) {
			OCP\JSON::success();
		} else {
			OCP\JSON::error($explain);
			fwrite($fh, "\nFAIL:\n###########################\nFAILURE :: $success\n");
		} 


function findFileVersion($value) {
	$pattern  = '/_v\d+/i';
	preg_match($pattern, $value,$match);
	foreach ($match as $k => $v) {
		$f = explode(".",$value);
		foreach ($f as $ke => $va) {
			fwrite($fh, "# $ke => $va #");
		}
		//fwrite($fh, "\n###### MATCH: $k => $v + $mime_org ##############");
		//fwrite($fh, "\n###### SCAN: $key => $value :: ".$filename.$ext."  ##############");  
	}
}



}
