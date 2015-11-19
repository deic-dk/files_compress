(function() { 

	if (!OCA.Files_compress) {
		OCA.Files_compress = {};
	}
	OCA.Files_compress.Util = {

		initialize: function(fileActions) {
			FileActions.register('application/zip', 'Extract', OC.PERMISSION_READ, OC.imagePath('core', 'filetypes/file.png'), function(filename, context) {
 context.fileActions.extract(filename, context)
			});
			FileActions.register('application/x-gzip', 'Extract', OC.PERMISSION_READ, OC.imagePath('core', 'filetypes/file.png'), function(filename, context) {
 context.fileActions.extract(filename, context)
			});
			FileActions.register('application/x-rar-compressed', 'Extract', OC.PERMISSION_READ, OC.imagePath('core', 'filetypes/file.png'), function(filename, context) {
 context.fileActions.extract(filename, context)
			});
			FileActions.register('application/x-compressed', 'Extract', OC.PERMISSION_READ, OC.imagePath('core', 'filetypes/file.png'), function(filename, context) {
 context.fileActions.extract(filename, context)
			});
		},


        extract: function (filename, context) {
            var dir = context.dir || context.fileList.getCurrentDirectory();
            $.ajax(OC.linkTo('files_compress', 'ajax/extract.php'), {
                type: 'POST',
                data: {
                    filename: filename,
                    dir: dir
                },
                dataType: 'json',
                success: function(s) {
                    if (s['status'] == "success") {
                        alert('Extraction was successful.');
                    } else {
                        alert('Could not extract.');
                    }
                },
                error: function(s) {
                    alert('An error occurred.');
                },
	});
}}();




$(document).ready(function() {
	if (!_.isUndefined(OCA.Files)) {
		OCA.Files_compress.Util.initialize(OCA.Files.fileActions);
	}
	}); 



}); 

