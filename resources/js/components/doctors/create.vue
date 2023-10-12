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
                <li class="breadcrumb-item"><a href="#">Home</a></li>
                <li class="breadcrumb-item active">Company</li>
              </ol>
            </div>
          </div>
        </div>
      </section>
      <section class="content">
        <div class="container-fluid">
          <div class="row">
            <div class="col-12">
              <div class="card card-primary">
                <div class="card-header">
                  <h3 class="card-title">Add Doctor</h3>
                  <div class="card-tools">
                    <button
                      type="button"
                      class="btn btn-tool"
                      data-card-widget="collapse"
                    >
                      <i class="fas fa-minus"></i>
                    </button>
                  </div>
                </div>
                <div class="card-body">
                  <form
                    class="user"
                    @submit.prevent="addEmployee"
                    enctype="multipart/form-data"
                  >
                    <div class="row">
                      <div class="col-sm-4">
                        <div class="form-group">
                          <label>Doctor</label>
                          <input
                            type="text"
                            class="form-control"
                            id=""
                            v-model="form.name"
                          />
                          <small class="text-danger" v-if="errors.name">{{
                            errors.name[0]
                          }}</small>
                        </div>
                      </div>
                    </div>
                    <div class="form-group">
                      <button type="submit" class="btn btn-primary btn-block">
                        Submit
                      </button>
                    </div>
                  </form>
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
export default {
  created() {
    if (!User.loggedIn()) {
      this.$router.push({ name: "/" });
    }
    let checkId = this.$route.params.id;
    if (checkId != 0) {
      this.getId = checkId;
      this.editForm();
      this.isNew = false;
    }
  },

  data() {
    return {
      form: {
        name: "",
      },
      user_info: {
        patientname: "",
        contactno: "",
        pk_pspatregisters: "",
      },
      getId: 0,
      isNew: true,
      errors: {},
    };
  },

  methods: {
    addEmployee() {
      if (this.isNew) {
        api
          .post("/doctors-add", this.form)
          .then((res) => {
            this.$router.push({ name: "doctors_list" });
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
      } else {
        api
          .post("/doctors-update", {
            data: this.form,
            id: this.getId,
          })
          .then((res) => {
            this.$router.push({ name: "doctors_list" });
            Toast.fire({
              icon: "success",
              title: "Updated successfully",
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
      }
    },
    editForm() {
      let id = this.$route.params.id;
      api
        .get("/api/doctors-detail/" + id)
        .then(({ data }) => (this.form.name = data.name))
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
</style>
