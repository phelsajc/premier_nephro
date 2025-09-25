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
                <li class="breadcrumb-item active">Claim Status</li>
              </ol>
            </div>
          </div>
        </div>
      </section>
      <section class="content">
        <div class="container-fluid">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Claim Status</h3>
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
                      <label>Status</label>
                      <select name="" id="" class="form-control" v-model="filter.claimStatus">
                        <option value="All">All</option>
                        <option value="Denied">Denied</option>
                        <option value="RTH">RTH</option>
                        <option value="Expired">Expired</option>
                        <option value="Unpaid">Unpaid</option>
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
                      <button type="button" @click="exportPDF()" class="btn btn-primary">
                        Export
                      </button>
                      <button type="button" @click="exportPDFSummary()" class="btn btn-danger">
                        Summary
                      </button>
                      <button type="button" @click="exportDetailedReport()" class="btn btn-warning">
                        Export Detailed Report
                      </button>
                    </div>
                  </div>
                </div>
                <progressBar :getStatus="showProgress"></progressBar>
                <table class="table">
                  <thead>
                    <tr>
                      <th>Patient</th>
                      <th>Status</th>
                      <th>Date</th>
                      <th>Amount</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="e in results">
                      <td>
                        {{ e.patient }}
                      </td>
                      <td>
                        {{ e.status }}
                      </td>
                      <td>
                        {{ e.session }}
                      </td>
                      <td>
                        {{ e.amount }}
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
        claimStatus: "",
      },
      results: [],
      export: [],
      exportDetailed: [],
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
    getDoctor() {
      return this.doctors_list.find((e) => e.id == this.filter.doctors);
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
        .post("claim_status", this.filter)
        .then((response) => {
          console.log( response)
          this.results = response.data.data;
          this.export = response.data.summary;
          this.exportDetailed = response.data.detailed;
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
    exportPDF() {
      api.post("/pdf", { responseType: "blob" }).then((response) => {
        const doc = new jsPDF();
        doc.setFontSize(11);
        //name = this.getDoctor.name;
        name = this.filter.doctors !='All'?this.getDoctor.name:'All';
        doc.text("Summary of "+this.filter.claimStatus+" Claims Status of "+name, 20, 12);
        doc.setFontSize(9);
        doc.text("From: " + moment(this.filter.fdate).format('MMMM DD, YYYY') + ' To ' + moment(this.filter.tdate).format('MMMM DD, YYYY'), 20, 16);     
        doc.setFontSize(8);
        doc.text("Prepared by: " + localStorage.getItem("user"), 20, 19);doc.autoTable({
          headStyles :{
            fillColor : [65, 105, 225]
          }, 
          head: [
            [
              "Patient",
              "Status",
              "Date",
              "Amount",
            ],
          ],
          margin: { top: 30 },
          body: this.results.map((user) => [
            user.patient,
            user.status,
            user.session,
            user.amount,
          ]),
        });
        doc.save("claims_status_from_"+moment(this.filter.fdate).format('YYYY_MMMM_DD')+"_to_"+moment(this.filter.tdate).format('YYYY_MMMM_DD')+".pdf");
      });
    },
    exportPDFSummary() {
      api.post("/pdf", { responseType: "blob" }).then((response) => {
        const doc = new jsPDF();
        doc.setFontSize(11);
        //name = this.getDoctor.name;
        name = this.filter.doctors !='All'?this.getDoctor.name:'All';
        doc.text("Summary of "+this.filter.claimStatus+" Claims Status", 20, 12);
        doc.setFontSize(9);
        doc.text("From: " + moment(this.filter.fdate).format('MMMM DD, YYYY') + ' To ' + moment(this.filter.tdate).format('MMMM DD, YYYY'), 20, 16);     
        doc.setFontSize(8);
        doc.text("Prepared by: " + localStorage.getItem("user"), 20, 19);doc.autoTable({
          headStyles :{
            fillColor : [65, 105, 225]
          }, 
          head: [
            [
              "Doctor",
              "Session",
              "Amount",
            ],
          ],
          margin: { top: 30 },
          body: this.export.map((user) => [
            user.doctor,
            user.session,
            user.amount,
          ]),
        });
        doc.save("claims_status_from_"+moment(this.filter.fdate).format('YYYY_MMMM_DD')+"_to_"+moment(this.filter.tdate).format('YYYY_MMMM_DD')+".pdf");
      });
    },
    exportDetailedReport() {
      api.post("/pdf", { responseType: "blob" }).then((response) => {
        const doc = new jsPDF();
        doc.setFontSize(11);
        name = this.filter.doctors !='All'?this.getDoctor.name:'All';
        doc.text("Summary of "+this.filter.claimStatus+" Claims Status", 20, 12);
        doc.setFontSize(9);
        doc.text("From: " + moment(this.filter.fdate).format('MMMM DD, YYYY') + ' To ' + moment(this.filter.tdate).format('MMMM DD, YYYY'), 20, 16);     
        doc.setFontSize(8);
        doc.text("Prepared by: " + localStorage.getItem("user"), 20, 19);doc.autoTable({
          headStyles :{
            fillColor : [65, 105, 225]
          }, 
          head: [
            [
              "Doctor",
              "No. of Sessions",
              "Dates",
            ],
          ],
          margin: { top: 30 },
          body: this.exportDetailed.map((user) => [
            user.doctor,
            user.sessions,
            user.date_session,
          ]),
        });
        doc.save("claims_status_from_"+moment(this.filter.fdate).format('YYYY_MMMM_DD')+"_to_"+moment(this.filter.tdate).format('YYYY_MMMM_DD')+".pdf");
      });
    }
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
