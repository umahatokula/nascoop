<template>
  <div>

    <loading :active.sync="isLoading" 
    :can-cancel="true" 
    :is-full-page="false"></loading>
       
    <div class="row">
      <div class="col-md-6">
        <div class="card text-white bg-info">
          <div class="card-body">
            <h5 class="card-title">STL Balance: &#8358; {{ lastLoan ? lastLoan.bal : 0 | number_format }}</h5>
          </div>
        </div>
        <form @submit.prevent="submitForm">
          <div class="form-group row">
            <label for="ippis" class="col-sm-3 col-form-label">IPPIS</label>
            <div class="col-sm-9">
              <input v-model="loan.ippis" type="text" class="form-control" disabled />
              <small v-if="errors.ippis" class="text-danger">{{ errors.ippis[0] }}</small>
            </div>
          </div>
          <div class="form-group row">
            <label for="pf" class="col-sm-3 col-form-label">Loan Date</label>
            <div class="col-sm-9">
              <input v-model="loan.loan_date" type="date" class="form-control" />
              <small v-if="errors.loan_date" class="text-danger">{{ errors.loan_date[0] }}</small>
            </div>
          </div>
          <div class="form-group row">
            <label for="pf" class="col-sm-3 col-form-label">Description</label>
            <div class="col-sm-9">
              <input v-model="loan.ref" type="text" class="form-control" />
              <small v-if="errors.ref" class="text-danger">{{ errors.ref[0] }}</small>
            </div>
          </div>
          <div class="form-group row">
            <label for="email" class="col-sm-3 col-form-label">Duration</label>
            <div class="col-sm-9">
              <!-- <select @change="calMaxLoan($event)" v-model="loan.no_of_months" class="form-control">
                <option v-for="(period, index) in periods" v-bind:value="period.number_of_months" :key="index">
                  {{period.duration}}
                </option>
              </select> -->
              <v-select label="duration" :options="periods" @input="calMaxLoan($event)"></v-select>
              <small v-if="errors.no_of_months" class="text-danger">{{ errors.no_of_months[0] }}</small>
            </div>
          </div>
          <div class="form-group row">
            <label for="email" class="col-sm-3 col-form-label">Amount</label>
            <div class="col-sm-9">
              <input v-model="loan.total_amount" type="text" class="form-control" />
              <small class="float-right">Max Loan Amt: &#8358; {{max_loan_amount|number_format}}</small>
              <small v-if="errors.total_amount" class="text-danger">{{ errors.total_amount[0] }}</small>
            </div>
          </div>
          <div class="form-group row">
            <label for="email" class="col-sm-3 col-form-label">Adjustment</label>
            <div class="col-sm-9">
              <input disabled v-model="loan.adjustment" type="text" class="form-control" />
            </div>
          </div>
          <div class="form-group row">
            <label for="email" class="col-sm-3 col-form-label">Processing Fee</label>
            <div class="col-sm-9">
              <input disabled v-model="loan.processing_fee" type="text" class="form-control" />
            </div>
          </div>
          <div class="form-group row">
            <label for="email" class="col-sm-3 col-form-label">Interest</label>
            <div class="col-sm-9">
              <input disabled v-model="loan.interest" type="text" class="form-control" />
              <small class="float-right">Interest: {{ calInterest }}%</small>
            </div>
          </div>
          <div class="form-group row">
            <label for="email" class="col-sm-3 col-form-label">Transfer To</label>
            <div class="col-sm-9">
              <v-select label="name" :options="banks" @input="onSelectBank($event)"></v-select>
              <small class="float-right">Transfer Charge: &#8358;{{ loan.bank_charges }}</small>
              <small v-if="errors.bank_charges" class="text-danger">{{ errors.bank_charges[0] }}</small>
            </div>
          </div>
          <div class="form-group row">
            <label for="email" class="col-sm-3 col-form-label">NET PAYMENT</label>
            <div class="col-sm-9">
              <b>{{netPayment | number_format}}</b>
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
      <div class="col-md-6">
        <h6>Repayment Details</h6>
        <table class="table table-bordered table-striped">
          <thead>
            <tr>
              <td class="text-center">Month</td>
              <td class="text-right">Amount</td>
            </tr>
          </thead>
          <tr v-for="(item, index) in repayments" :key="index">
            <td class="text-center">{{ item.month }}</td>
            <td class="text-right">{{ item.amount }}</td>
          </tr>
        </table>
      </div>
    </div>
  </div>
