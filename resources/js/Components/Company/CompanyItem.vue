<script setup>
// import { ref } from 'vue';
import { ArrowRightBold, Search, MapLocation, Message } from '@element-plus/icons-vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const props = defineProps({
    company: Object,
});

const getStatusBgColor = (status) => {
    const callStatus = page.props.callStatuses.find(cStatus => cStatus.status == status);
    if (callStatus) {
        return callStatus.group.color;
    }
    return '#var(--el-fill-color-blank)';
};
</script>

<template>
    <li class="p-2">
        <div
            class="group/item hover:bg-slate-100 dark:hover:bg-gray-900 group-[.is-active]/company:bg-indigo-100 dark:group-[.is-active]/company:bg-gray-900 flex items-center cursor-pointer space-x-4 rtl:space-x-reverse rounded-lg p-2 transition duration-150 ease-in-out focus:outline-none focus-visible:ring focus-visible:ring-orange-500/50">
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-gray-900 truncate dark:text-white">
                    {{ company.name }}
                </p>
                <p class="text-xs text-gray-500 truncate dark:text-gray-400">
                    {{ company.address_country }}
                    <el-divider direction="vertical" class="-top-[1px] !mx-1 group-[.is-active]/company:border-gray-400" />
                    <span v-if="company.website">
                        <a :href="company.website" target="_blank" title="Website">
                            <Search class="h-4 w-4 inline-block me-1 cursor-pointer hover:opacity-75" />
                        </a>
                    </span>
                    <span v-if="company.email">
                        <a :href="`mailto:${company.email}`" target="_blank" title="Email">
                            <Message class="h-4 w-4 inline-block me-1 cursor-pointer hover:opacity-75" />
                        </a>
                    </span>
                    <span v-if="company.address_street && company.address_city && company.address_state">
                        <a :href="`https://www.google.com/maps/search/?api=1&query=${company.address_street} ${company.address_city} ${company.address_state}`"
                            target="_blank" title="Address">
                            <MapLocation class="h-4 w-4 inline-block me-1 cursor-pointer hover:opacity-75" />
                        </a>
                    </span>
                    <span v-if="company.linkedin">
                        <a :href="company.linkedin" target="_blank" title="Linkedin">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"
                                class="h-4 w-4 inline-block me-1 cursor-pointer hover:opacity-75"><!--! Font Awesome Free 6.4.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. -->
                                <path
                                    d="M416 32H31.9C14.3 32 0 46.5 0 64.3v383.4C0 465.5 14.3 480 31.9 480H416c17.6 0 32-14.5 32-32.3V64.3c0-17.8-14.4-32.3-32-32.3zM135.4 416H69V202.2h66.5V416zm-33.2-243c-21.3 0-38.5-17.3-38.5-38.5S80.9 96 102.2 96c21.2 0 38.5 17.3 38.5 38.5 0 21.3-17.2 38.5-38.5 38.5zm282.1 243h-66.4V312c0-24.8-.5-56.7-34.5-56.7-34.6 0-39.9 27-39.9 54.9V416h-66.4V202.2h63.7v29.2h.9c8.9-16.8 30.6-34.5 62.9-34.5 67.2 0 79.7 44.3 79.7 101.9V416z" />
                            </svg>
                        </a>
                    </span>

                </p>
                <div class="inline-flex items-center">
                    <el-tag effect="dark"
                        :style="`background-color: ${getStatusBgColor(company.call_status)}; border-color: ${getStatusBgColor(company.call_status)}`">
                        {{ company.call_status }}
                    </el-tag>
                </div>
            </div>
            <button
                class="group/edit hidden relative flex items-center whitespace-nowrap rounded-full py-[4px] px-[6px] text-sm text-slate-500 transition hover:bg-slate-200 group-hover/item:block">
                <ArrowRightBold
                    class="mt-px h-4 w-4 text-slate-400 transition group-hover/edit:translate-x-0.5 group-hover/edit:text-slate-500" />
            </button>
        </div>
    </li>
</template>
