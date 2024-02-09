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
                      <label>Batch</label>
                      <select name="" id="" class="form-control" v-model="filter.batch">
                        <option v-for="e in batches" :value="e.batch">
                          {{ e.batch }}
                        </option>
                      </select>
                    </div>
                  </div>
                  <div class="col-sm-2">
                    <div class="form-group">
                      <label>Doctor</label>
                      <select class="form-control" v-model="filter.doctors">
                        <option value="All">All</option>
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
                      <!-- <button type="button" @click="exportCsv()" class="btn btn-success">
                        Excel
                      </button> -->
                      <!-- <button type="button" @click="exportPDF()" class="btn btn-danger">
                        PHIC 350
                      </button>
                      <button type="button" @click="exportPDF()" class="btn btn-danger">
                        PHIC 2250
                      </button> -->
                      <div class="btn-group" role="group">
                        <button
                          id="btnGroupDrop1"
                          type="button"
                          class="btn btn-primary dropdown-toggle"
                          data-toggle="dropdown"
                          aria-haspopup="true"
                          aria-expanded="false"
                        >
                          Export
                        </button>
                        <div class="dropdown-menu" aria-labelledby="btnGroupDrop1">
                          <a class="dropdown-item" href="#" @click="exportPDF()">PHIC 350</a>
                          <a class="dropdown-item" href="#" @click="exportSharingPDF()">PHIC 2250</a>
                          <a class="dropdown-item" href="#" @click="exportByDctor()">By Doctor</a>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <dl class="row">
                  <dt class="col-sm-2">Total Amount:</dt>
                  <dd class="col-sm-8">
                    {{ totalAmount.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",") }}
                  </dd>
                </dl>
                <dl class="row">
                  <dt class="col-sm-2">Total Session:</dt>
                  <dd class="col-sm-8">
                    {{ total_sessions.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",") }}
                  </dd>
                </dl>
                <dl class="row">
                  <dt class="col-sm-2">Doctor:</dt>
                  <dd class="col-sm-8">
                    {{ filter.doctors != "All" ? getDoctorName : "" }}
                  </dd>
                </dl>
                <progressBar :getStatus="showProgress"></progressBar>
                <table class="table">
                  <thead>
                    <tr>
                      <th>Patient</th>
                      <th>Paid Sessions</th>
                      <th>Date</th>
                      <th>PHIC NEPHRO 350</th>
                      <!-- <th>PHIC Sharing 2250</th>
                      <th>PNCSI Sharing(25%)</th> -->
                      <th>ACPN No.</th>
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
                          data-toggle="tooltip"
                          data-placement="top"
                          :title="e.update_by"
                          class="btn btn-xs btn-success"
                          style="margin-right: 5px"
                          v-for="d in e.datesArr"
                        >
                          {{ d.date }} {{ d.name }}
                        </button>
                      </td>
                      <td>
                        {{ e.total }}
                      </td>
                      <!-- <td>
                        {{ e.phic25 }}
                      </td>
                      <td>
                        {{ e.phic25tax }}
                      </td> -->
                      <td>
                        {{ e.acpn }}
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
        <phicModal
          v-if="showModal"
          @close="showModal = false"
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
import jsPDF from "jspdf";
import "jspdf-autotable";

