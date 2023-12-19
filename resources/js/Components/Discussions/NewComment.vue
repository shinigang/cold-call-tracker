<script setup>
import { ref } from 'vue';

const comment = ref('');

const emit = defineEmits(['saveComment']);

const saveComment = (e) => {
    e.preventDefault();
    if (comment.value == '') {
        return;
    }

    emit('saveComment', comment.value);
    comment.value = '';
};
</script>

<template>
    <div class="p-[2px]">
        <textarea v-model="comment" @keydown.enter="saveComment" placeholder="Type your comment and press enter..."
            class="block p-2.5 w-[calc(100%-4px)] text-sm text-gray-900 bg-white rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"></textarea>
    </div>
</template>