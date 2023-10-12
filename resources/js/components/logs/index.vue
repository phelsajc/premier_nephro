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
                <li class="breadcrumb-item active">Logs</li>
              </ol>
            </div>
          </div>
        </div>
      </section>
      <section class="content">
        <div class="container-fluid">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Log Reports</h3>
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
                      <label>&nbsp;</label> <br />
                      <button type="button" @click="showReport()" class="btn btn-info">
                        Filter
                      </button>
                    </div>
                  </div>
                </div>
                <table class="table">
                  <thead>
                    <tr>
                      <th>Status</th>
                      <th>Date</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="e in results">
                      <td>
                        {{ e.status }}
                      </td>
                      <td>
                        {{ e.date }}
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
import api from "../../Helpers/api";
export default {
  created() {
    if (!User.loggedIn()) {
      this.$router.push({ name: "/" });
    }
  },
  components: {
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
      results: [],
      month: null,
      doctors_list: [],
      token: localStorage.getItem("token"),
    };
  },
  methods: {
    showReport() {
      api
        .post("log-report", this.filter)
        .then((response) => {
          this.results = response.data.data;
          console.log(this.results);
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
