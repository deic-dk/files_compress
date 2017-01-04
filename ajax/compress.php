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
        $archive_dir    = str_replace("//", "/", $files_dir  . $dir . "/");

		$ext       =  ".zip";
		$mime	   = strtolower(pathinfo($filename, PATHINFO_EXTENSION)); 
		$mime_org  = pathinfo($filename, PATHINFO_EXTENSION); 

		$pattern  = '/\((\d+)\)/i';
		preg_match($pattern, $filename,$match);

		$compress_entry = $archive_dir . $filename; // file name including path to files directory
		$tempfile = $temp_dir . $filename . $ext; // archive name including path to temp directory
		$zipfile  = $compress_entry . $ext; // archive name including path
		
		$success = FALSE;

		/* we should do our dirty work in a tempdir */
		if (!file_exists($temp_dir)) {
			mkdir($temp_dir);
		}

		$dirlist = array();
		$dirlist = scandir($compress_entry);

		// Check if archive exists and create versioning if not
		if (file_exists($zipfile)) {
            $zipexists = TRUE;
		} else {
            $zipexists = FALSE;
        }

		$checkfile = file_exists($zipfile);

		/* Check if file exists */

		if (!$zipexists) {

			$tid1 = time();
			$zipcmd = escapeshellcmd('/usr/local/bin/zip -r '.escapeshellarg($tempfile).' '.escapeshellarg($filename));

			$cmd    = 'cd '.escapeshellarg($archive_dir).' && ';

			if (exec($cmd . $zipcmd)) {
				$success = TRUE;
			} else {

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
		} else {
			$success = FALSE;
			$explain = array('message'=>'Archive file exists!');
		}
		if ($success == TRUE) {
			OCP\JSON::success();
		} else {
			OCP\JSON::error($explain);
		} 

}
