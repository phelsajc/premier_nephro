
<template>
    <div>
        <input type="text" placeholder="what are you looking for?" v-model="form.val" v-on:change="autoComplete" class="form-control">
        <div class="panel-footer" v-if="results.length">
            <ul class="list-group">
                <li class="list-group-item" v-for="result in results" @click="getMedicine(result)" v-if="!results3.find(e => e.item_description  === result.itemdesc)">
                    {{ result.genericname }} ({{ result.itemdesc }})
                </li>
            </ul>
        </div>
        
        
        <div class="panel-footer" v-if="results3.length">
            <ul class="list-group">
                <li class="list-group-item" v-for="(resultx,index) in results3">
                 {{ resultx.item_description }}  <span class="badge bg-danger" @click="remove(index)"><i class="fa fa-times"></i></span>
                </li>
            </ul>
        </div>
    </div>
   </template>
   <script>
    export default{
     data(){
      return {
                form: {
                    val: '',
                },
          results: [],
          results3: [],
        results2: {
            pk_iwitems: '',
            itemdesc: '',
            genericname: '',
            dc_price: 0,
            sc_price: 0,
            reg_price: 0,         
       }
      }
     },watch: {
    testProp(newVal, oldVal) {
      this.sayHello();
    },
  },
     methods: {
      autoComplete(){
            this.results = [];  
            if(this.form.val.length > 2){              
                axios.post('/api/searchDiagnostic',this.form)
                .then(res => {
                    this.results = res.data  
                    console.log(res) 

                    if(!res.data.length){
                       // console.log("no dtaa"+ this.form.val)
                        this.results3.push({
                            'diagnostic': this.form.val,
                            'item_description': this.form.val,
                            'pk_iwitems': 0,
                            'item_reg_price': 0,
                            'item_sc_price': 0,
                        });
                    }
                })
                .catch(error => this.errors = error.response.data.errors)
            }
       
         },
         getMedicine(id) {
             this.getValue = id
            
             this.results2.pk_iwitems = id.pk_iwitems;
                this.results2.itemdesc = id.itemdesc;
                this.results2.genericname = id.genericname;
                this.results2.dc_price = id.discounted_price;
                this.results2.sc_price = id.sc_price;
             this.results2.reg_price = id.price;
             this.form.val = id.itemdesc;
             this.results = []
                    this.$emit( 'get-diagnostics-data', this.results2 );
         },
        setValue(value) {
            this.results3.push({
                'diagnostic': value.itemdesc,
                'item_description': value.itemdesc,
                'pk_iwitems': value.pk_iwitems,
                'item_reg_price': value.reg_price,
                'item_sc_price': value.sc_price,
            });
             this.form.val = null;
             console.log(this.results3)
         },
         remove(index) {
            console.log(this.results3)
            this.results3.splice(index, 1);
         },
         finalD() {
            
                    this.$emit( 'get-diagnostics-fdata', this.results3 );
        }
         
     },
     
     created() {
       this.$parent.$on('update', this.setValue);
     },
        props: ['products'],
    }
   </script>