</template>

<script>
import Multiselect from 'vue-multiselect'
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';

export default {
  name: "NewShortTermLoan",
  props: {
    member: {
      required: true
    }
  },
  components: {
    'multiselect' : Multiselect,
    'loading' : Loading
  },
  data() {
    return {
      loan: {
        ref: null,
        loan_date: null,
        ippis: null,
        no_of_months: null,
        total_amount: null,
        adjustment: null,
        processing_fee: null,
        bank_charges: null,
        interest: null,
        interest_percentage: null,
      },
      errors: [],
      savings: null,
      periods: [],
      max_loan_amount: 0,
      lastLoan: null,
      banks: [],
    };
  },
  computed: {
    calInterest () {
      if (this.loan.total_amount) {
        this.loan.interest = this.loan.total_amount * (this.loan.interest_percentage / 100)
        return this.loan.interest_percentage
      }
    },
    netPayment () {
      return this.loan.total_amount - (this.loan.adjustment + this.loan.processing_fee + this.loan.bank_charges + this.loan.interest)
    },
    repayments: function() {
      let amt = this.loan.total_amount / this.loan.no_of_months;

      let repayArray = [];
      for (let i = 0; i < this.loan.no_of_months; i++) {
        repayArray.push({ month: i + 1, amount: amt });
      }

      return repayArray;
    }
  },
  methods: {
    onSelectBank (bank) {
      this.loan.bank_charges = bank.transfer_charge
    },
    calMaxLoan: function(period) {
      // console.log(period)

      this.max_loan_amount = period.determinant_factor

      this.loan.no_of_months = period.number_of_months

      this.loan.interest_percentage = period.interest
    },
    submitForm: function() {

      if (parseInt(this.loan.total_amount) > parseInt(this.max_loan_amount)) {
        alert('Loan amount is more than Maximum Allowed')
        return;
      }

      if ((this.loan.total_amount - (this.loan.adjustment + this.loan.processing_fee + this.loan.bank_charges + this.loan.interest)) <= 0) {
          alert('Loan amount must be greater than zero.')
          return;
      }
      
      const confirmation = confirm("Are you sure?");

      if (!confirmation) {
        return;
      }

      axios
        .post(`members/post-new-short-loan/${this.member.ippis}`, this.loan)
        .then(res => {
          // console.log(res.data);
          const short_term_loan = res.data.short_term_loan
          var getUrl = window.location;
          var baseUrl =
            getUrl.protocol +
            "//" +
            getUrl.host +
            "/" +
            // getUrl.pathname.split("/")[1];
            // window.location.href = baseUrl + `/public/members/${this.member.ippis}/short-term`;
            getUrl.pathname.split("/")[0];
            window.location.href = baseUrl + `members/${short_term_loan.id}/short-term/payment-voucher`;
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
    this.loan.ippis = this.member.ippis;

    axios
      .get(`members/${this.member.ippis}/new-short-loan`, this.loan)
      .then(res => {
        // console.log(res.data);
        // this.max_loan_amount     = res.data.maxLoanAmount
        this.savings             = res.data.savings
        this.periods             = res.data.periods
        this.banks               = res.data.banks
        this.loan.processing_fee = res.data.processingFee.amount
        this.loan.adjustment     = res.data.lastLoan ? res.data.lastLoan.bal : 0
        this.lastLoan            = res.data.lastLoan
        this.isLoading           = false
      })
      .catch(e => {
        console.log(e);
      });
  }
};
</script>

<style scoped>
</style>