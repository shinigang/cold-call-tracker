<script setup>
import { ref } from 'vue';
import ConsultantStatCard from '@/Components/Analytics/ConsultantStatCard.vue';

const props = defineProps({
    analytics: Object,
});

const emit = defineEmits(['updateAnalytics']);

const duration = ref(30);
const durationLbl = {
    0: 'All-Time',
    30: '30 days',
    90: '90 days',
    180: '6 months',
    365: '12 months'
};

const changeDuration = (interval) => {
    duration.value = interval;
    emit('updateAnalytics', 'consultants', interval);
};
</script>

<template>
    <div class="container items-center pb-2 m-auto">
        <div class="flex flex-col lg:flex-row">
            <h4 class="flex-none self-center font-bold text-xl mb-1 lg:mb-0">Top Consultants</h4>
            <ul
                class="grow md:w-full text-xs sm:text-sm justify-center lg:justify-end items-center flex flex-row space-x-1 overflow-hidden">
                <li>
                    <button @click="changeDuration(30)"
                        :class="`px-4 py-2 ${duration == 30 ? 'bg-indigo-500 text-gray-100' : 'bg-gray-200 dark:bg-gray-800 text-gray-700'} dark:text-gray-100 dark:hover:text-gray-300 rounded-full text-sm hover:bg-indigo-700 hover:text-gray-200`">
                        30 days
                    </button>
                </li>
                <li>
                    <button @click="changeDuration(90)"
                        :class="`px-4 py-2 ${duration == 90 ? 'bg-indigo-500 text-gray-100' : 'bg-gray-200 dark:bg-gray-800 text-gray-700'} dark:text-gray-100 dark:hover:text-gray-300 rounded-full text-sm hover:bg-indigo-700 hover:text-gray-200`">
                        90 days
                    </button>
                </li>
                <li>
                    <button @click="changeDuration(180)"
                        :class="`px-4 py-2 ${duration == 180 ? 'bg-indigo-500 text-gray-100' : 'bg-gray-200 dark:bg-gray-800 text-gray-700'} dark:text-gray-100 dark:hover:text-gray-300 rounded-full text-sm hover:bg-indigo-700 hover:text-gray-200`">
                        6 months
                    </button>
                </li>
                <li>
                    <button @click="changeDuration(365)"
                        :class="`px-4 py-2 ${duration == 365 ? 'bg-indigo-500 text-gray-100' : 'bg-gray-200 dark:bg-gray-800 text-gray-700'} dark:text-gray-100 dark:hover:text-gray-300 rounded-full text-sm hover:bg-indigo-700 hover:text-gray-200`">
                        12 months
                    </button>
                </li>
                <li>
                    <button @click="changeDuration(0)"
                        :class="`px-4 py-2 ${duration == 0 ? 'bg-indigo-500 text-gray-100' : 'bg-gray-200 dark:bg-gray-800 text-gray-700 '} dark:text-gray-100 dark:hover:text-gray-300 rounded-full text-sm hover:bg-indigo-700 hover:text-gray-200`">
                        All-Time
                    </button>
                </li>
            </ul>
        </div>
        <div class="grid md:grid-cols-2 lg:grid-cols-4 w-full pt-2 gap-2.5">
            <ConsultantStatCard v-for="consultant in analytics.topConsultants" :key="consultant.id" :showInfo="duration != 0">
                <template #photo>
                    <img :src="consultant.profile_photo_url" class="rounded-full " />
                </template>
                <template #name>
                    <div class="text-right">
                        <div class="font-semibold">{{ consultant.name }}</div>
                        <span class="text-xs">{{ consultant.email }}</span>
                    </div>
                </template>
                <template #value>
                    {{ consultant.total_appointments.toLocaleString() }}
                </template>
                <p>Total Appointments</p>
                <template #info>
                    <div class="text-xs mb-2">
                        Total {{ durationLbl[duration] }} ago:<br>
                        <b>
                            {{ consultant.prev_total_appointments ? consultant.prev_total_appointments.toLocaleString()
                                : 0 }}
                            appointment{{ consultant.prev_total_appointments != 1 ? 's' : '' }}
                        </b>
                    </div>
                    <div class="text-xs">
                        Total increase:<br>
                        <b>
                            {{ (consultant.total_appointments - (consultant.prev_total_appointments ??
                                0)).toLocaleString() }}
                            appointment{{ consultant.total_appointments - (consultant.prev_total_appointments ?? 0) != 1
                                ? 's'
                                : '' }}
                        </b>
                    </div>
                </template>
            </ConsultantStatCard>
        </div>
    </div>
</template>