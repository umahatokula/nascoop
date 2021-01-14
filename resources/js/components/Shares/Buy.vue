<template>
  <div>

    <loading :active.sync="isLoading" 
    :can-cancel="true" 
    :is-full-page="false"></loading>

    <div v-if="member.savings_locked">
      <p>To proceed, process pending transactions for this member.</p>
    </div>  
        
    <div class="row" v-if="!member.savings_locked">
      <div class="col-12">

        <form @submit.prevent="submitForm">

          <div class="form-group row">
            <label for="ippis" class="col-sm-3 col-form-label">IPPIS</label>
            <div class="col-sm-9">
              <input v-model="shares.ippis" type="text" class="form-control" disabled />
              <small v-if="errors.ippis" class="text-danger">{{ errors.ippis[0] }}</small>
            </div>
          </div>

          <div class="form-group row">
            <label for="pf" class="col-sm-3 col-form-label">Date Bought</label>
            <div class="col-sm-9">
              <input v-model="shares.date_bought" type="date" class="form-control" />
              <small v-if="errors.date_bought" class="text-danger">{{ errors.date_bought[0] }}</small>
            </div>
          </div>

          <div class="form-group row">
            <label for="email" class="col-sm-3 col-form-label">Payment Method</label>
            <div class="col-sm-9">
              <select v-model="shares.payment_method" class="form-control">
                <option v-for="(m, index) in payment_methods" v-bind:value="m.key" :key="index">
                  {{m.value}}
                </option>
              </select>
              <small v-if="errors.shares_mode" class="text-danger">{{ errors.shares_mode[0] }}</small>
            </div>
          </div>

          <div class="form-group row">
            <label for="email" class="col-sm-3 col-form-label">Amount</label>
            <div class="col-sm-9">
              <input v-model="shares.amount" type="text" class="form-control" />
              <small class="float-right">Max deductible from savings: &#8358; {{ max_deductable_savings_amount >= 0 ? max_deductable_savings_amount : 0.00  | number_format }}</small>
              <small v-if="errors.amount" class="text-danger">{{ errors.amount[0] }}</small>
            </div>
          </div>

          <div class="form-group row">
            <label for="email" class="col-sm-3 col-form-label">Units</label>
            <div class="col-sm-9">
              <!-- <input v-model="shares.units" type="text" class="form-control" readonly /> -->
              <p>{{amountToUnits}}</p>
              <small v-if="errors.units" class="text-danger">{{ errors.units[0] }}</small>
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
  name: 'BuyShares',
  props: {
    member: {
      required: true
    }
  },
  components: {
    'loading' : Loading
  },
  data() {
    return {
      shares: {
          ippis: null,
          units: null,
          amount: null,
          payment_method: null,
          date_bought: null,
          rate_when_bought: null,
      },
      settings: null,
      errors: [],
      payment_methods: [],
      max_deductable_savings_amount: 0,
      last_long_term_payment: 0,
      last_monthly_saving: 0,
      isLoading: false,
      fullPage: true,
    }
  },
  computed: {
    amountToUnits () {
      if (this.shares.amount > 0) {
        this.shares.units = this.shares.amount / this.settings.rate
      }
      return this.shares.units
    },
  },
  methods: {
    submitForm() {

      if (this.shares.payment_method == 'savings') {
        if (this.shares.amount > this.max_deductable_savings_amount) {
          alert('Amount is more than Maximum Allowed')
          return;
        }
      }

      const confirmation = confirm("Are you sure?");

      if (!confirmation) {
        return;
      }

      axios
      .post('shares/buy/post', this.shares)
      .then(res => {
        // console.log(res)
        var getUrl = window.location;
        var baseUrl =  getUrl.protocol + "//" + getUrl.host + "/" + getUrl.pathname.split("/")[0];
            window.location.href = baseUrl + `shares/${this.member.ippis}/show`;
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
      this.shares.ippis = this.member.ippis
      this.isLoading = true

      axios
        .get(`shares/${this.member.ippis}/buy`)
        .then(res => {
          // console.log(res.data);member
          this.payment_methods = res.data.payment_methods
          this.shares.rate_when_bought = res.data.settings.rate
          this.settings = res.data.settings

          this.last_long_term_payment = res.data.last_long_term_payment
            ? res.data.last_long_term_payment
            : 0;
          this.last_monthly_saving = res.data.last_monthly_saving;

          if (this.last_long_term_payment == 0) {
            this.max_deductable_savings_amount = this.last_monthly_saving.bal;
          } else {
            if(this.last_long_term_payment.long_term_loan.no_of_months == 36) {
              this.max_deductable_savings_amount = this.last_monthly_saving.bal - this.last_long_term_payment.bal / 3;
            } else {
              this.max_deductable_savings_amount = this.last_monthly_saving.bal - this.last_long_term_payment.bal / 2;
            }
            
          }
          
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