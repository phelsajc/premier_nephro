<template>
  <transition name="modal">
    <div class="modal-mask">
      <div class="modal-wrapper">
        <div class="modal-container">
          <div class="modal-header">
            <slot name="header">Update Status</slot>
            <button
              type="button"
              class="close"
              data-dismiss="modal"
              aria-label="Close"
              @click="$emit('close')"
            >
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <slot name="body">
              <form
                class="user"
                @submit.prevent="updatePhic"
                enctype="multipart/form-data"
              >
                <div class="card-body">
                  <div class="form-group">
                    <label for="exampleInputEmail1">Remarks</label>
                    <input
                      type="text"
                      class="form-control"
                      placeholder="Enter Desctiption"
                      v-model="form.remarks"
                    />
                    <input type="hidden" class="form-control" v-model="form.id" />
                  </div>
                  <div class="form-group">
                    <label for="exampleInputEmail1">ACPN</label>
                    <input
                      type="text"
                      class="form-control"
                      placeholder="Enter ACPN Numbere"
                      v-model="form.acpn"
                    />
                    <input type="hidden" class="form-control" v-model="form.acpn" />
                  </div>
                  <div class="form-check">
                    <input
                      type="checkbox"
                      class="form-check-input"
                      v-model="form.status"
                    />
                    <label class="form-check-label" for="exampleCheck1">Is Paid?</label>
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
import Datepicker from "vuejs-datepicker";

export default {
  props: {
    sessionid: {
      type: String,
      default: "",
    },
  },
  components: {
    Datepicker,
  },
  watch: {
    sessionid(v) {
      this.form.id = v;
    },
  },
  created() {
    this.getSessionData();
  },
  data() {
    return {
      token: localStorage.getItem("token"),
      form: {
        id: this.sessionid,
        remarks: "",
        acpn: "",
        status: false,
      },
    };
  },
  methods: {
    updatePhic() {
      const headers = {
        Authorization: "Bearer ".concat(this.token),
      };

      axios
        .post(
          "/api/phic-update",
          {
            data: this.form,
          },
          {
            headers: headers,
          }
        )
        .then((res) => {
          // this.results = res.data
          //this.month = moment(this.filter.date).format('MMMM YYYY')
          Toast.fire({
            icon: "success",
            title: "Saved successfully",
          });
          //close modal
          this.$emit('close');
        })
        .catch((error) => console.log(error));
    },
    id: function (value) {
      this.form.equipment_id_id = value.id;
      this.form.equipment_id_value = value.name;
    },
    showVendor: function (value) {
      console.log(value);
      this.form.vendor_id_id = value.id;
      this.form.vendor_id_value = value.name;
    },
    showDepartment: function (value) {
      this.form.dept_id_id = value.id;
      this.form.dept_id_value = value.name;
    },
    getSessionData() {
      const headers = {
        Authorization: "Bearer ".concat(this.token),
      };
      axios
        .get("/api/phic-edit/" + this.form.id, {
          headers: headers,
        })
        .then((res) => {
          console.log(res);
          this.form.status = res.data.status == "PAID" ? true : false;
          this.form.remarks = res.data.remarks;
          this.form.acpn = res.data.acpn_no;
          //alert(this.form.status)
        })
        .catch((error) => console.log(error));
    },
  },
};
</script>
<style scoped>
.container-iframe {
  position: relative;
  overflow: hidden;
  width: 100%;
  padding-top: 56.25%; /* 16:9 Aspect Ratio (divide 9 by 16 = 0.5625) */
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
