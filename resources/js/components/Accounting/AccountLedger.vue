<template>
  <div>

    <loading :active.sync="isLoading" 
    :can-cancel="true" 
    :is-full-page="false"></loading>

    <div class="row">
      <div class="col-12">
      <table class="table table-bordered table-striped table-responsive-md">
        <thead>
          <tr>
            <th>Date</th>
            <th class="text-right">Dr</th>
            <th class="text-right">Cr</th>
          </tr>
        </thead>
        <tbody>
          
        </tbody>
      </table>

      </div>
    </div>

  </div>
</template>

<script>

import Vue from 'vue'
import { VueGoodTable } from 'vue-good-table';
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';

export default {
  name: 'AccountLedger',
  props: ['account_code'],
  data() {
    return {
      accounts: [],
      isLoading: false,
      fullPage: true,
    }
  },
  components: {
    VueGoodTable,
    'loading' : Loading,
  },
  computed: {
  },
  methods: {
    getData() {
      this.isLoading = true

      axios.get(`accounting/account-ledger/?account_code=/${this.account_code}`)
      .then(res => {
        console.log(res.data.data.trial_balances)
        this.accounts = res.data.data.trial_balances
        this.isLoading = false
      })
      .catch(e => {
        // console.log(e)
        if (e.response.status == 422) {
          this.errors = e.response.data.errors;
        }
      })
    }
  },
  created() {
      this.getData()
  }
}
</script>

<style>
.account-links .btn {
  border-radius: 3px;
  font-size: 12px;
}
</style>