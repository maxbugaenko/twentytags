function loadMore(insertAfter, url) {
    $.get(url,
        function(data) {
            if (data != "FINISH") {
                alert(data);
                $(data).insertAfter(insertAfter);
                $(window).data("offset", parseInt($(window).data("offset"))+20);
            }
        }
    )
}