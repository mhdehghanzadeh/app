<template>
    <div>
      <Head title="Dashboard" />
      <Map class="h-screen w-screen"/>
    </div>
  </template>
  
  <script>
  import { Head } from '@inertiajs/vue3'
  import Map from '@/Shared/Map.vue'

  export default {
    components: {
      Head,
      Map
    }
  }
  </script>
  