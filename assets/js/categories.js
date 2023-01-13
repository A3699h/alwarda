import '../css/categories.scss';

$('#category_iconFile_file').on('change', function () {
    let filename = $(this).val();
    const idx = filename.lastIndexOf("\\");
    filename = filename.substr(idx + 1);
    $('label[for=\'category_iconFile_file\']').html(filename);
});
