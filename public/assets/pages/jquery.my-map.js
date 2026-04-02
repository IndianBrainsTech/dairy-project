!function($) {
    "use strict";
    var GoogleMap = function() {};

    GoogleMap.prototype.createMarkers = function($container,$lat,$lng,$title,$content) {
        var map = new GMaps({
            div: $container,
            lat: $lat,
            lng: $lng
        });

        map.addMarker({
            lat: $lat,
            lng: $lng,
            title: $title,
            infoWindow: {
                content: $content
            }
        });

        return map;
    },

    GoogleMap.prototype.init = function($container,$lat,$lng,$title,$content) {
        var $this = this;
        $(document).ready(function(){
            $this.createMarkers($container,$lat,$lng,$title,$content);
        });
    },

    //init
    $.GoogleMap = new GoogleMap, $.GoogleMap.Constructor = GoogleMap
}(window.jQuery);