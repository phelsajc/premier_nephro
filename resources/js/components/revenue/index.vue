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
                <li class="breadcrumb-item active">Revenue Sharing</li>
              </ol>
            </div>
          </div>
        </div>
      </section>
      <section class="content">
        <div class="container-fluid">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Revenue Sharing Report</h3>
            </div>

            <div class="card-body">
              <form class="user" enctype="multipart/form-data">
                <div class="row">
                  <div class="col-sm-2">
                    <div class="form-group">
                      <label>From Date</label>
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
                      <label>To Date</label>
                      <!-- <datepicker
                        name="date"
                        required
                        input-class="dpicker"
                        :minimumView="'month'"
                        :maximumView="'month'"
                        v-model="filter.tdate"
                        :bootstrap-styling="true"
                      ></datepicker> -->
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
                      <label>Status for Export</label>
                      <select class="form-control" v-model="filter.status">
                        <option selected value="All">All</option>
                        <option  value="Paid">Paid</option>
                        <option  value="Unpaid">Unpaid</option>
                      </select>
                    </div>
                  </div>

                  <div class="col-sm-2">
                    <div class="form-group">
                      <label>&nbsp;</label> <br />
                      <button type="button" @click="showReport()" class="btn btn-info">
                        Filter
                      </button>
                      <button
                        type="button"
                        @click="exportAllData()"
                        class="btn btn-primary"
                      >
                        Export All
                      </button>
                    </div>
                  </div>
                </div>

                <table class="table table-bordered">
                  <thead>
                    <tr>
                      <th colspan="2"></th>
                      <th>PNCSI</th>
                      <th>PREMIER</th>
                      <th>TAX</th>
                      <th rowspan="2" style="text-align: center">NET</th>
                      <th rowspan="2" style="text-align: center">Total Paid</th>
                      <th rowspan="2" style="text-align: center">Total Unpaid</th>
                      <th rowspan="2" style="text-align: center">Total Paid Session</th>
                      <th rowspan="2" style="text-align: center">Total Unpaid Session</th>
                      <!-- <th rowspan="2" style="text-align: center">Balance</th> -->
                    </tr>
                    <tr>
                      <th></th>
                      <th></th>
                      <th style="color: red">2,250.00</th>
                      <th></th>
                      <th></th>
                    </tr>
                    <tr>
                      <th>MONTH</th>
                      <th>NO. OF SESSIONS</th>
                      <th>GROSS INCOM</th>
                      <th>SHARE(25%)</th>
                      <th>5%</th>
                      <th></th>
                      <th></th>
                      <th></th>
                      <th></th>
                      <th></th>
                      <!-- <th></th> -->
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="e in results">
                      <td>
                        {{ e.month }}
                      </td>
                      <td>
                        {{ e.sessions }}
                      </td>
                      <td>
                        {{
                          e.gross
                            .toFixed(2)
                            .toString()
                            .replace(/\B(?=(\d{3})+(?!\d))/g, ",")
                        }}
                      </td>
                      <td>
                        {{
                          e.share
                            .toFixed(2)
                            .toString()
                            .replace(/\B(?=(\d{3})+(?!\d))/g, ",")
                        }}
                      </td>
                      <td>
                        {{
                          e.tax
                            .toFixed(2)
                            .toString()
                            .replace(/\B(?=(\d{3})+(?!\d))/g, ",")
                        }}
                      </td>
                      <td>
                        {{
                          e.net
                            .toFixed(2)
                            .toString()
                            .replace(/\B(?=(\d{3})+(?!\d))/g, ",")
                        }}
                      </td>
                      <td>
                        {{
                          e.total
                            .toFixed(2)
                            .toString()
                            .replace(/\B(?=(\d{3})+(?!\d))/g, ",")
                        }}
                      </td>
                      <td>
                        {{
                          e.total_unpaid
                            .toFixed(2)
                            .toString()
                            .replace(/\B(?=(\d{3})+(?!\d))/g, ",")
                        }}
                      </td>
                      <td>
                        <button
                          type="button"
                          class="btn btn-success"
                          @click="
                            getPatientSessions(
                              'paid',
                              e.getPaidPatientSessions,
                              e.session_paid,
                              e.total_paid_for_summary
                            )
                          "
                        >
                          {{ e.session_paid }}
                        </button>
                      </td>
                      <td>
                        <button
                          type="button"
                          class="btn btn-danger"
                          @click="
                            getPatientSessions(
                              'unpaid',
                              e.getUnPaidPatientSessions,
                              e.session_unpaid,
                              e.total_unpaid_for_summary
                            )
                          "
                        >
                          {{ e.session_unpaid }}
                        </button>
                      </td>
                      <!-- <td>
                        {{
                          e.balance
                            .toFixed(2)
                            .toString()
                            .replace(/\B(?=(\d{3})+(?!\d))/g, ",")
                        }}
                      </td> -->
                    </tr>
                    <tr>
                      <td></td>
                      <td></td>
                      <td></td>
                      <td></td>
                      <td></td>
                      <td>
                        {{
                          totalNet
                            .toFixed(2)
                            .toString()
                            .replace(/\B(?=(\d{3})+(?!\d))/g, ",")
                        }}
                      </td>
                      <td>
                        {{
                          totalPaid
                            .toFixed(2)
                            .toString()
                            .replace(/\B(?=(\d{3})+(?!\d))/g, ",")
                        }}
                      </td>
                      <td>
                        {{
                          totalBalance
                            .toFixed(2)
                            .toString()
                            .replace(/\B(?=(\d{3})+(?!\d))/g, ",")
                        }}
                      </td>
                      <td>
                        {{ total_sessions }}
                      </td>
                      <td>
                        
                      </td>
                    </tr>
                  </tbody>
                </table>
              </form>
            </div>
            <!-- /.card-body -->

            <div id="chart">
              <apexchart
                ref="radar"
                type="line"
                height="350"
                :options="chartOptions"
                :series="series"
              ></apexchart>
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
import moment from 'moment-timezone';
import ApexCharts from "apexcharts";
import VueApexCharts from "vue-apexcharts";
import jsPDF from "jspdf";
import "jspdf-autotable";
export default {
  created() {
    if (!User.loggedIn()) {
      this.$router.push({ name: "/" });
    }

    //this.checkToken();
    this.getDoctors();
  },
  components: {
    Datepicker,
    apexchart: VueApexCharts,
  },
  data() {
    return {
      getTotalExportResult: 0,
      totalNet: 0,
      totalPaid: 0,
      totalBalance: 0,
      patientSessionsPaymentList: [],
      allPatientSessionsPaymentList: [],
      filter: {
        fdate: "",
        tdate: "",
        doctor: 0,
        status: "",
      },
      series: [],
      chartOptions: {
        chart: {
          height: 350,
          type: "line",
          zoom: {
            enabled: false,
          },
        },
        dataLabels: {
          enabled: true,
          background: {
            enabled: true,
            foreColor: "#000000",
            padding: 4,
            borderRadius: 2,
            borderWidth: 1,
            borderColor: "#000000",
            opacity: 0.9,
            dropShadow: {
              enabled: false,
              top: 1,
              left: 1,
              blur: 1,
              color: "#000000",
              opacity: 0.45,
            },
          },
        },
        stroke: {
          curve: "straight",
        },
        title: {
          text: "Monthly Report",
          align: "left",
        },
        grid: {
          row: {
            colors: ["#f3f3f3", "transparent"],
            opacity: 0.5,
          },
        },
        xaxis: {
          categories: [],
        },
        legend: {
          position: "top",
          horizontalAlign: "right",
          floating: true,
          offsetY: -25,
          offsetX: -5,
        },
      },
      results: [],
      month: null,
      doctors_list: [],
      cntAll: 0,
      token: localStorage.getItem("token"),
      getAllPaid: 0,
      getAllUnPaid: 0,
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
      return (this.total_sessions * 150).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    },
  },
  methods: {
    getPatientInformation() {
      axios
        .get("/api/getPxInfo/" + this.$route.params.id)
        .then(({ data }) => (this.user_info = data))
        .catch();
    },
    clickedShowDetailModal: function (value) {
      this.getSelectdeProduct = value;
      this.productList.product = this.getSelectdeProduct.product;
      this.productList.description = this.getSelectdeProduct.description;
      this.productList.price = this.getSelectdeProduct.price;
      this.productList.id = this.getSelectdeProduct.id;
    },
    calculateTotal() {
      this.productList.total = this.productList.price * this.productList.qty;
    },
    showReport() {
      const timezone = 'Asia/Manila';
      const bfdate =  this.filter.fdate
      const btdate =  this.filter.tdate
      this.filter.fdate = moment.tz(this.filter.fdate, timezone).format('YYYY-MM-DD')
      this.filter.tdate = moment.tz(this.filter.tdate, timezone).format('YYYY-MM-DD')
      
      this.getAllPaid = [];
          this.getAllUnPaid = [];
          this.series = [];
          this.results = [];
          this.totalNet = [];
          this.totalPaid = [];
          this.totalBalance = 0;
          this.series = [];
          this.cntAll = 0;
          this.allPatientSessionsPaymentList = [];
      const headers = {
        Authorization: "Bearer ".concat(this.token),
      };
      axios
        .post(
          "/api/revenue-report",
          {
            data: this.filter,
          },
          {
            headers: headers,
          }
        )
        .then((res) => {
          this.getAllPaid = res.data.allpaid;
          this.getAllUnPaid = res.data.allunpaid;
          this.series = res.data.net[0].net;
          this.results = res.data.data;
          this.totalNet = res.data.totalNet;
          this.totalPaid = res.data.totalPaid;
          this.totalBalance = res.data.totalBalance;
          this.series = res.data.net;
          this.cntAll = res.data.cntAll;
          this.allPatientSessionsPaymentList = res.data.getPatientAllSessions;
          this.chartOptions = {
            xaxis: {
              categories: res.data.month,
            },
          };
          this.month = moment(this.filter.date).format("MMMM YYYY");
          Toast.fire({
            icon: "success",
            title: "Saved successfully",
          });
          
          this.filter.fdate = moment(bfdate).format("DD MMMM YYYY");
          
          this.filter.tdate = moment(btdate).format("DD MMMM YYYY");
          
        })
        .catch(
          (error) => console.log(error),
          Toast.fire({
            icon: "error",
            title: "Session End. Login again.",
          })
        );
    },
    getDoctors() {
      axios
        .get("/api/getDoctors")
        .then(({ data }) => (this.doctors_list = data))
        .catch();
    },
    getPatientSessions(type, e, total, total_unpaid) {
      this.patientSessionsPaymentList = e;

      api.post("/pdf", { responseType: "blob" }).then((response) => {
        const doc = new jsPDF();

        let check_dr = this.doctors_list.find((e) => e.id == this.filter.doctor)
        let forwho = this.filter.doctor!=0?check_dr.name:"ALL";
        doc.text("Summary of " + type + " sessions of patients for "+forwho, 20, 12);
        doc.setFontSize(8);
        doc.text("Prepared by: " + localStorage.getItem("user"), 20, 16);
        doc.text("Total Session/s: " + total, 20, 20);
        doc.text("Total: " + total_unpaid, 20, 24);
        doc.text("Period from" + moment(this.filter.fdate).format("MMMM YYYY") + " to " + moment(this.filter.tdate).format("MMMM YYYY") , 20, 28);
        doc.autoTable({
          headStyles: {
            fillColor: [65, 105, 225],
          },
          columnStyles: {
            0: { cellWidth: "auto" },
            1: { cellWidth: "auto" },
            2: { cellWidth: "auto" },
            2: { cellWidth: "auto" },
          },
          head: [["Patient", "No. of Sessions", "Sessions", "Amount"]],
          margin: { top: 30 },
          body: e.map((user) => [user.name, user.cnt_sess, user.cnt, user.amt]),
        });
        doc.save("generated.pdf");
      });
    },
    exportAllData() {
      api.post("/pdf", { responseType: "blob" }).then((response) => {
        const doc = new jsPDF("landscape");

        let dr_data = this.allPatientSessionsPaymentList
            .filter((user) =>
              this.filter.doctor != 0 ? user.id === this.filter.doctor : user.id > 0
            );
            /* .filter((user) =>
              user.id === 2
            ); */

            let check_dr = this.doctors_list.find((e) => e.id == this.filter.doctor)
            let amount = 0;
            if(this.filter.status=='All'){
                amount = this.getAllPaid + this.getAllUnPaid;
            }else if(this.filter.status=='Paid'){
                amount = this.getAllPaid;
            }else if(this.filter.status=='Unpaid'){
                amount = this.getAllUnPaid;
            }

            let forwho = this.filter.doctor!=0?check_dr.name:"ALL";

        doc.text("PHIC-"+this.filter.status+" sessions for "+forwho, 20, 12);
        doc.setFontSize(8);
        doc.text("Prepared by: " + localStorage.getItem("user"), 20, 16);
        //doc.text("Total: " + dr_data.length, 20, 20);
        doc.text("Total: " + amount , 20, 20);
        doc.text("Period from" + moment(this.filter.fdate).format("MMMM YYYY") + " to " + moment(this.filter.tdate).format("MMMM YYYY") , 20, 24);
        doc.autoTable({
          headStyles: {
            fillColor: [65, 105, 225],
          },
          columnStyles: {
            0: { cellWidth: "auto" },
            1: { cellWidth: "auto" },
            2: { cellWidth: "auto" },
            3: { cellWidth: "auto" },
          },
          head: [["Patient", "No. of Sessions", "Sessions", "Doctor"]],
          margin: { top: 30 },
          body: dr_data
            .map((user) => [user.name, user.cnt, user.dates, user.doc]),
        });
        
        doc.save((this.filter.doctor!=0?check_dr.name:"ALL")+"_"+this.filter.status+".pdf");
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
