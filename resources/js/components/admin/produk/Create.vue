<template>
  <b-col cols lg="10" xs=12>
    <div class="login-form mx-auto text-center">
      <h2>Buat Produk</h2>
      <form @submit.prevent="createProduk">
        <div class="form-group row">
          <label for="kode_produk" class="col-md-4 col-form-label text-md-right">Kode Produk</label>
          <div class="col-md-6">
            <input id="kode_produk" type="text" class="form-control" name="kode_produk" v-model="produk.kode_produk">
          </div>
        </div>
        <div class="form-group row">
          <label for="nama_produk" class="col-md-4 col-form-label text-md-right">Nama Produk</label>
          <div class="col-md-6">
            <input id="nama_produk" type="text" class="form-control" name="nama_produk" required autofocus v-model="produk.nama_produk">
          </div>
        </div>
        <div class="form-group row mb-0">
          <div class="col-md-8 offset-md-4">
          <button type="submit" class="btn btn-primary">
            Simpan
          </button>
          </div>
        </div>
      </form>
    </div>
  </b-col>
</template>
<script>
  export default {
    data() {
      return {
        produk: {},
      }
    },
    created() {

    },
    methods: {
      createProduk() {
        this.axios
          .post('/api/produk/store', this.produk)
          .then(response => (
            this.$router.push({name: 'master-produk'}),
            console.log(response)
          ))
          .catch(error => console.log(error))
          .finally(() => this.loading = false)
      }
    }
  }
</script>
