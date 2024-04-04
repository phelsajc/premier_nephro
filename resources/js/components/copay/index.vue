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
                      <button type="button" @click="exportData()" class="btn btn-danger">
                        PDF
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
                  <dd class="col-sm-8">
                    {{
                      filter.doctors != null && filter.doctors != "All"
                        ? getDoctor.name
                        : ""
                    }}
                  </dd>
                </dl>
                <progressBar :getStatus="showProgress"></progressBar>
                <table v-if="filter.doctors != 'All'" class="table">
                  <thead>
                    <tr>
                      <th>Patient</th>
                      <!-- <th>Sessions</th> -->
                      <th>Date</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="e in results">
                      <td>
                        {{ e.name }}
                      </td>
                      <td>
                        {{ e.dates }}
                      </td>
                      <!-- <td>
                        <button type="button" class="btn btn-success btn-xs" style=" margin-right: 5px;"
                          v-for="d in e.datesArr">
                          {{ d }}
                        </button>
                      </td> -->
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
                    <tr v-for="(e, index) in results">
                      <td>
                        <!-- {{index}} -->
                        {{ e.name }}
                      </td>
                      <td>
                        {{ e.session }}
                      </td>
                      <td>150</td>
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
import Datepicker from "vuejs-datepicker";
import jsPDF from "jspdf";
import "jspdf-autotable";
import moment from "moment";
import api from "../../Helpers/api";
import { ExportToCsv } from "export-to-csv";
export default {
  created() {
    if (!User.loggedIn()) {
      this.$router.push({ name: "/" });
    }
    //this.checkToken()
    this.getDoctors();
  },
  components: {
    jsPDF,
    Datepicker,
  },
  data() {
    return {
      filter: {
        fdate: "",
        tdate: "",
        doctors: null,
        type: "BOTH",
      },
      progressStatus: true,
      results: [],
      getTotalSession: 0,
      export: [],
      month: null,
      doctors_list: [],
      token: localStorage.getItem("token"),
      getMonthTitle: "",
    };
  },
  computed: {
    total_sessions() {
      if (this.filter.doctors != "All") {
        return this.getTotalSession;
      } else {
        return this.results.reduce((sum, item) => sum + parseFloat(item.sessions), 0);
      }
    },
    getDoctor() {
      return this.doctors_list.find((e) => e.id == this.filter.doctors);
    },
    totalAmount() {
      return (this.total_sessions * 150).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    },
    showProgress() {
      return this.progressStatus;
    },
  },
  methods: {
    getCompany() {
      api
        .get("getCompanies")
        .then((response) => {
          this.companies = response.data;
        })
        .catch((error) => {
          if (error.response.data.message == "Token has expired") {
            this.$router.push({ name: "/" });
            Toast.fire({
              icon: "error",
              title: "Token has expired",
            });
          }
        });
    },
    showReport() {
      this.filter.fdate = moment.utc(this.filter.fdate).utcOffset("+08:00").format();
      this.filter.tdate = moment.utc(this.filter.tdate).utcOffset("+08:00").format();
      this.progressStatus = false;
      api
        .post("copay-report", this.filter)
        .then((response) => {
          this.getTotalSession = response.data.sessions;
          this.results = response.data.data;
          this.export = response.data.export;
          this.getMonthTitle = response.data.month;
          this.month = moment(this.filter.fdate).format("MMMM YYYY");
          //this.month = moment.utc(this.filter.date).utcOffset('+08:00').format(); // '+08:00' represents the UTC offset for Asia/Manila
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
              title: "Token has expired",
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
        .catch((error) => {
          if (error.response.data.message == "Token has expired") {
            this.$router.push({ name: "/" });
            Toast.fire({
              icon: "error",
              title: "Token has expired",
            });
          }
        });
    },
    exportPDF() {
      const employee = {
        less_wtx: "",
        name: "Total",
        net: "",
        session: this.total_sessions,
        sessions: 0,
        total_amount: "",
      };

      this.results.push(employee);
      api.post("/pdf", { responseType: "blob" }).then((response) => {
        const doc = new jsPDF();
        // Save or open the PDF
        doc.text("Summary of Nephros(Co-Pay)", 20, 12);
        doc.text("for the month of " + this.month, 20, 20);
        doc.text("Prepared by: " + localStorage.getItem("user"), 20, 27);
        doc.autoTable({
          head: [
            [
              "Nephrologist",
              "No. of Sessions",
              "Amount",
              "Total Amount",
              "Less with Tax",
              "Net",
            ],
          ],
          margin: { top: 30 },
          body: this.results.map((user) => [
            user.name,
            user.session,
            user.copay_amount,
            user.total_amount,
            user.less_wtx,
            user.net,
          ]),
        });
        doc.save("copay_report_" + this.getMonthTitle + ".pdf");
      });
    },
    exportData(){
      if (this.filter.doctors != "All") {
        this.exportDr();
      } else {
        this.exportPDF();
      }
    },
    exportDr(){
      const employee = {
        less_wtx: "",
        name: "Total",
        net: "",
        session: this.total_sessions,
        sessions: 0,
        total_amount: "",
      };
      let d = this.getDoctor;
      this.results.push(employee);
      api.post("/pdf", { responseType: "blob" }).then((response) => {
        const doc = new jsPDF();
        // Save or open the PDF
        doc.text("Summary of Co-PAY for "+d.name, 20, 12);
        doc.text("for the month of " + this.month, 20, 20);
        doc.setFontSize(9);
        doc.text("Prepared by: " + localStorage.getItem("user"), 20, 27);
        doc.autoTable({
          head: [
            [
              "Date",
              "Name",
              "Nephrologist",
              "PF",
              "T/C",
              "",
            ],
          ],
          margin: { top: 30 },
          body: this.export.map((user) => [
            user.Date,
            user.Name,
            user.NEPHROLOGIST,
            user.PF,
            user.tc,
            user.cnt,
          ]),
        });
        doc.save("copay_report_" + this.getMonthTitle + ".pdf");
      });
    },
    exportCsv() {
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
        filename: "copay_" + this.getMonthTitle,
        // headers: ['Column 1', 'Column 2', etc...] <-- Won't work with useKeysAsHeaders present!
      };
      const csvExporter = new ExportToCsv(options);
      csvExporter.generateCsv(this.export);
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
