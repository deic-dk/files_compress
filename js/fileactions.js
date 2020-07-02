		function files_compress_expand(filename, context) {
			var dir = context.dir || context.fileList.getCurrentDirectory();

			files_compress_throbber('Decompressing file(s) ...');
			$.ajax(OC.linkTo('files_compress', 'ajax/extract.php'), {
				type: 'POST',
				data: {
					filename: filename,
					dir: dir
				},
				dataType: 'json',
				success: function(s) {
					if (s.status == "success") {
						files_compress_throbber_hide();
						//FileList.reload();
						for(var i=0; i<s.data.length; ++i){
							FileList.add(s.data[i], {hidden: false, animate: true});
						}
					}
					else {
						files_compress_throbber_hide();
						OC.dialogs.alert(s.message, t('files_compress', 'Could not extract'));
					}
				},
				error: function(s) {
					files_compress_throbber_hide();
					alert('An error occurred.');
				},
			});
		}

		function files_compress_compress(filename, context) {
			files_compress_throbber('Archiving file(s) ...');

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
						files_compress_throbber_hide();
						//FileList.reload();
						FileList.add(s.data, {hidden: false, animate: true});
					}
					else {
						files_compress_throbber_hide();
						OC.dialogs.alert(s.message, t('files_compress', 'Could not compress'));
					}
				},
				error: function(s) {
					files_compress_throbber_hide();
					alert('An error occurred');
				},
			});
		}

		function files_compress_throbber(compress_txt) {
			/* Load throbber div */
   
			$('<div/>', {
				id: 'throbber',
				style: 'width: 100%; height: 100%; position: fixed; display: block; text-align: center; z-index:1234;',
			}).appendTo("body");

			$('<div/>', {
				id: 'throbber-txt',
				style: 'top: 45%; left: 25%; right: 25%; margin: auto; width:50%; height: 30px; position: fixed; text-align: center; display: block;',
				text: compress_txt,
			}).appendTo('#throbber');

			$('<div/>', {
				id: 'throbber-img-div',
				style: 'position: fixed; width: 50%; height: 35px; left: 25%; right: 25%; top: 50%; text-align: center; margin: auto;',
			}).appendTo('#throbber-txt');

			$('<img/>', {
				id: 'img-throbber',
				src: OC.imagePath('core', 'loading.gif'),
				style: 'margin: auto;',
				alt: 'Loading',
			}).appendTo("#throbber-img-div");
		}

		function files_compress_throbber_hide() {

			var throbberDiv = document.getElementById('throbber');
			if ($("#throbber").css("display") == 'block') {
				throbberDiv.style.display = 'none';
				$("#throbber").remove();
			}
		}
	
		$(document).ready(function() {
			if (typeof FileActions !== 'undefined') {

				FileActions.register('application/zip',  'Extract', OC.PERMISSION_READ, '',
					function(filename, context) {
						files_compress_expand(filename, context)
					});
				FileActions.register('application/x-gzip',  'Extract', OC.PERMISSION_READ, '',
					function(filename, context) {
						files_compress_expand(filename, context)
					});
				FileActions.register('application/x-compressed',  'Extract', OC.PERMISSION_READ, '',
					function(filename, context) {
						files_compress_expand(filename, context)
					});
				FileActions.register('application/x-tar',  'Extract', OC.PERMISSION_READ, '',
						function(filename, context) {
							files_compress_expand(filename, context)
						});

				FileActions.register('all',  'Compress', OC.PERMISSION_READ, '',
					function(filename, context) {
						files_compress_compress(filename, context);
					});

			}
			// Add action to top bar (visible when files are selected)
			/*if(!$('.nav-sidebar li[data-id="sharing_in"] a.active').length &&
					!$('.nav-sidebar li[data-id="trash"] a.active').length &&
					(typeof OCA.Files !== 'undefined' && OCA.Files.FileList.prototype.getGetParam('view')!='trashbin')){
			$('#headerName .selectedActions').prepend(
					'<a class="compress btn btn-xs btn-default" id="compress" href=""><i class="icon icon-compress"></i>' + t('files_compress', ' Compress') + '</a>&nbsp;');
			}*/

		});
