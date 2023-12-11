<script setup>
import { ref, nextTick, onMounted, onUpdated, watch } from 'vue';
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';

dayjs.extend(relativeTime);

const props = defineProps({
    contact: {
        type: Object
    },
    messages: {
        type: Array,
        required: true
    }
});

const feed = ref(null);
const messageItems = ref(props.messages);

const scrollToBottom = () => {
    feed.value.scrollTop = feed.value.scrollHeight - feed.value.clientHeight;
};

onMounted(async () => {
    await nextTick();
    scrollToBottom();
});

onUpdated(async () => {
    await nextTick();
    scrollToBottom();
});

watch(() => props.messages, (messages) => {
    if (messages) {
        messageItems.value = [];
        setTimeout(() => {
            messageItems.value = messages;
        }, 1);
    }
});
</script>

<template>
    <div class="h-full max-h-[525px] p-2 overflow-y-auto" ref="feed">
        <ul v-if="contact">
            <li v-for="message in messageItems" :key="message.id"
                :class="`flex items-start gap-2.5 mb-3 ${message.to == contact.id ? 'flex-row-reverse' : ''}`">
                <img class="w-8 h-8 rounded-full"
                    :src="message.to == contact.id ? $page.props.auth.user.profile_photo_url : contact.profile_photo_url"
                    :alt="contact.name" />
                <div class="flex flex-col gap-1 max-w-[420px]">
                    <div
                        :class="`hidden flex items-center space-x-2 ${message.to == contact.id ? 'flex-row-reverse' : ''}`">
                        <span class="text-sm font-semibold text-gray-900 dark:text-white me-1">
                            {{ message.to == contact.id ? $page.props.auth.user.name : contact.name }}
                        </span>
                    </div>
                    <div class="flex flex-col leading-1.5 p-4 border-gray-200 bg-gray-100 dark:bg-gray-700"
                        :class="`${message.to == contact.id ? 'text-right rounded-s-xl rounded-ee-xl' : 'rounded-e-xl rounded-es-xl'}`">
                        <p class="text-sm font-normal text-gray-900 dark:text-white break-all">
                            {{ message.text }}
                        </p>
                    </div>
                    <div :class="`flex items-center space-x-2 ${message.to == contact.id ? 'flex-row-reverse' : ''}`">
                        <span class="text-xs font-normal text-gray-500 dark:text-gray-400">
                            {{ dayjs(message.created_at).fromNow() }}
                        </span>
                    </div>
                </div>
            </li>
        </ul>
    </div>
</template>