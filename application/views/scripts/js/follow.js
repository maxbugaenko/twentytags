(function($) {
    $.fn.FollowEntity = function() {
        $(this).click(function() {
            var entityID = this.id.substr(7);
            if ($("#follow-"+entityID).hasClass("user-not-logged")) {
                var loginUrl = $("#login-url-"+entityID).html();
                var decoded = loginUrl.replace(/&amp;/g, '&');
                location.href = decoded;
            } else {
                if ($("#follow-"+entityID).hasClass("followed")) {
                    var url = "<?=$this->url(array('controller'=>'index', 'action'=>'unfollow'), 'default', true)?>";
                } else {
                    var url = "<?=$this->url(array('controller'=>'index', 'action'=>'follow'), 'default', true)?>";
                }
                $.post(
                    url,
                    { 'entity': entityID },
                    function(data) {
                        if (data == "OK") {
                            $("#item-follow-button-" + entityID).toggleClass("make-visible");
                            $("#follow-"+entityID).toggleClass("followed");
                            if ($("#follow-"+entityID).hasClass("followed")) {
                                $("#follow-"+entityID).html("<i class='icon-tag'></i><span class='desktop-and-medium-only'>Отписаться</span>").addClass("button-default");
                            } else {
                                $("#follow-"+entityID).html("<i class='icon-tag'></i><span class='desktop-and-medium-only'>Подписаться</span>").removeClass("button-default");;
                            }
                        }
                    }
                )
            }
        })
    }
}(jQuery));