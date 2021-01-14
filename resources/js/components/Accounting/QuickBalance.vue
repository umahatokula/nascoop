<template>
  <div>

    <loading :active.sync="isLoading" 
    :can-cancel="true" 
    :is-full-page="false"></loading>
        
    <div class="row">
      <div class="col-lg-4">

        <form @submit.prevent="submitForm">

          <div class="form-group row">
            <label for="account_name" class="col-sm-3 col-form-label">Account Code</label>
            <div class="col-sm-9">
              <input v-model="account.ledger_no" type="text" class="form-control" />
              <small v-if="errors.ledger_no" class="text-danger">{{ errors.ledger_no[0] }}</small>
            </div>
          </div>

          <div class="form-group row">
            <div class="col-12">
              <button class="btn btn-primary btn-block">Get Balance</button>
            </div>
          </div>

        </form>
      </div>
      <div class="col-lg-8">
        <table v-if="result.length != 0" class="table table-bordered table-condensed">
          <thead>
            <tr>
              <th>Account Code</th>
              <th>Account Name</th>
              <th class="text-right">Balance (NGN)</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(res, index) in result" :key="index">
              <td>{{ res[0].ledger_no }}</td>
              <td>{{ res[0].account_name }}</td>
              <td class="text-right">{{ res[1] | number_format }}</td>
            </tr>
          </tbody>
          <tfoot>
            <tr>
              <th class="text-center" colspan="2">Total</th>
              <th class="text-right">{{total | number_format}}</th>
            </tr>
          </tfoot>
        </table>
        <div v-else>No result</div>
      </div>
    </div>
  </div>
</template>

<script>
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';

export default {
  name: 'QuickBalance',
  components: {
    'loading' : Loading
  },
  data() {
    return {
      account: {
        ledger_no: ''
      },
      result: [],
      errors: [],
      total: 0,
      isLoading: false,
      fullPage: true,
    }
  },
  methods: {
    submitForm(event) {

      this.isLoading = true

      axios
      .post('api/accounting/quick-balance', this.account)
      .then(res => {
        // console.log(res)
        this.result = res.data.data.result
        this.total = res.data.data.total
        this.errors = []
        this.isLoading = false
        
      })
      .catch(e => {
        // console.log(e)
        if (e.response.status == 422) {
          this.errors = e.response.data.errors;
          this.isLoading = false
        }
      })
    },
  },
  created() {
    
  }
}
</script>

<style>

</style>