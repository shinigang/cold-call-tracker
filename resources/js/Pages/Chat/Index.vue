<script setup>
import { ref, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';

import AppLayout from '@/Layouts/AppLayout.vue';
import Conversation from '@/Components/Chat/Conversation.vue';
import ContactsList from '@/Components/Chat/ContactsList.vue';
import SearchContact from '@/Components/Chat/SearchContact.vue';

const page = usePage();

const props = defineProps({
    contacts: {
        type: Array,
        default: []
    }
});

const contactItems = ref(props.contacts);
const selectedContact = ref(null);
const messages = ref([]);

const searchContact = async (keyword) => {
    const response = await axios.post(route('chat.search-contacts'), {
        keyword: keyword
    });
    contactItems.value = response.data;
};

const startConversationWith = async (contact) => {
    updateUnreadCount(contact, true);
    const response = await axios.get(route('chat.messages', contact.id));
    messages.value = response.data;
    selectedContact.value = contact;
};

const saveNewMessage = (message) => {
    messages.value.push(message);
};

const handleIncoming = (message) => {
    if (selectedContact.value && message.from == selectedContact.value.id) {
        saveNewMessage(message);
        updateUnreadCount(message.from_contact, true);
        axios.post(route('chat.read', message.id));

        return;
    }
    updateUnreadCount(message.from_contact, false);
};

const updateUnreadCount = (contact, reset) => {
    contactItems.value = contactItems.value.map((contactItem) => {
        if (contactItem.id != contact.id) {
            return contactItem;
        }

        if (reset) {
            contactItem.unread = 0;
        }
        else {
            contactItem.unread += 1;
        }

        return contactItem;
    });
};

const handleContactUpdate = (user) => {
    console.log('updating contact...');
    console.log(user);
    if (selectedContact.value && user.id == selectedContact.value.id) {
        selectedContact.value = user;
    }

    contactItems.value = contactItems.value.map((contactItem) => {
        if (contactItem.id != user.id) {
            return contactItem;
        }
        contactItem = user;
        return contactItem;
    });
};

onMounted(() => {
    Echo.private(`chat.${page.props.auth.user.id}`)
        .listen('NewMessage', (e) => {
            handleIncoming(e.message);
        });

    Echo.private(`user-activities`)
        .listen('UserMetadataUpdate', (e) => {
            handleContactUpdate(e.user);
        });
});
</script>

<template>
    <AppLayout title="Calls">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Team Chat
            </h2>
        </template>

        <div class="py-4">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <SearchContact @search="searchContact" />
                <div class="bg-white dark:bg-gray-800 shadow-xl rounded-lg">
                    <div class="grid flex-none lg:flex">
                        <Conversation :contact="selectedContact" :messages="messages" @new="saveNewMessage"
                            class="grow w-full p-2 h-[640px]" />
                        <ContactsList :contacts="contactItems" :active-contact="selectedContact"
                            @selected-contact="startConversationWith"
                            class="min-w-full lg:min-w-[300px] lg:max-w-[300px] h-[640px] border-l border-gray-100 dark:border-gray-700" />
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
