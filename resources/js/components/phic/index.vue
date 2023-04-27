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
                  <li class="breadcrumb-item active">CoPay</li>
                  </ol>
              </div>
              </div>
          </div>
          </section>
          <section class="content">
              <div class="container-fluid">
                  
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">PHIC Report</h3>
               
              </div>

              <div class="card-body">

                  <form class="user" enctype="multipart/form-data">
                          <div class="row">
                              <div class="col-sm-2">
                                  <div class="form-group ">
                                      <label>Date</label>
                                      <datepicker name="date" required input-class ="dpicker" :minimumView="'month'" :maximumView="'month'" v-model="filter.date" :bootstrap-styling=true></datepicker>
                                  </div>
                              </div>
                              <div class="col-sm-2">
                                  <div class="form-group ">
                                      <label>Doctor</label>                                    
                                      <select class="form-control" v-model="filter.doctors">
                                        <option value="All">All for the month</option>
                                            <option v-for="e in doctors_list" :value="e.id">{{e.name}}</option>
                                        </select> </div>
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
                  <dt class="col-sm-2">Total Session:</dt>
                  <dd class="col-sm-8">{{total_sessions}}</dd>
                </dl>
                <dl class="row">
                  <dt class="col-sm-2">Total Amount Due:</dt>
                  <dd class="col-sm-8">{{ totalAmount }}</dd>
                </dl>
                <dl class="row">
                  <dt class="col-sm-2">Total PAID:</dt>
                  <dd class="col-sm-8">{{ totalAmountPaid }}</dd>
                </dl>
                <dl class="row">
                  <dt class="col-sm-2">Month:</dt>
                  <dd class="col-sm-8">{{ month }}</dd>
                </dl>
                <dl class="row">
                  <dt class="col-sm-2">Doctor:</dt>
                  <dd class="col-sm-8">{{ filter.doctors!=null&&filter.doctors!='All'?getDoctor.name:'' }}</dd>
                </dl>
              <table class="table">
                  <thead>
                    <tr>
                      <th>Patient</th>
                      <th>Sessions</th>
                      <th>Date</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="e in results">
                      <td>
                        {{ e.name }}
                      </td>
                      <td>
                        {{ e.sessions }}
                      </td>
                      <td>
                        <button type="button" @click="showModal = true;getId(d.id)" :class="['btn', 'btn-xs',{'btn-warning':d.status=='UNPAID'},{'btn-success':d.status=='PAID'}]" style="margin-right:5px;" v-for="d in e.datesArr">
                          {{d.date}}
                        </button> 
                      </td>
                    </tr>
                  </tbody>
                </table>
                      </form>
              </div>
              <!-- /.card-body -->
            </div>  
              </div>
              <phicModal  v-if="showModal" @close="showModal = false" :sessionid="getsessionid.toString()"></phicModal>
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
              showModal: false,           
              filter:{
                  date: '',
                  doctors: null,
                  type: 'BOTH'
              },
              results: [],
              getsessionid: '',
              month: null,
              doctors_list: [],
              getTotalPaidClaims: 0,
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
              return (this.total_sessions *350).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",")
            },
            totalAmountPaid(){
              return (this.getTotalPaidClaims *350).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",")
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
                axios.post('/api/phic-report', {
                            data:this.filter,
                        } ,{
                        headers: headers
                      }
                        )
                        .then(res => {
                          console.log(res.data)
                          this.getTotalPaidClaims = res.data.getPaidClaims
                          this.results = res.data.data
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

              getId(id){
                this.getsessionid = id
              }
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