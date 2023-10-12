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
                      <label>ACPN</label>
                      <input type="text" class="form-control" v-model="filter.acpn" />
                    </div>
                  </div>                  
                  <div class="col-sm-2">
                    <div class="form-group">
                      <label>&nbsp;</label> <br>
                      <button type="button" @click="showReport()" class="btn btn-info">
                        Filter
                      </button>
                    </div>
                  </div>
                </div>
                <dl class="row">
                  <dt class="col-sm-2">Total Amount:</dt>
                  <dd class="col-sm-8">{{total_sessions}}</dd>
                </dl>
                <dl class="row">
                  <dt class="col-sm-2">Total Session:</dt>
                  <dd class="col-sm-8">{{ total_number_session}}</dd>
                </dl>
                <table class="table">
                  <thead>
                    <tr>
                      <th>Patient</th>
                      <th>Date</th>
                      <th>Doctor</th>
                      <th>Batch</th>
                      <th></th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="e in results">
                      <td>
                        {{ e.patient }}
                      </td>
                      <td>
                        {{ e.session }}
                      </td>
                      <td>
                        {{ e.doctor }}
                      </td>
                      <td>
                        {{ e.batch }}
                      </td>
                      <td>
                        {{ e.updated }}
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
import { ExportToCsv } from 'export-to-csv';
import api from '../../Helpers/api';

export default {
  created() {
    if (!User.loggedIn()) {
      this.$router.push({ name: '/' })
    }
    this.getBatches();
  },
  components: {
    Datepicker,
  },
  data() {
    return {
      progressStatus: true,
      showModal: false,
      filter: {
        acpn: null,
      },
      results: [],
      //export: [],
      getsessionid: '',
      month: null,
      total_number_session: null,
      total_sessions: null
    }
  },
  ///05292023-06022023 //05152023-05192023
  methods: {
    showReport() {
      api.post('acpn-report-list', this.filter)
        .then(response => {
          this.results = response.data.acpn
          this.total_number_session = response.data.total
          this.total_sessions = response.data.total_amount 
          Toast.fire({
            icon: 'success',
            title: 'Saved successfully'
          });
        }).catch(error => {
         if(error.response.data.message == 'Token has expired'){
          this.$router.push({ name: '/' });
          Toast.fire({
            icon: 'error',
            title: 'Token has expired'
          })
         }
      });
    },
    exportCsv() {
      const options = {
        fieldSeparator: ',',
        quoteStrings: '"',
        decimalSeparator: '.',
        showLabels: true,
        showTitle: true,
        title: 'ACPN REPORT \n ' + moment(this.filter.fdate).format('MMMM DD YYYY') + ' ' + moment(this.filter.tdate).format('MMMM DD YYYY'),
        useTextFile: false,
        useBom: true,
        useKeysAsHeaders: true,
      };
      const csvExporter = new ExportToCsv(options);
      csvExporter.generateCsv(this.export);
    },
    getBatches() {
      axios.get('api/get-batches')
        .then(response => {
          this.batches = response.data
        }).catch(error => {
         if(error.response.data.message == 'Token has expired'){
          this.$router.push({ name: '/' });
          Toast.fire({
            icon: 'error',
            title: 'Token has expired'
          })
         }
      });
    },
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