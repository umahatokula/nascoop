<template>
  <div>

    <loading :active.sync="isLoading" 
    :can-cancel="true" 
    :is-full-page="false"></loading>

    <h4>Journal Entries</h4>
        
    <div class="row">
      <div class="col-12">
        <table class="table table-bordered table-condensed table-striped table-responsive-md">
          <thead>
            <tr>
              <th class="text-left">Posting Date</th>
              <th class="text-left">Value Date</th>
              <th class="text-left">Description</th>
              <th>Account</th>
              <th class="text-right">Debit</th>
              <th class="text-right">Credit</th>
            </tr>
          </thead>
          <tbody v-for="entry in entries" :key="entry.id" style="border-top: #000 2px solid;">
            <tr style="line-height: 0;">
              <td class="text-left">{{ entry.date_time | moment("Do MMMM YYYY") }}</td>
              <td class="text-left">{{ entry.value_date | moment("Do MMMM YYYY") }}</td>
              <td class="text-left"><br>{{ entry.description }}</td>
              <td>{{ entry.ledger_dr ? entry.ledger_dr.account_name : '' }}</td>
              <td class="text-right">{{ entry.amount | number_format }}</td>
              <td class="text-right"><br>&nbsp</td>
            </tr>
            <tr style="line-height: 0;">
              <td class="text-center">&nbsp</td>
              <td class="text-left"><br>&nbsp</td>
              <td class="text-left"><br>&nbsp</td>
              <td>{{ entry.ledger_cr ? entry.ledger_cr.account_name : '' }}</td>
              <td class="text-right">&nbsp</td>
              <td class="text-right"><br>{{ entry.amount | number_format }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    
  </div>
</template>

<script>
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';

export default {
  name: 'JournalEntries',
  components: {
    'loading' : Loading
  },
  data() {
    return {
      entries: [],
      isLoading: false,
      fullPage: true,
    }
  },
  methods: {
    getData() {
      axios
        .get(`api/accounting/journal`)
        .then(res => {
          // console.log(res.data);
          this.entries = res.data.data.entries

          this.isLoading = false
        })
        .catch(e => {
          console.log(e);
        });
    }
  },
  created() {
      this.isLoading = true
      this.getData()
  }
}
</script>

<style>

</style>