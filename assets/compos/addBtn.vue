<template>
  <button @click="onHandle">Ajouter</button>
</template>

<script setup>
import {useCart} from "@/stores";

const obj = defineProps(['id'])
const store = useCart();

const onHandle = () => {
  console.debug(obj) //store.increase(obj)
}
</script>

<style scoped>

</style>