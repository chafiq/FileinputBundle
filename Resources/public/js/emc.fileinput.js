$(function(){
    $('input[type=file].fileinput').each(function(item){
        var that = this;
        var deletedFiles = document.getElementById($(that).data('deletedIds'));
    
        var removeImage = function(id, thumbnail) {
            var deletedFileIds = deletedFiles.value.length > 0 ? deletedFiles.value.split(',') : [];
            deletedFileIds.push(id);
            deletedFiles.value = deletedFileIds.join(',');

            $(thumbnail).remove();
            return false;
        };
        
        var bindRemoveButton = function(button) {
            $(button)
                .off()
                .prop('disabled', false)
                .removeClass('disabled')
                .on('click', function(event){
                    event.stopPropagation();
                    event.preventDefault();
                    return removeImage($(button).data('key'), button.parentNode.parentNode.parentNode.parentNode);
                });
        };
        
        $(this).fileinput({
            initialPreview: $(this).data('files').map(function(file){
                return '<img src="' + file.path + '" class="file-preview-image"/>';
            }),
            initialPreviewConfig: $(this).data('files').map(function(file){
                return { key: file.id };
            }),
            maxFileSize: 5000,
            initialPreviewCount: true,
            initialCaption: "Files(s)",
            browseIcon : '<i class="icon-browse"></i>',
            removeIcon : '<i class="icon-close"></i>',
//            allowedFileExtensions: ["jpg", "png", "gif"],
            showUpload: false,
            showRemove: this.multiple,
            validateInitialCount: true,
            overwriteInitial: !this.multiple,
            previewFileIconSettings: {
                doc: '<i class="fa fa-file-word-o text-primary"></i>',
                xls: '<i class="fa fa-file-excel-o text-success"></i>',
                ppt: '<i class="fa fa-file-powerpoint-o text-danger"></i>',
                jpg: '<i class="fa fa-file-photo-o text-warning"></i>',
                pdf: '<i class="fa fa-file-pdf-o text-danger"></i>',
                zip: '<i class="fa fa-file-archive-o text-muted"></i>',
                htm: '<i class="fa fa-file-code-o text-info"></i>',
                txt: '<i class="fa fa-file-text-o text-info"></i>',
                mov: '<i class="fa fa-file-movie-o text-warning"></i>',
                mp3: '<i class="fa fa-file-audio-o text-warning"></i>',
            },
            previewFileExtSettings:{
                doc: function(ext) { return ext.match(/(doc|docx)$/i); },
                xls: function(ext) { return ext.match(/(xls|xlsx)$/i); },
                ppt: function(ext) { return ext.match(/(ppt|pptx)$/i); },
                jpg: function(ext) { return ext.match(/(jp?g|png|gif|bmp)$/i); },
                zip: function(ext) { return ext.match(/(zip|rar|tar|gzip|gz|7z)$/i); },
                htm: function(ext) { return ext.match(/(php|js|css|htm|html)$/i); },
                txt: function(ext) { return ext.match(/(txt|ini|md)$/i); },
                mov: function(ext) { return ext.match(/(avi|mpg|mkv|mov|mp4|3gp|webm|wmv)$/i); },
                mp3: function(ext) { return ext.match(/(mp3|wav)$/i); }
            }
        })
        .on('fileimageloaded', function(event){
            var deletedFiles = document.getElementById($(that).data('deletedIds'));
            var deletedFileIds = deletedFiles.value.length > 0 ? deletedFiles.value.split(',') : [];

            $fileinput
                .find('.kv-file-remove')
                    .each(function(item) {
                        if (deletedFileIds.indexOf($(this).data('key').toString()) > -1) {
                            $(this.parentNode.parentNode.parentNode.parentNode).remove();
                        } else {
                            bindRemoveButton(this);
                        }
                    });
            
        });

        var $fileinput = $(this.parentNode.parentNode.parentNode.parentNode);
        
        $fileinput
            .find('.kv-file-remove')
                .each(function(item) {bindRemoveButton(this);});
            
        if (!this.multiple){
            $(this.parentNode.parentNode.parentNode.parentNode)
                    .addClass("file-input-small");
        }
    });
});