$(function(){
    var fileIconClass = '.fa-file-image-o';

    var rename = function(){
        var $name = $(this);
        var $input = $('<input>');
        $name.hide();
        $name.after($input);
        $input.val($name.text());
        $input.focus();
        $input.select();

        var done = function(){
            var newName = $input.val();
            $input.remove();
            $name.show();

            if(newName != $name.text() && newName != ""){
                $name.html(newName);
                $name.attr('title', newName);
                $name.prev().attr('title', newName);

                $.get($name.data('rename'), {name:newName});
            }
        };

        $input.blur(function(){
            done();
        });

        $input.keypress(function(e){
            if(e.which == 13) done();
        });
    };

    $('.photo .name').click(rename);

    var id = '#' + $('#upload-manager').data('file-input-id');

    $(id).change(function(){
        var $this = $(this);
        var $manager = $('#upload-manager');

        $.each($this[0].files, function(i, file){
            var html = $manager.find('.template').html();
            var size = file.size > 1000000 ? Math.round(file.size / 100000)/10 + 'Mo':Math.round(file.size / 1000) + 'ko';

            html = html.replace(new RegExp('__name__', 'g'), file.name);
            html = html.replace('__size__', size);
            html = html.replace('__id__', "file" + i);
            html = html.replace(/^\s+|\s+$/g, '');

            var $container = $(html);

            $manager.append($container);
        });

        function upload(i, files){
            var file = files[i];
            var size = file.size > 1000000 ? Math.round(file.size / 100000)/10 + 'Mo':Math.round(file.size / 1000) + 'ko';
            var $file = $('#file' + i);
            var $loadingBar = $file.find('.progress');
            var $statusBar = $file.find('.status');

            // Make sure we have an image
            if(file.type != 'image/gif' && file.type != 'image/jpeg' && file.type != 'image/pjpeg' && file.type != 'image/png' && file.type != 'image/x-png'){
                $loadingBar.remove();
                $file.find(fileIconClass).css('color', '#ca6060');
                $file.removeAttr('id');
                $statusBar.html("Ceci n'est pas une image : " + file.type);

                if(++i < files.length)
                    upload(i, files);

                return;
            }

            // Make sure it's under 64 Mo
            if(file.size > 64000000){
                $loadingBar.remove();
                $file.find(fileIconClass).css('color', '#ca6060');
                $file.removeAttr('id');
                $statusBar.html("Fichier trop gros (64Mo max)");

                if(++i < files.length)
                    upload(i, files);

                return;
            }

            $statusBar.html('Upload en cours...');

            var xhr = new XMLHttpRequest();

            xhr.open('POST', window.location);

            xhr.upload.onprogress = function(e) {
                $loadingBar.find('.progress-bar').css('width', e.loaded / e.total * 100 + '%');
                $loadingBar.find('.progress-bar').text(Math.round(e.loaded / e.total * 100) + '%');
            };

            xhr.onload = function() {
                $loadingBar.remove();
                var response = xhr.status == 200 ? JSON.parse(xhr.response): false;

                // An error occured
                if(xhr.status != 200 || !response.success){
                    $file.find(fileIconClass).css('color', '#ca6060');
                    $file.removeAttr('id');
                    $statusBar.html("Une erreur est survenue...");
                }
                // Everything is good =)
                else{
                    $statusBar.html("Done !");
                    $.get(response.template, function(data){
                        $file.before(data);
                        $file.prev().find('.name').click(rename);
                        $file.remove();
                    });
                }

                if(++i < files.length)
                    upload(i, files);
            };

            var form = new FormData();
            form.append($manager.data('name-input-name'), file.name);
            form.append($manager.data('size-input-name'), size);
            form.append($manager.data('file-input-name'), file);
            form.append($manager.data('taken-input-name'), file.lastModified);
            form.append($manager.data('comment-input-name'), '');
            form.append($manager.data('token-input-name'), $manager.data('token-input-value'));

            xhr.send(form);
        }

        upload(0, $this[0].files);
    });
});