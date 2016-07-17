$(document).ready(function() {
    $("#search_form").on('submit', function (e) {
        e.preventDefault();
        searchCitiesInRoutes();
        return false;
    });
    function searchCitiesInRoutes() {
        $('#clear_search').removeClass('search_clear_icon no_display').addClass('search_process_icon');
        var city_a = $('input[name="city_a"]').val();
        var city_b = $('input[name="city_b"]').val();
        var search_object = $('select[name="search_object"]').val();
        if(search_object == 'default') search_object = 'time';
        $.ajax({
            url: '/script.php',
            method: 'POST',
            dataType: 'json',
            data: {
                city_a: city_a,
                city_b: city_b,
                search_object: search_object
            },
            success: function (response) {
                $('.insert_data').remove();
                if(response['error']) {
                    alert(response['error']);
                } else {
                    for (key in response) {
                        $('#routes tr:last').after("<tr class='insert_data'>" +
                            "<td>" + response[key]['city_a'] + "</td>" +
                            "<td>" + response[key]['city_b'] + "</td>" +
                            "<td>" + response[key]['distance'] + "</td>" +
                            "<td>" + response[key]['time'] + "</td>" +
                            "</tr>");
                    }
                    $('#table_div').removeClass('no_display');
                }
                $('#clear_search').removeClass('search_process_icon').addClass('search_clear_icon no_display');
            },
            error: function(){
                alert('Error, please try again');
                $('#clear_search').removeClass('search_process_icon').addClass('search_clear_icon no_display');
            }
        });
    }
})