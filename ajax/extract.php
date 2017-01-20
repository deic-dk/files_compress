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
	$filename = $_POST["filename"];
	$dir      = $_POST["dir"];
	$user     = \OCP\USER::getUser();
	$tank_dir = \OCP\Config::getSystemValue('datadirectory',1);

	$user_dir = $tank_dir . "/" . $user . "/";
	$temp_dir = $user_dir . "fc_tmp/";

	/* Archive name */
	$filenameWOext = pathinfo($filename, PATHINFO_FILENAME);
	$mime          = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
	
	$archive_dir = $tank_dir . "/" . $user . "/files" . $dir . "/";
	$archive_dir = str_replace("//", "/", $archive_dir);
	$extract_dir = $archive_dir . $filenameWOext . "/";
	$newdest     = $archive_dir . $filenameWOext;
	$temp_file   = $temp_dir . $filenameWOext;
	$ziparchive = $archive_dir . $filename; // Archive to extract

	$success = FALSE;
	$buffer_size = 8192; // buffer for extraction

	// for debugging - remove
	$sti = "/tank/data/tmp";
	$fh = fopen($sti."/compress.log", 'a');

	// find tar.gz file
	$pattern  = '/\.(tgz$)|(tar\.gz$)/i';
	preg_match($pattern, $filename,$match);

	foreach ($match as $key => $value) {
		if ($value == 'tar.gz') {
			$mime = 'tar.gz';
		}
	}

	if ($mime === 'zip' || $mime === 'gz' || $mime === 'tgz' || $mime === 'tar' || $mime === 'bz2' || $mime = 'tar.gz') {

			// we should do our dirty work in a tempdir
			if (!file_exists($temp_dir)) {
				mkdir($temp_dir) || die('Could not create '.$temp_dir);
			}
			$tid1 = time();
			// Which mimetype to use
			switch ($mime) {
				case 'zip':
					$unzip = new ZipArchive();
					
					$b64_tmpfile = base64_encode($filenameWOext);
					$ziparchive =  $archive_dir . $filename;

					// check if we can open archive
					/* open archive */
					if ($unzip->open($ziparchive) === TRUE) {
						// unzip if archive is open
						$success = $unzip->extractTo($temp_dir);
					} else {
						$success = FALSE;
					}
					$unzip->close();
					break;

				case 'tar.gz':
					
					$cmd = escapeshellcmd('/usr/bin/tar -xzf '.escapeshellarg($ziparchive).' -C '.escapeshellarg($temp_dir));
					$tar_file = pathinfo($filenameWOext, PATHINFO_FILENAME);
					$newdest = $archive_dir.$tar_file;
					$temp_file = $temp_dir.$tar_file;

					if (exec($cmd)) {
						
					}
					break;
				case 'tgz':
					
					$cmd = escapeshellcmd('/usr/bin/tar -xzf '.escapeshellarg($ziparchive).' -C '.escapeshellarg($temp_dir));

					if (exec($cmd)) {
						
					}
					break;
				case 'gz':
	
					$gz = gzopen($ziparchive, "rb");

					// temp destination file in binary mode
					$ds = fopen($temp_file, "wb");

					if($ds){
						while (!gzeof($gz)) {
							fwrite($ds, gzread($gz, $buffer_size));
						}
						$success = TRUE;
					} else {
						$success = FALSE;
					}

					gzclose($gz);
					fclose($ds);

					if (is_file($temp_file)) {
						# code...
					}
					
					// decompress from gz
					/* Prøv med Phar igen, når den kan installeres
					$p = new PharData($ziparchive);
					$p->decompress(); // creates /path/to/my.tar
					*/
					break; 
				case 'bz2':
					$bz = bzopen($ziparchive, "r") or die("Couldn't open $ziparchive for reading");


					//$bz = bzopen($ziparchive, "rb");
					$ds = fopen($temp_file, "wb");

					if ($ds) {
						while (!feof($bz)) {
							fwrite($ds, bzread($bz, $buffer_size));
						}
						$success = TRUE;
					} else {
						$success = FALSE;
					}
					bzclose($bz);
					fclose($ds);
					$tid2 = time();
					$tid = $tid2-$tid1;
					break;
				case 'tar':
					$cdcmd  = 'cd '.escapeshellarg($temp_dir).' && ';
					$tarcmd = '/usr/bin/tar xf';

					// untar to temp dir
					$cmd = escapeshellcmd('/usr/bin/tar xf '.escapeshellarg($ziparchive).' -C '.escapeshellarg($temp_dir));
					if (exec($cmd)) {
						
					}
					$tid2 = time();
					$tid = $tid2-$tid1;
					break;
				default:
					# code...
					break;
			}
			$tid2 = time();
			$tid = $tid2-$tid1;



			// Findes filen eller noget der ligner
			
			$dirlist = array();
			$dirlist = scandir($temp_dir);
			$versioning = array();
			$matchfile  = array();
			$versionNumber = 0;
			
			foreach ($dirlist as $key => $file) {
					if( in_array(substr($file, strrpos($file, '/')+1), array('.', '..')) ) {
						continue;
					}
				//$pattern  = '/^('.$file_parts[0].')/i';
				//if (preg_match($pattern, $value, $m)) {
					//fwrite($fh, "FILE: $value\n\n");
					//$dir = rtrim($archive_dir,'/');
					$target = OCP\Files::buildNotExistingFileName(stripslashes($dir), $file);
					//fwrite($fh, "TARGET: $file::$target\n".stripslashes($dir)."\n");
					OCP\Util::writeLog("files_compress", "B: $target::$file::$dir", \OC_Log::ERROR);
					//array_push($matchfile, $value);
				//}
			}



			/* Move the files to correct path */
			if (is_dir($temp_file) === true) {
				$target = OCP\Files::buildNotExistingFileName(stripslashes($dir), $temp_file);
				fwrite($fh, "FILE: ".$temp_file."\nNEW FILE: $target\n######\n");
				$mvcmd = escapeshellcmd("mv ".escapeshellarg($temp_file).' '.escapeshellarg($archive_dir));

				$mvfiles = 1;
			} else {
				$extract_dir = rtrim($extract_dir, "/");
				$mvfiles = 1;
			}


			// cleanup by deleting all files and temp directory
			//$files = glob($temp_dir . '{,.}*', GLOB_BRACE);
			$files = scandir($temp_dir);

			if ($mvfiles === 1) {

				foreach ($files as $file) { // iterate files
					// Skip '.' and '..'
					if( in_array(substr($file, strrpos($file, '/')+1), array('.', '..')) ) {
						continue;
					}
					$dir = rtrim($dir,'/');
					$archive_dir = rtrim($archive_dir,'/');
					$target = OCP\Files::buildNotExistingFileName(stripslashes($dir), $file);

					//$mvcmd = escapeshellcmd("mv ".escapeshellarg($file).' '.escapeshellarg($archive_dir.stripslashes($target)));
					$newdest = $archive_dir.stripslashes($target);
					fwrite($fh, "FILE: ".$file."\nNEW FILE: $newdest\n######\n");

					rename($temp_dir.$file, $newdest);
					/*if (exec($mvcmd)) {
						$success = TRUE;
					}
*/
					if (is_file($file)) {
						//unlink($file);
					} else { // delete file
						$p2 = '/(\.)|(\.\.)/';
					}
				
				}
			} else {
				if (exec($mvcmd)) {
					$success = TRUE;
				}
			}
	
		// $success = file_exists($newdest);

		// cleanup by deleting temp directory
		if (file_exists($temp_dir)) {
			rmdir($temp_dir);
		}
	}        

	if ($success) {
			OCP\JSON::success();
	} else {
			OCP\JSON::error();
	}
} 

