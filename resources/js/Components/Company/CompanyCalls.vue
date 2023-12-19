<script setup>
import { ref, watch } from 'vue';
import { Warning } from '@element-plus/icons-vue';
import NewCallLogModal from '@/Components/Company/NewCallLogModal.vue';

import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';

dayjs.extend(relativeTime);

const emit = defineEmits(['callAdded']);

const props = defineProps({
    company: Object,
    calls: Array
});
const companyCalls = ref(props.calls.sort((a, b) => new Date(b.called_at) - new Date(a.called_at)));
const modalVisible = ref(false);

const onCallAdded = (newCallResponse) => {
    companyCalls.value = newCallResponse.company.calls;

    emit('callAdded', newCallResponse);
};

const onModalClosed = () => {
    modalVisible.value = false;
};

watch(
    () => props.calls,
    (calls) => {
        companyCalls.value = [];
        setTimeout(() => {
            companyCalls.value = calls.sort((a, b) => new Date(b.called_at) - new Date(a.called_at));
        }, 1);
    }
);
</script>

<template>
    <div class="calls-box px-[10px] pt-3">
        <button @click="modalVisible = true"
            class="w-full text-sm py-1 mb-3 font-bold rounded-md text-center border-dashed border-2 border-gray-300 dark:border-gray-700 hover:border-gray-500 dark:hover:border-gray-600 text-gray-600 dark:text-gray-500 dark:hover:text-gray-300">+
            Add Call Log</button>
        <el-timeline v-if="companyCalls.length">
            <el-timeline-item v-for="call in companyCalls" :key="call.id" :timestamp="`${dayjs(call.called_at).fromNow()}`">
                <template #dot>
                    <el-avatar :size="22" :src="call.user.profile_photo_url" class="relative right-[6px]" />
                </template>
                <p>
                    <span class="block font-semibold">{{ call.user.name }}</span>
                    <span class="text-xs">{{ call.contact_number }}</span>
                    <el-divider direction="vertical" />
                    <span class="text-xs">{{ call.status }}</span>
                </p>
            </el-timeline-item>
        </el-timeline>
        <el-empty v-else description="No Calls" class="!py-0 mb-2">
            <template #image>
                <p align="center" class="m-0">
                    <Warning class="text-center !w-12 !h-12" />
                </p>
            </template>
        </el-empty>

        <NewCallLogModal :show="modalVisible" :company="company" @call-added="onCallAdded" @modal-closed="onModalClosed" />
    </div>
</template>

<style>
.calls-box .el-empty__description {
    margin-top: 4px;
}
</style>
