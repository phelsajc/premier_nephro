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
                    <label for="exampleInputEmail1">Batch</label>
                    <select name="" id="" class="form-control" v-model="form.remarks">
                      <option v-for="e in batches" :value="e.batch">{{ e.batch }}</option>
                    </select>
                    <input type="hidden" class="form-control" v-model="form.id" />
                  </div>
                  <div class="form-group">
                    <label for="exampleInputEmail1">ACPN</label>
                    <input
                      type="text"
                      class="form-control"
                      placeholder="Enter ACPN Number"
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
                  <div class="form-check">
                    <input
                      type="checkbox"
                      class="form-check-input"
                      v-model="form.iscash"
                    />
                    <label class="form-check-label" for="exampleCheck1">Is Cash?</label>
                  </div>
                  <div class="form-group" v-if="form.iscash">
                    <label for="exampleInputEmail1">Cash</label>
                    <input
                      type="text"
                      class="form-control"
                      placeholder="Enter Cash"
                      v-model="form.cash"
                    />
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
import api from "../../Helpers/api";

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
    this.getBatches();
    this.getSessionData();
  },
  data() {
    return {
      batches: [],
      token: localStorage.getItem("token"),
      form: {
        id: this.sessionid,
        remarks: "",
        acpn: "",
        status: false,
        iscash: false,
        cash: 0,
      },
    };
  },
  methods: {
    updatePhic() {
      api.post("phic-update", this.form)
        .then((res) => {
          Toast.fire({
            icon: "success",
            title: "Saved successfully",
          });
          //close modal
          this.$emit("close");
        })
        .catch((error) => console.log(error));
    },
    getSessionData() {
      api
        .get("phic-edit/" + this.form.id)
        .then((response) => {
          console.log(response);
          this.form.status = response.data.status == "PAID" ? true : false;
          this.form.iscash = response.data.iscash;
          this.form.remarks = response.data.remarks;
          this.form.acpn = response.data.acpn_no;
          this.form.cash = response.data.cash;
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

      /* const headers = {
        Authorization: "Bearer ".concat(this.token),
      };
      api
        .get("/phic-edit/" + this.form.id, {
          headers: headers,
        })
        .then((res) => {
          console.log(res);
          this.form.status = res.data.status == "PAID" ? true : false;
          this.form.remarks = res.data.remarks;
          this.form.acpn = res.data.acpn_no;
        }).catch(error => {
          if (error.response.data.message == 'Token has expired') {
            this.$router.push({ name: '/' });
            Toast.fire({
              icon: 'error',
              title: 'Token has expired'
            })
          }
        }); */
    },
    getBatches() {
      api
        .get("/get-batches")
        .then((response) => {
          this.batches = response.data;
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
