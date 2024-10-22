<template>
<div>
    <label v-if="label" class="form-label font-semibold text-gray-500 mb-5 flex items-center" :for="id"><i v-if="required" class="fa-duotone fa-star-of-life font-bold ml-1 text-xs text-red-500"></i>{{ label }}</label>
    <div class="border rounded-md h-full w-full">
        <div class='mapPane h-full w-full'>
            <div id='map' class="h-full w-full z-0"></div>
        </div>
    </div>
    <div v-if="hint || error || description" class="flex flex-col space-y-2">
        <div v-if="description && descriptionText != 'null'" class="flex bg-green-100 p-2 py-3 rounded-lg relative mt-3 leading-normal text-sm">
            <span class="flex text-green-500  justify-center items-center"><i class="fa-duotone fa-circle-info"></i></span>
            <span class="flex px-2 text-green-500"> {{descriptionText}} </span>
        </div>
        <div v-if="error" class="flex bg-red-100 p-2 py-3 rounded-lg relative mt-3 leading-normal text-sm">
            <span class="flex text-red-500  justify-center items-center"><i class="fa-duotone fa-circle-exclamation"></i></span>
            <span class="flex px-2 text-red-500"> {{ error }} </span>
        </div>
        <div v-if="hint" class="flex bg-yellow-100 p-2 py-3 rounded-lg relative mt-3 leading-normal text-xs">
            <span class="flex text-orange-500  justify-center items-center"><i class="fa-duotone fa-circle-info"></i></span>
            <span class="flex px-2 text-orange-500"> {{ hint }} </span>
        </div>
    </div>
</div>
</template>

<script>
import maplibregl from 'maplibre-gl'

import {
    ref
} from 'vue'
import {
    v4 as uuid
} from 'uuid'

export default {
    name: "Map",
    props: {
        inheritAttrs: false,
        id: {
            type: String,
            default () {
                return `map-${uuid()}`
            },
        },
        label: String,
        modelValue: String,
        inputclass: String,
        error: String,
        required: Boolean,
        hint: String,
        description: Boolean,
        descriptionText: String,
        draggable: Boolean,
        zoom: Number
    },
    emits: ['update:modelValue'],
    data() {
        return {
    
        }
    },
    mounted: async function () {
        this.mapCreate();
    },
    methods: {
        mapCreate: function () {
            if (maplibregl.getRTLTextPluginStatus() === 'unavailable' && maplibregl.getRTLTextPluginStatus() !== 'loaded') {
                maplibregl.setRTLTextPlugin(
                    '/js/mapbox-gl-rtl-text.min.js',
                    null,
                    true // Lazy load the plugin
                );
            }

            const map = new maplibregl.Map({
                container: 'map',
                style: `https://api.maptiler.com/maps/4d9a87b1-7509-43ab-9563-83aaa5268c84/style.json?key=JqsZ7ZZqbob6l425EOwg`,
                center: this.modelValue ? [this.modelValue.split(",")[1], this.modelValue.split(",")[0]] : [51.3347, 35.7219],
                zoom: this.zoom ? this.zoom : 10,
                hash: false,
            });

            map.addControl(new maplibregl.NavigationControl());

            var marker = new maplibregl.Marker({
                    draggable: this.draggable
                })
                .setLngLat(this.modelValue ? [this.modelValue.split(",")[1], this.modelValue.split(",")[0]] : [51.389702601417696, 35.690196927973886])
                .addTo(map);

            marker.on('dragend', () => {
                var lngLat = marker.getLngLat();
                this.$emit('update:modelValue', lngLat.lat + ',' + lngLat.lng)
            });
        },
    }
}
</script>
