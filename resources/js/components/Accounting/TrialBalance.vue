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
            <th>Code</th>
            <th>Account Name</th>
            <th class="text-right">Balance</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(account, index) in accounts" :key="account.index" v-if="account[0].usage == 'header'">
            <td><b><a @click.prevent="openAccountLedger(account[0].ledger_no)">{{ account[0].ledger_no }}</a></b></td>
            <td><b>{{ account[0].account_name }}</b></td>
            <td class="text-right"><b>{{ account[1] | number_format}}</b></td>
          </tr>
          <tr v-else>
            <td>{{ account[0].ledger_no }}</td>
            <td>{{ account[0].account_name }}</td>
            <td class="text-right">{{ account[1] | number_format}}</td>
          </tr>
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
    openAccountLedger (account_code ) {

      var getUrl = window.location;
      var baseUrl =  getUrl.protocol + "//" + getUrl.host + "/" + getUrl.pathname.split("/")[0];
      window.location.href = baseUrl + `accounting/account-ledger/?account_code=${account_code}`;
      // window.location = url;
    },
    getData() {
      this.isLoading = true

      axios.get('api/accounting/trial-balance')
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