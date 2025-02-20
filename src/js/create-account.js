import { html } from '@arrow-js/core';
import axios from 'axios';

const app = document.getElementById('app');

const createAccount = html`
    <div>
        <h1>Create Account</h1>
        <form @submit="${handleSubmit}">
            <input type="text" name="username" placeholder="Username" required />
            <input type="email" name="email" placeholder="Email" required />
            <input type="password" name="password" placeholder="Password" required />
            <input type="password" name="confirmPassword" placeholder="Confirm Password" required />
            <button type="submit">Create Account</button>
        </form>
    </div>
`;

async function handleSubmit(e) {
    e.preventDefault(); // Add explicit prevention
    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData);

    if (data.password !== data.confirmPassword) {
        alert('Passwords do not match');
        return;
    }

    try {
        const response = await axios.post('/api/create-account', data);
        console.log(response.data);
        // Redirect or handle success
    } catch (error) {
        console.error('Error creating account:', error);
        // Handle error
    }
}

createAccount(app);