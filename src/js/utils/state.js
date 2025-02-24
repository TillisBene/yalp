import { reactive } from '@arrow-js/core';
import { getCookie } from './getCookie.js';

export const GlobalState = reactive({
    user: {
        id: null,
        username: null,
        email: null,
        profilePicture: null,
    },
    APItoken: null,
    authToken: null,
    csrfToken: getCookie('CSRF-TOKEN'),
    currentPage: 'home',
    pages: {},
    allowedPages: ['home', 'login', 'register', 'profile', 'settings'],
    TITLE_PREFIX: 'YALP SOZIAL | ',
});