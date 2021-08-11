<template>
  <div>

    <loading :active.sync="isLoading" 
    :can-cancel="true" 
    :is-full-page="false"></loading>
        
    <div class="row">
      <div class="col-12">

        <form @submit.prevent="submitForm">

          <!-- <div class="row my-2">
            <div class="col-md-6">Commodity Loans</div>
            <div class="col-md-6 text-right">
              <a class="btn btn-success" @click.prevent="addCommDuration" href="#"><i class="dripicons-plus"></i>Add</a>
            </div>
          </div> -->
          <table class="table table-bordered table-responsive table-striped">
            <thead>
              <th>Duration</th>
              <th>Number of Months</th>
              <th>Interest</th>
              <th>Action</th>
            </thead>
            <tbody>
              <tr v-for="(comm, index) in durations.comm" :key="index">
                <td>
                  <input v-model="comm.duration" type="text" class="form-control" />
                  <small v-if="errors.comm" class="text-danger">{{ errors.ippis[0] }}</small>
                </td>
                <td>
                  <input v-model="comm.number_of_months" type="text" class="form-control" readonly />
                  <small v-if="errors.comm" class="text-danger">{{ errors.ippis[0] }}</small>
                </td>
                <td>
                  <input v-model="comm.interest" type="text" class="form-control" />
                  <small v-if="errors.comm" class="text-danger">{{ errors.ippis[0] }}</small>
                </td>
                <td class="text-center"><a class="text-danger" href="#" @click="removeCommDuration(index)"><i class="dripicons-trash" ></i></a></td>
              </tr>
            </tbody>
          </table>

          <div class="form-group row">
            <label for="coop_no" class="col-sm-3 col-form-label">&nbsp</label>
            <div class="col-sm-9 text-right">
              <button class="btn btn-primary">Save</button>
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
  name: 'CommLoansDurations',
  components: {
    'loading' : Loading
  },
  data() {
    return {
      durations: {
          comm: [],
          code: 'comm',
      },
      errors: [],
      isLoading: false,
      fullPage: true,
    }
  },
  methods: {
    addCommDuration() {
      // console.log('add comm')
      const newComm = {
        code: 'comm',
        duration: null,
        number_of_months: null,
        interest: null,
      }
      this.durations.comm.push(newComm)
    },
    removeCommDuration(index) {
      this.durations.comm.splice(index, 1)
    },
    submitForm() {

      const confirmation = confirm("Are you sure?");

      if (!confirmation) {
        return;
      }

      axios
      .post('loans/settings/post', this.durations)
      .then(res => {
        // console.log(res)
        var getUrl = window.location;
        var baseUrl =  getUrl.protocol + "//" + getUrl.host + "/" + getUrl.pathname.split("/")[0];
            // window.location.href = baseUrl + `/loans/settings`;
      })
      .catch(e => {
        // console.log(e)
        if (e.response.status == 422) {
          this.errors = e.response.data.errors;
        }
      })
    }
  },
  created() {
      this.isLoading = true

      axios
        .get(`loans/settings`)
        .then(res => {
          console.log(res.data)

          res.data.durations.forEach(duration => {
            if(duration.code == 'comm') {
              this.durations.comm.push(duration)
            }
          });

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