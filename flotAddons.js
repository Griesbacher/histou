    (function ($) {
    function init(plot) {
        function fixGaps(plot, series, datapoints) {
                var points = datapoints.points, stepSize = datapoints.pointsize;
                var timerangeInMillisec = points[points.length-stepSize]-points[0];
                var datapoints = series.data.length;
                var avgTimeBetweenPoints = timerangeInMillisec / datapoints;
                var indices = [];
            for (var i = stepSize; i < points.length; i += stepSize){
                if ((points[i]-points[i-stepSize]) > avgTimeBetweenPoints*2) {
                    indices.push(i)
                }
            }
            for (var i = indices.length-1; i >= 0; i--) {
                var pointIndex = indices[i];
                for (var j = 0; j < stepSize; j++){
                    points.splice(pointIndex,0,null);
                }
            }
        }

        function addHorizontalRuler(plot, options) {
                options.crosshair.mode = 'xy';
        }

        plot.hooks.processDatapoints.push(fixGaps);
        plot.hooks.processOptions.push(addHorizontalRuler);
    }

        $.plot.plugins.push(
            {
                init: init,
                options: {},
                name: "customiseGrafana",
                version: "0.1"
            }
        );
})(jQuery);

