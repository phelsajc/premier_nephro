<template>
  <div class="wrapper">
    <navComponent></navComponent>
    <sidemenuComponent></sidemenuComponent>
    <div class="content-wrapper">
      <section class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <h1>Batch Lists</h1>
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
                  <router-link to="/batch_add/0" class="btn btn-primary">Add</router-link>
                </div>
                <div class="card-body">
                  <div class="spin_center" :class="{ 'd-none': isHidden }">
                    <div class="overlay">
                      <i class="fas fa-3x fa-sync-alt fa-spin"></i>
                      <div class="text-bold pt-2">Loading...</div>
                    </div>
                  </div>
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
                      :to="{ name: 'batch_add', params: { id: e.id } }"
                    >
                      <li class="list-group-item">
                        <div class="row">
                          <div class="col-6 float-left">
                            <div class="d-flex w-100 justify-content-between">
                              <h5 class="mb-1">
                                <strong>{{ e.batch }} </strong>
                              </h5>
                            </div>
                          </div>
                          <div class="col-12 float-left">
                            <div class="d-flex w-100 justify-content-between">
                              <span class="badge badge-success">{{ e.desc }}</span>
                            </div>
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

export default {
  created() {
    if (!User.loggedIn()) {
      this.$router.push({ name: "/" });
    }
    this.allEmployee();
  },
  data() {
    return {
      file: "",
      content: [],
      parsed: false,
      hasError: false,
      isHidden: true,
      form: {
        searchTerm2: null,
        start: 0,
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
        return e.batch.match(this.searchTerm);
      });
    },
  },
  methods: {
    allEmployee() {
      this.isHidden = false;
      axios
        .get("/api/batches")
        .then(
          ({ data }) => (
            (this.employees = data[0].data),
            (this.countRecords = data[0].count),
            (this.showing = data[0].showing),
            (this.isHidden = true)
          )
        )
        .catch();
    },
    deleteRecord(id) {
      Swal.fire({
        title: "Are you sure?",
        text: "You won't be able to revert this!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, delete it!",
      }).then((result) => {
        if (result.isConfirmed) {
          axios
            .delete("/api/batch/" + id)
            .then(() => {
              this.employees = this.employees.filter((e) => {
                return e.id != id;
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

          Swal.fire("Deleted!", "Your file has been deleted.", "success");
        }
      });
    },
    filterEmployee() {
      this.employees = [];
      this.countRecords = null;
      this.form.start = 0;
      this.isHidden = false;
      axios
        .post("/api/batches", this.form)
        .then((res) => {
          this.employees = res.data[0].data;
          this.countRecords = res.data[0].count;
          console.log(res.data.data);
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
    getPageNo(id) {
      this.form.start = (id - 1) * 10;
      this.isHidden = false;
      console.log(this.isHidden);
      axios
        .post("/api/batches", this.form)
        .then((res) => {
          this.employees = res.data[0].data;
          this.countRecords = res.data[0].count;
          (this.showing = res.data[0].showing), console.log(res.data[0]);
          this.isHidden = true;
          console.log(this.isHidden);
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
