<template>
  <div>
    <input type="text" placeholder="what are you looking for?" v-model="form.searchVal" v-on:keyup="autoComplete"
      class="form-control">
    <div class="panel-footer" v-if="results.length">
      <ul class="list-group list-group-company">
        <li class="list-group-item" v-for="result in results" @click="getCompany(result)">
          {{ result.name }}
        </li>
      </ul>
    </div>
  </div>
</template>
<script>
import api from '../Helpers/api';
export default {
  data() {
    return {
      form: {
        val: this.meds,
        searchVal: null
      },
      results: [],
      results2: {
        id: '',
      }
    }
  },
  methods: {
    autoComplete() {
      this.results = [];
      if (this.form.searchVal.length >= 3) {
        api.post('company-find', this.form)
          .then(response => {
            this.results = response.data
          })
          .catch(error => console.log(error))
      } else {
        this.form.patientid = 0
      }
    },
    getCompany(id) {
      this.results2.id = id;
      this.form.searchVal = id.name;
      this.results = []
      this.$emit('return-response', this.results2);
    },
    setValue(value) {
      this.form.val = value
    },
    clearForm() {
      this.form.val = ''
    }
  },
  props:['patientid'],
  created() {
    this.$parent.$on('update', this.setValue);
  },
}
</script>

<style>
.list-group-company {
    position: absolute;
}
</style>