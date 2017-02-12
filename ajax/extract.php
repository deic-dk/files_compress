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
                $archive_dir    = str_replace("//", "/", $archive_dir);
                $extract_dir = $archive_dir . $filenameWOext . "/";
                $temp_file   = $temp_dir . $filenameWOext;

                $tid = "";
                
                $sti = "/tank/data/tmp";
                $fh = fopen($sti."/extract.log", 'a');

                $success = FALSE;

                if ($mime === 'zip' || $mime === 'gz' || $mime === 'tgz' || $mime === 'tar' || $mime === 'bz2') {

                        // we should do our dirty work in a tempdir
                        if (!file_exists($temp_dir)) {
														fwrite($fh, 'Creating directory '.$temp_dir."\n");
                            mkdir($temp_dir) || die('Could not create '.$temp_dir);
                        }
                        $tid1 = time();
                        // Which mimetype to use
                        switch ($mime) {
                                case 'zip':
                                    $unzip = new ZipArchive();
                                    
                                    // check if we can open archive
                                    $ziparchive =  $archive_dir . $filename;

                                    /* open archive */
                                    if ($unzip->open($ziparchive) === TRUE) {
                                        // unzip if archive is open
                                        $success = $unzip->extractTo($temp_dir);        
                                    } else {
                                        $success = FALSE;
                                    }

                                    $tid2 = time();
                                    $tid = $tid2-$tid1;
                                    fwrite($fh, "UNZIP:: ".$temp_dir." # $success :: $tid secs.\n");
                                    $unzip->close();
                                    break;
                                case 'gz':
                                    //
                                    
                                    $ziparchive = $archive_dir . $filename;
                                    
                                    //$gunzipcmd = 'gunzip -c '.$ziparchive.' > '.$temp_dir;

                                    //if (exec(escapeshellcmd($gunzipcmd))) { $success = 1; error_log($gunzipcmd); }

                                    $gz = gzopen($ziparchive, "rb");
                                    //$content = gzread($ziparchive, 250000);

                                    fwrite($fh, "GUNZIP:: ".$temp_file."\n");

                                    // temp destination file
                                    $ds = fopen($temp_file, "wb");
                                    
														if($ds){
															while (!gzeof($gz)) {
																fwrite($ds, gzread($gz, 4096));
															}
															$success = TRUE;
														} else {
															$success = FALSE;
															fwrite($fh, "GUNZIP:: Could not write to ".$temp_file."\n");
														}

                                    gzclose($gz);
                                    fclose($ds);

                                    $tid2 = time();
                                    $tid = $tid2-$tid1;
                                    fwrite($fh, "GUNZIP:: ".$temp_dir." # $success :: $tid secs.\n");
                                    
                                    break;    
                                default:
                                    # code...
                                    break;
                        }


                        /* Move the files to correct path */
                        if (is_dir($temp_file) === true) {

                            $mvcmd = escapeshellcmd("mv ".$temp_file.' '.$archive_dir);
            
                        } else {
                            $extract_dir = rtrim($extract_dir, "/");
                        }

 
                        // cleanup by deleting all files and temp directory
                        $files = glob($temp_dir . '{,.}*', GLOB_BRACE);

                        foreach ($files as $file) { // iterate files
                            if( in_array(substr($file, strrpos($file, '/')+1), array('.', '..')) ) {
                                continue;
                            }
                                
                            if (is_file($file)) {
                                
                                //$test = $archive_dir.$path[count($path)-1];
                                //$test = escapeshellarg($test);
                                //unlink($file);
                            } else { // delete file
                                $p2 = '/(\.)|(\.\.)/';
                        /*
                                if (preg_match($p2, $file, $match)) {
                                    fwrite($fh, "DIR OUT:: $file\nREGEX: $file\n###############\n");
                                } else {
                                    //$success = rename($file, $archive_dir);
                                    fwrite($fh, "DIR IN:: $file\nTMP_FILE: $temp_file\nARCHIVE_DIR: $archive_dir\nSUCCESS: $success###############\n\n");
                                }
                                */
                            }

                            $mvcmd = escapeshellcmd("mv ".escapeshellarg($file).' '.escapeshellarg($archive_dir));
                       
                            if (exec($mvcmd)) {
                                $success = TRUE;
                            }
 
                    }


                    // cleanup by deleting temp directory
                    if (file_exists($temp_dir)) {
                        //rmdir($temp_dir);
                    }
                }        


                if ($success) {
                        fwrite($fh, "SUCCESS :: $success\n");
                        OCP\JSON::success();
                } else {
                        OCP\JSON::error();
                        fwrite($fh, "LOOP: FEJL :: $success\n");
                }
                fclose($fh);
} 

