$(document).ready(function() {
    tinyMCE.init({
        mode: "exact",
        elements: "content",
        theme: "modern",
        language: "de",
        entity_encoding: "raw",
        toolbar: "bold italic underline | separator | undo redo",
        plugins: [
            "advlist autolink lists link charmap print preview",
            "searchreplace visualblocks visualchars code fullscreen",
            "nonbreaking save contextmenu directionality",
            "template paste"
        ],
        setup: function(ed) {
            ed.on('change', function(e) {
                window.modified = true;
            });
        },
        menu: {
            edit: {title: 'Edit', items: 'undo redo | cut copy paste | selectall | searchreplace'},
            view: {title: 'View', items: 'visualaid | preview fullscreen | code'}
            // format: {title: 'Format', items: 'bold italic underline superscript subscript | formats | removeformat'},
            // tools: {title: 'Tools', items: 'code'}
        }

    });

});
