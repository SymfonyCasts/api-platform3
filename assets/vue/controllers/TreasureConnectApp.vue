<template>
    <div class="purple flex flex-col min-h-screen">
        <div class="px-8 py-8">
            <img class="h-16 w-auto" :src="coinLogoPath" alt="Treasure Connect Logo">
        </div>
        <div class="flex-auto flex flex-col sm:flex-row justify-center px-8">
            <LoginForm
                v-on:user-authenticated="onUserAuthenticated"></LoginForm>
            <div
                class="book shadow-md rounded sm:ml-3 px-8 pt-8 pb-8 mb-4 sm:w-1/2 md:w-1/3 text-center">
                You are currently
                <span v-if="user">
                    authenticated as {{ user.username }}

                    <a href="/logout" class="btn btn-warning btn-sm">Log out</a>
                </span>
                <span v-else>not authenticated</span>

                <p>Check out the <a :href="entrypoint" class="underline">API Docs</a></p>
            </div>
        </div>
        <img :src="goldPilePath" alt="A pile of gold!">
    </div>
</template>

<script setup>
import { ref } from 'vue';
import LoginForm from '../LoginForm';
import coinLogoPath from '../../images/coinLogo.png';
import goldPilePath from '../../images/GoldPile.png';

defineProps(['entrypoint']);
const user = ref(null);

const onUserAuthenticated = async (userUri) => {
    const response = await fetch(userUri);
    user.value = await response.json();
}
</script>
