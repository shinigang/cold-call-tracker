<script setup>
import { QuestionFilled } from '@element-plus/icons-vue';

defineProps({
    showInfo: true
});

</script>

<template>
    <div
        class="flex flex-col overflow-hidden gap-1 p-4 bg-white dark:bg-gray-800 hover:bg-gradient-to-br hover:from-purple-400 hover:via-blue-400 hover:to-blue-500 dark:hover:from-gray-800 dark:hover:via-gray-800 dark:hover:to-gray-700 rounded-xl shadow-lg duration-300 hover:shadow-2xl group">
        <div class="flex flex-row justify-between items-center">
            <div class="p-4 bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 rounded-xl bg-opacity-30">
                <slot name="icon" />
            </div>
            <div
                class="inline-flex text-sm text-gray-600 dark:text-gray-400 group-hover:text-gray-200 dark:text-gray-300 sm:text-base">
                <slot name="versus" />
            </div>
        </div>
        <h1
            class="text-2xl sm:text-3xl xl:text-4xl font-bold text-gray-700 dark:text-gray-400 mt-1 group-hover:text-gray-50 dark:group-hover:text-gray-200">
            <slot name="value" />
        </h1>
        <div
            class="flex text-sm flex-row justify-between dark:text-gray-400 group-hover:text-gray-200 dark:group-hover:text-gray-200">
            <slot />
            <el-popover placement="top-end" trigger="hover" v-if="showInfo">
                <template #reference>
                    <QuestionFilled
                        class="h-5 w-5 text-indigo-600 dark:text-gray-500 group-hover:text-gray-200 dark:group-hover:text-gray-400" />
                </template>
                <template #default>
                    <slot name="info" />
                </template>
            </el-popover>
        </div>
    </div>
</template>