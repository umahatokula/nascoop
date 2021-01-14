<template>
  <div>

    <loading :active.sync="isLoading" 
    :can-cancel="true" 
    :is-full-page="false"></loading>

    <hr>

    <div class="row">
      <div class="col-12">
        <component v-bind:is="component" ></component>
      </div>
    </div>


  </div>
</template>

<script>
import COA from '../../components/Accounting/COA';
import LinkAccounts from '../../components/Accounting/LinkAccounts';
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';
import BalanceSheet from '../../components/Accounting/Reports/BalanceSheet';

export default {
  name: 'AccountingIndex',
  components: {
    'COA'          : COA,
    'link_accounts': LinkAccounts,
    'loading'      : Loading,
    BalanceSheet,
  },
  data() {
    return {
      component: 'COA',
      isLoading: false,
      fullPage: true,
    }
  },
  methods: {
    setComponent(s) {
      this.component = s
    },
  },
  created() {

  }
}
</script>

<style>

</style>