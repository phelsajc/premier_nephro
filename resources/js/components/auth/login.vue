<template>
    <div class="hold-transition login-page">
    <div class="text-center">                       
        <img :src="img" alt="" class="profile-user-img img-fluid img-circle">
    </div> <br>
        <div class="login-box">
  <div class="card card-outline card-primary">
    <div class="card-header text-center">
      <a href="javascript:void(0);" class="h1"><b>PREMIER </b></a><br>
      <a href="javascript:void(0);" class="h6"><b>NEPPHRO CARE SYSTEM INC </b></a>
    </div>
    <div class="card-body">
      <p class="login-box-msg">Sign in to start your session</p>

      <small class="text-danger" v-if="errors==true">Please check your login credentials.</small>
        <form class="user" @submit.prevent="login">
            <div class="input-group mb-3">
                <input type="text" class="form-control" id="exampleInputEmail" aria-describedby="emailHelp" placeholder="Enter Username" v-model="form.username">
                <div class="input-group-append">
                    <div class="input-group-text">
                    <span class="fas fa-user"></span>
                    </div>
                </div>
            </div>        
            <div class="input-group mb-3">
                <input type="password" class="form-control" id="exampleInputPassword" placeholder="Enter Password" v-model="form.password">
                <div class="input-group-append">
                    <div class="input-group-text">
                    <span class="fas fa-lock"></span>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary btn-block">Login</button>
            </div>
            <hr>
        </form>                
    </div>
    <!-- /.card-body -->
  </div>
  <!-- /.card -->
</div>
    </div>
</template>

<style lang="scss" scoped>
    @import "../../../../public/backend2/dist/css/adminlte.min.css";
    /* {{ asset('backend2/dist/css/adminlte.min.css') }} */
</style>    

<script type="text/javascript">

    export default {
        created(){
            if(!User.loggedIn()){
              //  this.$router.push({name: '/'})
            }
        },

        data() {
            return {
                form: {
                    username: null,
                    password: null,
                },
                errors:false,
                img: '../../../../backend2/premier.jpg'
            }
        },
        methods: {
            async login() {
                await axios.post('/api/auth/login',this.form)
                    .then(res => {
                        this.errors = true;
                    User.responseAfterLogin(res)
                    Toast.fire({
                        icon: 'success',
                        title: 'Signed-in Successfully'
                    })
                    //this.$router.push({name: 'home'})
                    
                    //this.$router.push({ name: 'transaction_list' })
                    location  = '/manage_session'
                    //location = "/all_employee"
                })
                .catch(error => error?this.errors = true:false)
                .catch(
                   
                    Toast.fire({
                        icon: 'warning',
                        title: 'User Not Found!'
                    }),
                    console.log(this.errors)
                )
            }
        },
    }
    
</script>

<style>

</style>