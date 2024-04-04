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
                  <div class="col-sm-8">
                    <div class="form-group">
                      <label>ACPN</label>
                      <input type="text" class="form-control" v-model="filter.acpn" />
                    </div>
                  </div>
                  <div class="col-sm-2">
                    <div class="form-group">
                      <label>Doctor</label>
                      <select class="form-control" v-model="filter.doctor">
                        <option selected value="0">All</option>
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
                          <a class="dropdown-item" href="#" @click="exportPDF()"
                            >Summary ACPN</a
                          >
                          <a class="dropdown-item" href="#" @click="exportByDctor()"
                            >Summary By Doctor</a
                          >
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <dl class="row">
                  <dt class="col-sm-2">Total Amount:</dt>
                  <dd class="col-sm-8">{{ total_sessions }}</dd>
                </dl>
                <dl class="row">
                  <dt class="col-sm-2">Total Session:</dt>
                  <dd class="col-sm-8">{{ total_number_session }}</dd>
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
    this.getBatches();
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
        acpn: "",
        doctor: 0,
      },
      results: [],
      resultsAcpn: [],
      resultsAcpnDctr: [],
      doctors_list: [],
      //export: [],
      getTx: "",
      getNet: "",
      getPf: "",
      getEwt: "",
      month: null,
      total_number_session: null,
      total_sessions: null,
    };
  },
  computed: {
    getDoctor() {
      return this.doctors_list.find((e) => e.id == this.filter.doctors);
    },
  },
  ///05292023-06022023 //05152023-05192023
  methods: {
    showReport() {
      api
        .post("acpn-report-list", this.filter)
        .then((response) => {
          this.results = response.data.acpn;
          this.resultsAcpn = response.data.data_arrayAcpn;
          this.resultsAcpnDctr = response.data.data_arrayAcpn_dctr;
          this.total_number_session = response.data.total;
          this.total_sessions = response.data.total_amount;
          this.getTx = response.data.total_session_doctor;
          this.getNet = response.data.sharing_net;
          this.getPf = response.data.sharing_pf;
          this.getEwt = response.data.sharing_ewt;
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
              title: "Token has expired",
            });
          }
        });
    },
    exportPDF() {
      api.post("/pdf", { responseType: "blob" }).then((response) => {
        const doc = new jsPDF();
        const myArray = this.filter.acpn.split(",");

        doc.text("Summary of ACPN", 20, 12);
        doc.setFontSize(8);
        doc.text("Prepared by: " + localStorage.getItem("user"), 20, 16);
        doc.autoTable({
          headStyles: {
            fillColor: [65, 105, 225],
          },
          head: [["ACPN", "Session", "Amount", "Batch"]],
          margin: { top: 30 },
          body: this.resultsAcpn.map((user) => [
            user.acpn,
            user.session,
            user.amount,
            user.batch,
          ]),
        });
        doc.save("summary_of_acpn.pdf");
      });
    },
    exportByDctor() {
      api.post("/pdf", { responseType: "blob" }).then((response) => {
        const doc = new jsPDF();
        const myArray = this.filter.acpn.split(",");

        const textBlockHeight = (doc.getLineHeight() * doc.text.length);
        var height = 0;
        var width = 0;
        doc.text("Summary of ACPN by Nephrologist", 20, 12);
        doc.setFontSize(8);
        doc.text("Prepared by: " + localStorage.getItem("user"), 20, 16);
        doc.text("Acpn/s: " + this.filter.acpn, 20, 20);
        doc.autoTable({
          headStyles: {
            fillColor: [65, 105, 225],
          },
          head: [["Nephrologist", "TX", "PF", "EWT", "NET"]],
          margin: { top: 30 },
          body: this.resultsAcpnDctr.map((user) => [
            user.nephro,
            user.tx,
            user.pf,
            user.ewt,
            user.net,
          ]),
          didParseCell: function (HookData) {
             // height = HookData.table.height
          },
          /* createdCell: function (cell, data) {
              height = data.table.height
          } */
          didDrawPage: function(data) {
            height = data.cursor.y;
            width = data.cursor.x;
            console.log(data.cursor)
          }
        });
        /* console.log(width)
        doc.setFontSize(12).setFont(undefined, 'bold');
        doc.text("25% Premier Sharing", 25, height+10);
        doc.text(""+this.getTx, 85,  height+10);
        doc.text(""+this.getPf, 101,  height+10);
        doc.text(""+this.getEwt, 135,  height+10);
        doc.text(""+this.getNet, 15,  height+10); */
        doc.save("summary_of_acpn_by_nephro.pdf");
      });
    },
    getBatches() {
      axios
        .get("api/get-batches")
        .then((response) => {
          this.batches = response.data;
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
      axios
        .get("/api/getDoctors")
        .then(({ data }) => (this.doctors_list = data))
        .catch();
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
