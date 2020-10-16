<script>
import ApiDirections from "./api_directions";
import ApiPlaces from "./api_places";
export default {
  props: ["enumData", "model", "placeSaveRoute"],
  components: {
    ApiDirections,
    ApiPlaces
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
