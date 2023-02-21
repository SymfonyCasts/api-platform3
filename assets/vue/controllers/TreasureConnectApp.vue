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
                <div v-if="user">
                    Authenticated as: <strong>{{ user.username }}</strong>

                    | <a href="/logout" class="underline">Log out</a>
                    <br>
                    <h3 class="text-left font-semibold mt-2">Tokens</h3>
                    <div v-if="null === tokens">Refresh to see tokens...</div>
                    <dl v-else class="text-left max-w-md text-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                        <div class="flex flex-col py-3" v-for="token in tokens" :key="token">
                            <dd class="text-xs whitespace-normal break-words">{{ token }}</dd>
                        </div>
                    </dl>
                </div>
                <div v-else>Not authenticated</div>

                <hr class="my-10 mx-auto" style="border-top: 1px solid #ccc; width: 70%;" />

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

const props = defineProps(['entrypoint', 'user', 'tokens'])
const user = ref(props.user);

const onUserAuthenticated = async (userUri) => {
    const response = await fetch(userUri);
    user.value = await response.json();
}
</script>
