<template>
    <div class="purple flex flex-col min-h-screen">
        <div class="px-8 py-8">
            <img class="h-16 w-auto" src="/img/coinLogo.png">
        </div>
        <div class="flex-auto flex flex-col sm:flex-row justify-center px-8">
            <form
                class="book shadow-md rounded px-8 pt-6 pb-8 mb-4 sm:w-1/2 md:w-1/3">
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2"
                           for="username">
                        Username
                    </label>
                    <input
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        id="username" type="text" placeholder="Username">
                </div>
                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2"
                           for="password">
                        Password
                    </label>
                    <input
                        class="shadow appearance-none border border-red-500 rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline"
                        id="password" type="password"
                        placeholder="******************">
                    <p class="text-red-500 text-xs italic">Please choose a
                        password.</p>
                </div>
                <div class="flex items-center justify-between">
                    <button
                        class="bg-indigo-700 hover:bg-indigo-900 shadow-lg text-white font-semibold py-2 px-4 rounded-lg focus:outline-none focus:shadow-outline text-sm"
                        type="button">
                        Log In
                    </button>
                    <a class="inline-block align-baseline font-bold text-sm text-gray-500 hover:text-blue-800"
                       href="#">
                        Forgot Password?
                    </a>
                </div>
            </form>
            <div
                class="book shadow-md rounded sm:ml-3 px-8 pt-8 pb-8 mb-4 sm:w-1/2 md:w-1/3 text-center">
                <p>You are not currently authenticated</p>
                <p>Check out the <a href="#">API Docs</a></p>
            </div>
        </div>
        <img src="/img/GoldPile.png">
    </div>
</template>

<script>
    import loginForm from '../LoginForm';

    export default {
        components: {
            loginForm
        },
        props: ['entrypoint'],
        methods: {
            onUserAuthenticated(userUri) {
                axios
                    .get(userUri)
                    .then(response => (this.user = response.data))
            }
        },
        data() {
            return {
                user: null
            }
        }
    }
</script>
