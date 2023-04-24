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
                    <li class="breadcrumb-item active">Products</li>
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
                        <h3 class="card-title">Add Product</h3>
                        <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                        </div>
                    </div>
                    <div class="card-body">            
                        
                        <form class="user" @submit.prevent="addProduct" enctype="multipart/form-data">
                            
                            <div class="row">
                                <div class="col-sm-6">
                                <div class="form-group">
                                    <label>Product</label>
                                    <input type="text" class="form-control" id="" placeholder="Enter Product name" v-model="form.name">
                                    <small class="text-danger" v-if="errors.name">{{ errors.name[0] }}</small>
                                </div>
                                </div>
                                <div class="col-sm-6">
                                <div class="form-group">
                                    <label>Description</label>
                                    <input type="text" class="form-control" id="" placeholder="Enter Product Description" v-model="form.desc">
                                    <small class="text-danger" v-if="errors.desc">{{ errors.desc[0] }}</small>
                                </div>
                                </div>
                            </div>
                            <div class="row">
                                <!-- <div class="col-sm-6">
                                    <div class="form-group">
                                        <label>Quantity</label>
                                        <input type="text" class="form-control" id="" placeholder="Enter Product Quantity" v-model="form.qty">
                                        <small class="text-danger" v-if="errors.qty">{{ errors.qty[0] }}</small>
                                    </div>
                                </div> -->
                                <!-- <div class="col-sm-6">
                                    <div class="form-group">
                                        <label>UOM</label>
                                        <select  class="form-control" v-model="form.uom">
                                            <option value="PCS">PCS</option>
                                            <option value="KG">KG</option>
                                            <option value="LT">LT</option>
                                        </select>
                                        <small class="text-danger" v-if="errors.uom">{{ errors.uom[0] }}</small>
                                    </div>
                                </div> -->
                            </div>
                            <div class="row">
                                <!-- <div class="col-sm-4">
                                    <div class="form-group">
                                        <label>Date Purchase</label>
                                        <datepicker name="dop" v-model="form.dop" :bootstrap-styling=true></datepicker>
                                        <small class="text-danger" v-if="errors.dop">{{ errors.dop[0] }}</small>
                                    </div>
                                </div> -->
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label>UOM</label>
                                        <select  class="form-control" v-model="form.uom">
                                            <option value="PFS">PFS</option>
                                            <option value="BXS">BXS</option>
                                            <option value="BOT">BOT</option>
                                        </select>
                                        <small class="text-danger" v-if="errors.uom">{{ errors.uom[0] }}</small>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label>Price </label>
                                        <input type="number" class="form-control" id="" placeholder="Enter Product Price" v-model="form.price">
                                        <small class="text-danger" v-if="errors.price">{{ errors.price[0] }}</small>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label>Code </label>
                                        <input type="text" class="form-control" id="" placeholder="Enter Product Code" v-model="form.code">
                                        <small class="text-danger" v-if="errors.code">{{ errors.code[0] }}</small>
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
                form: {
                    name: null,
                    desc: null,
                    uom: null,
                    code: null,
                    price: null,
                },
                user_info:{
                    patientname: '',
                    contactno: '',
                    pk_pspatregisters: '',
                },
                getId: 0,
                isNew: true,
                errors:{}
            }
        },

        methods:{
            addProduct(){
                if(this.form.name==null||this.form.desc==null||this.form.uom==null||this.form.code==null||this.form.price==null){
                        Toast.fire({
                            icon: 'error',
                            title: 'Please provide quantity'
                        });
                }else{
                    if(this.isNew){
                        axios.post('/api/products-add',this.form)
                        .then(res => {
                            this.$router.push({name: 'product_list'});
                            Toast.fire({
                                icon: 'success',
                                title: 'Saved successfully'
                            });
                        })
                        .catch(error => this.errors = error.response.data.errors)
                    }else{
                        axios.post('/api/products-update',{
                            data: this.form,
                            id: this.getId
                        })
                        .then(res => {
                            Toast.fire({
                                icon: 'success',
                                title: 'Saved successfully'
                            });
                        })
                        .catch(error => this.errors = error.response.data.errors)
                    }
                }
            },      
            editForm(){                
                let id = this.$route.params.id
                axios.get('/api/products-detail/'+id)
                    .then(({ data }) => (
                        this.form.name = data.product,  
                        this.form.desc =  data.description,             
                        //this.form.qty =  data.quantity,             
                        this.form.uom =  data.uom,             
                        //this.form.dop =  data.dop,             
                        this.form.code =  data.code,             
                        this.form.price =  data.price                                              
                ))
                .catch(error => this.errors = console.log(error.response.data.errors))
            }            
        }
    }
    
</script>

<style>
 .pull-right{
    float:right !important;
 }
</style>