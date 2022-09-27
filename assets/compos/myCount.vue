<template>
  <span>€{{data.total}}</span>
</template>

<script setup>
import {useCart} from "@/stores";
import {computed} from "vue";

const store = useCart();
const data = computed(() => store.data)
</script>

<style scoped>

</style>