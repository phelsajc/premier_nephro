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
                    <li class="breadcrumb-item active">Collection Report</li>
                    </ol>
                </div>
                </div>
            </div>
            </section>
            <section class="content">
                <div class="container-fluid">
                    
              <div class="card">
                <div class="card-header">
                  <h3 class="card-title">Report</h3>
                 
                </div>

                <div class="card-body">

                    <form class="user" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-sm-2">
                                    <div class="form-group ">
                                        <label>Date</label>
                                        <datepicker name="date" required input-class ="dpicker" v-model="filter.date" :bootstrap-styling=true></datepicker>
                                    </div>
                                </div>
                                <div class="col-sm-2">
                                    <div class="form-group ">
                                        <label>Type</label>
                                        <select required class="form-control" v-model="filter.type" >
                                            <option value="BOTH">BOTH</option>
                                            <option value="Cash">CASH</option>
                                            <option value="Cheque">CHEQUE</option>
                                        </select>
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

                            <table class="table">
                    <thead>
                      <tr>
                        <th>Company</th>
                        <th>Type</th>
                        <th>CR No</th>
                        <th>DS No</th>
                        <th>Amount</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="e in results">
                        <td>
                          {{ e.company }}
                        </td>
                        <td>
                          {{ e.type }}
                        </td>
                        <td>
                          {{ e.crno }}
                        </td>
                        <td>
                         <strong> {{ e.dsno }}</strong>
                        </td>
                        <td>
                          <strong>  {{ e.amount }}</strong>
                        </td>
                      </tr>
                      <tr>
                        <td>
                          
                        </td>
                        <td>
                        
                        </td>
                        <td>
                        
                        </td>
                        <td>
                         <strong> Total: </strong>
                        </td>
                        <td>
                          <strong>  {{ grand_total }}</strong>
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
    export default {
        created(){
            if(!User.loggedIn()){
                this.$router.push({name: '/'})
            }
        },
        components: {
            Datepicker,
        },
        data() {
            return {                
                filter:{
                    date: '',
                    type: 'BOTH'
                },
                results: [],
                grand_total: 0,
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
                /* axios.get('/api/report')
                .then(({data}) => ( this.series = data))
                .catch() */
                axios.post('/api/collection-report', {
                    items:this.filter,
                })
                .then(res => {
                    this.grand_total = 0;
                    this.results = res.data
                    console.log(res.data)
                    Toast.fire({
                        icon: 'success',
                        title: 'Saved successfully'
                    });
                    this.results.forEach(e => {
                        this.grand_total += parseFloat(e.amount);
                    })
                })
                .catch(error => console.log(error))
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