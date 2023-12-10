<script setup>
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';

dayjs.extend(relativeTime);

const props = defineProps({
    comments: Array | null,
});

</script>

<template>
    <div class="company-comments">
        <div v-if="comments.length > 0" v-for="comment in comments" :key="comment.id">
            <p>{{ comment.user.name }} - {{ comment.body }}</p>
            <small>{{ dayjs(comment.created_at).fromNow() }}</small>
        </div>
        <el-empty v-else description="No Discussions" class="!p-0 mb-2" />
        <button
            class="w-full text-sm py-1 mt-2 font-bold rounded-md text-center border-dashed border-2 border-gray-300 dark:border-gray-700 hover:border-gray-500 dark:hover:border-gray-600 text-gray-600 dark:text-gray-500 dark:hover:text-gray-300">
            + Add Comment
        </button>
    </div>
</template>
