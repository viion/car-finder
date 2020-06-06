$('.fave').on('click', function(event) {
    event.preventDefault();

    var link = $('.fave').attr('href');

    $.ajax({
        url: link,
        success: function() {
            location.reload();
        }
    })
});
