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

class OC_Files_Archive_Util {

public static function readDirectory($path) {
	if ($handle = opendir($path)) {
		$tree = [];
	    while (false !== ($entry = readdir($handle))) {
	    	if (!($entry==="." || $entry==="..")){
	    		if (is_dir($path.$entry)){
	    			$array['dir'] = true;
	    			$array['name'] = $entry;
	    			$array['entry'] = self::readDirectory($path.$entry."/");
	    			array_push($tree, $array);
	    		} else {
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

}

