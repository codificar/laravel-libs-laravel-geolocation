<script>
import ApiDirections from "./api_directions";
import ApiPlaces from "./api_places";
import ApiMaps from "./api_maps";
export default {
  props: ["enumData", "model", "placeSaveRoute"],
  components: {
    ApiDirections,
    ApiPlaces,
    ApiMaps
  },
  data() {
    return {     
     
    };
  },
  methods: {
  },
  async mounted() {
  
  },
};
</script>
<template>
    <div>
        <ApiMaps
          :enum-data="enumData"
          :model="model"
          :place-save-route="placeSaveRoute"
        />
        <ApiPlaces
            :enum-data="enumData"
            :model="model"
            :place-save-route="placeSaveRoute"
        />
        <ApiDirections
            :enum-data="enumData"
            :model="model"
            :place-save-route="placeSaveRoute"
        />        
    </div>
</template>
