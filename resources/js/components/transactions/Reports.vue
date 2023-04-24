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
                    
                    <form class="user" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-sm-3">
                                    <div class="form-group ">
                                        <label>From</label>
                                        <datepicker name="birthdate" input-class ="dpicker" :minimumView="'month'" :maximumView="'month'" v-model="filter.from" :bootstrap-styling=true></datepicker>                                      
                                    </div>
       
                                </div>
                                <div class="col-sm-3">
                                    <div class="form-group ">
                                        <label>To</label>
                                        <datepicker name="birthdate" input-class ="dpicker" :minimumView="'month'" :maximumView="'month'" v-model="filter.to" :bootstrap-styling=true></datepicker>
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

                            
                            
                        </form>
                    <div id="chart">
                        <apexchart ref="radar"  type="line" height="350" :options="chartOptions" :series="series"></apexchart>
                    </div>
                </div>
            </section>
        </div>
        <footerComponent></footerComponent>
    </div>
</template>

<script type="text/javascript">
import Datepicker from 'vuejs-datepicker'
import ApexCharts from 'apexcharts'
import VueApexCharts from 'vue-apexcharts'
    export default {
        created(){
            if(!User.loggedIn()){
                this.$router.push({name: '/'})
            }
        },
        components: {
            Datepicker,
            apexchart: VueApexCharts,
        },
        data() {
            return {
               /*  series: [
            {
              name: "High - 2013",
              data: [28, 29, 33, 36, 32, 32, 33]
            },
            {
              name: "Low - 2013",
              data: [12, 11, 14, 18, 17, 13, 13]
            }
          ], */
         /*  series : [{
                    name: "Grace",
                    data: [10, 41, 35, 51, 49, 62, 69, 91, 33]
                },{
                    name: "Mercury",
                    data: [14, 51, 45, 61, 59, 72, 79, 101, 31]
                }], */
                filter:{
                    from: '',
                    to: '',
                },
                series : [],
                chartOptions: {
                    chart: {
                        height: 350,
                        type: 'line',
                        zoom: {
                            enabled: false
                        }
                    },
                    dataLabels: {
                        enabled: true
                    },
                    stroke: {
                        curve: 'straight'
                    },
                    title: {
                        text: 'Monthly Report',
                        align: 'left'
                    },
                    grid: {
                        row: {
                            colors: ['#f3f3f3', 'transparent'], // takes an array which will be repeated on columns
                            opacity: 0.5
                        },
                    },
                    xaxis: {
                        categories: [],//['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep'],
                    //categories: ['2001', '2002', '2003', '2004', '2005', '2006', '2007', '2008', '2009'],
                    },
                    legend: {
                        position: 'top',
                        horizontalAlign: 'right',
                        floating: true,
                        offsetY: -25,
                        offsetX: -5
                    }
                    /* chart: {
                        height: 350,
                        type: 'line',
                        dropShadow: {
                            enabled: true,
                            color: '#000',
                            top: 18,
                            left: 7,
                            blur: 10,
                            opacity: 0.2
                        },
                        toolbar: {
                            show: false
                        }
                        },
                        colors: ['#77B6EA', '#545454'],
                        dataLabels: {
                        enabled: true,
                        },
                        stroke: {
                        curve: 'smooth'
                        },
                        title: {
                        text: 'Average High & Low Temperature',
                        align: 'left'
                        },
                        grid: {
                        borderColor: '#e7e7e7',
                        row: {
                            colors: ['#f3f3f3', 'transparent'], // takes an array which will be repeated on columns
                            opacity: 0.5
                        },
                        },
                        markers: {
                        size: 1
                        },
                        xaxis: {
                        categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul'],
                        title: {
                            text: 'Month'
                        }
                        },
                        yaxis: {
                        title: {
                            text: 'Temperature'
                        },
                        min: 5,
                        max: 40
                        },
                        legend: {
                        position: 'top',
                        horizontalAlign: 'right',
                        floating: true,
                        offsetY: -25,
                        offsetX: -5
                        } */
                },
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
                this.getSelectdeProduct.price = Number(this.productList.price);        
                this.getSelectdeProduct.total = this.productList.price * this.productList.qty;          
                this.getSelectdeProduct.qty = Number(this.productList.qty);  
                this.$emit('update', this.getSelectdeProduct)  
            },
            save(){
                axios.post('/api/saveTransaction', {
                    items:this.$refs.productVal.results3,
                    head: this.transactionDetail,                    
                    user: User.user_id(),
                })
                .then(res => {
                    console.log(res)
                    this.getId  = res.data;
                    this.getAddedItems();
                    Toast.fire({
                        icon: 'success',
                        title: 'Saved successfully'
                    });
                    this.$refs.productVal.results3 = [];
                })
                .catch(error => console.log(error))
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
                axios.post('/api/report', {
                    items:this.filter,
                })
                .then(res => {
                    this.series = res.data[0].series
                    //this.chartOptions.xaxis = 
                    console.log(res.data[0])
                    console.log(res.data[0].cat)
                    this.chartOptions = {
                        xaxis: {
                            categories: res.data[0].cat
                        }
                    }
                    console.log(this.chartOptions.xaxis.categories)
                    /* this.chartOptions = {...this.chartOptions, ...{
                        xaxis: {
                            data: res.data[0].cat
                        }
                    }} */
                    /* this.$refs.radar.updateOptions({
                        xaxis: {
                            categories: res.data[0].cat//ie ["a","b","c","d"]
                        }
                    }) */
                    
                    //this.$refs.radar.refresh();
                    Toast.fire({
                        icon: 'success',
                        title: 'Saved successfully'
                    });
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