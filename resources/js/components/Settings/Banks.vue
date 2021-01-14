<template>
  <div>

    <loading :active.sync="isLoading" 
    :can-cancel="true" 
    :is-full-page="false"></loading>
   
    <div class="row">
      <div class="col-md-12">
        <h6>New Entry</h6>
        <form @submit.prevent="submitForm">
          <div class="form-group row">
            <div class="col-sm-3 mb-1">
              <input v-model="bank.name" type="text" class="form-control" placeholder="Bank Name" />
              <small v-if="errors.name" class="text-danger">{{ errors.name[0] }}</small>
            </div>
            <div class="col-sm-3 mb-1">
              <input v-model="bank.code" type="text" class="form-control" placeholder="Code" />
              <small v-if="errors.code" class="text-danger">{{ errors.code[0] }}</small>
            </div>
            <div class="col-sm-3 mb-1">
              <input v-model="bank.transfer_charge" type="text" class="form-control" placeholder="Transfer Charge" />
              <small v-if="errors.transfer_charge" class="text-danger">{{ errors.transfer_charge[0] }}</small>
            </div>
            <div class="col-sm-3 mb-1">
                <button class="btn btn-primary">Save</button>
            </div>
          </div>
        </form>
        <table class="table table-bordered table-stripped">
          <thead>
            <tr>
              <th class="text-center">Name</th>
              <th class="text-center">Code</th>
              <th class="text-center">Transfer Charges (&#8358;)</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(bank, index) in banks" :key="bank.id">
              <td>
                <span v-if="toEdit != index">{{ bank.name }}</span>
                <input v-if="toEdit == index" v-model="bank.name" type="text" class="form-control" placeholder="Bank Name" />
              </td>
              <td class="text-center">
                <span v-if="toEdit != index">{{ bank.code }}</span>
                <input v-if="toEdit == index" v-model="bank.code" type="text" class="form-control" placeholder="Code" />
              </td>
              <td class="text-right">
                <span v-if="toEdit != index">{{ bank.transfer_charge }}</span>
                <input v-if="toEdit == index" v-model="bank.transfer_charge" type="text" class="form-control" placeholder="Transfer Charge" />
              </td>
              <td class="text-center">
                <span v-if="toEdit != index"><a href="#" @click="editBank(index)">Edit</a></span> &nbsp
                <span v-if="toEdit != index"><a href="#" class="text-danger"  @click="deleteBank(bank.id)">Delete</a></span>
                <span v-if="toEdit == index"><a href="#" class="text-success" @click="editBankSave(index)">Save</a></span>
              </td>
            </tr>
          </tbody>
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
  name: "Banks",
  components: {
    'multiselect' : Multiselect,
    'loading' : Loading
  },
  data() {
    return {
      bank: {
        name: null,
        code: null,
        transfer_charge: 0
      },
      banks: [],
      errors: [],
      toEdit: null
    };
  },
  methods: {
    editBank (index) {
      this.toEdit = index
    },
    deleteBank (id) {
      const confirmation = confirm('Are you sure?')

      if(!confirmation) {
        return
      }
      
      axios
        .get(`settings/charges/bank/delete/${id}`)
        .then(res => {
          // console.log(res.data);
          this.banks = res.data.banks
          this.toEdit = null
        })
        .catch(e => {
          // console.log(e);
          if (e.response.status == 422) {
            this.errors = e.response.data.errors;
            // Vue.toasted.error("There are errors");
          }
        });
    },
    editBankSave (index) {
      const bank = this.banks[index]

      axios
        .post(`settings/charges/bank/edit`, bank)
        .then(res => {
          // console.log(res.data);
          this.banks = res.data.banks
          this.toEdit = null
        })
        .catch(e => {
          // console.log(e);
          if (e.response.status == 422) {
            this.errors = e.response.data.errors;
            // Vue.toasted.error("There are errors");
          }
        });

    },
    submitForm: function() {
      axios
        .post(`settings/charges/bank/save`, this.bank)
        .then(res => {
          // console.log(res.data);
          this.banks = res.data.banks
          this.bank.name = ''
          this.bank.code = ''
          this.bank.transfer_charge = 0
        })
        .catch(e => {
          // console.log(e);
          if (e.response.status == 422) {
            this.errors = e.response.data.errors;
            // Vue.toasted.error("There are errors");
          }
        });
    },
    getData () {
      axios
        .get(`settings/charges`)
        .then(res => {
          // console.log(res.data);
          this.banks = res.data.banks 
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
};
</script>

<style scoped>
</style>