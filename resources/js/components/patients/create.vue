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
                  <h3 class="card-title">Add Patient</h3>
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
                          <label>Patient</label>
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
                      <div class="col-sm-4">
                        <div class="form-group">
                          <label>Date of Birth</label>
                          <datepicker
                            v-model="form.dob"
                            :bootstrap-styling="true"
                          ></datepicker>
                          <small class="text-danger" v-if="errors.dob">{{
                            errors.dob[0]
                          }}</small>
                        </div>
                      </div>
                      <div class="col-sm-4">
                        <div class="form-group">
                          <label>Gender</label>
                          <select class="form-control" v-model="form.sex">
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                          </select>
                          <small class="text-danger" v-if="errors.sex">{{
                            errors.sex[0]
                          }}</small>
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-sm-4">
                        <div class="form-group">
                          <label>Contact</label>
                          <input
                            type="text"
                            class="form-control"
                            id=""
                            v-model="form.contact"
                          />
                          <small class="text-danger" v-if="errors.contact">{{
                            errors.contact[0]
                          }}</small>
                        </div>
                      </div>
                      <div class="col-sm-4">
                        <div class="form-group">
                          <label>Attending Doctor</label>
                          <select class="form-control" v-model="form.doctor">
                            <option v-for="e in doctors" :value="e.id">
                              {{ e.name }}
                            </option>
                          </select>
                          <small class="text-danger" v-if="errors.doctor">{{
                            errors.doctor[0]
                          }}</small>
                        </div>
                      </div>
                      <div class="col-sm-4">
                        <div class="form-group">
                          <label>Status</label>
                          <select class="form-control" v-model="form.status">
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                          </select>
                          <small class="text-danger" v-if="errors.status">{{
                            errors.status[0]
                          }}</small>
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-sm-4">
                        <div class="form-group">
                          <label>Marital Status</label>
                          <select class="form-control" v-model="form.mstatus">
                            <option value="Single">Single</option>
                            <option value="Married">Married</option>
                            <option value="Widowed">Widowed</option>
                          </select>
                          <small class="text-danger" v-if="errors.mstatus">{{
                            errors.mstatus[0]
                          }}</small>
                        </div>
                      </div>
                      <div class="col-sm-4">
                        <div class="form-group">
                          <label>PHIC</label>
                          <input
                            type="text"
                            class="form-control"
                            id=""
                            v-model="form.phic"
                          />
                          <small class="text-danger" v-if="errors.phic">{{
                            errors.phic[0]
                          }}</small>
                        </div>
                      </div>
                      <div class="col-sm-2">
                        <div class="form-group">
                          <label>Type</label>
                          <input
                            type="text"
                            class="form-control"
                            id=""
                            v-model="form.ptype"
                          />
                          <small class="text-danger" v-if="errors.ptype">{{
                            errors.ptype[0]
                          }}</small>
                        </div>
                      </div>
                      <div class="col-sm-2">
                        <div class="form-group">
                          <label>Active</label>
                          <input
                            type="checkbox"
                            v-model="form.ptype"
                          />
                          <small class="text-danger" v-if="errors.ptype">{{
                            errors.ptype[0]
                          }}</small>
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-sm-12">
                        <div class="form-group">
                          <label>Address</label>
                          <textarea
                            class="form-control"
                            rows="3"
                            placeholder="Enter ..."
                            v-model="form.address"
                          ></textarea>
                          <small class="text-danger" v-if="errors.address">{{
                            errors.address[0]
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
import AppStorage from "../../Helpers/AppStorage";
import Datepicker from "vuejs-datepicker";
import api from "../../Helpers/api";

export default {
  components: {
    Datepicker,
  },
  created() {
    if (!User.loggedIn()) {
      this.$router.push({ name: "/" });
    }
    this.getDoctors();
    let checkId = this.$route.params.id;
    if (checkId != 0) {
      //this.getId = checkId;
      this.form.id = checkId;
      this.editForm();
      this.isNew = false;
    }
  },

  data() {
    return {
      doctors: [],
      form: {
        name: "",
        dob: "",
        sex: "",
        contact: "",
        doctor: "",
        status: "",
        address: "",
        mstatus: "",
        phic: "",
        ptype: "",
        id: "",
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
          .post("patients-add", this.form)
          .then((response) => {
            this.$router.push({ name: "patients_list" });
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
          .post("patients-update", this.form)
          .then((response) => {
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
        .get("patients-detail/" + id)
        .then((response) => {
          (this.form.name = response.data.name),
            (this.form.address = response.data.address),
            (this.form.sex = response.data.sex),
            (this.form.contact = response.data.contact_no),
            (this.form.doctor = response.data.attending_doctor),
            (this.form.dob = response.data.birthdate),
            (this.form.status = response.data.status),
            (this.form.mstatus = response.data.civil_status),
            (this.form.phic = response.data.phic),
            (this.form.ptype = response.data.patient_type);
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
          this.doctors = response.data;
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
</style>
