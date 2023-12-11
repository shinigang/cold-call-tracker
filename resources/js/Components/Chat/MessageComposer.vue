<script setup>
import { ref } from 'vue';

const message = ref('');

const emit = defineEmits(['send']);

const send = (e) => {
    e.preventDefault();
    if (message.value == '') {
        return;
    }

    emit('send', message.value);
    message.value = '';
};
</script>

<template>
    <div class="composer">
        <textarea v-model="message" @keydown.enter="send" placeholder="Type your message and press enter..."
            class="block p-2.5 w-full text-sm text-gray-900 bg-white rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"></textarea>
    </div>
</template>