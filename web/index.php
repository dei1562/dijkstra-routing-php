<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no">
    <meta charset="utf-8">
    <title>DSTG 2015</title>
    <style>
        html, body, #map-canvas {
            font-family: sans-serif;
            font-size: 16px;
            height: 100%;
            margin: 0px;
            padding: 0px
        }

        .labels {
            font-size: 16px;
            color: #FFF;
            /*text-shadow: 1px 1px 1px rgba(0, 0, 0, 1);*/
            padding: 3px 5px;
            background: #000;;
        }

        #menu {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: #000;
            height: 25px;
            z-index: 999;;
        }

    </style>
    <script src="https://maps.googleapis.com/maps/api/js?v=3.exp"></script>
    <script src="https://code.jquery.com/jquery-1.11.2.min.js"></script>
    <script src="http://google-maps-utility-library-v3.googlecode.com/svn/tags/markerwithlabel/1.1.8//src/markerwithlabel.js"></script>
    <script>
        var graphPaths = [];
        var spPath;
        var modifiedSpPath;

        function hideAll() {
            spPath.setOptions({visible: false});
            modifiedSpPath.setOptions({visible: false});
            for (var i = 0; i < graphPaths.length; i++) {
                graphPaths[i].setOptions({visible: false});
            }
        }

        function showAll() {
            spPath.setOptions({visible: true});
            modifiedSpPath.setOptions({visible: true});
            for (var i = 0; i < graphPaths.length; i++) {
                graphPaths[i].setOptions({visible: true});
            }
        }

        function spOnly() {
            hideAll();
            spPath.setOptions({visible: true});
        }

        function modifiedOnly() {
            hideAll();
            modifiedSpPath.setOptions({visible: true});
        }

        function connOnly() {
            hideAll();
            for (var i = 0; i < graphPaths.length; i++) {
                graphPaths[i].setOptions({visible: true});
            }
        }

        $(function () {

            $('body').mousemove(function (e) {
                window.mouseXPos = e.pageX;
                window.mouseYPos = e.pageY;
            });


            var mapOptions = {
                zoom: 6,
                center: new google.maps.LatLng(46.4431231, 10.5191702),
                mapTypeId: google.maps.MapTypeId.ROAD
            };

            var map = new google.maps.Map(document.getElementById('map-canvas'),
                mapOptions);

            var positions = {};

            // Draw full graph
            $.getJSON("data/positions.json", function (json) {
                $.each(json, function () {
                    positions[this.label] = new google.maps.LatLng(this.lat, this.lng);
                });
            });


            $.getJSON("data/connections.json", function (connections) {
                for (var from in connections) {
                    if (connections.hasOwnProperty(from)) {

                        new MarkerWithLabel({
                            position: positions[from],
                            draggable: false,
                            map: map,
                            labelContent: from,
                            labelAnchor: new google.maps.Point(0, 0),
                            labelClass: "labels" // the CSS class for the label
                        });

                        for (var to in connections[from]) {
                            if (connections[from].hasOwnProperty(to)) {

                                var id = from + to;
                                path = new google.maps.Polyline({
                                    id: id,
                                    weight: connections[from][to],
                                    path: [positions[from], positions[to]],
                                    geodesic: true,
                                    strokeColor: '#FF0000',
                                    strokeOpacity: 1.0,
                                    strokeWeight: 2
                                });
                                path.setMap(map);
                                graphPaths.push(path)
                                polyListener(path);
                            }
                        }
                    }
                }
            });


            $.getJSON("data/sp_modified_data.json", function (sp) {
                var shortestPathCoordinates = [];
                for (var i = 0; i < sp.length; i++) {
                    shortestPathCoordinates.push(positions[sp[i]]);
                }

                modifiedSpPath = new google.maps.Polyline({
                    path: shortestPathCoordinates,
                    geodesic: true,
                    strokeColor: '#FFFF00',
                    strokeOpacity: 1.0,
                    strokeWeight: 4
                });

                modifiedSpPath.setMap(map);
            });

            $.getJSON("data/sp_data.json", function (sp) {
                var shortestPathCoordinates = [];
                for (var i = 0; i < sp.length; i++) {
                    shortestPathCoordinates.push(positions[sp[i]]);
                }
                var shortestPath = new google.maps.Polyline({
                    path: shortestPathCoordinates,
                    geodesic: true,
                    strokeColor: '#009900',
                    strokeOpacity: 1.0,
                    strokeWeight: 4
                });
                shortestPath.setMap(map);
                spPath = shortestPath;

            });

        })

        function polyListener(path) {
            google.maps.event.addListener(path, "mouseover", function (event) {
                $("#weight-label").css({top: window.mouseYPos, left: window.mouseXPos});
                console.log(window.mouseYPos);
                $("#weight-label").show();
                $("#weight-label").html(this.weight);
                this.setOptions({
                    strokeWeight: 6.0,
                    strokeColor: "#0000FF"
                });
            });

            google.maps.event.addListener(path, "mouseout", function (event) {
                this.setOptions({
                    strokeWeight: 2.0,
                    strokeColor: "#FF0000"
                });
            });
        }

    </script>
</head>
<body>
<div id="map-canvas"></div>
<div id="weight-label"
     style="position:absolute;padding: 5px 10px;  5px;text-align: center; background: #0000FF; color:#fff; z-index: 9999;"></div>
</body>
<div id="menu">
    <button onClick="showAll()">All</button>
    <button onClick="spOnly()">Shortest path</button>
    <button onClick="modifiedOnly()">Modified SP</button>
    <button onClick="connOnly()">Graph</button>
</div>
</html>


