<script setup>
import { computed } from 'vue';

import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';

dayjs.extend(relativeTime);

const props = defineProps({
    actionLogs: {
        type: Array,
        default: () => []
    }
});

const modifiedActionLogs = computed(() => {
    return (props.actionLogs.length ? props.actionLogs.map(actionLog => ({
        userPhoto: actionLog.user.profile_photo_url,
        content: `
            <b>${actionLog.user.name}</b> ${actionLog.action_type}
            ${actionLog.action_old_value ? ` from <s>${actionLog.action_old_value}</s> to` : ''}
            <i>${actionLog.action_value}<i>`,
        timestamp: actionLog.created_at,
    })).reverse() : []);
});
</script>

<template>
    <div class="px-[10px]">
        <el-timeline v-if="modifiedActionLogs.length > 0">
            <el-timeline-item v-for="actionLog in modifiedActionLogs"
                :timestamp="`${dayjs(actionLog.timestamp).fromNow()}`">
                <template #dot>
                    <el-avatar :size="22" :src="actionLog.userPhoto" class="relative right-[6px]" />
                </template>
                <p v-html="actionLog.content" />
            </el-timeline-item>
        </el-timeline>
        <el-empty v-else description="No Activities" />
    </div>
</template>
