<template>
  <div>
    <div class="col-lg-12">
      <div class="card card-outline-info">
        <!--/ CARD -->
        <div class="card-header">
          <slot name="card-title"> </slot>
        </div>
        <!--/ Content -->
        <div class="card-block">
          <div class="box box-warning">
            <div class="box-header">
              <slot name="card-content-title"> </slot>
            </div>
            <slot name="card-content"> </slot>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
/**
 * @author Gustavo Silva  <gustavo.silva@codifica.com.br> 08/07/2020
 *
 * Render Content Card
 *
 */
export default {
  name: "Card",
};
</script>

<style></style>
