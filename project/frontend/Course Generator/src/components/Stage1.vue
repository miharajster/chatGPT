<template>
  <div id="stage1">
    <img src="../assets/welcome.png">
    <input v-model="inputContent" placeholder="Enter the desired topic..." @keyup.enter="write">
    <button :disabled="isInputEmpty" @click="write">Write</button>
  </div>
</template>

<script>
export default {
  props: ['startCountdown'],
  data() {
    return {
      inputContent: '',
    };
  },
  computed: {
    isInputEmpty() {
      return this.inputContent.trim() === '';
    }
  },
  methods: {
    async write() {
      this.$emit('write', this.inputContent);
    },
  },
};
</script>
