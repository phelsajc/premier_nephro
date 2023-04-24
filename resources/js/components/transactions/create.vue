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
                    <li class="breadcrumb-item active">Employee</li>
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
                            <h3 class="card-title">CHARGE SALES INVOICE</h3>
                            <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                            </div>
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
                                        <label>Date</label>
                                        <input type="text" class="form-control" id="" v-model="form.name">
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
                        <th>Qty</th>
                        <th>Price</th>
                        <th>Invoice No.</th>
                        <th>Total</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="e in results">
                        <td>
                          {{ e.company }}
                        </td>
                        <td>
                          {{ e.qty }}
                        </td>
                        <td>
                          {{ e.price }}
                        </td>
                        <td>
                          {{ e.inv }}
                        </td>
                        <td>
                          {{ e.sales }}
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
                        
                        </td>
                        <td>
                            <strong> Total:   {{ grand_total }}</strong>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                            
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


<script>
import Papa from 'papaparse';

export default {
  data() {
    return {
        file: '',
        content: [],
        parsed: false
    };
  },
  methods: {
    handleFileUpload( event ){
        this.file = event.target.files[0];
        this.parseFile();
    },
    parseFile(){
        Papa.parse( this.file, {
            header: true,
            skipEmptyLines: true,
            complete: function( results ){
                this.content = results;
                this.parsed = true;
            }.bind(this)
        } );
    },
    test(){
        console.log("this.content")
        console.log(this.content.data)
        axios.post('/api/import', {
                    data:this.content.data,
                })
                .then(res => {
                    Toast.fire({
                        icon: 'success',
                        title: 'Saved successfully'
                    });
                })
                .catch(error => console.log(error))
    }
  }
};
</script>
<style>
 .pull-right{
    float:right !important;
 }
</style>