<template>
  <div>

    <loading :active.sync="isLoading" 
    :can-cancel="true" 
    :is-full-page="false"></loading>
     
    <div class="row">
      <div class="col-md-12">
        <h6>Processing Fee</h6>
        <form>
          <div class="form-group row">
            <div class="col-sm-3 mb-1">
              <label>Processing Fee</label>
            </div>
            <div class="col-sm-3 mb-1">
                <span v-if="!toEdit">{{ fee.amount | number_format }}</span>
                <input v-if="toEdit" v-model="fee.amount" type="text" class="form-control" placeholder="Code" />
            </div>
            <div class="col-sm-3 mb-1">
                <a href="#" v-if="!toEdit" class="text-primary" @click.prevent="toEdit = true">Edit</a>
                <a href="#" v-if="toEdit" class="text-danger" @click.prevent="editFee">Save</a>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>

import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';

export default {
  name: "ProcessingFee",
  components: {
    'loading' : Loading
  },
  data() {
    return {
      fee: {
        amount: 0
      },
      errors: [],
      toEdit: false
    };
  },
  methods: {
    editFee () {
      axios
        .post(`settings/charges/processing-fee/edit`, this.fee)
        .then(res => {
          // console.log(res.data);
          this.fee = res.data.processing_fee
          this.toEdit = false
        })
        .catch(e => {
          // console.log(e);
          if (e.response.status == 422) {
            this.errors = e.response.data.errors;
            // Vue.toasted.error("There are errors");
          }
        });

    },
    getData () {
      axios
        .get(`settings/charges`)
        .then(res => {
          // console.log(res.data);
          this.fee = res.data.processing_fee
          this.isLoading = false
        })
        .catch(e => {
          console.log(e);
        });
    }
  },
  created() {
    this.isLoading = true
    this.getData()
  }
};
</script>

<style scoped>
</style>