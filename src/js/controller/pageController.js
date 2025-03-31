import { html } from '@arrow-js/core';
import { GlobalState } from '../utils/state';

export const pageController = () => {
    const TITLE_PREFIX = GlobalState.TITLE_PREFIX;
    const URL_PREFIX = window.location.pathname.startsWith('/app') ? '/app' : '';
    const ALLOWED_PREFIX = '/app';

    const setPageTitle = (page) => {
        document.title = `${TITLE_PREFIX}${page.charAt(0).toUpperCase() + page.slice(1)}`;
    };

    const changePage = (page) => {
        if (!GlobalState.allowedPages.includes(page)) return;
        GlobalState.currentPage = page;
        window.history.pushState({}, '', `${URL_PREFIX}/${page}`);
        setPageTitle(page);
    };

    const addPage = (index, pageContent) => {
        if (!GlobalState.allowedPages.includes(index)) return;
        GlobalState.pages[index] = pageContent;
    };

    const createPageLink = (pageIndex, linkText) => {
        return html`
            <a href="#" @click="${(e) => {e.preventDefault();changePage(pageIndex);}}">${linkText}</a>
        `;
    };

    const renderCurrentPage = () => {
        setPageTitle(GlobalState.currentPage);
        return GlobalState.pages[GlobalState.currentPage] || html`<div>Page not found</div>`;
    };

    // Initialize URL handling
    window.addEventListener('popstate', () => {
        const path = window.location.pathname.replace(ALLOWED_PREFIX, '').slice(1) || 'home';
        if (!window.location.pathname.startsWith(ALLOWED_PREFIX)) {
            window.location.href = `${ALLOWED_PREFIX}/home`;
            return;
        }
        if (GlobalState.allowedPages.includes(path)) {
            changePage(path);
        }
    });

    return {
        changePage,
        addPage,
        createPageLink,
        renderCurrentPage
    };
};