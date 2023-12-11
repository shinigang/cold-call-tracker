<script setup>
import { ref, computed, onMounted } from 'vue';
import _ from 'lodash';

const props = defineProps(['contacts', 'activeContact']);

const emit = defineEmits(['selectedContact']);

const selected = ref(null);

const selectContact = (contact) => {
    selected.value = contact.id;
    emit('selectedContact', contact);
};

const sortedContacts = computed(() => _.orderBy(props.contacts, ['last_message_at', 'unread'], ['desc', 'desc']));

onMounted(() => {
    if (props.activeContact) {
        selected.value = props.activeContact.id;
    }
});
</script>

<template>
    <div class="h-[640px] overflow-y-auto">
        <ul role="list" class="divide-y divide-dashed divide-gray-200 dark:divide-gray-700">
            <li v-for="contact in sortedContacts" :key="contact.id" @click="selectContact(contact)"
                class="p-2 group/contact" :class="{ 'is-active': contact.id == selected }">
                <div
                    class="flex items-center p-2 group/contact hover:bg-slate-100 dark:hover:bg-gray-900 group-[.is-active]/contact:bg-indigo-100 dark:group-[.is-active]/contact:bg-gray-900 flex items-center cursor-pointer space-x-4 rtl:space-x-reverse rounded-lg transition duration-150 ease-in-out focus:outline-none focus-visible:ring focus-visible:ring-orange-500/50">
                    <div class="relative flex-shrink-0">
                        <img class="h-10 w-10 rounded-full object-cover" :src="contact.profile_photo_url"
                            :alt="contact.name">
                        <span v-if="contact.metadata.logged_in"
                            class="-top-[2px] -end-[2px] absolute w-3.5 h-3.5 bg-green-500 border-2 border-white dark:border-gray-800 rounded-full"></span>
                        <span v-else
                            class="-top-[2px] -end-[2px] absolute w-3.5 h-3.5 bg-red-500 border-2 border-white dark:border-gray-800 rounded-full"></span>
                    </div>

                    <div class="relative flex-1 min-w-0">
                        <div class="font-medium text-base text-gray-800 dark:text-gray-200">
                            <span class="font-semibold">{{ contact.name }}</span>
                        </div>
                        <div class="font-medium text-sm text-gray-500">
                            {{ contact.email }}
                        </div>
                        <div v-if="contact.unread"
                            class="absolute inline-flex items-center justify-center w-6 h-6 text-xs font-bold text-white bg-red-500 border-2 border-white rounded-full -top-[12px] -end-[12px] dark:border-gray-900">
                            {{ contact.unread }}</div>
                    </div>
                </div>
            </li>
        </ul>
    </div>
</template>