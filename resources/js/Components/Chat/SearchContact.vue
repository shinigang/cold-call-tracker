<script setup>
import { ref } from 'vue';
import { debounce } from 'lodash';

const emit = defineEmits(['search']);

const searchKeyword = ref('');

const searchContacts = debounce(() => {
    emit('search', searchKeyword.value);
}, 500);

</script>

<template>
    <div
        class="focus-within:border-indigo-400 lg:h-12 mb-5 searh-field flex flex-col lg:flex-row items-center border-0 lg:border-b transition-all js-search border-gray-300 dark:border-gray-700">
        <input v-model="searchKeyword" type="text" placeholder="Search contact by name or email..." @input="searchContacts"
            class="placeholder:italic placeholder:text-slate-400 grow w-full lg:w-auto h-12 lg:h-auto bg-transparent text-black dark:text-gray-300 border-b border-gray-300 dark:border-gray-700 lg:border-0 mb-2 lg:mb-0 text-medium border-0 pb-0 focus:outline-none focus:ring-transparent focus:border-purple-100 ltr:pr-10 rtl:pl-10">
    </div>
</template>