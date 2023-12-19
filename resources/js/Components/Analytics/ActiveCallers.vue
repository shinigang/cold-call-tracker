<script setup>
// import { computed } from 'vue';
// import { useForm } from '@inertiajs/vue3';
import { Watch, User } from '@element-plus/icons-vue';
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';

dayjs.extend(relativeTime);

const props = defineProps({
    callers: Array,
});

const hasNoFilters = (metadata) => {
    return !metadata.search_keyword && !metadata.call_status && !metadata.filtered_country && !metadata.filtered_state && !metadata.filtered_city;
};
</script>

<template>
    <div class="active-callers-box">
        <p
            class="flex justify-start items-center border-y border-gray-200 dark:border-gray-700 dark:bg-gray-700 py-2 ps-3 -mx-[9px] -mt-[8px]">
            <el-tag size="small" effect="dark" round class="me-2 font-semibold !bg-indigo-500 !border-indigo-500">{{
                callers.length }}</el-tag>
            <span class="uppercase text-sm font-semibold text-gray-700 dark:text-gray-50">Active Callers</span>
        </p>

        <el-scrollbar height="210px">
            <ul v-if="callers.length > 0" role="list" class="divide-y divide-dashed divide-gray-200 dark:divide-gray-700">
                <li v-for="caller in callers" :key="caller.id" class="py-2 sm:py-3">
                    <div class="flex items-start space-x-3 rtl:space-x-reverse">
                        <div class="flex-shrink-0 items-start relative">
                            <img class="w-8 h-8 rounded-full" :src="caller.profile_photo_url" :alt="caller.name">

                            <el-tooltip class="box-item" effect="dark" placement="right"
                                :content="`Last login: ${dayjs(caller.metadata.last_login_at).fromNow()}`">
                                <Watch
                                    class="h-4 absolute top-[38px] left-2 inline-block me-1 text-indigo-500 dark:text-gray-200" />
                            </el-tooltip>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-gray-900 truncate dark:text-white">
                                {{ caller.name }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                <el-tag v-if="hasNoFilters(caller.metadata)" class="me-1 mt-1" type="info" effect="plain">
                                    <i>No Search or Filters</i></el-tag>
                                <el-tag v-if="caller.metadata.search_keyword" class="me-1 mt-1" type="info"
                                    effect="light">Search: {{ caller.metadata.search_keyword }}</el-tag>
                                <el-tag v-if="caller.metadata.call_status" class="me-1 mt-1" effect="light">Call Status: {{
                                    caller.metadata.call_status }}</el-tag>
                                <el-tag v-if="caller.metadata.filtered_country" class="me-1 mt-1" type="success"
                                    effect="light">Country: {{ caller.metadata.filtered_country }}</el-tag>
                                <el-tag v-if="caller.metadata.filtered_state" class="me-1 mt-1" type="warning"
                                    effect="light">State: {{ caller.metadata.filtered_state }}</el-tag>
                                <el-tag v-if="caller.metadata.filtered_city" class="me-1 mt-1" type="danger"
                                    effect="light">City: {{ caller.metadata.filtered_city }}</el-tag>
                            </p>
                        </div>
                    </div>
                </li>
            </ul>
            <el-empty v-else description="No active callers." class="mb-2">
                <template #image>
                    <p align="center" class="m-0">
                        <User class="!w-12 !h-12" />
                    </p>
                </template>
            </el-empty>
        </el-scrollbar>
    </div>
</template>

<style>
.active-callers-box .el-empty__description {
    margin-top: 4px;
}
</style>
