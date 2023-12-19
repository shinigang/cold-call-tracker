<script setup>
import { ref, watch } from 'vue';
import { Warning } from '@element-plus/icons-vue';
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';

dayjs.extend(relativeTime);

const props = defineProps({
    comments: {
        type: Array,
        required: true
    }
});
const commentItems = ref(props.comments);

watch(() => props.comments, (comments) => {
    if (comments) {
        commentItems.value = [];
        setTimeout(() => {
            commentItems.value = comments;
        }, 1);
    }
});
</script>

<template>
    <div class="comments-box p-2">
        <ul v-if="commentItems.length > 0">
            <li v-for="comment in commentItems" :key="comment.id" :class="`flex items-start gap-2.5 mb-3`">
                <img class="shrink-0 w-8 h-8 rounded-full" :src="comment.user.profile_photo_url" :alt="comment.user.name" />
                <div class="w-full">
                    <div class="flex flex-col gap-1">
                        <div :class="`flex items-center space-x-2`">
                            <span class="text-sm font-semibold text-gray-900 dark:text-white me-1">
                                {{ comment.user.name }}
                            </span>
                            <span class="text-xs font-normal text-gray-500 dark:text-gray-400">
                                {{ dayjs(comment.created_at).format('hh:mm A') }}
                            </span>
                        </div>
                        <div
                            class="w-full flex flex-col leading-1.5 p-4 border-gray-200 bg-gray-100 dark:bg-gray-700 rounded-e-xl rounded-es-xl">
                            <p class="text-sm font-normal text-gray-900 dark:text-white break-all">
                                {{ comment.body }}
                            </p>
                        </div>
                    </div>
                </div>
            </li>
        </ul>
        <el-empty v-else description="No comments yet." class="!p-0 !pt-3 mb-2">
            <template #image>
                <p align="center" class="m-0">
                    <Warning class="text-center !w-12 !h-12" />
                </p>
            </template>
        </el-empty>
    </div>
</template>

<style>
.comments-box .el-empty__description {
    margin-top: 4px;
}
</style>