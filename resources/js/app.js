import './bootstrap';

$(document).ready(function() {
    $('#uploadModal').hide(); 

    $('.add-btn').on('click', function() {
        $('#uploadModal').fadeIn(300);
    });

    $('.close-btn').on('click', function() {
        $('#uploadModal').fadeOut(300);
    });

    $(window).on('click', function(event) {
        if ($(event.target).is('#uploadModal')) {
            $('#uploadModal').fadeOut(300);
        }
    });

    $('#fileInput').on('change', function(event) {
        event.preventDefault();

        var fileName = $(this).val().split('\\').pop(); 
        var fileNameDisplay = $('#fileName');

        console.log('Selected file name: ', fileName);

        if (fileName) {
            fileNameDisplay.text(fileName);
            fileNameDisplay.show();

            var formData = new FormData($('#uploadForm')[0]);

            $.ajax({
                url: $('#uploadForm').attr('action'),
                type: 'POST',
                headers: {
                    'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')
                },
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    $('#uploadModal').fadeOut(300);
                    $('#homeowner-list').empty();
                    $('#homeowner-list-header').removeClass('hidden');
                    $('#homeowner-list').removeClass('hidden');
                    $('.heroContent').addClass('hidden');


                    if (response.homeowners && response.homeowners.length > 0) {
                        response.homeowners.forEach(function(homeowner) {
                            const title = homeowner.title || ''; // blank if not provided
                            const firstName = homeowner.firstname || ''; // blank if not provided
                            const initial = homeowner.initial ? homeowner.initial + '.' : ''; // blank if not provided
                            const lastName = homeowner.lastname || ''; // blank if not provided
                            $('#homeowner-list').append(`<div class="homeowner">${title} ${firstName} ${initial} ${lastName}</div>`);
                        });
                    } else {
                        $('#homeowner-list').append('<div>No homeowners found.</div>');
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error('Upload failed:', textStatus, errorThrown);
                    console.log('Upload failed: ' + jqXHR.responseText);
                }
            });
        } else {
            fileNameDisplay.hide();
        }
    });
});
