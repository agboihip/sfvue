export type ICatal = {items: {content: {[id: string]: IItem}, number?: number}, types: Array<string>}
export interface IItem {id: string, name?: string, pprix: number, images?: string}
export interface CartData {
    name?: string;
    prId: string;
    qte: number;
    prIx: number;
    image?: string;
}

export const useStorage = (value:Boolean|Record<string, CartData> = false, key = 'shop_cart') => {
    switch (value) {
        case false: return JSON.parse(localStorage.getItem(key) as any);
        case true: return localStorage.removeItem(key);
        default: return localStorage.setItem(key, JSON.stringify(value));
    }
}

export const arrTobj = (items:Array<any> = [], key = 'id') => items.reduce((acc, item, idx) => (acc[item[key]||idx] = item, acc), {});