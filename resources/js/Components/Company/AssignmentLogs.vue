<script setup>
import { computed } from 'vue';

import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';

dayjs.extend(relativeTime);

const props = defineProps({
    assignments: Array | null,
});

const modifiedAssignments = computed(() => {
    return (props.assignments ? props.assignments.map(assignment => {
        return {
            userPhoto: assignment.user.profile_photo_url,
            content: `<b>${assignment.user.name}</b> was assigned as ${assignment.role}`,
            timestamp: assignment.created_at,
        };
    }).reverse() : []);
});
</script>

<template>
    <div class="px-[10px]">
        <el-timeline v-if="modifiedAssignments.length > 0">
            <el-timeline-item v-for="(assignment, index) in  modifiedAssignments " :key="index" :hollow="index != 0"
                :type="index == 0 ? 'primary' : ''" :timestamp="`${dayjs(assignment.timestamp).fromNow()}`">
                <p v-html="assignment.content" />
                <template #dot>
                    <el-avatar :size="22" :src="assignment.userPhoto" class="relative right-[6px]" />
                </template>
            </el-timeline-item>
        </el-timeline>
        <el-empty v-else description="No Assignments" />
    </div>
</template>
