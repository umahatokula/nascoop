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

          <div class="form-group row">
            <label for="title" class="col-sm-3 col-form-label">Account Name</label>
            <div class="col-sm-9">
              <input v-model="account.account_name" type="text" class="form-control" />
              <small v-if="errors.account_name" class="text-danger">{{ errors.account_name[0] }}</small>
            </div>
          </div>

          <div class="form-group row">
            <label for="ledger_no" class="col-sm-3 col-form-label">Account Ledger_no</label>
            <div class="col-sm-9">
              <input v-model="account.ledger_no" type="text" class="form-control" />
              <small v-if="errors.ledger_no" class="text-danger">{{ errors.ledger_no[0] }}</small>
            </div>
          </div>

          <div class="form-group row">
            <label for="coop_no" class="col-sm-3 col-form-label">&nbsp</label>
            <div class="col-sm-9">
              <button class="btn btn-primary">Submit</button>
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
      },
      account_types: [],
      accountTypeChildren: [],
      errors: [],
      accountCreatedSuccess: false,
      isLoading: false,
      fullPage: true,
    }
  },
  methods: {
     onSelectAccountType (accountType) {
       this.account.account_type = accountType.account_type

       this.getAccountTypeChildren(accountType.account_type)
     },
     onSelectParent (parent) {
       this.account.parent_id = parent.id
     },
     getAccountTypeChildren(accountType) {
       this.isLoading = true
       axios
        .get(`accounts/new/ledger/internal/accountype/chidlren/${accountType}`)
        .then(res => {
          
          this.accountTypeChildren = res.data.children
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
      .post('accounts/new/ledger/internal/post', this.account)
      .then(res => {
        // console.log(res)
        this.accountCreatedSuccess = true
        this.account.account_name  = null
        this.account.ledger_no     = null
        this.account.parent_id     = null
        
      })
      .catch(e => {
        // console.log(e)
        if (e.response.status == 422) {
          this.errors = e.response.data.errors;
          Vue.toasted.error("There are errors");
        }
      })
    }
  },
  created() {
      this.isLoading = true

      axios
        .get(`accounts/new/ledger/internal`)
        .then(res => {
          // console.log(res.data);member

          this.account_types = res.data.account_types

          this.isLoading = false
        })
        .catch(e => {
          console.log(e);
        });
  }
}
</script>

<style>

</style>