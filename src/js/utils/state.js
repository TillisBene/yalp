import { reactive } from '@arrow-js/core';

export const GlobalState = reactive({
    user: {
        id: null,
        username: null,
        email: null,
        profilePicture: null,
    },
    APItoken: null,
    authToken: null,
    currentPage: 'home',
    pages: {},
    allowedPages: ['home', 'login', 'register', 'profile', 'settings'],
    TITLE_PREFIX: 'YALP SOZIAL | ',
});