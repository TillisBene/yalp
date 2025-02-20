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
        ${controller.createPageLink('login', 'Go to Login')}
        <!-- Or use direct changePage -->
        <button @click=${() => controller.changePage('login')}>Login</button>
    </div>
`);

controller.addPage('login', html`
    <div>
        <h1>Login Page</h1>
        ${controller.createPageLink('home', 'Back to Home')}
    </div>
`);

// Mount app
const template = html`${controller.renderCurrentPage}`;
template(app);