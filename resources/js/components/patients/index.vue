<template>
  <div class="wrapper">
    <navComponent></navComponent>
    <sidemenuComponent></sidemenuComponent>
    <div class="content-wrapper">
      <section class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <h1>Patient Lists</h1>
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
                  <router-link to="/patients_add/0" class="btn btn-primary pull-left"
                    >Add</router-link
                  >
                  <div class="pull-right">
                    <input type="file" accept=".csv" @change="handleFileUpload($event)" />
                    <button
                      type="button"
                      @click="uploadCSV()"
                      class="btn btn-info btn-sm"
                    >
                      Upload
                    </button>
                  </div>
                </div>
                <div class="card-body">
                  <div class="spin_center" :class="{ 'd-none': isHidden }">
                    <div class="overlay">
                      <i class="fas fa-3x fa-sync-alt fa-spin"></i>
                      <div class="text-bold pt-2">Loading...</div>
                    </div>
                  </div>

                  <div class="row">
                    <div class="pull-left col-md-3">
                      <select class="form-control" v-model="form.doctor">
                        <option selected value="0">All</option>
                        <option v-for="e in doctors" :value="e.id">{{ e.name }}</option>
                      </select>
                    </div>
                    <div class="pull-left col-md-3">
                      <button
                        type="button"
                        @click="filterEmployeesDoctor()"
                        class="btn btn-info"
                      >
                        Filter
                      </button>
                      <button
                        type="button"
                        @click="cleasFilter()"
                        class="btn btn-warning"
                      >
                        Clear
                      </button>
                    </div>
                  </div>
                  <br />

                  <ul class="list-group">
                    <input
                      type="text"
                      v-model="form.searchTerm2"
                      @change="filterEmployee()"
                      class="form-control to-right"
                      style="width: 100%"
                      placeholder="Search patients here"
                    />
                    <router-link
                      v-for="e in filtersearch"
                      :key="e.id"
                      :to="{ name: 'patients_add', params: { id: e.id } }"
                    >
                      <li class="list-group-item">
                        <div class="row">
                          <div class="col-6 float-left">
                            <div class="d-flex w-100 justify-content-between">
                              <h5 class="mb-1">
                                <strong>{{ e.name }} </strong>
                              </h5>
                            </div>
                            <span class="badge badge-secondary"> {{ e.address }}</span>
                          </div>
                        </div>
                      </li>
                    </router-link>
                  </ul>
                  <br />
                  <nav aria-label="Page navigation example" class="to-right">
                    <ul class="pagination">
                      <li class="page-item" v-for="(e, index) in this.countRecords">
                        <a class="page-link" @click="getPageNo(index + 1)" href="#">{{
                          index + 1
                        }}</a>
                      </li>
                    </ul>
                  </nav>
                  <nav aria-label="Page navigation example" class="">
                    {{ showing }}
                  </nav>
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
import Papa from "papaparse";
import api from "../../Helpers/api";

export default {
  created() {
    if (!User.loggedIn()) {
      this.$router.push({ name: "/" });
    }
    this.getDoctors();
    this.allPatients();
  },
  data() {
    return {
      file: "",
      content: [],
      parsed: false,
      hasError: false,
      isHidden: true,
      doctors: [],
      form: {
        searchTerm2: null,
        start: 0,
        doctor: 0,
      },
      employees: [],
      searchTerm: "",
      countRecords: 0,
      getdctr: "-",
      utype: User.user_type(),
      token: localStorage.getItem("token"),
      showing: "",
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
    getDoctors() {
      api
        .get("getDoctors")
        .then((response) => {
          this.doctors = response.data;
        })
        .catch((error) => {
          console.log(error);
        });
    },
    allPatients() {
      this.isHidden = false;
      api
        .get("patients")
        .then((response) => {
          (this.employees = response.data.data),
            (this.countRecords = response.data.count),
            (this.showing = response.data.showing),
            (this.isHidden = true);
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
    filterEmployee() {
      this.employees = [];
      this.countRecords = null;
      this.form.start = 0;
      this.isHidden = false;
      api
        .post("patients", this.form)
        .then((response) => {
          this.employees = response.data.data;
          this.countRecords = response.data.count;
          this.isHidden = true;
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
    filterEmployeesDoctor() {
      this.employees = [];
      this.countRecords = null;
      this.form.start = 0;
      this.isHidden = false;
      api
        .post("patients", this.form)
        .then((response) => {
          this.employees = response.data.data;
          this.countRecords = response.data.count;
          this.isHidden = true;
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
    cleasFilter() {
      this.form.doctor = 0;
      this.filterEmployeesDoctor();
    },
    getPageNo(id) {
      this.form.start = (id - 1) * 10;
      this.isHidden = false;
      api
        .post("patients", this.form)
        .then((response) => {
          this.employees = response.data.data;
          this.countRecords = response.data.count;
          (this.showing = response.data.showing), (this.isHidden = true);
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
    handleFileUpload(event) {
      this.file = event.target.files[0];
      this.parseFile();
    },
    parseFile() {
      Papa.parse(this.file, {
        header: true,
        skipEmptyLines: true,
        complete: function (results) {
          this.form = results;
          this.parsed = true;
        }.bind(this),
      });
    },
    uploadCSV() {
      this.isHidden = false;
      api
        .post("patients-import", this.form)
        .then((response) => {
          this.allPatients();
          this.isHidden = true;
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
