(function($) {
    $.fn.FollowEntity = function() {
        $(this).click(function() {
            var entityID = this.id.substr(7);
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
                        $("#follow-"+entityID).toggleClass("button-gray");
                        if ($("#follow-"+entityID).hasClass("followed")) {
                            $("#follow-"+entityID).html("<i class='fa fa-check-circle'></i> Unfollow");
                        } else {
                            $("#follow-"+entityID).html("<i class='fa fa-check-circle'></i> Follow");
                        }
                    }
                }
            )
        })
    }
}(jQuery));