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
                    <li class="breadcrumb-item active">Users</li>
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
                        <h3 class="card-title">Add Users</h3>
                        <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                        </div>
                    </div>
                    <div class="card-body">            
                        
                        <form class="user" @submit.prevent="addEmployee" enctype="multipart/form-data">

                            <div class="form-group">
                                <div class="form-row">
                                
                                    <div class="col-md-12">
                                        <h4>Name</h4>
                                        <input type="text" class="form-control" id="" placeholder="Enter Fullname" v-model="form.name">
                                        <small class="text-danger" v-if="errors.name">{{ errors.name[0] }}</small>
                                    </div>
                                </div>
                            </div>


                            <div class="form-group">
                                <div class="form-row">
                                    <div class="col-md-12">
                                        <h4>Type</h4>
                                        <!-- <input type="text" class="form-control" id="" placeholder="Enter Fullname" v-model="form.name"> -->
                                        <select  class="form-control" v-model="form.type">
                                            <option value="Staff">Staff</option>
                                            <option value="Doctor">Doctor</option>
                                            <option value="Administrator">Administrator</option>
                                        </select>
                                        <small class="text-danger" v-if="errors.type">{{ errors.type[0] }}</small>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="form-row">
                                    <div class="col-md-12">
                                        <h4>PRC</h4>
                                        <input type="text" class="form-control" id="" placeholder="Enter PRC" v-model="form.prc">
                                        <small class="text-danger" v-if="errors.prc">{{ errors.prc[0] }}</small>
                                    </div>
                                </div>
                            </div>
                            

                            <div class="form-group">
                                <div class="form-row">
                                    <div class="col-md-12">
                                        <h4>Specialization</h4>
                                        <input type="text" class="form-control" id="" placeholder="Enter Specialization" v-model="form.specialization">
                                        <small class="text-danger" v-if="errors.specialization">{{ errors.specialization[0] }}</small>
                                    </div>
                                </div>
                            </div>


                            <div class="form-group">
                                <div class="form-row">
                                    <div class="col-md-12">
                                        <h4>Username</h4>
                                        <input type="text" class="form-control" id="" placeholder="Enter User" v-model="form.username">
                                        <small class="text-danger" v-if="errors.username">{{ errors.username[0] }}</small>
                                    </div>
                                </div>
                            </div>


                            <div class="form-group">
                                <div class="form-row">
                                    <div class="col-md-12">
                                        <h4>Password</h4>
                                        <input type="password" class="form-control" id="" placeholder="Enter Password" v-model="form.password">
                                        <small class="text-danger" v-if="errors.password">{{ errors.password[0] }}</small>
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
import AppStorage from '../../Helpers/AppStorage';


    export default {
        created(){
            if(!User.loggedIn()){
                this.$router.push({name: '/'})
            }
            this.getPatientInformation();
            this.editForm();
        },

        data() {
            return {
                form: {
                    name: '',
                    username: '',
                    password: '',
                    type: '',
                    id:'',
                    prc: '',
                    specialization: ''
                },
                user_info:{
                    patientname: '',
                    contactno: '',
                    pk_pspatregisters: '',
                },
                errors:{}
            }
        },

        methods:{
            addEmployee(){
                axios.post('/api/addusers',this.form)
                .then(res => {
                    this.$router.push({name: 'userslist'});
                    Notification.success()
                })
                .catch(error => this.errors = error.response.data.errors)
            },
            onFileSelected(event){
                let file = event.target.files[0];
                if(file.size > 1048770){
                    Notification.image_Validation()
                    console.log(1)
                }else{
                    let reader = new FileReader();
                    reader.onload = event => {
                        this.form.newphoto =  event.target.result
                    };
                    reader.readAsDataURL(file)
                }

            },
            addInitialdata(){
                axios.post('/api/saveInitialData',this.form)
                .then(res => {
                    Notification.success()
                    Toast.fire({
                        icon: 'success',
                        title: 'Saved successfully'
                    })
                    this.$router.push({name: 'all_employee'});
                })
                .catch(error => this.errors = error.response.data.errors)
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
            }
            
        }
    }
    
</script>

<style>
 .pull-right{
    float:right !important;
 }
</style>