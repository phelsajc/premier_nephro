<template>
  <div class="spinner">
    <div class="text-center " :class="{ 'd-none': getProrgessStatus }">
      Please wait. Process in-progress...
    </div><br>
    <div class="d-flex justify-content-center">
      <div class="spinner-border" role="status" :class="{ 'd-none': getProrgessStatus }">
        <span class="sr-only">Loading...</span>
      </div>
    </div>
  </div>
</template>
<script>
import api from '../Helpers/api';
export default {
  data() {
    return {      
      isHidden: this.getStatus,
    }
  },
  computed:{
    getProrgessStatus(){
      return this.getStatus
    }
  },
  methods: {
    
  },

  created() {
    console.log(this.getProrgessStatus)
  },
  props: ['getStatus']
  
}
</script>

<style>
  .spinner{    
    position: fixed;
    margin: -5% 0% 0% 38%;
    width: 20%;
    /*background-color: rgba(136, 114, 114, 0.253);*/
    border-radius: 20%;
  }
</style>