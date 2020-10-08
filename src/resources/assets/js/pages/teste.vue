<script>
import axios from "axios";
import AddressAutocomplete from "../components/AddressAutocomplete"
export default {
  props: ["AutocompleteUrl", "GeocodeUrl"],
  components: {
   AddressAutocomplete
  },
  data() {
    return {
     
    };
  },
  methods: {
    getSelectedData(value) {
		this.location = value;
		console.log(value);
	},
  },
  async mounted() {
    console.log("MOUNT TESTE");
  },
};
</script>
<template>
     <div class="col-lg-12">
    <!-- google maps autocomplete-->
    <label> {{trans('requests.address') }}*</label>        
        <AddressAutocomplete
            ref="autocomplete"
            PlaceHolderText="Escreva o endereço"
            NeedAddressNumberText="Escreva o endereço"
            :AutocompleteParams='{ 
              "id": "",
              "token": "",
              "latitude": -19.743745,
              "longitude": -43.8386481
            }'            
            :AutocompleteUrl='AutocompleteUrl'
            :GeocodeUrl='GeocodeUrl'
            :MinLength='5'
            :Delay='1000'
            @addressSelected='getSelectedData'
        />
    </div>
</template>
