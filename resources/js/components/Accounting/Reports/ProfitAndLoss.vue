<template>
  <div>

    <loading :active.sync="isLoading" 
    :can-cancel="true" 
    :is-full-page="false"></loading>

    <div class="row mb-3 mb-lg-5">
      <div class="col-12 text-center">
        <h2>Profit & Loss Statement</h2>
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
        <Income :dateFrom="filter.dateFrom" :dateTo="filter.dateTo" ref="incomeComponent"></Income>
      </div>
      <div class="col-lg-12">
        <Expenses :dateFrom="filter.dateFrom" :dateTo="filter.dateTo" ref="expensesComponent"></Expenses>
      </div>
    </div>
    
  </div>
</template>

<script>
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';
import Income from '../../../components/Accounting/Reports/Income';
import Expenses from '../../../components/Accounting/Reports/Expenses';

export default {
  name: 'ProfitAndLoss',
  components: {
    'loading' : Loading,
    Income,
    Expenses,
  },
  data() {
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
      this.$refs.incomeComponent.getData()
      this.$refs.expensesComponent.getData()
    },
  },
}
</script>

<style>

</style>