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
                <li class="breadcrumb-item active">PHIC</li>
              </ol>
            </div>
          </div>
        </div>
      </section>
      <section class="content">
        <div class="container-fluid">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">PHIC Report</h3>
            </div>

            <div class="card-body">
              <div class="row">
                <div class="col-md-2">
                  <p>LEGENDS</p>
                </div>
                <div class="col-md-2">
                  <button type="button" class="btn btn-block btn-success btn-xs">
                    PAID
                  </button>
                </div>
                <div class="col-md-2">
                  <button type="button" class="btn btn-block btn-warning btn-xs">
                    PENDING
                  </button>
                </div>
              </div>

              <form class="user" enctype="multipart/form-data">
                <div class="row">
                  <div class="col-sm-2">
                    <div class="form-group">
                      <label>From</label>
                      <datepicker
                        name="date"
                        required
                        input-class="dpicker"
                        v-model="filter.fdate"
                        :bootstrap-styling="true"
                      ></datepicker>
                    </div>
                  </div>
                  <div class="col-sm-2">
                    <div class="form-group">
                      <label>To</label>
                      <datepicker
                        name="date"
                        required
                        input-class="dpicker"
                        v-model="filter.tdate"
                        :bootstrap-styling="true"
                      ></datepicker>
                    </div>
                  </div>
                  <div class="col-sm-2">
                    <div class="form-group">
                      <label>Doctor</label>
                      <select class="form-control" v-model="filter.doctors">
                        <option value="All">All for the month</option>
                        <option value="summary">Summary</option>
                        <option v-for="e in doctors_list" :value="e.id">
                          {{ e.name }}
                        </option>
                      </select>
                    </div>
                  </div>
                  <div class="col-sm-2">
                    <div class="form-group">
                      <label>&nbsp;</label> <br />
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
                  <dd class="col-sm-8">
                    {{ totalAmount.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",") }}
                  </dd>
                </dl>
                <dl class="row">
                  <dt class="col-sm-2">Total PAID:</dt>
                  <dd class="col-sm-8">
                    {{ totalAmountPaid.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",") }}
                  </dd>
                </dl>
                <dl class="row">
                  <dt class="col-sm-2">Balance:</dt>
                  <dd class="col-sm-8">
                    {{ balance.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",") }}
                  </dd>
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
                  <dd class="col-sm-8">
                    {{
                      filter.doctors != null &&
                      filter.doctors != "All" &&
                      filter.doctors != "summary"
                        ? getDoctor.name
                        : ""
                    }}
                  </dd>
                </dl>
                <progressBar :getStatus="showProgress"></progressBar>

                <table v-if="filter.doctors != 'summary'" class="table">
                  <thead>
                    <tr>
                      <th>Patient</th>
                      <th>Sessions</th>
                     <!--  <th>Date</th> -->
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
                        <button
                          type="button"
                          @click="
                            showModal = true;
                            getId(d.id);
                          "
                          data-toggle="tooltip" data-placement="top" :title="d.updatedBy"
                          :class="[
                            'btn',
                            'btn-xs',
                            { 'btn-warning': d.status == 'UNPAID' },
                            { 'btn-success': d.status == 'PAID' },
                          ]"
                          style="margin-right: 5px"
                          v-for="d in e.datesArr"
                        >
                          {{ d.date }}
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
                    <tr v-for="e in resultsSummary">
                      <td>
                        {{ e.name }}
                      </td>
                      <td>
                        {{ e.session }}
                      </td>
                      <td>350</td>
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
        <phicModal
          v-if="showModal"
          @close="closeModal()"
          :sessionid="getsessionid.toString()"
        ></phicModal>
      </section>
    </div>
    <footerComponent></footerComponent>
  </div>
</template>

<script type="text/javascript">
import Datepicker from "vuejs-datepicker";
import moment from "moment";
import { ExportToCsv } from "export-to-csv";
import api from "../../Helpers/api";

