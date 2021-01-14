<template>
  <div>

    <loading :active.sync="isLoading" 
    :can-cancel="true" 
    :is-full-page="false"></loading>
        
    <div class="row">
      <div class="col-12">

        <div v-if="accountCreatedSuccess" class="alert alert-success" role="alert">
          Account created successfully
        </div>

        <form @submit.prevent="submitForm">

          <div class="form-group row">
            <label for="account_name" class="col-sm-3 col-form-label">Account Name</label>
            <div class="col-sm-9">
              <input v-model="account.account_name" type="text" class="form-control" />
              <small v-if="errors.account_name" class="text-danger">{{ errors.account_name[0] }}</small>
            </div>
          </div>

          <div class="form-group row">
            <label for="account_type" class="col-sm-3 col-form-label">Account Type</label>
            <div class="col-sm-9">
              <v-select label="name" :options="account_types" @input="onSelectAccountType($event)"></v-select>
              <small v-if="errors.account_type" class="text-danger">{{ errors.account_type[0] }}</small>
            </div>
          </div>

          <div class="form-group row">
            <label for="parent_id" class="col-sm-3 col-form-label">Parent</label>
            <div class="col-sm-9">
              <v-select label="account_name" :options="accountTypeChildren" @input="onSelectParent($event)"></v-select>
              <small v-if="errors.parent_id" class="text-danger">{{ errors.parent_id[0] }}</small>
            </div>
          </div>

          <div v-if="parent" class="form-group row">
            <label for="parent_id" class="col-sm-3 col-form-label">Parent Account Code</label>
            <div class="col-sm-9">
              {{ parent.ledger_no }}
            </div>
          </div>

          <div class="form-group row">
            <label for="ledger_no" class="col-sm-3 col-form-label">Account Code</label>
            <div class="col-sm-9">
              <input v-model="account.ledger_no" type="text" class="form-control" />
              <small v-if="errors.ledger_no" class="text-danger">{{ errors.ledger_no[0] }}</small>
            </div>
          </div>

          <div class="form-group row">
            <label for="usage" class="col-sm-3 col-form-label">Usage</label>
            <div class="col-sm-9">
              <v-select label="label" :options="usages" @input="onSelectUsage($event)"></v-select>
              <small v-if="errors.usage" class="text-danger">{{ errors.usage[0] }}</small>
            </div>
          </div>
          
          <div v-if="account.usage == 'detail'" class="form-check my-3">
            <input class="form-check-input" type="checkbox" v-model="account.allow_manual_journal_entries" id="allow_manual_journal_entries">
            <label class="form-check-label" for="allow_manual_journal_entries">
              Allow Manual Journal Entries
            </label>
          </div>
          
          <!-- <div v-if="account.usage == 'header'" class="form-check my-3">
            <input class="form-check-input" type="checkbox" v-model="account.use_centers_as_detail_accounts" id="use_centers_as_detail_accounts">
            <label class="form-check-label" for="use_centers_as_detail_accounts">
              Use Centers As Detail Accounts
            </label>
          </div>
          
          <div v-if="account.usage == 'header'" class="form-check my-3">
            <input class="form-check-input" type="checkbox" v-model="account.ignore_trailing_zeros" id="ignore_trailing_zeros">
            <label class="form-check-label" for="ignore_trailing_zeros">
              Ignore Trailing Zeros in Reports Calculations
            </label>
          </div> -->
          
          <div class="form-check my-3">
            <input class="form-check-input" type="checkbox" v-model="account.show_in_report_as_header" id="show_in_report_as_header">
            <label class="form-check-label" for="show_in_report_as_header">
              Show In Report
            </label>
          </div>

          <div class="form-check my-3">
            <input class="form-check-input" type="checkbox" v-model="account.show_total_amount_in_report" id="show_total_amount_in_report">
            <label class="form-check-label" for="show_total_amount_in_report">
              Show Total Amount In Report
            </label>
          </div>

          <div class="form-group row">
            <label for="description" class="col-sm-3 col-form-label">Description</label>
            <div class="col-sm-9">
              <textarea v-model="account.description" class="form-control"></textarea>
              <small v-if="errors.description" class="text-danger">{{ errors.description[0] }}</small>
            </div>
          </div>

          <div class="form-group row">
            <label for="coop_no" class="col-sm-3 col-form-label">&nbsp</label>
            <div class="col-sm-9">
              <button class="btn btn-secondary" type="button">Cancel</button>
              <button class="btn btn-primary" type="submit">Add New Account</button>
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
  name: 'NewAccount',
  components: {
    'loading' : Loading
  },
  data() {
    return {
      account: {
          account_name: null,
          ledger_no: null,
          parent_id: null,
          usage: null,
          allow_manual_journal_entries: null,
          ignore_trailing_zeros: 1,
          use_centers_as_detail_accounts: 0,
          show_in_report_as_header: 0,
          show_total_amount_in_report: 1,
          description: null,
      },
      account_types: [],
      accountTypeChildren: [],
      errors: [],
      usages: [],
      accountCreatedSuccess: false,
      isLoading: false,
      fullPage: true,
      parent: null,
    }
  },
  methods: {
     onSelectUsage (usage) {
       this.account.usage = usage.value

       if (usage.value == 'detail') {
        this.account.ignore_trailing_zeros = null
       }

       if (usage.value == 'header') {
        this.account.allow_manual_journal_entries = null
       }
     },
     onSelectAccountType (accountType) {
       this.account.account_type = accountType.account_type

       this.getAccountTypeChildren(accountType.account_type)
     },
     onSelectParent (parent) {
       this.account.parent_id = parent.id
       this.parent = parent
       
       this.isLoading = true
       this.getSuggestedLedgerNumber()
     },
     getAccountTypeChildren(accountType) {
       this.isLoading = true
       axios
        .get(`api/accounting/accountype/chidlren/${accountType}`)
        .then(res => {
          
          this.accountTypeChildren = res.data.data.children
          this.isLoading = false

        })
        .catch(e => {
          console.log(e);
        });
     },
    submitForm(event) {

      const confirmation = confirm("Are you sure?");

      if (!confirmation) {
        return;
      }

      axios
      .post('api/accounting/new-account/post', this.account)
      .then(res => {
        // console.log(res)
        this.accountCreatedSuccess = true
        this.account.account_name  = ''
        this.account.ledger_no     = ''
        this.account.parent_id     = ''
        
      })
      .catch(e => {
        // console.log(e)
        if (e.response.status == 422) {
          this.errors = e.response.data.errors;
          Vue.toasted.error("There are errors");
        }
      })
    },
    getSuggestedLedgerNumber () {
      axios
        .get(`api/accounting/new-account/suggest-ledger-number/${this.parent.id}`)
        .then(res => {
          // console.log(res.data);member

          this.account.ledger_no = res.data.data.ledger_no
          this.isLoading = false

        })
        .catch(e => {
          console.log(e);
        });
    },
    getData () {
      axios
        .get(`api/accounting/new-account`)
        .then(res => {
          // console.log(res.data);member

          this.account_types = res.data.data.account_types
          this.usages        = res.data.data.usages

          this.isLoading = false
        })
        .catch(e => {
          console.log(e);
        });
    },
  },
  created() {
      this.isLoading = true
      this.getData()
  }
}
</script>

<style>

</style>