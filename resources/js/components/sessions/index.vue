<template>
  <div class="wrapper">
    <navComponent></navComponent>
    <sidemenuComponent></sidemenuComponent>
    <div class="content-wrapper">
      <section class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <h1>Session</h1>
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
                  <button type="button" @click="showModal = true" class="btn btn-primary btn-sm pull-left">Add</button>
                  <div class="pull-right">
                    <input type="file" accept=".csv" @change="handleFileUpload($event)" />
                    <button type="button" @click="uploadCSV()" class="btn btn-info btn-sm">Upload</button>
                  </div>
                </div>
                <div class="card-body">
                  <div class="text-center" :class="{ 'd-none': isHidden }">
                    import on-progress...
                  </div><br>
                  <div class="d-flex justify-content-center">
                    <div class="spinner-border" role="status" :class="{ 'd-none': isHidden }">
                      <span class="sr-only">Loading...</span>
                    </div>
                  </div>

                  <ul class="list-group">
                    <input type="text" v-model="form.searchTerm2" @change="filterEmployee()" class="form-control to-right"
                      style="width: 100%" placeholder="Search user here" />
                    <li v-for="e in filtersearch" :key="e.id" class="list-group-item">
                      <div class="row">
                        <div class="col-6 float-left">
                          <div class="d-flex w-100 justify-content-between">
                            <h5 class="mb-1">
                              <strong>{{ e.name }} </strong>
                            </h5>
                          </div>
                          <span v-if='e.incharge_dctr != e.attending_dctr' class="badge badge-success">Main Doctor:
                            {{ e.attending_dctr }} <br></span>
                          <span v-if='e.incharge_dctr != e.attending_dctr' class="badge badge-warning">Doctor In-charge:
                            {{ e.incharge_dctr }} </span>
                          <span v-else class="badge badge-success">Doctor : {{ e.attending_dctr }}</span><br>
                          <span class="badge badge-info">Date : {{ e.date }}</span>
                        </div>
                        <div class="col-6 pull-right">
                          <div class="pull-right">
                            <button type="button" class="btn btn-danger" @click="delete_session(e.id)">
                              Delete
                            </button>
                          </div>
                        </div>
                      </div>
                    </li>
                  </ul>
                  <br />
                  <nav aria-label="Page navigation example" class="to-right">
                    <ul class="pagination">
                      <li class="page-item" v-for="(e, index) in this.countRecords">
                        <a class="page-link" @click="getPageNo(index + 1)" href="#">{{ index + 1 }}</a>
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
        <addSessionModal v-if="showModal" @close="showModal = false" :sessionid="0" v-on:close="todayPatient">
        </addSessionModal>
      </section>
    </div>
    <footerComponent></footerComponent>
  </div>
</template>

<script type="text/javascript">
import Datepicker from 'vuejs-datepicker'
import Papa from 'papaparse';
import api from '../../Helpers/api';
export default {
  components: {
    Datepicker
  },
  created() {
    if (!User.loggedIn()) {
      this.$router.push({ name: '/' })
    }
    //Notification.success()
    this.todayPatient();
    //this.me();
  },
  data() {
    return {
      file: '',
      content: [],
      parsed: false,
      date: null,
      name: null,
      hasError: false,
      isHidden: true,
      form: {
        searchTerm2: null,
        start: 0,
        //data:[],
      },
      showModal: false,
      employees: [],
      searchTerm: '',
      countRecords: 0,
      getdctr: '-',
      utype: User.user_type(),
      //token: localStorage.getItem('token'),
      showing: '',
    }
  },
  computed: {
    filtersearch() {
      return this.employees.filter(e => {
        return e.name.match(this.searchTerm)
      })
    },
  },
  methods: {
    todayPatient() {
      this.isHidden = false
      api.get('schedule')
        .then(response => {
          this.employees = response.data[0].data,
            this.countRecords = response.data[0].count,
            this.showing = response.data[0].showing,
            this.isHidden = true
        })
        .catch(error => {
          console.log(error);
        });
    },
    filterEmployee() {
      this.employees = []
      this.countRecords = null
      this.form.start = 0
      this.isHidden = false
      api.post('schedule', this.form)
        .then(response => {
          this.employees = response.data[0].data
          this.countRecords = response.data[0].count
          this.isHidden = true
        })
        .catch(error => this.errors = error.response.data.errors)
    },
    getPageNo(id) {
      this.form.start = (id - 1) * 10
      this.isHidden = false
      api.post('schedule', this.form)
        .then(response => {
          this.employees = response.data[0].data
          this.countRecords = response.data[0].count
          this.showing = response.data[0].showing,
            this.isHidden = true
        })
        .catch(error => this.errors = error.response.data.errors)
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
          this.content = results;
          this.form = results;
          this.parsed = true;
        }.bind(this)
      });
    },
    uploadCSV() {
      this.isHidden = false
      api.post('schedule-import', this.form)
        .then(response => {
          this.todayPatient();
          Toast.fire({
            icon: 'success',
            title: 'Imported successfully'
          });
          this.isHidden = true
        })
        .catch(error => console.log(error))

    },
    delete_session(id) {

      Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!'
      }).then((result) => {
        if (result.isConfirmed) {

          api.get('schedule-delete/' + id)
            .then(response => {
              this.todayPatient();
              Toast.fire({
                icon: 'success',
                title: 'Deleted successfully'
              });
            })
            .catch(error => {
              console.log(error);
            });
          Swal.fire(
            'Deleted!',
            'Your file has been deleted.',
            'success'
          )
        }
      });
    }
  },
}
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
