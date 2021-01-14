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
        <h6>withdrawal Details</h6>
        <form @submit.prevent="submitForm">
          <div class="form-group row">
            <label for="ippis" class="col-sm-3 col-form-label">IPPIS</label>
            <div class="col-sm-9">
              <input v-model="withdrawal.ippis" type="text" class="form-control" disabled />
              <small v-if="errors.ippis" class="text-danger">{{ errors.ippis[0] }}</small>
            </div>
          </div>
          <div class="form-group row">
            <label for="pf" class="col-sm-3 col-form-label">Withdrawal Date</label>
            <div class="col-sm-9">
              <input v-model="withdrawal.withdrawal_date" type="date" class="form-control" />
              <small v-if="errors.withdrawal_date" class="text-danger">{{ errors.withdrawal_date[0] }}</small>
            </div>
          </div>
          <div class="form-group row">
            <label for="pf" class="col-sm-3 col-form-label">Description</label>
            <div class="col-sm-9">
              <input v-model="withdrawal.ref" type="text" class="form-control" />
              <small v-if="errors.ref" class="text-danger">{{ errors.ref[0] }}</small>
            </div>
          </div>

          <div class="form-group row">
            <label for="email" class="col-sm-3 col-form-label">Amount</label>
            <div class="col-sm-9">
              <input
                v-model="withdrawal.total_amount"
                type="text"
                class="form-control"
                :max="max_deductable_savings_amount"
              />
              <small
                class="float-right"
              >Max deductible from savings: &#8358; {{ max_deductable_savings_amount >= 0 ? max_deductable_savings_amount : 0.00  | number_format }}</small>
              <small v-if="errors.total_amount" class="text-danger">{{ errors.total_amount[0] }}</small>
            </div>
          </div>

          <div class="form-group row">
            <label for="email" class="col-sm-3 col-form-label">Withdrawal or Refund</label>
            <div class="col-sm-9 pt-3">
              <input type="radio" id="one" value="1" v-model="withdrawal.is_withdrawal">
              <label for="one">Withdrawal</label>

              <input type="radio" id="two" value="2" v-model="withdrawal.is_withdrawal">
              <label for="two">Refund</label>
              <br>
              <small v-if="errors.total_amount" class="text-danger">{{ errors.is_withdrawal[0] }}</small>
            </div>
          </div>
          <div class="form-group row custom-control custom-switch" v-if="withdrawal.is_withdrawal == 1">
            <div class="col-sm-9">
              <input v-model="withdrawal.apply_fee" type="checkbox" class="custom-control-input" id="customSwitch1">
            <label class="custom-control-label" for="customSwitch1">Apply withdrawal charge</label>
            </div>
          </div>
          <div class="form-group row" v-if="withdrawal.is_withdrawal == 1">
            <label for="email" class="col-sm-3 col-form-label">Processing Fee</label>
            <div class="col-sm-9">
              <input disabled v-model="withdrawal.processing_fee" type="text" class="form-control" />
            </div>
          </div>
          <div class="form-group row" v-if="withdrawal.is_withdrawal == 1">
            <label for="email" class="col-sm-3 col-form-label">Interest</label>
            <div class="col-sm-9">
              <input disabled v-model="withdrawal.interest" type="text" class="form-control" />
              <small class="float-right">Interest: {{ calInterest }}%</small>
            </div>
          </div>
          <div class="form-group row" v-if="withdrawal.is_withdrawal == 1">
            <label for="email" class="col-sm-3 col-form-label">Transfer To</label>
            <div class="col-sm-9">
              <v-select label="name" :options="banks" @input="onSelectBank($event)"></v-select>
              <small class="float-right">Transfer Charge: &#8358;{{ withdrawal.bank_charges }}</small>
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
    </div>
  </div>
</template>

<script>
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';

export default {
  name: "Withdrawal",
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
      withdrawal: {
        ref: null,
        withdrawal_date: null,
        ippis: null,
        total_amount: null,
        is_withdrawal: null,
        processing_fee: null,
        bank_charges: null,
        interest: null,
        interest_percentage: null,
        apply_fee: true,
        perform_12_months_check: true,
      },
      banks: '',
      errors: [],
      max_deductable_savings_amount: 0,
      last_long_term_payment: 0,
      last_monthly_saving: 0,
      interest_percentage_charge: 0,
    };
  },
  computed: {
    calInterest () {
      if (this.withdrawal.apply_fee) {
        this.withdrawal.interest_percentage = this.interest_percentage_charge
        if (this.withdrawal.total_amount) {
          this.withdrawal.interest = this.withdrawal.total_amount * (this.withdrawal.interest_percentage / 100)
          return this.withdrawal.interest_percentage
        }
      } else {
        this.withdrawal.interest = 0
        this.withdrawal.interest_percentage = 0

        return this.withdrawal.interest_percentage
      }
    },
    netPayment () {
      if (this.withdrawal.is_withdrawal == 1) {
        return this.withdrawal.total_amount - (this.withdrawal.processing_fee + this.withdrawal.bank_charges + this.withdrawal.interest)
      } 
      if (this.withdrawal.is_withdrawal == 2) {
        return this.withdrawal.total_amount
      }
      
    }
  },
  methods: {
    onSelectBank (bank) {
      this.withdrawal.bank_charges = bank.transfer_charge
    },
    submitForm: function() {

      if (this.withdrawal.total_amount > this.max_deductable_savings_amount) {
        alert("Amount exceeds Maximum Allowed from savings");
        return;
      }

      const confirmation = confirm("Are you sure?");

      if (!confirmation) {
        return;
      }

      axios
        .post(
          `members/post-savings-withdrawal/${this.member.ippis}`,
          this.withdrawal
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
            window.location.href = baseUrl + `members/${withdrawal.id}/withdrawal/payment-voucher`;
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
    if (!this.member.savings_locked) {
      this.isLoading = true
    }
    this.withdrawal.ippis = this.member.ippis;
    
    axios
      .get(`members/${this.member.ippis}/savings-withdrawal`, this.withdrawal)
      .then(res => {
        // console.log(res.data);
        this.interest_percentage_charge = res.data.interest_percentage
        this.withdrawal.interest_percentage = res.data.interest_percentage
        this.banks = res.data.banks       
        this.withdrawal.processing_fee = res.data.processingFee.amount

        this.last_long_term_payment = res.data.last_long_term_payment
          ? res.data.last_long_term_payment
          : 0;
        this.last_monthly_saving = res.data.last_monthly_saving;

        if (this.last_long_term_payment.bal == 0) {
          this.max_deductable_savings_amount = this.last_monthly_saving.bal;
        } else {

          // this.max_deductable_savings_amount = this.last_monthly_saving.bal;

          if(this.last_long_term_payment.long_term_loan.no_of_months == 36) {
            this.max_deductable_savings_amount = this.last_monthly_saving.bal - this.last_long_term_payment.bal / 3;
          } else {
            this.max_deductable_savings_amount = this.last_monthly_saving.bal - this.last_long_term_payment.bal / 2;
          }
          
        }
      })
      .catch(e => {
        console.log(e);
      });
        
      this.isLoading = false
  }
};
</script>

<style scoped>
</style>