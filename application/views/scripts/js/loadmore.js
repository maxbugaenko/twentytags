function loadMore(insertAfter, url) {
    $.get(url,
        function(data) {
            if (data != "FINISH") {
                $(data).insertAfter(insertAfter);
                alert($(data));
                $(window).data("offset", parseInt($(window).data("offset"))+20);
            }
        }
    )
}