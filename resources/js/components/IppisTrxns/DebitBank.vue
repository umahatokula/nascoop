<template>
  <div>

    <loading :active.sync="isLoading" 
    :can-cancel="true" 
    :is-full-page="false"></loading>
    
    <div class="row">
      <div class="col-12">
        <h6>Debit Bank</h6>
        <form @submit.prevent="submitForm">
          <div class="form-group row">
            <label for="pf" class="col-sm-3 col-form-label">Description</label>
            <div class="col-sm-9">
              <input v-model="ippisTrxn.ref" type="text" class="form-control" />
              <small v-if="errors.ref" class="text-danger">{{ errors.ref[0] }}</small>
            </div>
          </div>

          <div class="form-group row">
            <label for="email" class="col-sm-3 col-form-label">Amount</label>
            <div class="col-sm-9">
              <input
                v-model="ippisTrxn.amount"
                type="text"
                class="form-control"
              />
              <small v-if="errors.amount" class="text-danger">{{ errors.amount[0] }}</small>
            </div>
          </div>

          <div class="form-group row">
            <label for="email" class="col-sm-3 col-form-label">Deduction Month</label>
            <div class="col-sm-9">
              <input
                v-model="ippisTrxn.deduction_for"
                type="date"
                class="form-control"
              />
              <small v-if="errors.deduction_for" class="text-danger">{{ errors.deduction_for[0] }}</small>
            </div>
          </div>

          <div class="form-group row">
            <label for="email" class="col-sm-3 col-form-label">Value Date</label>
            <div class="col-sm-9">
              <input
                v-model="ippisTrxn.value_date"
                type="date"
                class="form-control"
              />
              <small v-if="errors.value_date" class="text-danger">{{ errors.value_date[0] }}</small>
            </div>
          </div>

          <div class="form-group row">
            <label for="ippis" class="col-sm-3 col-form-label">Center</label>
            <div class="col-sm-9">
              <v-select label="name" :options="centers" @input="onSelectCenter($event)"></v-select>
              <small v-if="errors.center_id" class="text-danger">{{ errors.center_id[0] }}</small>
            </div>
          </div>

          <div class="form-group row">
            <label for="email" class="col-sm-3 col-form-label">Transfer To</label>
            <div class="col-sm-9">
              <v-select label="account_name" :options="bankAccounts" @input="onSelectBank($event)"></v-select>
              <small v-if="errors.bank" class="text-danger">{{ errors.bank[0] }}</small>
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
  name: "DebitBank",
  components: {
    'loading' : Loading
  },
  data() {
    return {
      ippisTrxn: {
        ref: null,
        deduction_for: null,
        value_date: null,
        center_id: null,
        amount: null,
        bank: null,
      },
      centers: [],
      bankAccounts: [],
      errors: [],
    };
  },
  methods: {
    onSelectCenter(center) {
      this.ippisTrxn.center_id = center.id
    },
    onSelectBank (bank) {
      this.ippisTrxn.bank = bank.ledger_no
    },
    submitForm: function() {

      axios
        .post(
          `ippis/debit-bank/post`,
          this.ippisTrxn
        )
        .then(res => {
          // console.log(res.data);
          const withdrawal = res.data.withdrawal
          var getUrl = window.location;
          var baseUrl =
            getUrl.protocol +
            "//" +
            getUrl.host +
            "/" +
            // getUrl.pathname.split("/")[1];
            // window.location.href =
            // baseUrl + `/public/members/${this.member.ippis}/savings`;
            getUrl.pathname.split("/")[0];
            // window.location.href = baseUrl + `/members/${this.member.ippis}/savings`;
            window.location.href = baseUrl + `ippis/trxns`;
        })
        .catch(e => {
          // console.log(e);
          if (e.response.status == 422) {
            this.errors = e.response.data.errors;
            // Vue.toasted.error("There are errors");
          }
        });
    }
  },
  created() {
    this.isLoading = true
    
      axios
        .get(
          `ippis/debit-bank/data`,
          this.ippisTrxn
        )
        .then(res => {
          // console.log(res.data);
          this.centers = JSON.parse(res.data.centers)
          this.bankAccounts = res.data.bank_accounts
        })
        .catch(e => {
          // console.log(e);
          if (e.response.status == 422) {
            this.errors = e.response.data.errors;
          }
        });
    this.isLoading = false
  }
};
</script>

<style scoped>
</style>