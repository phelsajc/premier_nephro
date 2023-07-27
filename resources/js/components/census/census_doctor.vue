<template>
  <div class="wrapper">
    <navComponent></navComponent>
    <sidemenuComponent></sidemenuComponent>
    <div class="content-wrapper">
      <section class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <h1>&nbsp;</h1>
            </div>
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item">
                  <a href="#">Home</a>
                </li>
                <li class="breadcrumb-item active">Census</li>
              </ol>
            </div>
          </div>
        </div>
      </section>
      <section class="content">
        <div class="container-fluid">

          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Dcotors Census</h3>

            </div>

            <div class="card-body">

              <form class="user" enctype="multipart/form-data">
                <div class="row">
                  <div class="col-sm-2">
                    <div class="form-group ">
                      <label>From</label>
                      <datepicker name="date" required input-class="dpicker" v-model="filter.fdate"
                        :bootstrap-styling=true></datepicker>
                    </div>
                  </div>
                  <div class="col-sm-2">
                    <div class="form-group ">
                      <label>To</label>
                      <datepicker name="date" required input-class="dpicker" v-model="filter.tdate"
                        :bootstrap-styling=true></datepicker>
                    </div>
                  </div>
                  <div class="col-sm-2">
                    <div class="form-group ">
                      <label>Doctor</label>
                      <select class="form-control" v-model="filter.doctors">
                        <option value="All">All for the month</option>
                        <option v-for="e in doctors_list" :value="e.id">{{ e.name }}</option>
                      </select>
                    </div>
                  </div>
                  <div class="col-sm-2">
                    <div class="form-group">
                      <label>&nbsp;</label> <br>
                      <button type="button" @click="showReport()" class="btn btn-info">
                        Filter
                      </button>
                      <button type="button" @click="exportCsv()" class="btn btn-primary">
                        Export
                      </button>
                    </div>
                  </div>
                </div>

                <dl class="row">
                  <dt class="col-sm-2">Month:</dt>
                  <dd class="col-sm-8">{{ month }}</dd>
                </dl>
                <dl class="row">
                  <dt class="col-sm-2">Doctor:</dt>
                  <dd class="col-sm-8">{{ filter.doctors != null && filter.doctors != 'All' ? getDoctor.name : '' }}</dd>
                </dl>
                <table class="table">
                  <thead>
                    <tr>
                      <th>Patient</th>
                      <th>Date</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="e in results">
                      <td>
                        {{ e.name }}
                      </td>
                      <td>
                        <button type="button" class="btn btn-xs btn-success" style="margin-right:5px;">
                          {{ e.dates }}
                        </button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </form>
            </div>
            <!-- /.card-body -->
          </div>
        </div>
        <phicModal v-if="showModal" @close="showModal = false" :sessionid="getsessionid.toString()"></phicModal>
      </section>
    </div>
    <footerComponent></footerComponent>
  </div>
</template>

<script type="text/javascript">
import Datepicker from 'vuejs-datepicker'
import moment from 'moment';
import { ExportToCsv } from 'export-to-csv';
export default {
  created() {
    if (!User.loggedIn()) {
      this.$router.push({ name: '/' })
    }

    this.checkToken()
    this.getDoctors();
  },
  components: {
    Datepicker,
  },
  data() {
    return {
      export: [],
      showModal: false,
      filter: {
        fdate: '',
        tdate: '',
        doctors: null,
        type: 'BOTH'
      },
      results: [],
      getsessionid: '',
      month: null,
      doctors_list: [],
      token: localStorage.getItem('token'),
    }
  },
  computed: {
    total_sessions() {
      return this.results.reduce((sum, item) => sum + parseFloat(item.sessions), 0);
    },
    getDoctor() {
      return this.doctors_list.find(e => e.id == this.filter.doctors);
    },
    totalAmount() {
      return (this.total_sessions * 150).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",")
    }
  },
  methods: {
    getCompany() {
      axios.get('/api/getCompanies')
        .then(({ data }) => (
          this.companies = data,
          console.log(this.companies)
        ))
        .catch(console.log('error'))
    },
    getPatientInformation() {
      axios.get('/api/getPxInfo/' + this.$route.params.id)
        .then(({ data }) => (this.user_info = data))
        .catch()
    },
    calculateTotal() {
      this.productList.total = this.productList.price * this.productList.qty;
    },
    showReport() {
      const headers = {
        Authorization: "Bearer ".concat(this.token),
      }
      axios.post('/api/census-report', {
        data: this.filter,
      }, {
        headers: headers
      }
      )
        .then(res => {
          this.results = res.data.data;
          this.export = res.data.export;
          /* this.results.forEach(e => {
              this.total_sessions += parseFloat(e.sessions);
          }) */
          this.month = moment(this.filter.date).format('MMMM YYYY')
          Toast.fire({
            icon: 'success',
            title: 'Saved successfully'
          });
        })
        .catch(error => console.log(error))
    },
    checkToken() {
      const headers = {
        Authorization: "Bearer ".concat(this.token),
      }
      axios.get('/api/validate', {
        headers: headers
      }
      )
        .then(res => {

        })
        .catch(error => console.log(error))
    },
    getDoctors() {
      axios.get('/api/getDoctors')
        .then(({ data }) => (this.doctors_list = data))
        .catch()
    },

    getId(id) {
      this.getsessionid = id
    },
    exportCsv() {
      const options = {
        fieldSeparator: ',',
        quoteStrings: '"',
        decimalSeparator: '.',
        showLabels: true,
        showTitle: true,
        title: 'Patient of '+this.getDoctor.name+' for the month of '+this.month,
        useTextFile: false,
        useBom: true,
        useKeysAsHeaders: true,
        filename: 'copay_'+this.getDoctor.name+"_"+this.month,
        // headers: ['Column 1', 'Column 2', etc...] <-- Won't work with useKeysAsHeaders present!
      };
      const csvExporter = new ExportToCsv(options);
      csvExporter.generateCsv(this.export);
    }
  }
}

</script>

<style>
.pull-right {
  float: right !important;
}

.dpicker {
  background-color: white !important;
}</style>