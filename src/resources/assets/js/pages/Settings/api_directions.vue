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
      directionsOptions: [],

      directionsProviderRule: {
        name: "",
        redundancy_id: false,
        redundancy_url: false,
        value: "",
      },

      directionsProviderRedundancyRule: {
        name: "",
        redundancy_id: false,
        redundancy_url: false,
        value: "",
      },

      directionsDataModel: {
        directions_provider: "",
        directions_key: "",
        directions_url: "",
        places_application_id: "",

        directions_redundancy_rule: "",

        directions_provider_redundancy: "",
        directions_key_redundancy: "",
        directions_url_redundancy: "",
        places_application_id_redundancy: "",

        directions_matrix_distance: "",
        directions_matrix_distance_redundancy: "",
        directions_shortest_distance: "",

        directionsEstimateRule: "",
        directions_provider_estimate: "",
        directions_key_estimate: "",
        directions_url_estimate: "",
        places_application_id_estimate: "",
      },

      directionsDataErrors: {
        directions_provider: "",
        directions_key: "",
        directions_url: "",
        places_application_id: "",

        directions_redundancy_rule: "",

        directions_provider_redundancy: "",
        directions_key_redundancy: "",
        directions_url_redundancy: "",
        places_application_id_redundancy: "",

        directionsEstimateRule: "",
        directions_provider_estimate: "",
        directions_key_estimate: "",
        directions_url_estimate: "",
        places_application_id_estimate: "",
      },

      directionsProviderEstimateRule: {
        name: "",
        redundancy_id: false,
        redundancy_url: false,
        value: "",
      },
    };
  },
  methods: {
    selectDirectionService(selectedData) {
      this.directionsProviderRule = selectedData;
      this.directionsDataModel.directions_provider.value = selectedData.value;
    },
    selectDirectionRedundancyService(selectedData) {
      this.directionsProviderRedundancyRule = selectedData;
      this.directionsDataModel.directions_provider_redundancy.value =
        selectedData.value;
    },
    selectDirectionEstimateService(selectedData) {
      this.directionsProviderEstimateRule = selectedData;
      this.directionsDataModel.directions_provider_estimate.value =
        selectedData.value;
    },
    updateDirectionRedundancy(checkedValue) {
      const value = checkedValue.target.value;
      this.directionsDataModel.directions_redundancy_rule.value = value;
    },
    updateShortestDistance(checkedValue) {
      const value = checkedValue.target.value;
      this.directionsDataModel.directions_shortest_distance.value = value;
    },
    updateEstimateRule(checkedValue) {
      const value = checkedValue.target.value;
      this.directionsDataModel.directionsEstimateRule.value = value;
    },
    async savePlaces() {
      //Format Data in Array
      if (!this.validate(this.directionsDataModel)) {
        this.$toasted.show("Preencha todos os campo obrigatorios", {
          theme: "bubble",
          type: "error",
          position: "bottom-center",
          duration: 3000,
        });
      } else {
        let arrayDataModel = Object.keys(this.directionsDataModel).map(
          (key) => this.directionsDataModel[key]
        );
        const response = await axios.post(this.placeSaveRoute, arrayDataModel);
        this.$toasted.show("Salvo com sucesso", {
          theme: "bubble",
          type: "success",
          position: "bottom-center",
          duration: 3000,
        });
        this.cleanErrors();
        location.reload(true);
      }
    },
    cleanErrors() {
      this.directionsDataErrors = {
        directions_provider: "",
        directions_key: "",
        directions_url: "",
        places_application_id: "",

        directions_redundancy_rule: "",

        directions_provider_redundancy: "",
        directions_key_redundancy: "",
        directions_url_redundancy: "",
        places_application_id_redundancy: "",
      };
    },
    validate(data) {
      let isValid = true;
      if (
        this.directionsDataModel.directions_key.value == null ||
        this.directionsDataModel.directions_key.value.trim() == ""
      ) {
        isValid = false;
        this.directionsDataErrors.directions_key = this.trans(
          "geolocation.invalid_require"
        );
      }
      if (
        this.directionsDataModel.directions_provider.value == null ||
        this.directionsDataModel.directions_provider.value.trim() == ""
      ) {
        isValid = false;
        this.directionsDataErrors.directions_provider = this.trans(
          "geolocation.invalid_require"
        );
      }

      if (
        this.directionsDataModel.directions_url.value.trim() != "" &&
        this.directionsProviderRule.redundancy_url
      ) {
        this.directionsDataModel.directions_url.value =
          this.directionsDataModel.directions_url.value.trim();
        const validate = /^(ftp|http|https):\/\/[^ "]+$/.test(
          this.directionsDataModel.directions_url.value
        );

        if (!validate) {
          isValid = false;
          this.directionsDataErrors.directions_url = this.trans(
            "geolocation.invalid_url"
          );
        } else {
          this.directionsDataErrors.directions_url = "";
        }
      }

      if (
        (this.directionsDataModel.places_application_id.value == null ||
          this.directionsDataModel.places_application_id.value.trim() == "") &&
        this.directionsProviderRule.redundancy_id
      ) {
        isValid = false;
        this.directionsDataErrors.places_application_id = this.trans(
          "geolocation.invalid_require"
        );
      }

      if (this.directionsDataModel.directions_redundancy_rule.value == 1) {
        if (
          this.directionsDataModel.directions_key_redundancy.value == null ||
          this.directionsDataModel.directions_key_redundancy.value.trim() == ""
        ) {
          isValid = false;
          this.directionsDataErrors.directions_key_redundancy = this.trans(
            "geolocation.invalid_require"
          );
        }
        if (
          this.directionsDataModel.directions_provider_redundancy.value ==
            null ||
          this.directionsDataModel.directions_provider_redundancy.value.trim() ==
            ""
        ) {
          isValid = false;
          this.directionsDataErrors.directions_provider_redundancy = this.trans(
            "geolocation.invalid_require"
          );
        }

        if (
          this.directionsDataModel.directions_url_redundancy.value.trim() !=
            "" &&
          this.directionsProviderRedundancyRule.redundancy_url
        ) {
          this.directionsDataModel.directions_url_redundancy.value =
            this.directionsDataModel.directions_url_redundancy.value.trim();
          const validate = /^(ftp|http|https):\/\/[^ "]+$/.test(
            this.directionsDataModel.directions_url_redundancy.value
          );

          if (!validate) {
            isValid = false;
            this.directionsDataErrors.directions_url_redundancy = this.trans(
              "geolocation.invalid_url"
            );
          } else {
            this.directionsDataErrors.directions_url_redundancy = "";
          }
        }

        if (
          (this.directionsDataModel.places_application_id_redundancy.value ==
            null ||
            this.directionsDataModel.places_application_id_redundancy.value.trim() ==
              "") &&
          this.directionsProviderRedundancyRule.redundancy_id
        ) {
          isValid = false;
          this.directionsDataErrors.places_application_id_redundancy =
            this.trans("geolocation.invalid_require");
        }
      }

      //If directions is not google maps, so set optimize 0 (disable)
      if (
        this.directionsProviderRule.value != "google_maps" &&
        this.directionsDataModel.directions_google_optimize_route
      ) {
        this.directionsDataModel.directions_google_optimize_route.value = "0";
      }

      isValid = this.validateDirectionsEstimate();

      return isValid;
    },
    validateDirectionsEstimate() {
      let isValid = true;
      if (
        this.directionsDataModel.directions_key_estimate.value == null ||
        this.directionsDataModel.directions_key_estimate.value.trim() == ""
      ) {
        isValid = false;
        this.directionsDataErrors.directions_key_estimate = this.trans(
          "geolocation.invalid_require"
        );
      }
      if (
        this.directionsDataModel.directions_provider_estimate.value == null ||
        this.directionsDataModel.directions_provider_estimate.value.trim() == ""
      ) {
        isValid = false;
        this.directionsDataErrors.directions_provider_estimate = this.trans(
          "geolocation.invalid_require"
        );
      }

      if (
        this.directionsDataModel.directions_url_estimate.value.trim() != "" &&
        this.directionsProviderEstimateRule.directions_url
      ) {
        this.directionsDataModel.directions_url_estimate.value =
          this.directionsDataModel.directions_url_estimate.value.trim();
        const validate = /^(ftp|http|https):\/\/[^ "]+$/.test(
          this.directionsDataModel.directions_url_estimate.value
        );

        if (!validate) {
          isValid = false;
          this.directionsDataErrors.directions_url_estimate = this.trans(
            "geolocation.invalid_url"
          );
        } else {
          this.directionsDataErrors.directions_url_estimate = "";
        }
      }

      return isValid;
    }
  },
  async mounted() {
    const optionsList = JSON.parse(this.enumData);

    this.directionsDataModel = JSON.parse(this.model);
    console.log(this.directionsDataModel)
    this.directionsOptions = optionsList.directions_provider;
    //Set Selected Directions Provider
    const selectedDirectionProvider = this.directionsOptions.filter(
      (objectData) =>
        objectData.value == this.directionsDataModel.directions_provider.value
    );
    if (selectedDirectionProvider.length > 0)
      this.selectDirectionService(selectedDirectionProvider[0]);

    //Set Selected Redundancy Place Provider
    const selectedDirectionRedundancyProvider = this.directionsOptions.filter(
      (objectData) =>
        objectData.value ==
        this.directionsDataModel.directions_provider_redundancy.value
    );
    if (selectedDirectionRedundancyProvider.length > 0)
      this.selectDirectionRedundancyService(
        selectedDirectionRedundancyProvider[0]
      );

    //Set Selected estimate Place Provider
    const selectedDirectionEstimateProvider = this.directionsOptions.filter(
      (objectData) =>
        objectData.value ==
        this.directionsDataModel.directions_provider_estimate.value
    );
    console.log(selectedDirectionEstimateProvider)
    if (selectedDirectionEstimateProvider.length > 0) {
      this.selectDirectionEstimateService(selectedDirectionEstimateProvider[0]);
    }
  },
};
</script>
<template>
  <Card>
    <h4 slot="card-title" class="m-b-0 text-white">
      {{ trans("geolocation.api_directions") }}
    </h4>

    <h3 slot="card-content-title" class="box-title"></h3>
    <div slot="card-content">
      <div class="row">
        <div class="col-lg-6">
          <div class="form-group">
            <label> {{ trans("geolocation.api_places_provider") }}* </label>
            <v-select
              @input="selectDirectionService"
              :options="directionsOptions"
              label="name"
              v-model="directionsProviderRule"
            />
            <div class="help-block with-errors" style="color: red">
              {{ directionsDataErrors.directions_provider }}
            </div>
          </div>
        </div>

        <div class="col-lg-6">
          <div class="form-group">
            <label> {{ trans("geolocation.api_places_key") }}* </label>
            <input
              v-model="directionsDataModel.directions_key.value"
              type="text"
              class="form-control"
            />
            <div class="help-block with-errors" style="color: red">
              {{ directionsDataErrors.directions_key }}
            </div>
          </div>
        </div>
      </div>

      <div class="row">
        <div v-show="directionsProviderRule.redundancy_url" class="col-lg-6">
          <div class="form-group">
            <label>
              {{ trans("geolocation.api_places_url") }}
            </label>
            <input
              v-model="directionsDataModel.directions_url.value"
              type="text"
              class="form-control"
            />
            <div class="help-block with-errors" style="color: red">
              {{ directionsDataErrors.directions_url }}
            </div>
          </div>
        </div>

        <div v-show="directionsProviderRule.redundancy_id" class="col-lg-6">
          <div class="form-group">
            <label> {{ trans("geolocation.api_places_id") }}* </label>
            <input
              v-model="directionsDataModel.places_application_id.value"
              type="text"
              class="form-control"
            />
            <div class="help-block with-errors" style="color: red">
              {{ directionsDataErrors.places_application_id }}
            </div>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-lg-12">
          <div class="form-check">
            <label class="form-check-label pl-0">
              <h3 style="color: #54667a">
                {{ trans("geolocation.matrix_distance") }}
              </h3>
            </label>
            <label class="pl-1"
              ><input
                type="radio"
                name="radioMatrix"
                value="1"
                v-model="directionsDataModel.directions_matrix_distance.value"
              />{{ trans("geolocation.yes") }}</label
            >
            <label class="pl-1"
              ><input
                type="radio"
                name="radioMatrix"
                value="0"
                v-model="directionsDataModel.directions_matrix_distance.value"
              />{{ trans("geolocation.no") }}</label
            >
          </div>
        </div>

        <!-- Option google maps optimize route -->
        <div
          v-if="
            directionsProviderRule &&
            directionsProviderRule.value == 'google_maps'
          "
          class="col-lg-12"
        >
          <div class="form-check">
            <label class="form-check-label pl-0">
              <h3 style="color: #54667a">
                {{ trans("geolocation.optimize_route") }}
              </h3>
            </label>
            <label class="pl-1"
              ><input
                type="radio"
                name="radioOptimize"
                value="1"
                v-model="
                  directionsDataModel.directions_google_optimize_route.value
                "
              />{{ trans("geolocation.yes") }}</label
            >
            <label class="pl-1"
              ><input
                type="radio"
                name="radioOptimize"
                value="0"
                v-model="
                  directionsDataModel.directions_google_optimize_route.value
                "
              />{{ trans("geolocation.no") }}</label
            >
          </div>
        </div>

        <div class="col-lg-12">
          <div class="form-check">
            <label class="form-check-label pl-0">
              <h3 style="color: #54667a">
                {{ trans("geolocation.enable_red") }}
              </h3>
            </label>

            <label class="pl-1"
              ><input
                type="radio"
                name="radioDirections"
                value="1"
                @change="updateDirectionRedundancy"
                v-model="directionsDataModel.directions_redundancy_rule.value"
              />{{ trans("geolocation.yes") }}</label
            >
            <label class="pl-1"
              ><input
                type="radio"
                name="radioDirections"
                value="0"
                @change="updateDirectionRedundancy"
                v-model="directionsDataModel.directions_redundancy_rule.value"
              />{{ trans("geolocation.no") }}</label
            >
          </div>
        </div>

        <div class="col-lg-12">
          <div
            v-if="directionsDataModel.directions_shortest_distance"
            class="form-check"
          >
            <label class="form-check-label pl-0">
              <h3 style="color: #54667a">
                {{ trans("geolocation.shortest_distance") }}
              </h3>
            </label>
            <label class="pl-1"
              ><input
                type="radio"
                name="radioShortestDistance"
                value="1"
                v-model="directionsDataModel.directions_shortest_distance.value"
                @change="updateShortestDistance"
              />
              {{ trans("geolocation.yes") }}
            </label>
            <label class="pl-1"
              ><input
                type="radio"
                name="radioShortestDistance"
                value="0"
                v-model="directionsDataModel.directions_shortest_distance.value"
                @change="updateShortestDistance"
              />
              {{ trans("geolocation.no") }}</label
            >
          </div>
        </div>
      </div>
      <!-- Directions redundancy -->
      <div v-if="directionsDataModel.directions_redundancy_rule.value == 1">
        <div class="row">
          <div class="col-lg-6">
            <div class="form-group">
              <label>
                {{ trans("geolocation.red_api_places_provider") }}*
              </label>
              <v-select
                @input="selectDirectionRedundancyService"
                :options="directionsOptions"
                label="name"
                v-model="directionsProviderRedundancyRule"
              />
              <div class="help-block with-errors" style="color: red">
                {{ directionsDataErrors.directions_provider_redundancy }}
              </div>
            </div>
          </div>

          <div class="col-lg-6">
            <div class="form-group">
              <label> {{ trans("geolocation.api_places_key") }}* </label>
              <input
                v-model="directionsDataModel.directions_key_redundancy.value"
                type="text"
                class="form-control"
              />
              <div class="help-block with-errors" style="color: red">
                {{ directionsDataErrors.directions_key_redundancy }}
              </div>
            </div>
          </div>
        </div>

        <div class="row">
          <div
            v-show="directionsProviderRedundancyRule.redundancy_url"
            class="col-lg-6"
          >
            <div class="form-group">
              <label>
                {{ trans("geolocation.api_places_url") }}
              </label>
              <input
                v-model="directionsDataModel.directions_url_redundancy.value"
                type="text"
                class="form-control"
              />
              <div class="help-block with-errors" style="color: red">
                {{ directionsDataErrors.directions_url_redundancy }}
              </div>
              <div class="help-block with-errors" style="color: red">
                {{ directionsDataErrors.directions_url_redundancy }}
              </div>
              <div class="help-block with-errors" style="color: red">
                {{ directionsDataErrors.directions_url_redundancy }}
              </div>
            </div>
          </div>

          <div class="col-lg-12">
            <div class="form-check">
              <label class="form-check-label pl-0">
                <h3 style="color: #54667a">
                  {{ trans("geolocation.matrix_distance_redundancy") }}
                </h3>
              </label>
              <label class="pl-1"
                ><input
                  type="radio"
                  name="radioMatrixRedundancy"
                  value="1"
                  v-model="
                    directionsDataModel.directions_matrix_distance_redundancy
                      .value
                  "
                />{{ trans("geolocation.yes") }}</label
              >
              <label class="pl-1"
                ><input
                  type="radio"
                  name="radioMatrixRedundancy"
                  value="0"
                  v-model="
                    directionsDataModel.directions_matrix_distance_redundancy
                      .value
                  "
                />{{ trans("geolocation.no") }}</label
              >
            </div>
          </div>
        </div>
      </div>

      <!-- Estimativa durante trajeto -->
      <div class="row">
        <div class="col-lg-12">
          <div class="form-check">
            <label class="form-check-label pl-0">
              <h3 style="color: #54667a">
                habilitar provedor padrão para calcular estimativa durante o
                trajeto
              </h3>
            </label>

            <label class="pl-1">
              <input
                v-model="directionsDataModel.directionsEstimateRule.value"
                type="radio"
                name="radioEstimateDirections"
                value="1"
                @change="updateEstimateRule"
              />{{ trans("geolocation.yes") }}
            </label>
            <label class="pl-1">
              <input
                v-model="directionsDataModel.directionsEstimateRule.value"
                type="radio"
                name="radioEstimateDirections"
                value="0"
                @change="updateEstimateRule"
              />{{ trans("geolocation.no") }}
            </label>
          </div>
        </div>
      </div>

      <div v-if="directionsDataModel.directionsEstimateRule.value == 1">
        <div class="row">
          <div class="col-lg-6">
            <div class="form-group">
              <label> {{ trans("geolocation.api_places_provider") }}* </label>
              <v-select
                @input="selectDirectionEstimateService"
                :options="directionsOptions"
                label="name"
                v-model="directionsProviderEstimateRule"
              />
              <div class="help-block with-errors" style="color: red">
                {{ directionsDataErrors.directions_provider_estimate }}
              </div>
            </div>
          </div>

          <div class="col-lg-6">
            <div class="form-group">
              <label> {{ trans("geolocation.api_places_key") }}* </label>
              <input
                v-model="directionsDataModel.directions_key_estimate.value"
                type="text"
                class="form-control"
              />
              <div class="help-block with-errors" style="color: red">
                {{ directionsDataErrors.directions_key_estimate }}
              </div>
            </div>
          </div>
        </div>

        <div class="row">
          <div
            v-show="directionsProviderEstimateRule.redundancy_url"
            class="col-lg-6"
          >
            <div class="form-group">
              <label>
                {{ trans("geolocation.api_places_url") }}
              </label>
              <input
                v-model="directionsDataModel.directions_url_estimate.value"
                type="text"
                class="form-control"
              />
              <div class="help-block with-errors" style="color: red">
                {{ directionsDataErrors.directions_url_estimate }}
              </div>
              <div class="help-block with-errors" style="color: red">
                {{ directionsDataErrors.directions_url_estimate }}
              </div>
              <div class="help-block with-errors" style="color: red">
                {{ directionsDataErrors.directions_url_estimate }}
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="box-footer pull-right">
        <button @click="savePlaces" class="btn btn-success right" type="button">
          {{ trans("geolocation.save") }}
        </button>
      </div>
    </div>
  </Card>
</template>
