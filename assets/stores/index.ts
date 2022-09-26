import {arrTobj, useStorage, ICatal, IItem, CartData} from '@/utils';
import {createPinia, defineStore, Pinia} from 'pinia';
import {orderBy, take} from 'lodash';
import client from '@/api';

const pinia: Pinia = createPinia();

const recent = (list:Array<any>, size:number = 3) => take(orderBy(list, 'createdAt', 'desc'), size)

export const useCatalog = defineStore('catalog-store', {
    state: () => ({ items: {content: {}}, types: [] } as ICatal),
    getters: {
        gds: state => state.items,
        find: state => (id:string) => state.items?.content[id],
        top: state => recent(Object.values(state.items?.content)),
    },
    actions: {
        async getAll(obj = {}) {
            const data = await client('products', { method: 'GET', params: obj});
            this.items = {...data, content: arrTobj(data.content)}
            return data;
        },
        async init () {
            if (!this.gds?.number) await Promise.all([client('products'),client('tags')]).then(([p,t]) => {
                this.types = t;
                this.items = {...p, content: arrTobj(p.content)}
            })
        },
        async getOne(id: string) {
            if(!this.find(id)) this.items.content[id] = await client(`products/${id}`);
        }
    },
})

export const useCart = defineStore('cartStore', {
    state: (): { cart: Record<string, CartData> } => ({
        cart: useStorage() ?? {},
    }),

    getters: {
        data(): {count: number, total: number} {
            return Object.keys(this.cart).reduce((acc, id) => {
                const {prIx, qte} = this.cart[id]
                acc.total += prIx * qte;
                acc.count += qte;
                return acc;
            }, {count: 0, total: 0});
        },

        prod: (state): CartData[] => Object.values(state.cart),
    },

    actions: {
        insert() {
            //checkout process
        },

        increase({id, name, pprix, images}: IItem) {
            if (this.cart[id]) this.cart[id].qte += 1;
            else this.cart[id] = {prId: id, name, qte: 1, prIx: pprix, image: (images||[''])[0]};
        },
        decrease(id: number) {
            if (this.cart[id]) {
                this.cart[id].qte -= 1;
                if (this.cart[id].qte === 0)
                    delete this.cart[id];
            }
        },

        remove() {
            this.cart = {}; //useStorage(true);
        }
    },
});

export default pinia;