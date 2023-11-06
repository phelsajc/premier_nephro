<template>
  <div class="wrapper">
    <navComponent></navComponent>
    <sidemenuComponent></sidemenuComponent>
    <div class="content-wrapper">
      <section class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <h1>Stocks Inventory</h1>
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
                  <!-- <products class="col-md-3"></products> -->
                <form class="form-inline">
                    <div class="form-group mx-sm-3 mb-2">
                      <productComponent v-model="form.product" @return-response="getReturnResponse"></productComponent>
                    </div>
                    <button type="button" class="btn btn-primary mb-2" @click="stockInventory()">View</button>
                  </form>
                  
                </div>

                
                <div class="card-body">

                  <table class="table">
                    <thead>
                      <tr>
                        <th>Date</th>
                        <th>Particulars</th>
                        <!-- <th>Beginning Balance</th> -->
                        <th>Out</th>
                        <th>Balance</th>
                        <th>Cost/Vial</th>
                        <th>Amount</th>
                        <th>Balance</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="e in items">
                        <td>
                          {{ e.dop }}
                        </td>
                        <td>
                          {{ e.particulars }}
                        </td>
                        <td>
                          {{ e.sold }}
                        </td>
                        <td>
                          <strong> {{ e.balance }}</strong>
                        </td>
                        <td>
                          <strong  v-if="e.sold==0"> {{ e.cost }}</strong>
                        </td>
                        <td>
                          <!-- <strong> {{ e.amount.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",") }}</strong> -->
                          <strong> {{ e.amount }}</strong>
                        </td>
                        <td>
                          <!-- {{ e.amount_balance.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",") }} -->
                          {{ e.amount_balance }}
                        </td>
                        <!-- <td>
                          {{ e.total }}
                        </td> -->
                      </tr>
                    </tbody>
                  </table>



                  <nav aria-label="Page navigation example" class="">
                    {{ showing }}
                  </nav>
                </div>
                <!-- /.card-body -->
              </div>
              <!-- /.card -->

              <!-- /.card -->
            </div>
            <!-- /.col -->
          </div>
          <!-- /.row -->
        </div>
        <!-- /.container-fluid -->
      </section>
    </div>
    <footerComponent></footerComponent>
  </div>
</template>

<script type="text/javascript">

export default {
  created() {
    if (!User.loggedIn()) {
      this.$router.push({ name: '/' })
    }

    //this.stockInventory()
  },
  data() {

    return {
      form:{
        
        product: null,
      },
      items: [],
      
      productList: {
                product: '',
                description: '',
                qty: 0,
                code: '',
                price: 0,
                total: 0,
            },
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
    stockInventory() {
      this.isHidden = false
      /* api.get('/rec_inventory/'+this.form.product)
        .then(({ data }) => (
          this.items = data.data
        )).catch(error => {
          if (error.response.data.message == 'Token has expired') {
            this.$router.push({ name: '/' });
            Toast.fire({
              icon: 'error',
              title: 'Token has expired'
            })
          }
        }); */

        
        api
          .post("rec_inventory", this.form)
          .then((response) => {
          this.items = response.data.data
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
                title: "Token has expired.",
              });
            }
          });
    },
    pdf() {
      window.open("/api/pdf", '_blank');
    },
    formatDate(date) {
      const options = { year: 'numeric', month: 'long', day: 'numeric' }
      return new Date(date).toLocaleDateString('en', options)
    },
    filterEmployee() {
      this.employees = []
      this.countRecords = null
      this.form.start = 0
      this.isHidden = false
      //axios.post('/api/filterEmployee',this.form)
      axios.post('/api/products', this.form)

        .then(res => {
          this.employees = res.data[0].data
          this.countRecords = res.data[0].count
          console.log(res.data.data)
          this.isHidden = true
        })
        .catch(error => this.errors = error.response.data.errors)
    },
    getPageNo(id) {
      this.form.start = (id - 1) * 10
      this.isHidden = false
      //alert(a)
      /* this.employees = []
      this.countRecords = null */
      //axios.post('/api/filterEmployee',this.form)
      console.log(this.isHidden)
      axios.post('/api/products', this.form)
        .then(res => {
          this.employees = res.data[0].data
          this.countRecords = res.data[0].count
          this.showing = res.data[0].showing,
            console.log(res.data[0])
          this.isHidden = true
          console.log(this.isHidden)
        })
        .catch(error => this.errors = error.response.data.errors)
    },
    
    getReturnResponse: function (data) {
      this.form.product = data.id.id
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
