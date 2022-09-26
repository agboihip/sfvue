import { startStimulusApp } from '@symfony/stimulus-bridge';
import {createApp, onUnmounted} from 'vue';
import store, {useCart} from '@/stores';
import {useStorage} from '@/utils';
import plugin from '@/compos';
import './styles/app.css';

createApp({
    setup() {
        const sc = useCart();

        const unsub = sc.$subscribe(() => useStorage(sc.cart))

        onUnmounted(() => unsub()); //onBeforeMount(async () => await store.init())
    }
}).use(store).use(plugin).mount('#app');

export const app = startStimulusApp(require.context(
    '@symfony/stimulus-bridge/lazy-controller-loader!./controllers',
    true,
    /\.[jt]sx?$/
));// app.register('some_controller_name', SomeImportedController);
