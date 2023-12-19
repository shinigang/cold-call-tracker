<script setup>
import { ref, onMounted } from 'vue';
import ConsultantStats from '@/Components/Analytics/ConsultantStats.vue';
import axios from 'axios';

const props = defineProps({
    analytics: Object
});
const consultantStatsDuration = ref(30);

const consultantStatsData = ref(props.analytics.consultants ?? []);

const updateAnalytics = async (statsType = 'all', interval) => {
    if (statsType == 'consultants') {
        consultantStatsDuration.value = interval;
    }
    const response = await axios.post(route('dashboard.analytics'), {
        stats_type: statsType,
        duration: interval
    });
    if (statsType == 'consultants') {
        consultantStatsData.value = response.data.consultants;
    }
}

onMounted(() => {
    Echo.private(`call-updates`)
        .listenToAll((event, data) => {
            updateAnalytics('consultants', consultantStatsDuration.value);
        });
});
</script>

<template>
    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <ConsultantStats :analytics="consultantStatsData" @update-analytics="updateAnalytics" />
        </div>
    </div>
</template>
