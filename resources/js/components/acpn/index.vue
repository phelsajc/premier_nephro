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
                <li class="breadcrumb-item active">ACPN</li>
              </ol>
            </div>
          </div>
        </div>
      </section>
      <section class="content">
        <div class="container-fluid">

          <div class="card">
            <div class="card-header">
              <h3 class="card-title">ACPN Report</h3>
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
                    <div class="form-group">
                      <label>Batch</label>
                      <input type="text" class="form-control"  v-model="filter.batch">
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

                <!-- <dl class="row">
                  <dt class="col-sm-2">Total Session:</dt>
                  <dd class="col-sm-8">{{ total_sessions }}</dd>
                </dl> -->
                <dl class="row">
                  <dt class="col-sm-2">Total Amount:</dt>
                  <dd class="col-sm-8">{{ totalAmount.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",") }}</dd>
                </dl>
                <!-- <dl class="row">
                  <dt class="col-sm-2">Total PAID:</dt>
                  <dd class="col-sm-8">{{ totalAmountPaid.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",") }}</dd>
                </dl>
                <dl class="row">
                  <dt class="col-sm-2">Balance:</dt>
                  <dd class="col-sm-8">{{ balance.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",") }}</dd>
                </dl>
                <dl class="row">
                  <dt class="col-sm-2">Total PAID Session:</dt>
                  <dd class="col-sm-8">{{ getPaidClaims }}</dd>
                </dl>
                <dl class="row">
                  <dt class="col-sm-2">Total UnpAID Session:</dt>
                  <dd class="col-sm-8">{{ unpaid }}</dd>
                </dl>
                <dl class="row">
                  <dt class="col-sm-2">Doctor:</dt>
                  <dd class="col-sm-8">{{ filter.doctors != null && filter.doctors != 'All' ? getDoctor.name : '' }}</dd>
                </dl> -->
                <table class="table">
                  <thead>
                    <tr>
                      <th>Patient</th>
                      <th>Paid Sessions</th>
                      <th>Date</th>
                      <th>PHIC NEPHRO 350</th>
                      <th>PHIC Sharing 2250</th>
                      <th>PNCSI Sharing(25%)</th>
                      <!-- <th>Remarks</th> -->
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
                        <button type="button"
                          class="btn btn-success"
                          style="margin-right:5px;" v-for="d in e.datesArr">
                          {{ d.date }}
                        </button>
                      </td>
                      <td>
                        {{ e.total }}
                      </td>
                      <td>
                        {{ e.phic25 }}
                      </td>
                      <td>
                        {{ e.phic25tax }}
                      </td>
                      <!-- <td>
                        {{ e.remakrs }}
                      </td> -->
                    </tr>
                  </tbody>
                </table>
              </form>
            </div>
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
import api from '../../Helpers/api';

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
      showModal: false,
      filter: {
        fdate: '',
        tdate: '',
        doctors: null,
        type: 'BOTH'
      },
      results: [],
      export: [],
      getsessionid: '',
      month: null,
      doctors_list: [],
      getPaidClaims: 0,
      getTotalPaidClaims: 0,
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
      return (this.total_sessions * 350)
    },
    totalAmountPaid() {
      return (this.getTotalPaidClaims * 350)
    },
    balance() {
      return (this.totalAmount - this.totalAmountPaid)
    },
    unpaid() {
      return this.total_sessions - this.getPaidClaims
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
      api.post('acpn-report', this.filter)
        .then(response => {
          this.getTotalPaidClaims = response.data.getPaidClaims
          this.getPaidClaims = response.data.getPaidClaims
          this.results = response.data.data
          this.export = response.data.export
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
        title: 'ACPN REPORT \n '+ moment(this.filter.fdate).format('MMMM DD YYYY')+ ' '+ moment(this.filter.tdate).format('MMMM DD YYYY'),
        useTextFile: false,
        useBom: true,
        useKeysAsHeaders: true,
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
}
</style>