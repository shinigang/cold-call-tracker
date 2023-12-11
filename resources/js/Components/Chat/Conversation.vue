<script setup>
import MessagesFeed from '@/Components/Chat/MessagesFeed.vue';
import MessageComposer from '@/Components/Chat/MessageComposer.vue';

import { Pointer } from '@element-plus/icons-vue';

const props = defineProps({
    contact: {
        type: Object,
        default: null
    },
    messages: {
        type: Array,
        default: []
    }
});
const emit = defineEmits(['new']);

const sendMessage = async (message) => {
    if (props.contact) {
        const response = await axios.post(route('chat.send'), {
            contact_id: props.contact.id,
            text: message
        });
        emit('new', response.data);
    }
};
</script>

<template>
    <div>
        <div v-if="contact" class="flex flex-col justify-between h-full">
            <div class="flex gap-2 items-center border-b border-gray-100 dark:border-gray-700 pb-2">
                <img :src="contact.profile_photo_url" :alt="contact.name" class="rounded-full h-9 w-9" />
                <h1 class="text-xl">{{ contact.name }}</h1>
                <div class="ms-auto">
                    <span v-if="contact.metadata.logged_in"
                        class="inline-flex items-center bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded-full dark:bg-green-900 dark:text-green-300">
                        <span class="w-2 h-2 me-1 bg-green-500 rounded-full"></span>
                        Available
                    </span>
                    <span v-else
                        class="inline-flex items-center bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded-full dark:bg-red-900 dark:text-red-300">
                        <span class="w-2 h-2 me-1 bg-red-500 rounded-full"></span>
                        Unavailable
                    </span>
                </div>
            </div>
            <MessagesFeed :contact="contact" :messages="messages" />
            <MessageComposer @send="sendMessage" />
        </div>
        <div v-else class="pt-20 content-center text-center flex flex-col">
            <Pointer class="my-5 h-[300px] text-gray-200 dark:text-gray-700" />
            <span class="text-sm text-gray-400">Please select a contact.</span>
        </div>
    </div>
</template>