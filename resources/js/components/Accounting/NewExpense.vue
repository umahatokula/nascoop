<template>
  <div>

    <loading :active.sync="isLoading" 
    :can-cancel="true" 
    :is-full-page="false"></loading>
        
    <div class="row">
      <div class="col-12">

        <h4>Manual Entry</h4>

        <div v-if="entrySuccess" class="alert alert-success" role="alert">
          Entry was successful
        </div>
        
        

        <form @submit.prevent="submitForm">

          <div class="form-group row">
            <label for="debit_account" class="col-sm-3 col-form-label"> Debit Account</label>
            <div class="col-sm-9">
              <v-select label="name" :options="accounts" @input="onSelectDebitAccount($event)"></v-select>
              <small v-if="errors.debit_account" class="text-danger">{{ errors.debit_account[0] }}</small>
            </div>
          </div>

          <div class="form-group row">
            <label for="credit_account" class="col-sm-3 col-form-label"> Credit Account</label>
            <div class="col-sm-9">
              <v-select label="name" :options="accounts" @input="onSelectCreditAccount($event)"></v-select>
              <small v-if="errors.credit_account" class="text-danger">{{ errors.credit_account[0] }}</small>
            </div>
          </div>

          <div class="form-group row">
            <label for="entry" class="col-sm-3 col-form-label">Amount</label>
            <div class="col-sm-9">
              <input v-model="entry.amount" type="text" class="form-control" id="entry" />
              <small v-if="errors.amount" class="text-danger">{{ errors.amount[0] }}</small>
            </div>
          </div>

          <div class="form-group row">
            <label for="entry" class="col-sm-3 col-form-label">Booking Date (Entry Date)</label>
            <div class="col-sm-9">
              <input v-model="entry.entry_date" type="date" class="form-control" id="entry" />
              <small v-if="errors.entry_date" class="text-danger">{{ errors.entry_date[0] }}</small>
            </div>
          </div>

          <div class="form-group row">
            <label for="description" class="col-sm-3 col-form-label">Description</label>
            <div class="col-sm-9">
              <textarea v-model="entry.description" class="form-control" id="description"></textarea>
              <small v-if="errors.description" class="text-danger">{{ errors.description[0] }}</small>
            </div>
          </div>

          <div class="form-group row">
            <label for="coop_no" class="col-sm-3 col-form-label">&nbsp</label>
            <div class="col-sm-9">
              <button class="btn btn-secondary" type="button">Cancel</button>
              <button class="btn btn-primary" type="submit">Make Entry</button>
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
  name: 'MakeLedgerEntry',
  components: {
    'loading' : Loading
  },
  data() {
    return {
      entry: {
          debit_account: null,
          credit_account: null,
          amount: null,
          entry_date: '',
          description: '',
      },
      accounts: [],
      errors: [],
      entrySuccess: false,
      isLoading: false,
      fullPage: true,
      parent: null,
    }
  },
  methods: {
  	onSelectDebitAccount(account) {
  		this.entry.debit_account  = account.ledger_no
  	},
  	onSelectCreditAccount(account) {
  		this.entry.credit_account  = account.ledger_no
  	},
    submitForm(account) {

      const confirmation = confirm("Are you sure?");

      if (!confirmation) {
        return;
      }

      axios
      .post('api/accounting/new-expense/post', this.entry)
      .then(res => {
        // console.log(res)
        this.entrySuccess = true

          this.entry.debit_account  = ''
	      this.entry.credit_account  = ''
	      this.entry.amount  = ''
	      this.entry.entry_date = ''
	      this.entry.description = ''
        
      })
      .catch(e => {
        // console.log(e)
        if (e.response.status == 422) {
          this.errors = e.response.data.errors;
          Vue.toasted.error("There are errors");
        }
      })
    },
    getData () {
      axios
        .get(`api/accounting/new-expense`)
        .then(res => {
          // console.log(res.data);member

          this.accounts = res.data.data.accounts

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