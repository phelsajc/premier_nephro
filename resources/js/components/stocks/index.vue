<template>
  <div class="wrapper">
    <navComponent></navComponent>
    <sidemenuComponent></sidemenuComponent>
    <div class="content-wrapper">
      <section class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <h1>Stocks Inventory</h1>
            </div>
          </div>
        </div>
        <!-- /.container-fluid -->
      </section>

      <!-- Main content -->

      <section class="content">
        <div class="container-fluid">
          <div class="row">
            <div class="col-12">
              <div class="card">
                <div class="card-header">
                  <h3 class="card-title">&nbsp;</h3>
                 
                </div>

                <div class="card-body">

                  <table class="table">
                    <thead>
                      <tr>
                        <th>Codes</th>
                        <th>Products</th>
                        <th>Units</th>
                        <th>Received</th>
                        <th>Sales</th>
                        <th>Stock</th>
                        <th>Price</th>
                        <!-- <th></th> -->
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="e in items">
                        <td>
                          {{ e.codes }}
                        </td>
                        <td>
                          {{ e.products }}
                        </td>
                        <td>
                          {{ e.units }}
                        </td>
                        <td>
                         <strong> {{ e.rec }}</strong>
                        </td>
                        <td>
                          <strong>  {{ e.sales }}</strong>
                        </td>
                        <td>
                          <strong> {{ e.stock }}</strong>
                        </td>
                        <td>
                          {{ e.price }}
                        </td>
                        <!-- <td>
                          {{ e.total }}
                        </td> -->
                      </tr>
                    </tbody>
                  </table>
                  
             

                  <nav aria-label="Page navigation example" class="">
                    {{showing}}
                  </nav>
                </div>
                <!-- /.card-body -->
              </div>
              <!-- /.card -->

              <!-- /.card -->
            </div>
            <!-- /.col -->
          </div>
          <!-- /.row -->
        </div>
        <!-- /.container-fluid -->
      </section>
    </div>
    <footerComponent></footerComponent>
  </div>
</template>

<script type="text/javascript">

  export default {
created() {
          if(!User.loggedIn()){
              this.$router.push({name: '/'})
          }

          this.stockInventory()
      },
      data(){

          return {
              items:[]
          }
      },
      computed:{
          filtersearch(){
              return this.employees.filter(e => {
                return e.name.match(this.searchTerm)
              })
          },

      },
      methods: {
          stockInventory(){
            this.isHidden =  false
              //axios.get('/api/employee')
              axios.get('/api/stockInventory')
              .then(({data}) => (
                this.items = data
             ))
              .catch()
          },
          me(){
              axios.post('/api/auth/me','',{
                  headers: {
                    //"Content-Type": "application/x-www-form-urlencoded",
                    Authorization: "Bearer ".concat(this.token),
                    Accept: "application/jsons",
                  }
                })
                .then(res => {
                  console.log(res)
              })
            .catch(error => this.errors = error.response.data.errors)

          },
          pdf(){
              /* axios.get('/pdf')
              .then(({data}) => (
                  console.log(data)
              ))
              .catch() */
              window.open("/api/pdf", '_blank');
          },
        async  check_doctors_detail(id) {
          return await axios.get( '/api/check_doctors_detail/'+id)
            .then(response => {
              setTimeout(function() {
                return response.data;
              }, 3000);

            })
           /*  .then((response) => {
              return  Promise.resolve(response.data); }) */

          },
        /* async  check_doctors_detail(id) {
           return await axios.get( '/api/check_doctors_detail/'+id)
          }, */
          formatDate(date) {
              const options = { year: 'numeric', month: 'long', day: 'numeric' }
              return new Date(date).toLocaleDateString('en', options)
          },
          deleteRecord(id){
              Swal.fire({
                  title: 'Are you sure?',
                  text: "You won't be able to revert this!",
                  icon: 'warning',
                  showCancelButton: true,
                  confirmButtonColor: '#3085d6',
                  cancelButtonColor: '#d33',
                  confirmButtonText: 'Yes, delete it!'
              }).then((result) => {
                  if (result.isConfirmed) {
                      axios.delete('/api/employee/'+id)
                      .then(() => {
                          this.employees = this.employees.filter(e => {
                              return e.id != id
                          })
                      })
                      .catch(() =>{
                          //this.$router.push({name: 'all_employee'})
                          this.$router.push("/all_employee").catch(()=>{});
                      })

                      Swal.fire(
                      'Deleted!',
                      'Your file has been deleted.',
                      'success'
                      )
                  }
              })
          },
          filterEmployee(){
              this.employees = []
              this.countRecords = null
            this.form.start = 0
            this.isHidden =  false
              //axios.post('/api/filterEmployee',this.form)
              axios.post('/api/products',this.form)

              .then(res => {
                this.employees = res.data[0].data
                this.countRecords =res.data[0].count
                console.log(res.data.data)
                this.isHidden =  true
              })
              .catch(error => this.errors = error.response.data.errors)
          },
          getPageNo(id){
            this.form.start = (id-1) * 10
            this.isHidden =  false
            //alert(a)
            /* this.employees = []
            this.countRecords = null */
            //axios.post('/api/filterEmployee',this.form)
            console.log(this.isHidden)
            axios.post('/api/products',this.form)
              .then(res => {
                this.employees = res.data[0].data
                this.countRecords =res.data[0].count
                this.showing = res.data[0].showing,
                console.log(res.data[0])
                this.isHidden =  true
            console.log(this.isHidden)
            })
            .catch(error => this.errors = error.response.data.errors)
          },
      },
      /* mounted () {
        axios.get('/api/check_doctors_detail/'+id)
            .then(response => (this.getdctr = response))
      }, */
      /* created(){
          this.allEmployee();
      } */
  }
</script>

<style>
.em_photo {
  height: 40px;
  width: 40px;
}

.to-right {
  float: right;
}

.spin_center {
  position: absolute;
  top: 50%;
  left: 50%;
  width: 300px;
  text-align: center;
  transform: translateX(-50%);
  /*display: none;*/
}

.btn-app {
  height: unset !important;
  padding: 0 1.5em 0 1.5em;
}
</style>
