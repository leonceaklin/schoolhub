<template>
  <div class="barcode-scanner">
  <div class="scanner-wrapper">
  <div ref="scannerContainer"></div>
  <div class="barcode-overlay"><v-icon dark>mdi-barcode</v-icon></div>
  </div>
</div>
</template>

<script>
export default {
  data(){
    return {
      scanner: null,
      code: null,
    }
  },

  beforeDestroy(){
    Quagga.stop()
  },

  methods: {
    onDetected(data){
      this.$emit('scan', this.code)
    },
    onProcessed(data){
      if(data && data.codeResult && data.codeResult.code){
        if(data.codeResult.code != this.code && data.codeResult.code != null){
          this.code = data.codeResult.code
          if(!this.code.startsWith("978")){
            return
          }
          this.onDetected(data)
        }
      }
    }
  },

  mounted(){
    this.code = null
    Quagga.init({
      inputStream : {
        name : "Live",
        type : "LiveStream",
        target: this.$refs.scannerContainer
      },
      locate: false,
      decoder : {
        readers : ["ean_reader"],
        multiple: false
      },
    },
    (err) => {
      if(err){
        this.$emit('error', err)
        return
      }
      Quagga.start();
      Quagga.onProcessed((d) => { try{this.onProcessed(d)}catch(e){} })
    });
  }
}
</script>

<style lang="css" scoped>
</style>
