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
                <li class="breadcrumb-item active">Census</li>
              </ol>
            </div>
          </div>
        </div>
      </section>
      <section class="content">
        <div class="container-fluid">

          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Patient Census</h3>

            </div>

            <div class="card-body">

              <form class="user" enctype="multipart/form-data">
                <div class="row">
                  <div class="col-sm-2">
                    <div class="form-group ">
                      <label>From</label>
                      <datepicker name="date" required input-class="dpicker" v-model="filter.fdate"
                        :bootstrap-styling=true></datepicker>
                    </div>
                  </div>
                  <div class="col-sm-2">
                    <div class="form-group ">
                      <label>To</label>
                      <datepicker name="date" required input-class="dpicker" v-model="filter.tdate"
                        :bootstrap-styling=true></datepicker>
                    </div>
                  </div>
                  <div class="col-sm-2">
                    <div class="form-group ">
                      <label>Patient</label>
                      <patientComponent ref="patientVal" @return-response="getReturnResponse"></patientComponent>
                    </div>
                  </div>
                  <div class="col-sm-2">
                    <div class="form-check">
                      <label>ALL</label> <br>
                      <input type="checkbox" class="form-check-input" v-model="filter.isall">
                    </div>
                  </div>
                  <div class="col-sm-2">
                    <div class="form-group">
                      <label>&nbsp;</label> <br>
                      <button type="button" @click="showReport()" class="btn btn-info">
                        Filter
                      </button>
                    </div>
                  </div>
                </div>

                <dl class="row">
                  <dt class="col-sm-2">Month:</dt>
                  <dd class="col-sm-8">{{ month }}</dd>
                </dl>
                <dl class="row">
                  <dt class="col-sm-2">Doctor:</dt>
                  <dd class="col-sm-8">{{ filter.doctors != null && filter.doctors != 'All' ? getDoctor.name : '' }}</dd>
                </dl>
                <table class="table">
                  <thead>
                    <tr>
                      <th>Patient</th>
                      <th>Doctor</th>
                      <th @click="sortTable('dates')">Session Date</th>
                      <th>Action</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for=" e  in  sortedData ">
                      <td>
                        {{ e.name }}
                      </td>
                      <td>
                        {{ e.doctor }}
                      </td>
                      <td v-if="!filter.isall">
                        <button type="button" class="btn btn-xs btn-success" style="margin-right:5px;">
                          {{ e.dates }}
                        </button>
                      </td>
                      <td v-else>
                        <button type="button" @click="showModal = true; getId(d.id)"
                          :class="['btn', 'btn-xs', { 'btn-warning': d.status == 'UNPAID' }, { 'btn-success': d.status == 'PAID' }]"
                          style="margin-right:5px;" v-for=" d  in  e.datesArr ">
                          {{ d.date }}
                        </button>
                      </td>
                      <td>
                        <button type="button" class="btn btn-warning" @click="editSchedule(e.schedule_id,e.doctor_id,e.schedule,e.pid);showModal = true">Edit</button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </form>
            </div>
            <!-- /.card-body -->
          </div>
        </div>
        <!-- <phicModal v-if="showModal" @close="showModal = false" :sessionid="getsessionid.toString()"></phicModal> -->
        <addSessionModal v-if="showModal" @close="showModal = false" :sessionid="getScheduleId" :doctorId="doctorid" :scheduleDate="scheduledt" :patientid="pid"></addSessionModal>
      </section>
    </div>
    <footerComponent></footerComponent>
  </div>
</template>

<script type="text/javascript">
import Datepicker from 'vuejs-datepicker'
import moment from 'moment';
export default {
  created() {
    if (!User.loggedIn()) {
      this.$router.push({ name: '/' })
    }
  },
  components: {
    Datepicker,
  },
  data() {
    return {
      sortKey: 'date',
      columns: ['date'],
      reverse: false,
      getScheduleId: null,
      pid: null,
      doctorid: null,
      scheduledt: null,
      showModal: false,
      filter: {
        fdate: '',
        tdate: '',
        isall: false,
        patient: null,
      },
      results: [],
      month: null,
      patient_list: [],
      token: localStorage.getItem('token'),
      sortColumn: '',
      sortOrder: 'asc',
    }
  },
  computed: {
    total_sessions() {
      return this.results.reduce((sum, item) => sum + parseFloat(item.sessions), 0);
    },
    getDoctor() {
      return this.doctors_list.find(e => e.id == this.filter.doctors);
    },
    totalAmount() {
      return (this.total_sessions * 150).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",")
    },
    sortedData() {
      const column = this.sortColumn;
      const order = this.sortOrder === 'asc' ? 1 : -1;

      return this.results.slice().sort((a, b) => {
        if (a[column] < b[column]) return -1 * order;
        if (a[column] > b[column]) return 1 * order;
        return 0;
      });
    },
  },
  methods: {
    showReport() {
      this.filter.fdate = moment.utc(this.filter.fdate).utcOffset('+08:00').format();
      this.filter.tdate = moment.utc(this.filter.tdate).utcOffset('+08:00').format();
      api.post('census_px-report', this.filter)
        .then(response => {
          this.results = response.data
          Toast.fire({
            icon: 'success',
            title: 'Saved successfully'
          });
        }).catch(error => {
         if(error.response.data.message == 'Token has expired'){
          this.$router.push({ name: '/' });
          Toast.fire({
            icon: 'error',
            title: 'Token has expired'
          })
         }
      });
    },
    sortTable(column) {
      console.log(column)
      if (this.sortColumn === column) {
        this.sortOrder = this.sortOrder === 'asc' ? 'desc' : 'asc';
      } else {
        this.sortColumn = column;
        this.sortOrder = 'asc';
      }
    },
    getDoctors() {
      axios.get('/api/getDoctors')
        .then(({ data }) => (this.doctors_list = data))
        .catch()
    },
    getId(id) {
      this.getsessionid = id
    },
    getReturnResponse: function (id) {
      this.filter.patient = id.id
    },
    editSchedule(id,doctor,sd,p){
      this.getScheduleId = id;
      this.doctorid = doctor;
      this.scheduledt = sd;     
      this.pid = p;
    }
  }
}

</script>

<style>
.pull-right {
  float: right !important;
}

.dpicker {
  background-color: white !important;
}
</style>