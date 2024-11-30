function initializeSnowEditors() {
    $('.snow-editor-initialize').each(function (i, el) {
        var id = el.id;
        var editorId = 'snow-editor-' + id;
        var localSnowEditor = $('<div/>', {
            'id': editorId,
            'style': 'height: 300px;',
            'html': el.value,
        });
        el = $(el);
        el.parent().append(localSnowEditor);

        var localQuill = new Quill('#' + editorId, {
            theme: "snow",
            modules: {
                toolbar: [[
                    {
                        font: []
                    },
                    {
                        size: []
                    }], ["bold", "italic", "underline", "strike"], [{
                    color: []
                }, {
                    background: []
                }], [{
                    script: "super"
                }, {
                    script: "sub"
                }], [{
                    header: [!1, 1, 2, 3, 4, 5, 6]
                }, "blockquote", "code-block"], [{
                    list: "ordered"
                }, {
                    list: "bullet"
                }, {
                    indent: "-1"
                }, {
                    indent: "+1"
                }], ["direction", {
                    align: []
                }], ["link", "image", "video"], ["clean"]]
            },
        });
        localQuill.getModule('toolbar').addHandler('image', function () {
            selectLocalImage(localQuill);
        });


        localQuill.on('text-change', function (delta, oldDelta, source) {
            el.val(localQuill.root.innerHTML);
        });
    });
}

function selectLocalImage(quill) {
    const input = document.createElement('input');
    input.setAttribute('type', 'file');
    input.click();

    // Listen upload local image and save to server
    input.onchange = function () {
        const file = input.files[0];

        // file type is only image.
        if (/^image\//.test(file.type)) {
            saveToServer(file, quill);
        } else {
            console.warn('You could only upload images.');
        }
    };
}

function saveToServer(file, quill) {
    const data = new FormData();
    data.append('file', file);

    $.ajax({
        url: '/upload-file',
        data: data,
        cache: false,
        contentType: false,
        processData: false,
        method: 'POST',
        success: function (data) {
            if (data !== false) {
                insertToEditor(data);
            }
        }
    });
}

function insertToEditor(url, quill) {
    const range = quill.getSelection();
    quill.insertEmbed(range.index, 'image', url);
}