export default {
  created() {
    if (!User.loggedIn()) {
      this.$router.push({ name: "/" });
    }
    this.getDoctors();
  },
  components: {
    Datepicker,
  },
  data() {
    return {
      progressStatus: true,
      showModal: false,
      filter: {
        fdate: "",
        tdate: "",
        doctors: "All",
        type: "BOTH",
      },
      results: [],
      resultsSummary: [],
      export: [],
      export_summary: [],
      getsessionid: "",
      month: null,
      doctors_list: [],
      getPaidClaims: 0,
      getTotalPaidClaims: 0,
      getMonthTitle: "",
      token: localStorage.getItem("token"),
    };
  },
  computed: {
    total_sessions() {
      if (this.filter.doctors != "summary") {
        return this.results.reduce((sum, item) => sum + parseFloat(item.sessions), 0);
      } else {
        return this.resultsSummary.reduce(
          (sum, item) => sum + parseFloat(item.sessions),
          0
        );
      }
    },
    getDoctor() {
      return this.doctors_list.find((e) => e.id == this.filter.doctors);
    },
    totalAmount() {
      return this.total_sessions * 350;
    },
    totalAmountPaid() {
      return this.getTotalPaidClaims * 350;
    },
    balance() {
      return this.totalAmount - this.totalAmountPaid;
    },
    unpaid() {
      return this.total_sessions - this.getPaidClaims;
    },
    showProgress() {
      return this.progressStatus;
    },
  },
  methods: {
    closeModal() {
      this.showModal = false;
      this.showReport();
    },
    getCompany() {
      api
        .get("getCompanies")
        .then((response) => {
          this.companies = response.data;
        })
        .catch((error) => console.log(error));
    },
    showReport() {
      if (this.filter.doctors != "summary") {
        this.progressStatus = false;
        api
          .post("phic-report", this.filter)
          .then((response) => {
            this.getTotalPaidClaims = response.data.getPaidClaims;
            this.getPaidClaims = response.data.getPaidClaims;
            this.results = response.data.data;
            this.export = response.data.export;
            this.month = moment(this.filter.date).format("MMMM YYYY");
            Toast.fire({
              icon: "success",
              title: "Saved successfully",
            });
            this.progressStatus = true;
          })
          .catch((error) => {
            if (error.response.data.message == "Token has expired") {
              this.$router.push({ name: "/" });
              Toast.fire({
                icon: "error",
                title: "Token has expired.",
              });
            }
          });
      } else {
        this.showSummaryReport();
      }
    },
    showSummaryReport() {
      this.filter.fdate = moment.utc(this.filter.fdate).utcOffset("+08:00").format();
      this.filter.tdate = moment.utc(this.filter.tdate).utcOffset("+08:00").format();
      this.resultsSummary = [];
      api
        .post("phic-summary-report", this.filter)
        .then((response) => {
          //this.getTotalSession = response.data.sessions;
          this.resultsSummary = response.data.data;
          this.export_summary = response.data.export;
          this.getMonthTitle = response.data.month;
          //  this.month_summary = moment(this.filter.date).format('MMMM YYYY')
          //this.month = moment.utc(this.filter.date).utcOffset('+08:00').format(); // '+08:00' represents the UTC offset for Asia/Manila
          Toast.fire({
            icon: "success",
            title: "Saved successfully",
          });
        })
        .catch((error) => {
          if (error.response.data.message == "Token has expired") {
            this.$router.push({ name: "/" });
            Toast.fire({
              icon: "error",
              title: "Token has expired.",
            });
          }
        });
    },
    getDoctors() {
      api
        .get("getDoctors")
        .then((response) => {
          this.doctors_list = response.data;
        })
        .catch((error) => console.log(error));
    },
    getId(id) {
      this.getsessionid = id;
    },
    exportCsv() {
      if (this.filter.doctors != "summary") {
        const options = {
          fieldSeparator: ",",
          quoteStrings: '"',
          decimalSeparator: ".",
          showLabels: true,
          showTitle: true,
          title: "PHIC",
          useTextFile: false,
          useBom: true,
          useKeysAsHeaders: true,
          filename: "phic_350_" + this.getMonthTitle,
          // headers: ['Column 1', 'Column 2', etc...] <-- Won't work with useKeysAsHeaders present!
        };
        const csvExporter = new ExportToCsv(options);
        csvExporter.generateCsv(this.export);
      } else {
        const options = {
          fieldSeparator: ",",
          quoteStrings: '"',
          decimalSeparator: ".",
          showLabels: true,
          showTitle: true,
          title: "Summary of Nephros(Co - Pay) \n for the month of " + this.getMonthTitle,
          useTextFile: false,
          useBom: true,
          useKeysAsHeaders: true,
          filename: "phic_report",
          // headers: ['Column 1', 'Column 2', etc...] <-- Won't work with useKeysAsHeaders present!
        };
        const csvExporter = new ExportToCsv(options);
        csvExporter.generateCsv(this.export_summary);
      }
    },
  },
};
</script>

<style>
.pull-right {
  float: right !important;
}

.dpicker {
  background-color: white !important;
}
</style>
