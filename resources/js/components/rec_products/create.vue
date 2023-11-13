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
                <li class="breadcrumb-item active">Received Products</li>
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
                  <h3 class="card-title">Receive Product</h3>
                  <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                      <i class="fas fa-minus"></i>
                    </button>
                  </div>
                </div>
                <div class="card-body">
                  <form class="user" @submit.prevent="addProduct" enctype="multipart/form-data">
                    <div class="row">
                      <div class="col-sm-2">
                        <div class="form-group">
                          <label>Product</label>
                          <select class="form-control" v-model="form.pid" @change="selectProduct($event)">
                            <option v-for="e in products" :key="e.id" :value="e.id">
                              {{ e.product }}
                            </option>
                          </select>
                          <small class="text-danger" v-if="errors.name">{{ errors.name[0] }}</small>
                        </div>
                      </div>
                      <div class="col-sm-1">
                        <div class="form-group">
                          <label>Quantity</label>
                          <input type="text" class="form-control" id="" placeholder="Quantity" v-model="form.qty" />
                          <small class="text-danger" v-if="errors.qty">{{ errors.qty[0] }}</small>
                        </div>
                      </div>
                      <div class="col-sm-1">
                        <div class="form-group">
                          <label>Free</label>
                          <input type="text" class="form-control" id="" placeholder="Quantity" v-model="form.free" />
                          <small class="text-danger" v-if="errors.qty">{{ errors.qty[0] }}</small>
                        </div>
                      </div>
                      <div class="col-sm-2">
                        <div class="form-group">
                          <label>Particulars</label>
                          <input type="text" class="form-control" id="" placeholder="Particulars"
                            v-model="form.particulars" />
                          <small class="text-danger" v-if="errors.particulars">{{ errors.particulars[0] }}</small>
                        </div>
                      </div>
                      <div class="col-sm-2">
                        <div class="form-group">
                          <label>Reference</label>
                          <input type="text" class="form-control" id="" placeholder="Reference"
                            v-model="form.referenceNo" />
                          <small class="text-danger" v-if="errors.referenceNo">{{ errors.referenceNo[0] }}</small>
                        </div>
                      </div>
                      <div class="col-sm-2">
                        <div class="form-group">
                          <label>Company</label>
                          <companyComponent v-model="form.company" @return-response="getReturnResponse"></companyComponent>
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-sm-2">
                        <div class="form-group">
                          <label>Amount Purchase </label>
                          <input type="number" @change="calculatePrice()" class="form-control" id=""
                            placeholder="Amount Purchase" v-model="form.purchase" />
                          <small class="text-danger" v-if="errors.purchase">{{ errors.purchase[0] }}</small>
                        </div>
                      </div>
                      <div class="col-sm-2">
                        <div class="form-group date-bg">
                          <label>Date Purchase</label>
                          <datepicker name="dop" class="date-bg" v-model="form.dop" :bootstrap-styling="true">
                          </datepicker>
                          <small class="text-danger" v-if="errors.dop">{{ errors.dop[0] }}</small>
                        </div>
                      </div>
                      <div class="col-sm-2">
                        <div class="form-group">
                          <label>Unit Price </label>
                          <input type="text" class="form-control" id="" placeholder="Product Price"
                            v-model="form.price" />
                          <small class="text-danger" v-if="errors.price">{{ errors.price[0] }}</small>
                        </div>
                      </div>
                      <div class="col-sm-2">
                        <div class="form-group">
                          <label>Current Balance </label>
                          <input type="text" class="form-control" readonly id="" placeholder="Balance"
                            v-model="form.balance" />
                          <small class="text-danger" v-if="errors.code">{{ errors.code[0] }}</small>
                        </div>
                      </div>
                    </div>

                    <div class="form-group">
                      <button type="submit" class="btn btn-primary btn-block">
                        Save
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
import Datepicker from 'vuejs-datepicker'


export default {
  created() {
    if (!User.loggedIn()) {
      this.$router.push({ name: '/' })
    }
    this.getProducts();
    this.getLastBalance();
    let checkId = this.$route.params.id
    if (checkId != 0) {
      this.getId = checkId;
      this.editForm();
      this.isNew = false;
    }
  },
  components: {
    Datepicker
  },
  data() {
    return {
      form: {
        pid: '',
        particulars: '',
        qty: 0,
        free: 0,
        purchase: '',
        payment: 0,
        dop: '',
        balance: 0,
        price: 0,
        referenceNo: '',
        company: '',
      },
      user_info: {
        patientname: '',
        contactno: '',
        pk_pspatregisters: '',
      },
      errors: {},
      products: [],
      getId: 0,
      isNew: true,
    }
  },
  computed: {
    unitpriceCalc() {
      return this.form.price;
    },
    balanceCalc() {
      return this.form.balance;
    }
  },
  methods: {
    addProduct() {
      api.post('rec_products-add', this.form)
        .then(response => {
          this.$router.push({ name: 'ledger' });
          Toast.fire({
            icon: 'success',
            title: 'Saved successfully'
          });
        }).catch(error => {
          if (error.response.data.message == 'Token has expired') {
            this.$router.push({ name: '/' });
            Toast.fire({
              icon: 'error',
              title: 'Token has expired'
            })
          }
        });
    },
    calculatePrice() {
      this.form.price = this.form.purchase / this.form.qty
      this.form.balance = 0
      this.form.balance = (this.form.balance + this.form.purchase) - this.form.payment
    },
    editForm() {
      let id = this.$route.params.id
      api.get('rec_products-detail/' + id)
        .then(({ data }) => (
          this.form.pid = data.pid,
          this.form.desc = data.description,
          this.form.qty = data.quantity,
          this.form.uom = data.uom,
          this.form.dop = data.date_receive,
          this.form.code = data.code,
          this.form.price = data.price
        ))
        .catch(console.log('error'))
    },
    getProducts() {
      api.get('getProducts')
        .then(({ data }) => (this.products = data))
        .catch()
    },
    getLastBalance() {
      api.get('getLastBalance')
        .then(data => {
          if (data.data.length != 0) {
            this.form.balance = data.balance
          }
        })
        .catch()
    },
    selectProduct(e) {
      api.get('products-detail/' + e.target.value)
        .then(({ data }) => (
          this.form.uom = data.uom,
          this.form.price = data.price,
          this.form.code = data.code
        ))
        .catch(console.log('error'))
    },
    getReturnResponse: function (data) {
      this.form.company = data.id.id
    }
  }
}
</script>
  
<style>
.pull-right {
  float: right !important;
}

.date-bg .vdp-datepicker {
  background-color: #ffffff !important;
}
</style>
  