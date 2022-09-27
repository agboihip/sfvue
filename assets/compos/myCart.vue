<template>
  <table class="table">
    <thead>
      <tr>
        <th scope="col">#</th>
        <th scope="col">Name</th>
        <th scope="col">Price</th>
        <th scope="col">Qty</th>
        <th scope="col">Amount</th>
      </tr>
    </thead>
    <tbody class="table-group-divider">
      <tr v-for="(v,k) in item" :key="v.prId">
        <th scope="row">{{ k+1 }}</th>
        <td>{{ v.name }}</td>
        <td>{{ v.prIx }}</td>
        <td>{{ v.qte }}</td>
        <td>
          <button @click="incHandle(v)">+</button>
          {{ v.prIx*v.qte }}
          <button @click="decHandle(v)">-</button>
        </td>
      </tr>
      <tr>
        <td colspan="3"></td>
        <td>{{ data.count }}</td>
        <td>€{{ data.total }}</td>
      </tr>
    </tbody>
  </table>

  <button @click="onClear">Vider</button>
  <button @click="onSubmit">Valider</button>
</template>

<script setup>
import {useCart} from "@/stores";
import {computed} from "vue";

const store = useCart();
const data = computed(() => store.data)
const item = computed(() => store.prod)

const onSubmit = () => {}

const incHandle = ({prId, name, prIx, image}) => store.increase({
  id: prId, name, pprix: prIx, images: image
})
const decHandle = ({prId}) => store.decrease(prId)

const onClear = () => store.remove()
</script>

<style scoped>

</style>