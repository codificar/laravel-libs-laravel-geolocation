<script>
import axios from "axios";
import Card from "../../components/Layout/Card";
export default {
  props: ["enum", "model"],
  components: {
    Card,
  },
  data() {
    return {     
      placesOptions: [],
      selectedPlacesService: null,
      placeApiKey: null,
      enablePlacesRedundancy: false,


      directionsOptions: [],
      selectedDirectionsService: null
    };
  },
  methods: {
    savePlaces(selectedData){
      console.log("enablePlacesRedundancy", this.enablePlacesRedundancy);
      this.selectedDirectionsService = selectedData
    }
  },
  async mounted() {
    const optionsList = JSON.parse(this.enum)
    this.directionsOptions = optionsList.directions_provider
    this.placesOptions = optionsList.places_provider
  },
};
</script>
<template>
  <Card>
    <h4 slot="card-title" class="m-b-0 text-white">API Places</h4>

    <h3 slot="card-content-title" class="box-title"></h3>
      <div slot="card-content">
        <div class="row">
          <div class="col-lg-6">
            <div class="form-group">
              <label>
                Provedor do serviços *
              </label>           
              <v-select @input="savePlaces" :options="placesOptions" label="name"/>
            </div>
          </div>

          <div class="col-lg-6">
            <div class="form-group">
              <label>
                Chave de autenticação *
              </label>
              <input type="text" class="form-control" />
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-lg-12">
            <div class="form-check">            
              <label class="form-check-label pl-0"><h3>Habilitar redundância na consulta ?</h3> </label>
              
              <label class="pl-1"><input type="radio" name="placaRed" :value="true" v-model="enablePlacesRedundancy">Sim</label>
              <label class="pl-1"><input type="radio" name="placaRed" checked :value="false" v-model="enablePlacesRedundancy">Não</label>
              
            </div>
          </div>
        </div>
       
      </div>
  </Card>
</template>
