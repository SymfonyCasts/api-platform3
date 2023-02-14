<template>
    <form
        v-on:submit.prevent="handleSubmit"
        class="book shadow-md rounded px-8 pt-6 pb-8 mb-4 sm:w-1/2 md:w-1/3"
    >
        <div v-if="error" class="bg-red-500 text-white font-bold rounded-md py-2 px-4">
            {{ error }}
        </div>
        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2" for="email">
                Email
            </label>
            <input
                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                :class="{ 'border-red-500': error }"
                id="email"
                v-model="email"
                type="email"
                placeholder="Email">
            <p class="mt-1 text-xs text-gray-500">Try: <a href="#" tabindex="-1" @click.prevent="loadEmailField">bernie@dragonmail.com</a></p>
        </div>
        <div class="mb-6">
            <label class="block text-gray-700 text-sm font-bold mb-2" for="password">
                Password
            </label>
            <input
                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                :class="{ 'border-red-500': error }"
                id="password"
                v-model="password"
                type="password"
                placeholder="Password"
                >
            <p class="mt-1 text-xs text-gray-500">Try: <a href="#" tabindex="-1" @click.prevent="loadPasswordField">roar</a></p>
        </div>
        <div class="flex items-center justify-between">
            <button
                class="bg-indigo-700 hover:bg-indigo-900 shadow-lg text-white font-semibold py-2 px-4 rounded-lg focus:outline-none focus:shadow-outline text-sm"
                type="submit"
                :disabled="isLoading"
                :class="{ 'bg-indigo-400': isLoading, 'hover:bg-indigo-400': isLoading }"
            >
                Log In
            </button>
        </div>
    </form>
</template>

<script setup>

import { ref } from 'vue';

const email = ref('');
const password = ref('');
const error = ref('');
const isLoading = ref(false);
const emit = defineEmits(['user-authenticated']);

const loadEmailField = () => {
    email.value = 'bernie@dragonmail.com';
};
const loadPasswordField = () => {
    password.value = 'roar';
};

const handleSubmit = async () => {
    isLoading.value = true;
    error.value = '';

    const response = await fetch('/login', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            email: email.value,
            password: password.value
        })
    });

    isLoading.value = false;

    if (!response.ok) {
        const data = await response.json();
        console.log(data);
        // TODO: set error

        return;
    }

    email.value = '';
    password.value = '';
    //emit('user-authenticated', userIri);
}

</script>
