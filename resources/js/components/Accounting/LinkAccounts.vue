<template>
  <div>

    <loading :active.sync="isLoading" 
    :can-cancel="true" 
    :is-full-page="false"></loading>
        
    <div class="row">
      <div class="col-12">

        <form @submit.prevent="submitForm">

          <table class="table table-responsive">
            <thead>
              <tr>
                <th>Transaction Type</th>
                <th>Debit</th>
                <th>Credit</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(type, index) in trxn_types" :key="type.xact_type_code_ext">
                <td>{{ type.description }}</td>
                <td>
                  <v-select label="account_name" v-model="type.associated_trxns.dr" :options="detail_accounts" @input="onSelectDetailAccountDr($event)"></v-select>
                </td>
                <td>
                  <v-select label="account_name" v-model="type.associated_trxns.cr" :options="detail_accounts" @input="onSelectDetailAccountCr($event)"></v-select>
                </td>
                <td>
                  <a href="#" @click.prevent="linkAccount(index)" class="btn btn-xs btn-primary">Link</a>
                </td>
              </tr>
            </tbody>
          </table>

          <div class="form-group row">
            <label for="coop_no" class="col-sm-3 col-form-label">&nbsp</label>
            <div class="col-sm-9">
              <button class="btn btn-secondary" type="button">Cancel</button>
              <button class="btn btn-primary" type="submit">Save</button>
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
  name: 'LinkAccounts',
  components: {
    'loading' : Loading
  },
  data() {
    return {
      trxn_types: [],
      detail_accounts: [],
      associated_trxns: [],
      errors: [],
      isLoading: false,
      fullPage: true,
      dr: '',
      cr: '',
    }
  },
  methods: {
     onSelectDetailAccountDr (account) {
       this.dr = account
     },
     onSelectDetailAccountCr (account) {
       this.cr = account      
     },
     linkAccount(index) {
       const associated_trxns = {
         'dr' : this.dr.ledger_no,
         'cr' : this.cr.ledger_no,
       }
       this.trxn_types[index].associated_trxns = associated_trxns
     },
    submitForm(event) {

      const confirmation = confirm("Are you sure?");

      if (!confirmation) {
        return;
      }

      axios
      .post('api/accounting/link-accounts/post', this.trxn_types)
      .then(res => {
        // console.log(res)
      })
      .catch(e => {
        // console.log(e)
        this.$toastr('error', 'There was an error', 'hello')
        if (e.response.status == 422) {
          this.errors = e.response.data.errors;
        }
      })
    },
    getData () {
      axios
        .get(`api/accounting/link-accounts`)
        .then(res => {
          // console.log(res.data);member

          this.detail_accounts = res.data.data.detail_accounts
          this.trxn_types = res.data.data.trxn_types

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