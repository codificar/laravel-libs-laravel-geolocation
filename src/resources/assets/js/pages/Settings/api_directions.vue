<script>
import axios from "axios";
import Card from "../../components/Layout/Card";
export default {
  props: ["enumData", "model", "placeSaveRoute"],
  components: {
    Card,
  },
  data() {
    return {     
      placesOptions: [],
      placesProviderRule: {},
      placesDataModel: {
        places_provider_redundancy: "",
        places_provider: "",
        places_url: "",
        places_application_id: "",


        places_redundancy_rule: "",
        places_key: "",
        places_key_redundancy: ""
      },    
      enablePlacesRedundancy: false,

      
      directionsOptions: [],
    };
  },
  methods: {
    selectPlaceService(selectedData){
      this.placesProviderRule = selectedData
      this.placesDataModel.places_provider.value = selectedData.value
      console.log("this.placesProviderRule", this.placesProviderRule);
    },
    selectPlaceRedundancyService(selectedData){
      this.placesDataModel.places_provider_redundancy.value = selectedData.value
    },
    updatePlacesRedundancy(checkedValue){
      const value = checkedValue.target.value;
      this.placesDataModel.places_redundancy_rule.value = value
    },
    async savePlaces(){
      //Format Data in Array
      let arrayDataModel = Object.keys(this.placesDataModel).map(key => this.placesDataModel[key]);
      const response = await axios.post(this.placeSaveRoute, arrayDataModel)      
    }
  },
  async mounted() {
    const optionsList = JSON.parse(this.enumData)
    this.placesDataModel = JSON.parse(this.model)
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
                Provedor do serviços*
              </label>           
              <v-select @input="selectPlaceService" :options="placesOptions" label="name"/>
            </div>
          </div>

          <div class="col-lg-6">
            <div class="form-group">
              <label>
                Chave de autenticação*
              </label>
              <input v-model=placesDataModel.places_key.value type="text" class="form-control" />
            </div>
          </div>
        </div>

         <div class="row">
          <div v-show=placesProviderRule.redundancy_url class="col-lg-6">
            <div class="form-group">
              <label>
                URL do servidor*
              </label>           
              <input v-model=placesDataModel.places_url.value type="text" class="form-control" />
            </div>
          </div>

          <div v-show=placesProviderRule.redundancy_id class="col-lg-6">
            <div class="form-group">
              <label>
                ID da aplicação*
              </label>
              <input v-model=placesDataModel.places_application_id.value type="text" class="form-control" />
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-lg-12">
            <div class="form-check">            
              <label class="form-check-label pl-0"><h3>Habilitar redundância na consulta ?</h3> </label>
              
              <label class="pl-1"><input type="radio" name="placaRed" value="1" @change=updatePlacesRedundancy>Sim</label>
              <label class="pl-1"><input type="radio" name="placaRed" checked value="0" @change=updatePlacesRedundancy>Não</label>
              
            </div>
          </div>
        </div>
        <!-- Placed redundancy -->
        <div v-if="placesDataModel.places_redundancy_rule.value == 1">
          <div class="row">
            <div class="col-lg-6">
              <div class="form-group">
                <label>
                  Provedor do serviço de redundância*
                </label>           
                <v-select @input="selectPlaceRedundancyService" :options="placesOptions" label="name"/>
              </div>
            </div>

            <div class="col-lg-6">
              <div class="form-group">
                <label>
                  Chave de autenticação de redundância*
                </label>
                <input v-model=placesDataModel.places_key_redundancy.value type="text" class="form-control" />
              </div>
            </div>
          </div>
        </div>

        <div class="box-footer pull-right">
          <button
            @click="savePlaces"
            class="btn btn-success right"
            type="button"
          >
            Salvar
          </button>
        </div>
       
      </div>
  </Card>
</template>
