function loadMore(url) {
    $.get(url,
        function(data) {
            if (data != "FINISH") {
                $(data).insertAfter($("article:last-of-type"));
                $(window).data("offset", parseInt($(window).data("offset"))+20);
            }
        }
    )
}