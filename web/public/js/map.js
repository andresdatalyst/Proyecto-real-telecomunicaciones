<script src="https://js.arcgis.com/4.24/"></script>
require(["esri/config","esri/Map", "esri/views/MapView"], function (esriConfig,Map, MapView) {

    esriConfig.apiKey = "AAPK133b74b83a5147b2ac8bb10cd0ead170GpZGmE_VP3pKP-RxerhNmwBvlrbvDvRYuaG64xuv1CJpgUIAyk8GMIqU9_lq1od2";

    const map = new Map({
      basemap: "arcgis-topographic" // Basemap layer service
    });

    const view = new MapView({
      map: map,
      center: [-118.805, 34.027], // Longitude, latitude
      zoom: 13, // Zoom level
      container: "viewDiv" // Div element
    });

  });