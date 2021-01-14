<template>
  <div class="mb-lg-5">

    <loading :active.sync="isLoading" 
    :can-cancel="true" 
    :is-full-page="false"></loading>

    <h4>Assets</h4>
        
    <div class="row">
      <div class="col-12">
        <table class="table table-bordered table-condensed">
          <thead>
            <tr>
              <th>Account</th>
              <th>Amount</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="asset in assets_data" :key="asset.id">
              <td>{{ asset[0].account_name }}</td>
              <td v-if="asset[0].show_total_amount_in_report" class="text-right">{{ asset[1] | number_format }}</td>
              <td v-if="!asset[0].show_total_amount_in_report" class="text-right">&nbsp</td>
            </tr>
          </tbody>
          <tfoot>
            <tr>
              <td colspan="2" class="text-right">{{ assets_total | number_format }}</td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
    
  </div>
</template>

<script>
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';

export default {
  name: 'Assets',
  props: {
    dateFrom: {
      required: true,
    },
    dateTo: {
      required: true,
    },
  },
  components: {
    'loading' : Loading
  },
  data() {
    return {
      assets_data: [],
      assets_total: 0,
      isLoading: false,
      fullPage: true,
    }
  },
  methods: {
    getData() {
      
      this.isLoading = true

      axios
        .get(`api/accounting/asset/balance/${this.dateFrom}/${this.dateTo}`)
        .then(res => {
          // console.log(res.data);
          this.assets_data = res.data.data.account_type_data
          this.assets_total = res.data.data.account_type_total

          this.isLoading = false
        })
        .catch(e => {
          console.log(e);
        });
    }
  },
  created() {
      this.getData()
  }
}
</script>

<style>

</style>