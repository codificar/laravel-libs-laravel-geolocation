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
      mapsOptions: [],

      mapsProviderRule: {
        name: "",
        value: ""
      },
      mapsDataModel: {
        maps_provider: "",
        maps_key: "",
        required_key: false
      },  
      
      mapsDataErrors: {
        maps_provider: "",
        maps_key: ""     
      }

    };
  },
  methods: {
    selectMapService(selectedData){
      this.mapsProviderRule = selectedData
      this.mapsDataModel.maps_provider.value = selectedData.value
    },
    async saveMaps(){
      //Format Data in Array
      if(!this.validate(this.mapsDataModel)){
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
        let arrayDataModel = Object.keys(this.mapsDataModel).map(key => this.mapsDataModel[key]);
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
       this.mapsDataErrors = {
        maps_provider: "",
        maps_key: ""
      } 
    },
    validate(data){    
      let isValid = true
      if(this.mapsDataModel.maps_key.value == null || this.mapsDataModel.maps_key.value.trim() == "" && this.mapsProviderRule.required_key){
        isValid = false
        this.mapsDataErrors.maps_key = "Preencha este campo"
      } 
      if(this.mapsDataModel.maps_provider.value == null || this.mapsDataModel.maps_provider.value.trim() == ""){
        isValid = false
        this.mapsDataErrors.maps_provider = "Preencha este campo"
      }    
      
      return isValid        
    }
  },
  async mounted() {   
    const optionsList = JSON.parse(this.enumData)
    this.mapsDataModel = JSON.parse(this.model)   
    this.mapsOptions = optionsList.maps_provider

    //Set Selected Place Provider
    const selectedPlaceProvider = this.mapsOptions.filter(objectData => objectData.value == this.mapsDataModel.maps_provider.value);
    if(selectedPlaceProvider.length > 0) this.selectMapService(selectedPlaceProvider[0]) 
  },
};
</script>
<template>
  <Card>
    <h4 slot="card-title" class="text-white m-b-0">{{ trans("geolocation.api_maps") }}</h4>

    <h3 slot="card-content-title" class="box-title"></h3>
      <div slot="card-content">
        <div class="row">
          <div class="col-lg-6">
            <div class="form-group">
              <label>
                {{ trans("geolocation.api_maps_provider") }}*
              </label>           
              <v-select @input="selectMapService" :options="mapsOptions" label="name"  v-model="mapsProviderRule"/>
              <div class="help-block with-errors" style="color: red;">{{mapsDataErrors.maps_provider}}</div>	
            </div>
          </div>

          <div class="col-lg-6">
            <div class="form-group">
              <label>
                {{ trans("geolocation.api_maps_key") }} {{this.mapsProviderRule.required_key ? '*' : ''}}
              </label>
              <input v-model=mapsDataModel.maps_key.value type="text" class="form-control" />
              <div class="help-block with-errors" style="color: red;">{{mapsDataErrors.maps_key}}</div>	
            </div>
          </div>
        </div>
      

        <div class="box-footer pull-right">
          <button
            @click="saveMaps"
            class="btn btn-success right"
            type="button"
          >
            {{ trans("geolocation.save") }}
          </button>
        </div>
       
      </div>
  </Card>
</template>