export default {
  created() {
    if (!User.loggedIn()) {
      this.$router.push({ name: "/" });
    }
    //this.checkToken()
    this.getBatches();
    this.getDoctors();
  },
  components: {
    Datepicker,
    jsPDF,
    Datepicker,
  },
  data() {
    return {
      totaslNet: 0,
      batches: [],
      pdf: [],
      pdfSharing: [],
      progressStatus: true,
      showModal: false,
      filter: {
        fdate: "",
        tdate: "",
        doctors: null,
        type: "BOTH",
      },
      results: [],
      export: [],
      getsessionid: "",
      month: null,
      doctors_list: [],
      getPaidClaims: 0,
      getTotalPaidClaims: 0,
      token: localStorage.getItem("token"),
      getDoctorName: null,
    };
  },
  computed: {
    total_sessions() {
      return this.results.reduce((sum, item) => sum + parseFloat(item.sessions), 0);
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
    getCompany() {
      api
        .get("getCompanies")
        .then((response) => {
          this.companies = response.data;
        })
        .catch((error) => console.log(error));
    },
    showReport() {
      if (this.filter.doctors != "All") {
        this.getDoctorName = this.getDoctor.name;
      }
      this.progressStatus = false;
      api
        .post("acpn-report", this.filter)
        .then((response) => {
          this.getTotalPaidClaims = response.data.getPaidClaims;
          this.pdf = response.data.pdf;
          this.pdfSharing = response.data.sharing;
          this.totaslNet = response.data.total_sharing;
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
        .catch((error) => console.log(error));
    },
    getId(id) {
      this.getsessionid = id;
    },
    exportCsv() {
      console.log(this.filter.doctors);
      let name = "";
      if (this.filter.doctors != "All") {
        name = this.getDoctor.name;
      }
      const options = {
        fieldSeparator: ",",
        quoteStrings: '"',
        decimalSeparator: ".",
        showLabels: true,
        showTitle: true,
        //title: 'ACPN REPORT \n ' + moment(this.filter.fdate).format('MMMM DD YYYY') + ' ' + moment(this.filter.tdate).format('MMMM DD YYYY')+' \n'+this.getDoctor.name,
        title: "ACPN REPORT \n " + this.filter.batch + " \n" + name,
        useTextFile: false,
        useBom: true,
        useKeysAsHeaders: true,
        // headers: ['Column 1', 'Column 2', etc...] <-- Won't work with useKeysAsHeaders present!
      };
      const csvExporter = new ExportToCsv(options);
      csvExporter.generateCsv(this.export);
    },
    getBatches() {
      axios
        .get("api/get-batches")
        .then((response) => {
          this.batches = response.data;
        })
        .catch((error) => console.log(error));
    },

    exportPDF() {
      api.post("/pdf", { responseType: "blob" }).then((response) => {
        const doc = new jsPDF();
        doc.text("Summary of Sharing(PHIC-350)", 20, 12);
        doc.text("ACPN-" + this.filter.batch, 20, 20);
        doc.setFontSize(9);
        doc.text("Confinement Period: " + moment(this.filter.fdate).format('MMMM DD, YYYY') + ' to ' + moment(this.filter.tdate).format('MMMM DD, YYYY'), 20, 26);     
        doc.setFontSize(8);
        doc.text("Prepared by: " + localStorage.getItem("user"), 20, 29);doc.autoTable({
          headStyles :{
            fillColor : [65, 105, 225]
          }, 
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
          body: this.pdf.map((user) => [
            user.nephro,
            user.sess,
            "350",
            user.total,
            user.tx,
            user.net,
          ]),
        });
        doc.save("generated.pdf");
      });
    },

    exportSharingPDF() {
      api.post("/pdf", { responseType: "blob" }).then((response) => {
        const doc = new jsPDF();
        doc.text("Summary of Sharing(PHIC-2250)", 20, 12);
        doc.text("ACPN-" + this.filter.batch, 20, 20);
        doc.setFontSize(9);
        doc.text("Confinement Period: " + moment(this.filter.fdate).format('MMMM DD, YYYY') + ' to ' + moment(this.filter.tdate).format('MMMM DD, YYYY'), 20, 26);     
        doc.setFontSize(8);
        doc.text("Prepared by: " + localStorage.getItem("user"), 20, 29);doc.autoTable({
          headStyles :{
            fillColor : [65, 105, 225]
          }, 
          head: [
            [
              "Nephrologist",
              "No. of Sessions",
              "Philhealth Sharing",
              "Total Amount",
              "PNCSI 25%(Sharing)",
              "Less with Tax",
              "Net",
            ],
          ],
          margin: { top: 30 },
          body: this.pdfSharing.map((user) => [
            user.nephro,
            user.sess,
            user.amount_sharing,
            user.total,
            user.sharing,
            user.tx,
            user.net,
          ]),
        });
        doc.save("generated.pdf");
      });
    },

    exportByDctor() {
      api.post("/pdf", { responseType: "blob" }).then((response) => {
        console.log(this.getDoctor)
        const doc = new jsPDF('landscape');
        doc.text("ACPN REPORT", 20, 12);
        doc.text(this.filter.batch, 20, 20);
        doc.text(this.getDoctor.name, 20, 26);
        doc.setFontSize(9);
        doc.text("Confinement Period: " + moment(this.filter.fdate).format('MMMM DD, YYYY') + ' to ' + moment(this.filter.tdate).format('MMMM DD, YYYY'), 20, 29);
        doc.setFontSize(8);
        doc.text("Prepared by: " + localStorage.getItem("user"), 20, 32);
        doc.autoTable({
          headStyles :{
            fillColor : [65, 105, 225]
          }, 
          styles: { halign: 'center' },
          head: [
            [
              "Name",
              "No. of Sessions",
              "Dates",
              "PHIC NEPHRO 350",
              "ACPN No.",
            ],
          ],
          margin: { top: 35 },
          body: this.export.map((user) => [
            user.Name,
            user.nos,
            user.Dates,
            user.phic,
            user.acpns,
          ]),
        });
        doc.save("generated.pdf");
      });
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
