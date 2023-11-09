<template>
  <div class="wrapper">
    <navComponent></navComponent>
    <sidemenuComponent></sidemenuComponent>
    <div class="content-wrapper">
      <section class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <h1>Ledger</h1>
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
                  <form class="form-inline">
                    <div class="form-group mx-sm-3 mb-2">
                      <companyComponent v-model="form.company" @return-response="getReturnResponse"></companyComponent>
                    </div>
                    <button type="button" class="btn btn-primary mb-2" @click="allEmployee()">View</button>
                  </form>
                </div>
                <div class="card-body">
                  <table class="table">
                    <thead>
                      <tr>
                        <th>Date</th>
                        <th>Reference</th>
                        <th>Particulars</th>
                        <th>Sold</th>
                        <th>Unit Price</th>
                        <th>Purchased</th>
                        <th>Payment</th>
                        <th>Balance</th>
                        <th>Check #</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="e in employees">
                        <td>
                          {{ e.dop }}
                        </td>
                        <td>
                          {{ e.reference }}
                        </td>
                        <td>
                          {{ e.remarks }} | {{ e.particulars }}
                        </td>
                        <td>
                          {{ e.sold }}
                        </td>
                        <td>
                          <strong> {{ e.price }}</strong>
                        </td>
                        <td>
                          <!-- <strong> {{ e.purchased }}</strong> -->
                          <strong> {{ e.total_purchase }}</strong>
                        </td>
                        <td>
                          <strong> {{ e.payment }}</strong>
                        </td>
                        <td>
                          {{ e.balance }}
                        </td>
                        
                        <!-- <td>
                          <strong> {{ e.price.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",") }}</strong>
                        </td>
                        <td>
                          <strong> {{ e.purchased.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",") }}</strong>
                        </td>
                        <td>
                          <strong> {{ e.payment.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",") }}</strong>
                        </td>
                        <td>
                          {{ e.balance.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",") }}
                        </td> -->

                        <td>
                          {{ e.check }}
                        </td>
                      </tr>
                    </tbody>
                  </table>
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
      this.$router.push({ name: '/' })
    }
    //this.allEmployee();
  },
  data() {

    return {
      hasError: false,
      isHidden: true,
      form: {
        company: null,
      },
      employees: [],
      searchTerm: '',
      countRecords: 0,
      token: localStorage.getItem('token'),
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
    /* allEmployee(){
      this.isHidden =  false
        axios.get('/api/rec_products')
        .then(({data}) => (
          this.employees = data[0].data ,
          this.countRecords =data[0].count,
          this.showing = data[0].showing,
      this.isHidden =  true
       ))
        .catch()
    }, */
    allEmployee() {
      this.isHidden = false
      api.get('rec_ledgers/'+this.form.company)
        .then(response => {
          console.log(response.data.data)
          this.employees = response.data.data,
            this.countRecords = response.data.count,
            this.showing = response.data.showing,
            this.isHidden = true
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
    viewLedger(){
      alert(this.form.company)
    },
    async check_doctors_detail(id) {
      return await axios.get('/api/check_doctors_detail/' + id)
        .then(response => {
          setTimeout(function () {
            return response.data;
          }, 3000);

        })
    },
    formatDate(date) {
      const options = { year: 'numeric', month: 'long', day: 'numeric' }
      return new Date(date).toLocaleDateString('en', options)
    },
    getReturnResponse: function (data) {
      this.form.company = data.id.id
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
