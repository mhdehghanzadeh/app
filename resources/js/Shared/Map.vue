<template>
    <div :id="id" class="h-full w-full z-0" />
  </template>
  
  <script>
  import maplibregl from 'maplibre-gl'
  import { v4 as uuid } from 'uuid'
  
  export default {
    name: "Map",
    props: {
      inheritAttrs: false,
      id: {
        type: String,
        default() {
          return `map-${uuid()}`
        },
      },
      zoom: {
        type: Number,
        default: 11,
      },
    },
    data() {
      return {
        map: null, // To store map instance
        marker: null, // To store the single marker instance
      }
    },
    mounted: function () {
      this.mapCreate();
    },
    methods: {
      mapCreate: function () {
        // Ensure the RTL plugin is loaded correctly
        if (maplibregl.getRTLTextPluginStatus() === 'unavailable') {
          maplibregl.setRTLTextPlugin(
            '/js/mapbox-gl-rtl-text.min.js',
            null,
            true // Lazy load the plugin
          );
        }
  
        // Initialize the map instance
        this.map = new maplibregl.Map({
          container: this.id, // Use dynamic ID
          style: `https://maps.rpim.ir/api/maps/streets/style.json`,
          center: [51.3347, 35.7219],
          zoom: this.zoom,
          hash: false,
        });
  
        // Wait for the map to be fully loaded
        this.map.on('load', () => {
          this.map.addControl(new maplibregl.NavigationControl());
  
          // Add event listener for map click
          this.map.on('click', (e) => {
            this.addMarker(e.lngLat); // Pass clicked location
          });
        });
      },
      addMarker: function (lngLat) {
        // Check if a marker already exists, if so, remove it
        if (this.marker) {
          this.marker.remove();
        }
  
        // Add a new marker at the clicked position
        this.marker = new maplibregl.Marker()
          .setLngLat(lngLat)
          .addTo(this.map);
      },
    }
  }
  </script>
  