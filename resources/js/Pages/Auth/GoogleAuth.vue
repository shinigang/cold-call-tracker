<script setup>
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Link Google Account" />

    <div class="bg-white dark:bg-gray-900">
        <div class="p-12 mx-auto max-w-xl">
            <div
                class="bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-8 md:p-12 mb-8 text-center">
                <h3 class="font-bold text-sm text-blue-500">Just one more step!</h3>
                <h1 class="font-bold text-2xl mt-1">Link Your Google Calendar</h1>
                <p class="mt-1">
                    This will allow us to generate google meet links and automaticaly add events to your account.
                </p>
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" class="inline-block h-24 w-24"
                    viewBox="0 0 32 32">
                    <path fill="#4285f4" d="M22,4.5v6H10v11H4V6.5a2.0059,2.0059,0,0,1,2-2Z" />
                    <polygon fill="#ea4435" points="22 27.5 22 21.5 28 21.5 22 27.5" />
                    <rect width="6" height="12" x="22" y="9.5" fill="#ffba00" />
                    <rect width="6" height="12" x="13" y="18.5" fill="#00ac47" transform="rotate(90 16 24.5)" />
                    <path fill="#0066da" d="M28,6.5v4H22v-6h4A2.0059,2.0059,0,0,1,28,6.5Z" />
                    <path fill="#188038" d="M10,21.5v6H6a2.0059,2.0059,0,0,1-2-2v-4Z" />
                    <path fill="#4285f4"
                        d="M15.69,17.09c0,.89-.66,1.79-2.15,1.79a3.0026,3.0026,0,0,1-1.52-.39l-.08-.06.29-.82.13.08a2.3554,2.3554,0,0,0,1.17.34,1.191,1.191,0,0,0,.88-.31.8586.8586,0,0,0,.25-.65c-.01-.73-.68-.99-1.31-.99h-.54v-.81h.54c.45,0,1.12-.22,1.12-.82,0-.45-.31-.71-.85-.71a1.8865,1.8865,0,0,0-1.04.34l-.14.1-.28-.79.07-.06a2.834,2.834,0,0,1,1.53-.45c1.19,0,1.72.73,1.72,1.45a1.4369,1.4369,0,0,1-.81,1.3A1.52,1.52,0,0,1,15.69,17.09Z" />
                    <polygon fill="#4285f4"
                        points="18.71 12.98 18.71 18.79 17.73 18.79 17.73 14 16.79 14.51 16.58 13.69 17.95 12.98 18.71 12.98" />
                </svg>
                <a href="/auth/google/redirect"
                    class="inline-flex w-full mt-4 items-center justify-center px-4 py-2 bg-blue-500 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-base text-white dark:text-gray-800 tracking-wide hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth="1.5"
                        stroke="currentColor" class="w-5 h-5 mr-1">
                        <path strokeLinecap="round" strokeLinejoin="round"
                            d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244" />
                    </svg>
                    Link Google Account
                </a>
            </div>
        </div>
    </div>
</template>
