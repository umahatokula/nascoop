<template>
  <div>

    <loading :active.sync="isLoading" 
    :can-cancel="true" 
    :is-full-page="false"></loading>

    <div class="row mb-3">
      <div class="col-12 account-links">
        <small><a href="#" class="btn btn-default" @click="setQueryString('')">All ({{coas.length}})</a></small>
        <small><a href="#" class="btn btn-default" @click="setQueryString('asset')">Assets ({{assets.length}})</a></small>
        <small><a href="#" class="btn btn-default" @click="setQueryString('liability')">Liabilities ({{liabilities.length}})</a></small>
        <small><a href="#" class="btn btn-default" @click="setQueryString('equity')">Equity ({{equity.length}})</a></small>
        <small><a href="#" class="btn btn-default" @click="setQueryString('income')">Income ({{income.length}})</a></small>
        <small><a href="#" class="btn btn-default" @click="setQueryString('expense')">Expenses ({{expenses.length}})</a></small>
        <small><a href="#" class="btn btn-success float-right" @click="changeView('newAccount')">Add A New Account</a></small>
      </div>
    </div>

    <div class="row">
      <div class="col-12">
      <table v-if="component == 'coaView'" class="table table-bordered table-striped table-responsive">
        <thead>
          <tr>
            <th class="text-center">Code</th>
            <th class="text-center">Account Name</th>
            <th class="text-center">Type</th>
            <th class="text-center">Usage</th>
            <th class="text-center">In Use</th>
            <th class="text-center">Manual Entries Allowed</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(coa, index) in accounts" :key="coa.index">
            <td>{{ coa.ledger_no }}</td>
            <td>{{ coa.account_name }}</td>
            <td>{{ coa.account_type }}</td>
            <td>
              <span v-if="coa.usage == 'header'" class="badge badge-secondary">{{ coa.usage }}</span>
              <span v-else class="badge badge-primary">{{ coa.usage }}</span>
            </td>
            <td class="text-center">{{ coa.status ? 'Yes' : 'No' }}</td>
            <td class="text-center">{{ coa.allow_manual_journal_entries ? 'Yes' : 'No' }}</td>
          </tr>
        </tbody>
      </table>

      <div v-if="component == 'newAccount'">
        <NewAccount></NewAccount>
      </div>

      </div>
    </div>

  </div>
</template>

<script>

import Vue from 'vue'
import { VueGoodTable } from 'vue-good-table';
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';
import NewAccount from '../../components/Accounting/NewAccount';

export default {
  data() {
    return {
      coas: 0,
      queryString: '',
      isLoading: false,
      fullPage: true,
      component: 'coaView',
    }
  },
  components: {
    VueGoodTable,
    'loading' : Loading,
    NewAccount,
  },
  computed: {
    accounts () {
      if (this.queryString == '') {
        return this.coas
      }
      return this.coas.filter(coa => {
        return coa.account_type == this.queryString
      })
    },
    assets () {
      return this.coas.filter(coa => {
        return coa.account_type == 'asset'
      })
    },
    liabilities () {
      return this.coas.filter(coa => {
        return coa.account_type == `liability`
      })
    },
    equity () {
      return this.coas.filter(coa => {
        return coa.account_type == 'equity'
      })
    },
    income () {
      return this.coas.filter(coa => {
        return coa.account_type == 'income'
      })
    },
    expenses () {
      return this.coas.filter(coa => {
        return coa.account_type == 'expense'
      })
    },
  },
  methods: {
    changeView (view) {
      this.component = view
    },
    setQueryString (s) {
      this.changeView('coaView')
      
      this.queryString = s
    },
    getData() {

      axios.get('api/accounting/chart-of-accounts')
      .then(res => {
        console.log(res)
        this.coas = res.data.data.coas
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
      this.isLoading = true
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