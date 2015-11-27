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

        function files_compress_expand (filename, context) {
            var dir = context.dir || context.fileList.getCurrentDirectory();
            $.ajax(OC.linkTo('files_compress', 'ajax/extract.php'), {
                type: 'POST',
                data: {
                    filename: filename,
                    dir: dir
                },
                dataType: 'json',
                success: function(s) {
                    if (s.status == "success") { 
					FileList.reload();
                   } else {
                        alert('Could not extract.');
                    }
                },
                error: function(s) {
                    alert('An error occurred.');
                },
	});
}

        function files_compress_compress (filename, context) {
            var dir = context.dir || context.fileList.getCurrentDirectory();
            $.ajax(OC.linkTo('files_compress', 'ajax/compress.php'), {
                type: 'POST',
                data: {
                    filename: filename,
                    dir: dir
                },
                dataType: 'json',
                success: function(s) {
                    if (s.status == "success") { 
					FileList.reload();
                   } else {
                        alert('Could not compress.');
                    }
                },
                error: function(s) {
                    alert('An error occurred.');
                },
	});
}


$(document).ready(function() {
    if (typeof FileActions !== 'undefined') {

        FileActions.register('application/zip', t('files_compress','Extract'), OC.PERMISSION_READ, '',
            function(filename, context) {
                files_compress_expand(filename, context)
            });
        FileActions.register('application/x-gzip', t('files_compress','Extract'), OC.PERMISSION_READ, '',
            function(filename, context) {
                files_compress_expand(filename, context)
            });
        FileActions.register('application/x-rar-compressed', t('files_compress','Extract'), OC.PERMISSION_READ, '',
            function(filename, context) {
                files_compress_expand(filename, context)
            });
        FileActions.register('application/x-compressed', t('files_compress','Extract'), OC.PERMISSION_READ, '',
            function(filename, context) {
                files_compress_expand(filename, context)
            });
            
        FileActions.register('all', t('files_compress','Compress'), OC.PERMISSION_READ, OC.imagePath('core','actions/play'),
            function(filename, context) {
                files_compress_compress(filename, context)
            });
            
    }
});

