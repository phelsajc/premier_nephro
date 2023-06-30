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
                <li class="breadcrumb-item active">CoPay</li>
              </ol>
            </div>
          </div>
        </div>
      </section>
      <section class="content">
        <div class="container-fluid">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">CoPay Report</h3>
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
                  <dt class="col-sm-2">Total Session:</dt>
                  <dd class="col-sm-8">{{ total_sessions }}</dd>
                </dl>
                <dl class="row">
                  <dt class="col-sm-2">Total Amount Due:</dt>
                  <dd class="col-sm-8">{{ totalAmount }}</dd>
                </dl>
                <dl class="row">
                  <dt class="col-sm-2">Month:</dt>
                  <dd class="col-sm-8">{{ month }}</dd>
                </dl>
                <dl class="row">
                  <dt class="col-sm-2">Doctor:</dt>
                  <dd class="col-sm-8">{{ filter.doctors != null && filter.doctors != 'All' ? getDoctor.name : '' }}</dd>
                </dl>
                <table v-if="filter.doctors!='All'" class="table">
                  <thead>
                    <tr>
                      <th>Patient</th>
                      <th>Sessions</th>
                      <th>Date</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="e in results">
                      <td>
                        {{ e.name }}
                      </td>
                      <td>
                        {{ e.sessions }}
                      </td>
                      <td>
                        <button type="button" class="btn btn-success btn-xs" style=" margin-right: 5px;"
                          v-for="d in e.datesArr">
                          {{ d }}
                        </button>
                      </td>
                    </tr>
                  </tbody>
                </table>
                
                <table v-else class="table">
                  <thead>
                    <tr>
                      <th>Nephrologist</th>
                      <th># of Sessions</th>
                      <th>Amount</th>
                      <th>Total Amount</th>
                      <th>Less WTX</th>
                      <th>Net</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="e in results">
                      <td>
                        {{ e.name }}
                      </td>
                      <td>
                        {{ e.session }}
                      </td>
                      <td>
                        150
                      </td>
                      <td>
                        {{ e.total_amount }}
                      </td>
                      <td>
                        {{ e.less_wtx }}
                      </td>
                      <td>
                        {{ e.net }}
                      </td>
                    </tr>
                  </tbody>
                </table>
              </form>
            </div>
          </div>


        </div>
      </section>
    </div>
    <footerComponent></footerComponent>
  </div>
</template>

<script type="text/javascript">
import Datepicker from 'vuejs-datepicker'
import moment from 'moment';
import api from '../../Helpers/api';
import { ExportToCsv } from 'export-to-csv';
export default {
  created() {
    if (!User.loggedIn()) {
      this.$router.push({ name: '/' })
    }
    //this.checkToken()
    this.getDoctors();
  },
  components: {
    Datepicker,
  },
  data() {
    return {
      filter: {
        fdate: '',
        tdate: '',
        doctors: null,
        type: 'BOTH'
      },
      results: [],
      export: [],
      month: null,
      doctors_list: [],
      token: localStorage.getItem('token'),
      getMonthTitle: '',
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
      api.get('getCompanies')
        .then(response => {
          this.companies = response.data
        }).catch(error => console.log(error))
    },
    showReport() {
      api.post('copay-report', this.filter)
        .then(response => {
          this.results = response.data.data
          this.export = response.data.export
          this.getMonthTitle = response.data.month
          this.month = moment(this.filter.date).format('MMMM YYYY')
          Toast.fire({
            icon: 'success',
            title: 'Saved successfully'
          });
        })
        .catch(error => console.log(error))
    },
    /* checkToken() {
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
    }, */
    getDoctors() {
      api.get('getDoctors')
        .then(response => {
          this.doctors_list = response.data
        }).catch(error => console.log(error))
    },
    exportCsv() {
      const options = {
        fieldSeparator: ',',
        quoteStrings: '"',
        decimalSeparator: '.',
        showLabels: true,
        showTitle: true,
        title: 'Summary of Nephros(Co - Pay) \n for the month of '+this.getMonthTitle,
        useTextFile: false,
        useBom: true,
        useKeysAsHeaders: true,
        filename: 'copay_'+this.getMonthTitle
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