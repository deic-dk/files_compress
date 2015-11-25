<?php

/*                                                                                                                                
 * files_compress, ownCloud file decompression app 
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

// Include our class of library functions
OC::$CLASSPATH['OC_Files_Archive_Util'] ='apps/files_compress/lib/archiveutils.php';

// Load our action script
	if(\OCP\User::isLoggedIn() ){
		\OCP\Util::addScript('files_compress', 'fileactions'); 
}

