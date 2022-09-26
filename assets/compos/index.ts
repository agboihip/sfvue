import {camelCase, kebabCase, lowerCase, deburr} from 'lodash';
import {Form, Field, ErrorMessage} from "vee-validate";
import moment, { Moment } from 'moment';
import {Plugin, Component} from 'vue';
import https from '@/api';

const compos:{[name: string]: Component} = {VeeForm: Form, Field, ErrorMessage }, modules = require.context('.', false, /\.vue$/);
modules.keys().forEach(fn => compos[camelCase(fn.replace(/(\.\/|\.vue)/g, ''))] = modules(fn).default);

moment.locale('fr');
const plugin: Plugin =  {
    install(app, ...options) {
        app.config.globalProperties.$http = https; app.config.globalProperties.$moment = moment;
        app.provide('slugify', (str:string) => kebabCase(lowerCase(deburr(str)))); //app.directive('my-directive', { mounted (el, binding, vnode, oldVnode) {} })
        Object.keys(compos).forEach(name => app.component(name, compos[name]));
    }
}

export default plugin;