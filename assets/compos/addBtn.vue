<template>
  <button @click="onHandle">Ajouter</button>
</template>

<script setup>
import {useCart} from "@/stores";

const obj = defineProps({
  id: String, name: String, pprix: Number, images: String
})
const store = useCart();

const onHandle = () => {
  store.increase(obj)
}
</script>

<style scoped>

</style>