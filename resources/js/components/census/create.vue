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
                    <li class="breadcrumb-item active">Company</li>
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
                        <h3 class="card-title">Add Collection</h3>
                        <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                        </div>
                    </div>
                    <div class="card-body">            
                        
                        <form class="user" @submit.prevent="addCollection" enctype="multipart/form-data">
                            
                            <div class="row">
                                <div class="col-sm-2">
                                    <div class="form-group">
                                        <label>Type</label>                                        
                                        <select v-model="form.type" class="form-control" >
                                            <option value="Cash">Cash</option>
                                            <option value="Cheque">Cheque</option>
                                        </select>    
                                        <small class="text-danger" v-if="errors.type">{{ errors.type[0] }}</small>
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="form-group ">
                                        <label>Cheque Date</label>
                                        <datepicker name="chequeDate" v-model="form.chequeDate" input-class ="dpicker" :bootstrap-styling=true></datepicker>
                                        <small class="text-danger" v-if="errors.chequeDate">{{ errors.chequeDate[0] }}</small>
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label>Company</label>                                        
                                        <select v-model="form.company" class="form-control" >
                                            <option v-for="e in companies" :key="e.id" :value="e.id">{{e.company}}</option>
                                        </select>    
                                        <small class="text-danger" v-if="errors.company">{{ errors.company[0] }}</small>
                                    </div>
                                </div>
                                <div class="col-sm-2">
                                    <div class="form-group">
                                        <label>SI/DR No.</label>
                                        <input type="text" class="form-control" id="" placeholder="" v-model="form.sidr">
                                        <small class="text-danger" v-if="errors.sidr">{{ errors.sidr[0] }}</small>
                                    </div>
                                </div>
                                <div class="col-sm-2">
                                    <div class="form-group">
                                        <label>DS No.</label>
                                        <input type="text" class="form-control" id="" placeholder="" v-model="form.dsno">
                                        <small class="text-danger" v-if="errors.dsno">{{ errors.dsno[0] }}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-2">
                                    <div class="form-group">
                                        <label>Amount</label>     
                                        <input type="number" class="form-control" id="" placeholder="" v-model="form.amount">    
                                        <small class="text-danger" v-if="errors.amount">{{ errors.amount[0] }}</small>                                
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label>Details</label>     
                                        <input type="text" class="form-control" id="" placeholder="" v-model="form.details">    
                                        <small class="text-danger" v-if="errors.details">{{ errors.details[0] }}</small>                                
                                    </div>
                                </div>
                                <div class="col-sm-2">
                                    <div class="form-group">
                                        <label>WITH EWT Deductions</label>     
                                        <input type="number" class="form-control" id="" placeholder="" v-model="form.ewt">    
                                        <small class="text-danger" v-if="errors.ewt">{{ errors.ewt[0] }}</small>                                
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label>Date Deposited</label>     
                                        <datepicker name="birthdate" input-class ="dpicker" v-model="form.dateDeposited" class="dpicker" :bootstrap-styling=true></datepicker>
                                        <small class="text-danger" v-if="errors.dateDeposited">{{ errors.dateDeposited[0] }}</small>                                
                                    </div>
                                </div>
                                <div class="col-sm-2">
                                    <div class="form-group">
                                        <label>CR No.</label>     
                                        <input type="text" class="form-control" id="" placeholder="" v-model="form.crno">    
                                        <small class="text-danger" v-if="errors.desc">{{ errors.desc[0] }}</small>                                
                                    </div>
                                </div>
                            </div>                            
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary btn-block">Submit</button>
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
        created(){
            if(!User.loggedIn()){
                this.$router.push({name: '/'})
            }
            this.getCompany();
            let checkId = this.$route.params.id
            if(checkId!=0){
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
                companies: [],
                form: {
                    type: '',
                    chequeDate: '',
                    company: '',
                    sidr: '',
                    amount: 0,
                    details: '',
                    ewt: '',
                    dateDeposited: '',
                    crno: '',
                    dsno: '',
                    userid: User.user_id(),
                },
                user_info:{
                    patientname: '',
                    contactno: '',
                    pk_pspatregisters: '',
                },
                errors:{},
                getId: 0,
                isNew: true,
            }
        },

        methods:{
            addCollection(){
                if(this.isNew){
                    axios.post('/api/collection-add',this.form)
                    .then(res => {
                        this.$router.push({name: 'collection_list'});
                        Toast.fire({
                            icon: 'success',
                            title: 'Saved successfully'
                        })
                    })
                    .catch(error => this.errors = error.response.data.errors)
                }else{
                    axios.post('/api/collection-update',{
                        data: this.form,
                        id: this.getId
                    })
                    .then(res => {
                        Toast.fire({
                            icon: 'success',
                            title: 'Updated successfully'
                        });
                    })
                    .catch(error => this.errors = error.response.data.errors)
                }                
            },
            editForm(){                
                let id = this.$route.params.id
                axios.get('/api/collection-detail/'+id)
                    .then(({ data }) => (
                        this.form.type = data.type,  
                        this.form.chequeDate = data.check_date,             
                        this.form.company = data.companyid,             
                        this.form.sidr = data.si_dr_no,             
                        this.form.amount = data.amount,             
                        this.form.details = data.details,             
                        this.form.ewt = data.with_ewt_deductions ,      
                        this.form.dateDeposited = data.date_deposited  ,     
                        this.form.crno = data.crno  ,    
                        this.form.dsno = data.dsno                  
                ))
                .catch(console.log('error'))
            },            
            getCompany(){
                axios.get('/api/getCompanies')
                    .then(({ data }) => (
                        this.companies = data        
                ))
                .catch(console.log('error'))
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