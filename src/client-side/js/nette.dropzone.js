(function($, window, document, location, navigator) {
	$.nette.ext('dropzone', {
		PRELOADER_ANIMATION_DURATION : 150,
		init: function(){
			// Check dependences
			if (Dropzone === undefined) {
				console.error('Plugin "dropzone.js" is missing!');
				return;
			} else if ($.nette === undefined) {
				console.error('Plugin "nette.ajax.js" is missing!.');
				return;
			}
			var $self = $(this);
			$(this.dataDropzone).each(function() {
				var $this = $(this);
				if (!$this.data('nette-dropzone')) {
					$this.data('nette-dropzone', $self.dropzone($this, $this.data('settings')));
				}
			});
		},
		dropzone: function($element, options){
			var id = $element.attr('id');
			var settings = $element.data('nette-dropzone-settings');
			var previewNode = document.querySelector('#' + id + '-nette-dropzone-template');
			var previewTemplate = previewNode.parentNode.innerHTML,
				uploadSuccess = $element.data('nette-dropzone-success'),
				labelFiles = $element.data('nette-dropzone-files'),
				labelChosen = $element.data('nette-dropzone-chosen'),
				labelUploaded = $element.data('nette-dropzone-uploaded'),
				labelProcess = $element.data('nette-dropzone-process'),
				uploadBtnLabel = $element.find(settings.clickable + ' .upload-btn-label'),
				preloader = $element.find('.dropzone-preloader'),
				uploaded = 0,
				count = 0;
			var labelDefault = uploadBtnLabel.text();
			var files = [];

			previewNode.id = "";
			previewNode.parentNode.removeChild(previewNode);
			settings = $.extend({}, settings, {'previewTemplate' : previewTemplate});

			//console.log(settings);
			var myDropzone = new Dropzone($element.get(0), settings);

			/* DROPZONE EVENTS -------------------------------------------------------------------------------------------*/

			myDropzone.on("addedfile", function(file) {
				count = myDropzone.getFilesWithStatus(Dropzone.ADDED).length;
				uploadBtnLabel.text(labelChosen + ' ' + count + ' ' + labelFiles);
			});

			myDropzone.on("removedfile", function() {
				count = myDropzone.getFilesWithStatus(Dropzone.ADDED).length;
				uploadBtnLabel.text(labelChosen + ' ' + count + ' ' + labelFiles);
			});

			myDropzone.on("success", function(file, responseText) {
				if (responseText !== undefined) {
					files.push(responseText.shift());
				} else {
					files = [];
				}
			});

			myDropzone.on("totaluploadprogress", function(progress) {
				if (!settings.autoQueue) {
					uploaded = count - myDropzone.getFilesWithStatus(Dropzone.UPLOADING).length;
					uploadBtnLabel.text(labelProcess + ' ' + uploaded + '/' + count);
				} else {
					uploadBtnLabel.text(labelProcess)
				}
			});

			myDropzone.on("sending", function(file) {
				preloader.show(PRELOADER_ANIMATION_DURATION);
			});

			myDropzone.on("error", function(file, errorMessage, xhr) {
				alert(errorMessage + '(' + file.name + ')');
			});

			myDropzone.on("queuecomplete", function(progress) {
				uploadBtnLabel.text(labelDefault);

				$.nette.ajax({
					url: uploadSuccess,
					type: 'POST',
					data: {
						'files':files
					}
				});

				files = [];
				preloader.hide(PRELOADER_ANIMATION_DURATION);
			});

			var startBtn = document.querySelector('#' + id + ' .start');
			if (startBtn !== null) {
				startBtn.onclick = function() {
					myDropzone.enqueueFiles(myDropzone.getFilesWithStatus(Dropzone.ADDED));
				};
			}

			var cancelBtn = document.querySelector('#' + id + ' .cancel');
			if (cancelBtn !== null) {
				cancelBtn.onclick = function() {
					myDropzone.removeAllFiles(true);
					uploadBtnLabel.text(labelDefault);
				};
			}
		}
	},{
		dataDropzone:'[data-nette-dropzone]'
	});

    // Immediately invoke function with default parameters
})(jQuery, window, document, location, navigator);

