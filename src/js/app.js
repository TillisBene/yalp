import axios from 'axios';
import { html } from '@arrow-js/core';
import { GlobalState } from './utils/state';
import { pageController } from './controller/pageController';

const app = document.getElementById('app');
const controller = pageController();

// Setup pages with navigation
controller.addPage('home', html`
    <div>
        <h1>Home Page</h1>
    </div>
`);

controller.addPage('settings', html`
    <div>
        <h1>Settings</h1>
    </div>
`);

// Mount app
const template = html`
    ${controller.renderCurrentPage}
    ${controller.createPageLink('settings', 'settings')}
    ${controller.createPageLink('home', 'home')}
`;
template(app);