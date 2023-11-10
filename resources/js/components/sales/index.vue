<template>
  <div class="wrapper">
    <navComponent></navComponent>
    <sidemenuComponent></sidemenuComponent>
    <div class="content-wrapper">
      <section class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <h1>Sales</h1>
            </div>
          </div>
        </div>
      </section>
      <section class="content">
        <div class="container-fluid">
          <div class="row">
            <div class="col-12">
              <div class="card">
                <div class="card-header">
                  <h3 class="card-title">&nbsp;</h3>
                  <!-- <form class="form-inline">
                    <div class="form-group mx-sm-3 mb-2">
                      <companyComponent v-model="form.company" @return-response="getReturnResponse"></companyComponent>
                    </div>
                    <button type="button" class="btn btn-primary mb-2" @click="allEmployee()">View</button>
                  </form> -->
                </div>
                <div class="card-body">
                  <form class="user" enctype="multipart/form-data">
                    <div class="row">
                      <div class="col-sm-2">
                        <div class="form-group">
                          <label>Select Date</label>
                          <datepicker
                            name="date"
                            required
                            input-class="dpicker"
                            :minimumView="'month'"
                            :maximumView="'month'"
                            v-model="filter.fdate"
                            :bootstrap-styling="true"
                          ></datepicker>
                        </div>
                      </div>
                      <div class="col-sm-2">
                        <div class="form-group">
                          <label>Select Product</label>
                          <productComponent
                            v-model="filter.product"
                            @return-response="getReturnResponse"
                          ></productComponent>
                        </div>
                      </div>
                      <div class="col-sm-2">
                        <div class="form-group">
                          <label>&nbsp;</label> <br />
                          <button
                            type="button"
                            @click="showReport()"
                            class="btn btn-info"
                          >
                            Filter
                          </button>
                        </div>
                      </div>
                    </div>
                  </form>

                  <table class="table">
                    <thead>
                      <tr>
                        <th>Sales of {{ getProduct }}</th>
                        <th>{{ getTotalUnits }} unit/s</th>
                        <th>{{ getTotalSales }}</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr>
                        <td>Cost of Sale</td>
                        <td></td>
                        <td>
                          {{ grandTotalCos }}
                        </td>
                      </tr>
                      <tr v-for="e in items">
                        <td>{{ e.company }} {{ e.totalQtyPurchase }} * {{ e.price }}</td>
                        <td>
                          <!-- {{ e.cost_of_sales }} -->
                        </td>
                        <td>
                          <!-- {{ e.cost_of_sales }} -->
                        </td>
                      </tr>
                      <tr>
                        <td>Income from sale of {{ getProduct }}</td>
                        <td></td>
                        <td>
                          {{ income }}
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>
    <footerComponent></footerComponent>
  </div>
</template>

<script type="text/javascript">
import moment from "moment";
import Datepicker from "vuejs-datepicker";
export default {
  created() {
    if (!User.loggedIn()) {
      this.$router.push({ name: "/" });
    }
    //this.allEmployee();
  },
  components: {
    Datepicker,
  },
  data() {
    return {
      hasError: false,
      isHidden: true,
      filter: {
        company: null,
        fdate: null,
        tdate: null,
        product: null,
      },
      employees: [],
      searchTerm: "",
      countRecords: 0,
      token: localStorage.getItem("token"),
      getTotalUnits: 0,
      getTotalSales: 0,
      grandTotalCos: 0,
      income: 0,
      items: [],
      getProduct: null,
    };
  },
  computed: {
    filtersearch() {
      return this.employees.filter((e) => {
        return e.name.match(this.searchTerm);
      });
    },
  },
  methods: {
    /* allEmployee(){
      this.isHidden =  false
        axios.get('/api/rec_products')
        .then(({data}) => (
          this.employees = data[0].data ,
          this.countRecords =data[0].count,
          this.showing = data[0].showing,
      this.isHidden =  true
       ))
        .catch()
    }, */
    showReport() {
      this.isHidden = false;

      this.filter.fdate = moment
        .utc(this.filter.fdate)
        .utcOffset("+08:00")
        .format("YYYY-MM");
      api
        .post("checksales", this.filter)
        .then((response) => {
          this.items = response.data.data;
          this.getTotalUnits = response.data.GrandtotalQtyPurchase;
          this.getTotalSales = response.data.GrandtotalPurchase;
          this.grandTotalCos = response.data.GrandtotalCos;
          this.getProduct = response.data.product;
          this.income = response.data.income;
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
    viewLedger() {
      alert(this.form.company);
    },
    async check_doctors_detail(id) {
      return await axios.get("/api/check_doctors_detail/" + id).then((response) => {
        setTimeout(function () {
          return response.data;
        }, 3000);
      });
    },
    formatDate(date) {
      const options = { year: "numeric", month: "long", day: "numeric" };
      return new Date(date).toLocaleDateString("en", options);
    },
    getReturnResponse: function (data) {
      this.filter.product = data.id.id;
    },
  },
};
</script>

<style>
.em_photo {
  height: 40px;
  width: 40px;
}

.to-right {
  float: right;
}

.spin_center {
  position: absolute;
  top: 50%;
  left: 50%;
  width: 300px;
  text-align: center;
  transform: translateX(-50%);
  /*display: none;*/
}

.btn-app {
  height: unset !important;
  padding: 0 1.5em 0 1.5em;
}
</style>
