<template>
  <transition name="modal">
    <div class="modal-mask">
      <div class="modal-wrapper">
        <div class="modal-container">
          <div class="modal-header">
            <slot name="header">Add Session</slot>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close" @click="$emit('close')">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <slot name="body">
              <form class="user" @submit.prevent="save" enctype="multipart/form-data">
                <div class="card-body">
                  <div class="form-group">
                    <label for="exampleInputEmail1">Patient <font color="red">*</font> </label>
                    <patientComponent ref="patientVal" @return-response="getReturnResponse"></patientComponent>
                  </div>
                  <div class="form-group">
                    <label for="exampleInputEmail1">Date <font color="red">*</font></label>
                    <datepicker name="date" v-model="form.schedule" input-class="dpicker" :bootstrap-styling=true>
                    </datepicker>
                  </div>
                  <div class="form-group">
                    <label for="exampleInputEmail1">Doctor In-Charge</label>
                    <select class="form-control" v-model="form.doctor">
                      <option selected value="0">None</option>
                      <option v-for="e in doctors" :value="e.id">{{ e.name }}</option>
                    </select>
                  </div>
                </div>
                <div class="card-footer">
                  <button type="submit" class="btn btn-primary">Submit</button>
                </div>
              </form>
            </slot>
          </div>
        </div>
      </div>
    </div>
  </transition>
</template> 
<script>
import Datepicker from 'vuejs-datepicker';
import api from '../../Helpers/api';
export default {
  /* props: {
      sessionid: {
            type: String,
            default: ''
        },
  }, */
  components: {
    Datepicker,
  },
  /* watch: {
    sessionid(v) {
      this.form.id = v
    },       
  }, */
  created() {
    this.getDoctors();
  },
  data() {
    return {
      form: {
        searchVal: null,
        schedule: null,
        doctor: 0,
        patientid: 0,
      },
      doctors: [],
      results: [],
      sessionDate: '',
    }
  },
  methods: {
    getDoctors() {
      api.get('getDoctors')
        .then(response => {
          this.doctors = response.data
        })
        .catch(error => {
          console.log(error);
        });
    },
    autoComplete() {
      this.results = [];
      if (this.form.searchVal.length >= 3) {
        api.post('patients-find', this.form)
          .then(response => {
            this.results = response.data
          })
          .catch(error => console.log(error))
      } else {
        this.form.patientid = 0
      }
    },
    getPatient(id) {
      this.form.patientid = id.id
      this.form.searchVal = id.name
      this.results = [];
    },
    save() {
      //console.log(this.$refs.patientVal)
      if (this.form.patientid != 0 && this.form.schedule != null) {
        api.post('schedule-add', this.form)
          .then(response => {
            //this.form.patientid = 0;
            this.form.schedule = null;
            this.form.doctor = null;
            //this.$refs.patientVal.form.searchVal = ''
            Toast.fire({
              icon: 'success',
              title: 'Saved successfully'
            });
            
            if(response.data){
              alert("Duplicate schedule. Cannot save record!")
            }
          })
          .catch(error => console.log(error))
      } else {
        Toast.fire({
          icon: 'error',
          title: 'Please check required fields.'
        });
      }
    },
    getReturnResponse: function (id) {
      this.form.patientid = id.id
    }
  },
}
</script>
<style scoped>
.container-iframe {
  position: relative;
  overflow: hidden;
  width: 100%;
  padding-top: 56.25%;
  /* 16:9 Aspect Ratio (divide 9 by 16 = 0.5625) */
}

/* Then style the iframe to fit in the container div with full height and width */
.responsive-iframe {
  position: absolute;
  top: 0;
  left: 0;
  bottom: 0;
  right: 0;
  width: 100%;
  height: 100%;
}

* {
  box-sizing: border-box;
}

.modal-mask {
  position: fixed;
  z-index: 9998;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  transition: opacity 0.3s ease;
  overflow-x: auto;
}

.modal-container {
  width: 75%;
  height: 100%;
  margin: 149px 309px;
  padding: 20px 30px;
  background-color: #fff;
  border-radius: 2px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.33);
  transition: all 0.3s ease;
}

.modal-body {
  margin: 20px 0;
}

/*
           * The following styles are auto-applied to elements with
           * transition="modal" when their visibility is toggled
           * by Vue.js.
           *
           * You can easily play with the modal transition by editing
           * these styles.
           */
.modal-enter {
  opacity: 0;
}

.modal-leave-active {
  opacity: 0;
}

.modal-enter .modal-container,
.modal-leave-active .modal-container {
  -webkit-transform: scale(1.1);
  transform: scale(1.1);
}
</style>
  