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
                                    <form class="user" @submit.prevent="addInitialdata" enctype="multipart/form-data">
                                        <div class="row">
                                            <div class="col-sm-3">
                                                <div class="form-group ">
                                                    <label>Date of Transaction</label>
                                                    <datepicker name="birthdate" v-model="transactionDetail.dot"
                                                        :bootstrap-styling=true></datepicker>
                                                    <small class="text-danger" v-if="errors.name">{{ errors.name[0]
                                                    }}</small>
                                                </div>
                                            </div>
                                            <div class="col-sm-3">
                                                <div class="form-group">
                                                    <label>Company</label>
                                                    <select class="form-control" v-model="transactionDetail.companyid">
                                                        <option v-for="e in companies" :key="e.id" :value="e.id">
                                                            {{ e.company }}</option>
                                                    </select>
                                                    <small class="text-danger" v-if="errors.companyid">{{
                                                        errors.companyid[0] }}</small>
                                                </div>
                                            </div>
                                            <!-- <div class="col-sm-3">
                                                <div class="form-group">
                                                    <label>Invoice No:</label>
                                                    <input type="text" class="form-control"
                                                        v-model="transactionDetail.invoiceno">
                                                    <small class="text-danger" v-if="errors.invoiceno">{{
                                                        errors.invoiceno[0] }}</small>
                                                </div>
                                            </div> -->
                                            <!-- <div class="col-sm-2">
                                                <div class="form-group">
                                                    <label>Terms:</label>
                                                    <input type="text" class="form-control"
                                                        v-model="transactionDetail.terms">
                                                    <small class="text-danger" v-if="errors.terms">{{ errors.terms[0]
                                                    }}</small>
                                                </div>
                                            </div> -->
                                            <div class="col-sm-3">
                                                <div class="form-group">
                                                    <label>Particulars:</label>
                                                    <input type="text" class="form-control"
                                                        v-model="transactionDetail.particulars">
                                                    <small class="text-danger" v-if="errors.particulars">{{
                                                        errors.particulars[0] }}</small>
                                                </div>
                                            </div>
                                            <div class="col-sm-2">
                                                <div class="form-group">
                                                    <label>Reference No:</label>
                                                    <input type="text" class="form-control"
                                                        v-model="transactionDetail.referenceNo">
                                                    <small class="text-danger" v-if="errors.referenceNo">{{ errors.referenceNo[0]
                                                    }}</small>
                                                </div>
                                            </div>
                                        </div>


                                        <div class="row">
                                            <div class="col-sm-2">
                                                <div class="form-group">
                                                    <label>Item</label>
                                                    <products :meds="productList.product" ref="productVal"
                                                        @handle-form-data="clickedShowDetailModal"></products>
                                                </div>
                                            </div>
                                            <div class="col-sm-1">
                                                <div class="form-group">
                                                    <label>Price</label>
                                                    <input type="text" class="form-control" id="" placeholder="Enter Price"
                                                        v-model="productList.price">
                                                    <small class="text-danger" v-if="errors.desc">{{ errors.desc[0]
                                                    }}</small>
                                                </div>
                                            </div>
                                            <div class="col-sm-2">
                                                <div class="form-group">
                                                    <label>Stocks</label>
                                                    <input type="text" class="form-control" id=""
                                                        placeholder="Enter Quantity" readonly v-model="stocks">
                                                    <small class="text-danger" v-if="errors.desc">{{ errors.desc[0]
                                                    }}</small>
                                                </div>
                                            </div>
                                            <div class="col-sm-2">
                                                <div class="form-group">
                                                    <label>Quantity</label>
                                                    <input type="number" class="form-control" id=""
                                                        placeholder="Enter Quantity" @change="calculateTotal"
                                                        v-model="productList.qty">
                                                    <small class="text-danger" v-if="errors.desc">{{ errors.desc[0]
                                                    }}</small>
                                                </div>
                                            </div>
                                            <div class="col-sm-1">
                                                <div class="form-group">
                                                    <label>Amount</label>
                                                    <input type="text" class="form-control" id="" placeholder="Total"
                                                        v-model="productList.total">
                                                    <small class="text-danger" v-if="errors.desc">{{ errors.desc[0]
                                                    }}</small>
                                                </div>
                                            </div>
                                            <div class="col-sm-2">
                                                <label>&nbsp;</label>
                                                <div class="custom-control custom-checkbox">
                                                    <input class="custom-control-input" type="checkbox" id="customCheckbox2" v-model="productList.isFree"
                                                      :checked="productList.isFree">
                                                    <label for="customCheckbox2" class="custom-control-label">Free</label>
                                                </div>
                                            </div>
                                            <div class="col-sm-2">
                                                <div class="form-group">
                                                    <label>&nbsp;</label> <br>
                                                    <button :class="[(checkform ? '' : 'd-none')]" type="submit"
                                                        class="btn btn-success">
                                                        Add
                                                    </button>
                                                    <button :class="[(checkform ? '' : 'd-none')]" type="button"
                                                        @click="save()" class="btn btn-info">
                                                        Save
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                    </form>

                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Item</th>
                                                <th>Quantity</th>
                                                <th>Unit Price</th>
                                                <th>Amount</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="(e, index) in itemList2" :key="e.id" :value="e.id">
                                                <td>
                                                    {{ e.product }}
                                                </td>
                                                <td>
                                                    {{ e.qty }}
                                                </td>
                                                <td>
                                                    {{ e.price }}
                                                </td>
                                                <td>
                                                    {{ e.total }}
                                                </td>
                                                <td v-if="$route.params.id==0">
                                                    {{ e.isfree? 'Free':'Sales' }}
                                                </td>
                                                
                                                <td v-else>
                                                    {{ e.free==1? 'Free':'Sales' }}
                                                </td>
                                                <td>
                                                    <button type="button" @click="removeItem(index)"
                                                        class="btn btn-danger btn-sm"
                                                        :class="[(checkform ? '' : 'd-none')]">
                                                        Remove </button>
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
                                                    <strong>TOTAL: {{ total }}</strong>
                                                </td>
                                                <td>
                                                    
                                                </td>
                                                <td>
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
import Datepicker from 'vuejs-datepicker'
export default {
    created() {
        if (!User.loggedIn()) {
            this.$router.push({ name: '/' })
        }
        this.getCompany();
        //this.getAddedItems();

        let checkId = this.$route.params.id
        if (checkId != 0) {
            this.getId = checkId;
            this.getAddedItems();
            this.isDone = true;
            this.editForm();
            this.isNew = false;
        }
    },
    components: {
        Datepicker
    },
    data() {
        return {
            user_info: {
                patientname: '',
                contactno: '',
                pk_pspatregisters: '',
            },
            errors: {},
            companies: [],
            selectdD: [],
            transactionDetail: {
                particulars: '',
                terms: 0,
                invoiceno: '',
                dot: '',
                companyid: 0,
                referenceNo: '',
            },
            productList: {
                product: '',
                description: '',
                qty: 0,
                isFree: false,
                code: '',
                price: 0,
                total: 0,
            },
            getSelectdeProduct: [],
            getProductDetail: {},
            getId: 0,
            itemList: [],
            itemList2: [],
            grand_total: 0,
            isNew: true,
            isDone: false,
            newTotal: 0,
            stocks: 0
        }
    },
    computed: {
        total() {
            
            /* if (this.$route.params.id == 0) {
                return this.itemList2.reduce((sum, item) => item.isfree==false?sum + parseFloat(item.total):sum +0, 0);
            }else{
                return this.grand_total;
            } */
            if(this.isNew){
                //return this.itemList2.reduce((sum, item) => sum + item.total, 0);        
                return this.itemList2.reduce((sum, item) => item.isfree==false?sum + parseFloat(item.total):sum +0, 0);            
            }else{
                return this.newTotal
            }
        },
        checkform() {
            if (this.isNew) {
                return this.transactionDetail.dot != '' && this.transactionDetail.particulars != '' && this.transactionDetail.companyid != '' ? true : false
            } else {
                return false;
            }
        }
    },
    methods: {
        getCompany() {
            axios.get('/api/getCompanies')
                .then(({ data }) => (
                    this.companies = data,
                    console.log(this.companies)
                ))
                .catch(console.log('error'))
        },
        addInitialdata() {
            if (this.productList.product != null && this.productList.qty != 0 && this.productList.qty <= this.stocks) {
                this.getSelectdeProduct.price = Number(this.productList.price);
                this.getSelectdeProduct.total = this.productList.price * this.productList.qty;
                this.getSelectdeProduct.qty = Number(this.productList.qty);
                this.getSelectdeProduct.isFree = this.productList.isFree;
                this.$emit('update', this.getSelectdeProduct)
                this.itemList2 = this.$refs.productVal.results3
                console.log(this.itemList2)
                this.productList.qty = 0;
                this.productList.price = 0;
                this.productList.total = 0;
                this.productList.isFree = false;
                this.$refs.productVal.form.val = '';
            } else {
                Toast.fire({
                    icon: 'error',
                    title: 'Please check item'
                });
            }
        },
        save() {
            if (this.isNew) {
                api.post('saveTransaction', {
                    items: this.$refs.productVal.results3,
                    head: this.transactionDetail,
                    user: User.user_id(),
                })
                    .then(res => {
                this.newTotal = this.total;
                        this.getId = res.data;
                        this.isNew = false
                        Toast.fire({
                            icon: 'success',
                            title: 'Saved successfully'
                        });
                        this.$refs.productVal.results3 = [];
                    })
                    .catch(error => console.log(error))
            } else {
                axios.post('/api/updateTransaction', {
                    data: this.transactionDetail,
                    id: this.getId
                })
                    .then(res => {
                        Notification.success()
                    })
                    .catch(error => this.errors = error.response.data.errors)
            }
        },
        clickedShowDetailModal: function (value) {
            this.getSelectdeProduct = value;
            this.productList.product = this.getSelectdeProduct.product
            this.productList.description = this.getSelectdeProduct.description
            this.productList.price = this.getSelectdeProduct.price
            this.productList.id = this.getSelectdeProduct.id
            this.stocks = this.getSelectdeProduct.qty
            console.log(this.getSelectdeProduct.qty)
        },
        calculateTotal() {
            if (this.productList.qty <= this.stocks) {
                this.productList.total = this.productList.price * this.productList.qty;
            }
        },
        getAddedItems() {
            axios.get('/api/getTransaction/' + this.getId)
                .then(({ data }) => (
                    this.itemList2 = data,
                    this.itemList2.forEach(e => {
                        if(e.free==0){
                            this.newTotal += parseFloat(e.total)
                        }
                    })
                ))
                .catch()
        },
        removeItem(e) {
            this.itemList2.splice(e, 1);
        },
        editForm() {
            let id = this.$route.params.id
            axios.get('/api/getTransactionHeader/' + id)
                .then(({ data }) => (
                    this.transactionDetail.invoiceno = !Object.keys(data).length === 0 ? this.form.invoiceno : data.invoiceno,
                    this.transactionDetail.dot = !Object.keys(data).length === 0 ? this.form.transactiondate : data.transactiondate,
                    this.transactionDetail.companyid = !Object.keys(data).length === 0 ? this.form.companyid : data.companyid
                    //this.grand_total = !Object.keys(data).length === 0 ? this.grand_total : data.total
                ))
                .catch(console.log('error'))
        },
    }
}

</script>

<style>
.pull-right {
    float: right !important;
}</style>