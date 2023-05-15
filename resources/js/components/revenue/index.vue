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
                  <li class="breadcrumb-item active">Revenue Sharing</li>
                  </ol>
              </div>
              </div>
          </div>
          </section>
          <section class="content">
              <div class="container-fluid">
                  
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">Revenue Sharing Report</h3>
               
              </div>

              <div class="card-body">

                  <form class="user" enctype="multipart/form-data">
                          <div class="row">
                              <div class="col-sm-2">
                                  <div class="form-group ">
                                      <label>From Date</label>
                                      <datepicker name="date" required input-class ="dpicker" :minimumView="'month'" :maximumView="'month'" v-model="filter.fdate" :bootstrap-styling=true></datepicker>
                                  </div>
                              </div>
                              <div class="col-sm-2">
                                  <div class="form-group ">
                                      <label>To Date</label>
                                      <datepicker name="date" required input-class ="dpicker" :minimumView="'month'" :maximumView="'month'" v-model="filter.tdate" :bootstrap-styling=true></datepicker>
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
                          
              <table class="table table-bordered">
                  <thead>
                    <tr>
                      <th colspan="2"></th>
                      <th>PNCSI</th>
                      <th>PREMIER</th>
                      <th>TAX</th>
                      <th rowspan="2" style="text-align: center;">NET</th>
                      <th rowspan="2" style="text-align: center;">Total Paid</th>
                      <th rowspan="2" style="text-align: center;">Balance</th>
                    </tr>
                    <tr>
                      <th></th>
                      <th></th>
                      <th style="color: red;">2,250.00</th>
                      <th></th>
                      <th></th>
                    </tr>
                    <tr>
                      <th>MONTH</th>
                      <th>NO. OF SESSIONS</th>
                      <th>GROSS INCOM</th>
                      <th>SHARE(25%)</th>
                      <th>5%</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="e in results">
                      <td>
                        {{ e.month }}
                      </td>
                      <td>
                        {{ e.sessions }}
                      </td>
                      <td>
                        {{ e.gross.toFixed(2).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",") }}
                      </td>
                      <td>
                        {{ e.share.toFixed(2).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",") }}
                      </td>
                      <td>
                        {{ e.tax.toFixed(2).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",") }}
                      </td>
                      <td>
                        {{ e.net.toFixed(2).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",") }}
                      </td>
                      <td>
                        {{ e.total.toFixed(2).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",") }}
                      </td>
                      <td>
                        {{ e.balance.toFixed(2).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",") }}
                      </td>
                    </tr>
                  </tbody>
                </table>
                          
                      </form>
              </div>
              <!-- /.card-body -->
            </div>
                 
                      
              </div>
          </section>
      </div>
      <footerComponent></footerComponent>
  </div>
</template>

<script type="text/javascript">
import Datepicker from 'vuejs-datepicker'
import moment from 'moment';
  export default {
      created(){
          if(!User.loggedIn()){
              this.$router.push({name: '/'})
          }
   

          this.checkToken()
            this.getDoctors();
      },
      components: {
          Datepicker,
      },
      data() {
          return {                
              filter:{
                  fdate: '',
                  tdate: '',
              },
              results: [],
              month: null,
              doctors_list: [],
              token: localStorage.getItem('token'),
          }
      },
        computed: {
          total_sessions() {
                    return this.results.reduce((sum, item) => sum + parseFloat(item.sessions), 0);     
            },
            getDoctor(){
              return this.doctors_list.find(e => e.id == this.filter.doctors);
            },
            totalAmount(){
              return (this.total_sessions *150).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",")
            }
        },
      methods:{
          getCompany(){
              axios.get('/api/getCompanies')
                  .then(({ data }) => (
                      this.companies = data,
                      console.log(this.companies)              
              ))
              .catch(console.log('error'))
          },
          getPatientInformation(){
              axios.get('/api/getPxInfo/'+this.$route.params.id)
              .then(({data}) => ( this.user_info = data))
              .catch()
          },
          editForm(){                
              let id = this.$route.params.id
              axios.get('/api/getFormDetail/'+id)
                  .then(({ data }) => (
                  console.log("l "+data?data:0),
                      this.form.o2_stat = !Object.keys(data).length === 0 ? this.form.o2_stat : data.o2_stat,  
                      this.form.temp = !Object.keys(data).length === 0 ? this.form.temp : data.temp,             
                      this.form.rr = !Object.keys(data).length === 0 ? this.form.rr : data.rr,             
                      this.form.bp = !Object.keys(data).length === 0 ? this.form.bp : data.bp,             
                      this.form.weight = !Object.keys(data).length === 0 ? this.form.weight : data.weight,             
                      this.form.height = !Object.keys(data).length === 0 ? this.form.height : data.height,             
                      this.form.chiefcomplaints = !Object.keys(data).length === 0 ? this.form.chiefcomplaints : data.chiefcomplaints                                 
              ))
              .catch(console.log('error'))
          },
          clickedShowDetailModal: function (value) {
              this.getSelectdeProduct = value;            
              this.productList.product = this.getSelectdeProduct.product
              this.productList.description = this.getSelectdeProduct.description
              //this.productList.qty = this.getSelectdeProduct.qty
              this.productList.price = this.getSelectdeProduct.price
              //this.productList.price = this.getSelectdeProduct.price
              //this.productList.total = this.productList.qty * this.getSelectdeProduct.price
              this.productList.id = this.getSelectdeProduct.id
             /*  this.getSelectdeProduct.price = this.productList.price;        
              this.getSelectdeProduct.total = this.productList.price * this.productList.qty;          
              this.getSelectdeProduct.qty = this.productList.qty;       */    

              console.log(this.productList.qty)
             // this.$emit('update', this.getSelectdeProduct)  

          },
          calculateTotal(){
              this.productList.total = this.productList.price * this.productList.qty;
          },
          showReport(){                
            const headers = {
                    Authorization: "Bearer ".concat(this.token),
}
                axios.post('/api/revenue-report', {
                            data:this.filter,
                        } ,{
                        headers: headers
                      }
                        )
                        .then(res => {
                          this.results = res.data
                    /* this.results.forEach(e => {
                        this.total_sessions += parseFloat(e.sessions);
                    }) */
                    this.month = moment(this.filter.date).format('MMMM YYYY')
                            Toast.fire({
                                icon: 'success',
                                title: 'Saved successfully'
                            });
                        })
                        .catch(error => console.log(error))
          },
          checkToken(){                
            const headers = {
                    Authorization: "Bearer ".concat(this.token),
}
                axios.get('/api/validate', {
                        headers: headers
                      }
                        )
                        .then(res => {
                          
                        })
                        .catch(error => console.log(error))
          },
            getDoctors(){
                  axios.get('/api/getDoctors')
                  .then(({data}) => ( this.doctors_list = data))
                  .catch()
              },
      }
  }
  
</script>

<style>
.pull-right{
  float:right !important;
}
.dpicker{
  background-color: white !important;
}
</style>