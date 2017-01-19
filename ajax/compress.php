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
		$tank_dir    = \OCP\Config::getSystemValue('datadirectory',1);
		$user_dir    = $tank_dir . "/" . $user . "/";
		$files_dir   = $user_dir . "/files"; 
		$temp_dir    = $user_dir . "fc_tmp/";
		$archive_dir = str_replace("//", "/", $files_dir  . $dir . "/");

		$ext       =  ".zip";
		$mime	   = strtolower(pathinfo($filename, PATHINFO_EXTENSION)); 
		$mime_org  = pathinfo($filename, PATHINFO_EXTENSION);

		$compress_entry = $archive_dir . $filename; // file name including path to files directory
		$tempfile = $temp_dir . $filename . $ext; // archive name including path to temp directory
		$zipfile  = $compress_entry . $ext; // archive name including path 
		$success = FALSE;

		$file_parts = explode('.', $filename);	
		$no_parts = count($file_parts);

		// for debugging - remove
		$sti = "/tank/data/tmp";
		$fh = fopen($sti."/compress.log", 'a');

		// Check if archive exists and create versioning if not

		if ($mime !== 'zip') {
		
			// Check if archive exists and create versioning if true
			if (file_exists($zipfile)) {
				$zipexists = TRUE;
			} else {
				$zipexists = FALSE;
			}

			// Findes filen eller noget der ligner
			$dirlist = array();
			$dirlist = scandir($archive_dir);
			$versioning = array();
			$matchfile  = array();
			$versionNumber = 0;
			
			foreach ($dirlist as $key => $value) {
				$pattern  = '/^('.$file_parts[0].')/i';
				if (preg_match($pattern, $value, $m)) {
					array_push($matchfile, $value);
				}
			}

			$no_match = count($matchfile); // How many files did we find

			if ($no_match > 0) {
				fwrite($fh, "TODO: $no_match\n");
				switch ($no_match) {
					case '1':
						$chkversioning = 0;
						fwrite($fh, "MATCH 1: $mime => $matchfile[0]\n");
						break;
					case '2':
						$chkversioning = 1;
						$chkforzip = 0;
		
						foreach ($matchfile as $k => $v) {
							fwrite($fh, "SWITCH 2: $k => $v \n");
						}
						break;
					default:
						$chkversioning = 1;
						foreach ($matchfile as $k => $v) {
							fwrite($fh, "SWITCH D: $k => $v \n");
						}
						break;
				}
			}

			// Check versioning
			if ($chkversioning == 1) {
				$pattern  = '/(.*)\((\d+)\)(.*)/i';
				foreach ($matchfile as $k => $v) {
					if (preg_match($pattern, $v, $match)) {
						fwrite($fh, "VER 1: HIC\n");
						$versionmatch = 1;
						$chkversioning = 1;
						$orgFilenameWOver = rtrim($match[1]);
						$versionNumber = $match[2];
						foreach ($match as $key => $value) {
							fwrite($fh, "M: $key => $value\n");
						}
	
					} elseif ($versionNumber < 2) {
						fwrite($fh, "VER 2: $filename: $versionNumber\n");
						$chkversioning = 0;
						//$versionmatch = 0;
					}
				}
			}

			if ($chkversioning == 0) { 					

				$versionmatch = 0;
				//  file exists but has no versioning
				if ($zipexists) {
					//fwrite($fh, "CHK: $chkversioning $mime\n");
					// check mimetype
					//if ($mime != 'zip') {
						
						$versionNumber = 2;

						switch ($no_parts) {
							case '1':
								$newVersionFile = $file_parts[0] . " (".$versionNumber.")".$ext;
								break;
							case '2':
								$newVersionFile = $file_parts[0] . " (".$versionNumber.").".$file_parts[1].$ext;
								$versionmatch = 0;
								break;
							case '3':
								$newVersionFile = $file_parts[0] . " (".$versionNumber.").".$file_parts[1].".".$file_parts[2];
								break;
							default:
								# code...
								break;
						}
						$zipfile = $archive_dir . $newVersionFile;
						fwrite($fh, "NEW: $newVersionFile, antal: $no_parts\nLAST MIME: ".end($file_parts)."\n");
					/*} else {

						$zipexists = TRUE;
						$success = FALSE;
						$explain = array('message'=>'File is a zip-archive! Zip-archives are not compressed!');
					}*/

				} else {
					$zipexists = TRUE;
					$success = FALSE;
					$explain = array('message'=>'File is a zip-archive! Zip-archives are not compressed!');
					fwrite($fh, "NO VERSION: $filename, antal: $no_parts\nLAST MIME: ".end($file_parts)."\n\n");
				}
				
			}

		/*
		foreach ($file_parts as $key => $value) {
			fwrite($fh, "PARTS: $key => $value\n");
		}
		*/

			// If input is a file
			if ($versionmatch == 1) {
				$versioning = array();
				if (is_file($compress_entry)) {
					foreach ($matchfile as $key => $value) {
						$pattern  = '/\((\d+)\)/i';
						if (preg_match($pattern, $value, $m)) {
							array_push($versioning, $m[1]);

						}
					}
				} elseif (is_dir($compress_entry)) {
					foreach ($matchfile as $key => $value) {
						$pattern  = '/\((\d+)\)/i';
						if (preg_match($pattern, $value, $m)) {
							array_push($versioning, $m[1]);
							fwrite($fh, "PARTS: $key => $value + ".$m[1]."\n");
						}
					}
				}

				$newversionNumber = max($versioning);
				$newversionNumber++;
				if ($mime != '') {
					$mime = '.'.$mime;
				} else {
					$mime = '';
				}

				$newVersionFile = $orgFilenameWOver . " (".$newversionNumber.")".$mime.$ext;
				$zipfile = $archive_dir . $newVersionFile;
			}

		 
			fwrite($fh, "ZIP: ".$zipfile." \n###############\n\n");

			/* we should do our dirty work in a tempdir */
			if (!file_exists($temp_dir)) {
				mkdir($temp_dir);
			}


			$checkfile = file_exists($zipfile);

			/* Check if file exists */
			if (file_exists($zipfile)) {
				$zipexists = TRUE;
			} else {
				$zipexists = FALSE;
			}

			$b64_tmpfile = base64_encode($filename);
			$fc_tmpfile = $temp_dir . $b64_tmpfile . $ext;

			$b64_newfile = base64_encode($newVersionFile);
			$fc_newfile = $temp_dir . $b64_newfile;


		if (!$zipexists) {
			$base64_file = base64_encode($compress_entry);

			$tid1 = time();
			$zipcmd = "/usr/local/bin/zip -r ".escapeshellarg($fc_tmpfile)." ".escapeshellarg($filename);

			$cmd    = 'cd '.escapeshellarg($archive_dir).' && '.$zipcmd;

			if (exec($cmd)) {
				$success = TRUE;
			} else {
				$success = FALS;
			}

			$tid2 = time();
			$tid = $tid2 - $tid1;

			// move everything in place
			$return = rename($fc_tmpfile, $zipfile);
			
			fwrite($fh, "TMP: $fc_tmpfile \nZIP: $zipfile\nTID: $tid\n###############\n#\n");

			// cleanup by deleting all files and temp directory
			$files = glob($temp_dir . '{,.}*', GLOB_BRACE);
			foreach ($files as $file) { // iterate files
				if (is_file(escapeshellarg($file))) {
					unlink($file);
				} // delete file
			}
			
			if (file_exists($temp_dir)) {
				rmdir($temp_dir);
			}
			
			// success is determined by .zip file in proper place
			$success = file_exists($zipfile);
		} else {
			$success = FALSE;
			$explain = array('message'=>'Archive file exists!');
		}
	} else {
		$explain = array('message'=>'File is a zip-archive! Zip-archives are not to be compressed!');
	}
		if ($success == TRUE) {
			OCP\JSON::success();
		} else {
			OCP\JSON::error($explain);
		} 
fclose($fh);

}
