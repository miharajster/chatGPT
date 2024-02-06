<template>
  <div id="stage2">
    <img src="../assets/writting.gif">
    <h1>Thinking...</h1>
    <p><strong>{{ countdown }}</strong> seconds left. <br/>Grab a coffee and lean back.</p>
  </div>
</template>

<script>
export default {
  props: ['countdown'],
};
</script>
