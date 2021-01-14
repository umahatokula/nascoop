<template>
  <div>

    <loading :active.sync="isLoading" 
    :can-cancel="true" 
    :is-full-page="false"></loading>

    <div class="row mb-5">
      <div class="col-12 text-center">
        <h2>Balance Sheet</h2>
        <h6 class="text-center">NASRDA MEMBER MULTIPURPOSE COOPERATIVE SOCIETY LTD</h6>
      </div>
    </div>

    <!-- <div class="row">
      <div class="col-12">

        <form @submit.prevent="submitForm">

          <div class="form-row">
            <div class="form-group col-md-3">
              <label for="dateFrom">From</label>
              <input v-model="filter.dateFrom" type="date" class="form-control" id="dateFrom">
            </div>
            <div class="form-group col-md-3">
              <label for="dateTo">To</label>
              <input v-model="filter.dateTo" type="date" class="form-control" id="dateTo">
            </div>
            <div class="form-group col-md-3">
              <button type="submit" class="btn btn-primary mt-lg-4">Generate Balance Sheet</button>
            </div>
          </div>

        </form>

      </div>
    </div> -->
        
    <div class="row">
      <div class="col-lg-12">
        <Assets :dateFrom="filter.dateFrom" :dateTo="filter.dateTo" ref="assetsComponent"></Assets>
      </div>
      <div class="col-lg-12">
        <Liabilities :dateFrom="filter.dateFrom" :dateTo="filter.dateTo" ref="liabilitiesComponent"></Liabilities>
      </div>
      <div class="col-lg-12">
        <Equity :dateFrom="filter.dateFrom" :dateTo="filter.dateTo" ref="equityComponent"></Equity>
      </div>
    </div>

  </div>
</template>

<script>
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';
import Assets from '../../../components/Accounting/Reports/Assets';
import Liabilities from '../../../components/Accounting/Reports/Liabilities';
import Equity from '../../../components/Accounting/Reports/Equity';


export default {
  name: 'BalanceSheet',
  components: {
    'loading' : Loading,
    Assets,
    Liabilities,
    Equity,
  },
  data() {

    var date = new Date();
    var firstDay = new Date(date.getFullYear(), date.getMonth(), 1);
    var lastDay = new Date(date.getFullYear(), date.getMonth() + 1, 0);

    return {
      filter: {
        month: '',
        year: '',
        dateFrom: null,
        dateTo: null,
        showOption: 'month',
      },
      isLoading: false,
      fullPage: true,
      months: [],
      years: [],
      showOptions: [
        {
          label: 'Month',
          value: 'month',
        },
        {
          label: 'Date',
          value: 'date',
        },
      ],
    }
  },
  methods: {
    onSelectMonth(month) {
      this.filter.month = month.value
    },
    onSelectYear(year) {
      this.filter.year = year.value
    },
    onSelectShowOption(option) {
      this.filter.showOption = option.value
    },
    submitForm(event) {
      console.log(event)
      this.$refs.assetsComponent.getData()
      this.$refs.liabilitiesComponent.getData()
      this.$refs.equityComponent.getData()
    },
    getData () {

      this.isLoading = true
      
      axios
        .get(`api/accounting/balance-sheet`)
        .then(res => {
          // console.log(res.data);member

          this.months = res.data.data.months
          this.years = res.data.data.years

          this.isLoading = false
        })
        .catch(e => {
          console.log(e);
        });
    },
  },
  created() {
      this.getData()
  }
}
</script>

<style>

</style>