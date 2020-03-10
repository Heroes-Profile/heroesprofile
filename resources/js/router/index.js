import Vue from 'vue'
import VueRouter from 'vue-router'

Vue.use(VueRouter)

import GlobalLeaderboard from '@/components/GlobalLeaderboard.vue';
import HeroSummary from '@/components/Profile/HeroSummary.vue';
import OptOut from '@/components/OptOut/OptOut.vue';

let routes = [{
        path: '/',
        component: GlobalLeaderboard
    },
    {
        path: '/profile',
        component: HeroSummary
    },
    {
        path: '/optout',
        component: OptOut
    },

]

export default new VueRouter({
    routes: routes,
    mode: 'history', //removes # (hashtag) from url
    base: '/',
    fallback: true
})