<template>
    <div>
        <input type="text" placeholder="what are you looking for?" v-model="form.val" v-on:keyup="autoComplete" class="form-control">
        <div class="panel-footer" v-if="results.length">
        <ul class="list-group">
            <li class="list-group-item" v-for="result in results" @click="getProduct(result)">
                {{ result.product }}
            </li>
        </ul>
        </div>
    </div>
   </template>
   <script>
    export default
    {
        data(){
        return {
            form: {
                val: this.meds ,
            },
            results: [],
            results3: [],
            results2: {
                id: '',
                product: '',
                description: '',
                price: 0,
                qty: 0,
                code: 0,         
            },
            /* grand_total: 0, */
        }
        },
        props: ['products'],
        methods: {
            autoComplete(){
                    this.results = [];                
                    axios.post('/api/searchProduct',this.form)
                    .then(res => {
                        this.results = res.data  
                    })
                    .catch(error => this.errors = error.response.data.errors)
        
            },
            getProduct(id) {
                this.getValue = id            
                this.results2.id = id.id;
                this.results2.product = id.product;
                this.results2.description = id.description;
                this.results2.price = id.price;
                this.results2.qty = id.qty
                this.results2.code = id.code;
                this.form.val = id.product;
                this.results = []
                this.$emit( 'handle-form-data', this.results2 );
            },
            setValueOriginal(value) {
            this.form.val = value
            },
            setValue(value) {
                this.results3.push({
                    'product': value.product,
                    'description': value.description,
                    'total': value.total,
                    'price': value.price,
                    'qty': value.qty,
                    'id': value.id,
                });
                //this.form.val = null;
                /* console.log(this.results3)
                this.results3.forEach(e => {
                    this.grand_total += e.total;
                }) */
            },
            clearForm() {
                this.form.val = ''
            }   
        },     
        created() {
            this.$parent.$on('update', this.setValue);
        },
    }
   </script>