<template>
  <div>

    <loading :active.sync="isLoading" 
    :can-cancel="true" 
    :is-full-page="false"></loading>
     
    <div class="row">
      <div class="col-md-12">
        <h6>Withdrawal Charge</h6>
        <form>
          <div class="form-group row">
            <div class="col-sm-3 mb-1">
                <span v-if="!toEdit">{{ settings.name }}</span>
                <input v-if="toEdit" v-model="settings.name" type="text" class="form-control" placeholder="Code" />
            </div>
            <div class="col-sm-3 mb-1">
                <span v-if="!toEdit">{{ settings.value }}</span>
                <input v-if="toEdit" v-model="settings.value" type="text" class="form-control" placeholder="Code" />
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
      settings: {
        name: 0,
        type: 0,
        value: 0
      },
      errors: [],
      toEdit: false
    };
  },
  methods: {
    editFee () {
      axios
        .post(`settings/charges/withdrawal-percentage-charge/edit`, this.settings)
        .then(res => {
          // console.log(res.data);
          this.settings.name = res.data.withdrawal_setting.name
          this.settings.type = res.data.withdrawal_setting.type
          this.settings.value = res.data.withdrawal_setting.value
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
          this.settings.name = res.data.withdrawal_setting.name
          this.settings.type = res.data.withdrawal_setting.type
          this.settings.value = res.data.withdrawal_setting.value
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