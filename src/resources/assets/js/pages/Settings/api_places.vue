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

      placesProviderRule: {
        name: "",
        redundancy_id: false,
        redundancy_url: false,
        value: ""
      },

      placesProviderRedundancyRule: {
        name: "",
        redundancy_id: false,
        redundancy_url: false,
        value: ""
      },
      placesDataModel: {
        places_provider: "",
        places_key: "",
        places_url: "",
        places_application_id: "",      

        places_redundancy_rule: "",

        places_provider_redundancy: "",       
        places_key_redundancy: "",
        places_url_redundancy: "",
        places_application_id_redundancy: ""
      },  
      
      placesDataErrors: {
        places_provider: "",
        places_key: "",
        places_url: "",
        places_application_id: "",      

        places_redundancy_rule: "",

        places_provider_redundancy: "",       
        places_key_redundancy: "",
        places_url_redundancy: "",
        places_application_id_redundancy: ""
      }
    };
  },
  methods: {
    selectPlaceService(selectedData){
      this.placesProviderRule = selectedData
      this.placesDataModel.places_provider.value = selectedData.value
    },
    selectPlaceRedundancyService(selectedData){
      this.placesProviderRedundancyRule = selectedData
      this.placesDataModel.places_provider_redundancy.value = selectedData.value
    },
    updatePlacesRedundancy(checkedValue){
      const value = checkedValue.target.value;
      this.placesDataModel.places_redundancy_rule.value = value
    },
    async savePlaces(){
      //Format Data in Array
      if(!this.validate(this.placesDataModel)){
        this.$toasted.show(
        "Preencha todos os campo obrigatorios", 
          { 
            theme: "bubble", 
            type: "error" ,
            position: "bottom-center", 
            duration : 3000
          }
        );
      }else{
        let arrayDataModel = Object.keys(this.placesDataModel).map(key => this.placesDataModel[key]);
        const response = await axios.post(this.placeSaveRoute, arrayDataModel)     
        this.$toasted.show(
        "Salvo com sucesso", 
          { 
            theme: "bubble", 
            type: "success" ,
            position: "bottom-center", 
            duration : 3000
          }
        ); 
        this.cleanErrors()
        location.reload(true)
      }      
    },
    cleanErrors(){
       this.placesDataErrors = {
        places_provider: "",
        places_key: "",
        places_url: "",
        places_application_id: "",      

        places_redundancy_rule: "",

        places_provider_redundancy: "",       
        places_key_redundancy: "",
        places_url_redundancy: "",
        places_application_id_redundancy: ""
      } 
    },
    validate(data){
      let isValid = true
      if(this.placesDataModel.places_key.value == null || this.placesDataModel.places_key.value.trim() == ""){
        isValid = false
        this.placesDataErrors.places_key = "Preencha este campo"
      } 
      if(this.placesDataModel.places_provider.value == null || this.placesDataModel.places_provider.value.trim() == ""){
        isValid = false
        this.placesDataErrors.places_provider = "Preencha este campo"
      } 

      // if((this.placesDataModel.places_url.value == null || this.placesDataModel.places_url.value.trim() == "") && this.placesProviderRule.redundancy_url){
      //   isValid = false
      //   this.placesDataErrors.places_url = "Preencha este campo"
      // }

      if((this.placesDataModel.places_application_id.value == null || this.placesDataModel.places_application_id.value.trim() == "") && this.placesProviderRule.redundancy_id){
        isValid = false
        this.placesDataErrors.places_application_id = "Preencha este campo"
      } 
     
      if(this.placesDataModel.places_redundancy_rule.value == 1){
        if(this.placesDataModel.places_key_redundancy.value == null || this.placesDataModel.places_key_redundancy.value.trim() == ""){
          isValid = false
          this.placesDataErrors.places_key_redundancy = "Preencha este campo"
        } 
        if(this.placesDataModel.places_provider_redundancy.value == null || this.placesDataModel.places_provider_redundancy.value.trim() == ""){
          isValid = false
          this.placesDataErrors.places_provider_redundancy = "Preencha este campo"
        } 

        // if((this.placesDataModel.places_url_redundancy.value == null || this.placesDataModel.places_url_redundancy.value.trim() == "") && this.placesProviderRedundancyRule.redundancy_url){
        //   isValid = false
        //   this.placesDataErrors.places_url_redundancy = "Preencha este campo"
        // }
        
        if((this.placesDataModel.places_application_id_redundancy.value == null || this.placesDataModel.places_application_id_redundancy.value.trim() == "") && this.placesProviderRedundancyRule.redundancy_id){
          isValid = false
          this.placesDataErrors.places_application_id_redundancy = "Preencha este campo"
        } 
      }
      
      return isValid        
    }
  },
  async mounted() {   
    const optionsList = JSON.parse(this.enumData)
    this.placesDataModel = JSON.parse(this.model)   
    this.placesOptions = optionsList.places_provider

    //Set Selected Place Provider
    const selectedPlaceProvider = this.placesOptions.filter(objectData => objectData.value == this.placesDataModel.places_provider.value);
    if(selectedPlaceProvider.length > 0) this.selectPlaceService(selectedPlaceProvider[0]) 

    //Set Selected Redundancy Place Provider
    const selectedPlaceRedundancyProvider = this.placesOptions.filter(objectData => objectData.value == this.placesDataModel.places_provider_redundancy.value);
    if(selectedPlaceRedundancyProvider.length > 0) this.selectPlaceRedundancyService(selectedPlaceRedundancyProvider[0]) 
  },
};
</script>
<template>
  <Card>
    <h4 slot="card-title" class="m-b-0 text-white">{{ trans("geolocation.api_places") }}</h4>

    <h3 slot="card-content-title" class="box-title"></h3>
      <div slot="card-content">
        <div class="row">
          <div class="col-lg-6">
            <div class="form-group">
              <label>
                {{ trans("geolocation.api_places_provider") }}*
              </label>           
              <v-select @input="selectPlaceService" :options="placesOptions" label="name"  v-model="placesProviderRule"/>
              <div class="help-block with-errors" style="color: red;">{{placesDataErrors.places_provider}}</div>	
            </div>
          </div>

          <div class="col-lg-6">
            <div class="form-group">
              <label>
                {{ trans("geolocation.api_places_key") }}*
              </label>
              <input v-model=placesDataModel.places_key.value type="text" class="form-control" />
              <div class="help-block with-errors" style="color: red;">{{placesDataErrors.places_key}}</div>	
            </div>
          </div>
        </div>

        <div class="row">
          <div v-show=placesProviderRule.redundancy_url class="col-lg-6">
            <div class="form-group">
              <label>
                {{ trans("geolocation.api_places_url") }}
              </label>           
              <input v-model=placesDataModel.places_url.value type="text" class="form-control" />
              <div class="help-block with-errors" style="color: red;">{{placesDataErrors.places_url}}</div>	
            </div>
          </div>

          <div v-show=placesProviderRule.redundancy_id class="col-lg-6">
            <div class="form-group">
              <label>
                {{ trans("geolocation.api_places_id") }}*
              </label>
              <input v-model=placesDataModel.places_application_id.value type="text" class="form-control" />
              <div class="help-block with-errors" style="color: red;">{{placesDataErrors.places_application_id}}</div>	
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-lg-12">
            <div class="form-check">            
              <label class="form-check-label pl-0"><h3 style="color: #54667a;">{{ trans("geolocation.enable_red") }}</h3> </label>
              
              <label class="pl-1"><input type="radio" name="radioPlaces" value="1" @change=updatePlacesRedundancy v-model="placesDataModel.places_redundancy_rule.value">{{ trans("geolocation.yes") }}</label>
              <label class="pl-1"><input type="radio" name="radioPlaces" value="0" @change=updatePlacesRedundancy v-model="placesDataModel.places_redundancy_rule.value">{{ trans("geolocation.no") }}</label>
              
            </div>
          </div>
        </div>
        <!-- Placed redundancy -->
        <div v-if="placesDataModel.places_redundancy_rule.value == 1">
          <div class="row">
            <div class="col-lg-6">
              <div class="form-group">
                <label>
                  {{ trans("geolocation.red_api_places_provider") }}*
                </label>           
                <v-select @input="selectPlaceRedundancyService" :options="placesOptions" label="name" v-model="placesProviderRedundancyRule"/>
                <div class="help-block with-errors" style="color: red;">{{placesDataErrors.places_provider_redundancy}}</div>	
              </div>
            </div>

            <div class="col-lg-6">
              <div class="form-group">
                <label>
                  {{ trans("geolocation.red_api_places_key") }}*
                </label>
                <input v-model=placesDataModel.places_key_redundancy.value type="text" class="form-control" />
                <div class="help-block with-errors" style="color: red;">{{placesDataErrors.places_key_redundancy}}</div>	
              </div>
            </div>
          </div>

          <div class="row">
          <div v-show=placesProviderRedundancyRule.redundancy_url class="col-lg-6">
            <div class="form-group">
              <label>
                {{ trans("geolocation.red_api_places_url") }}
              </label>           
              <input v-model=placesDataModel.places_url_redundancy.value type="text" class="form-control" />
              <div class="help-block with-errors" style="color: red;">{{placesDataErrors.places_url_redundancy}}</div>	
            </div>
          </div>

          <div v-show=placesProviderRedundancyRule.redundancy_id class="col-lg-6">
            <div class="form-group">
              <label>
                {{ trans("geolocation.red_api_places_id") }}*
              </label>
              <input v-model=placesDataModel.places_application_id_redundancy.value type="text" class="form-control" />
              <div class="help-block with-errors" style="color: red;">{{placesDataErrors.places_application_id_redundancy}}</div>
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
            {{ trans("geolocation.save") }}
          </button>
        </div>
       
      </div>
  </Card>
</template>
