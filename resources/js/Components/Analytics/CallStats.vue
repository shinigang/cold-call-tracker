<script setup>
import { ref } from 'vue';
// import { useForm } from '@inertiajs/vue3';
import { Phone, TrophyBase, Clock, More, TopRight, BottomRight } from '@element-plus/icons-vue';
import CallStatCard from '@/Components/Analytics/CallStatCard.vue';

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
    emit('updateAnalytics', 'calls', interval);
};

const calcPercentage = (totalValue, partialValue) => {
    if (totalValue == 0) {
        return 0;
    }
    return ((partialValue / totalValue) * 100).toFixed(0);
};

const calcDiffPercentage = (initialValue, finalValue) => {
    if (finalValue == 0) {
        return 0;
    }
    const diff = finalValue - initialValue;
    let percentage = 100;
    if (initialValue > 0) {
        percentage = ((diff / initialValue) * 100).toFixed(0);
    }

    return percentage;
};
</script>

<template>
    <div class="container items-center pb-2 m-auto">
        <div class="flex flex-wrap mx-4 md:mx-24 lg:mx-0">
            <ul
                class="w-full text-xs sm:text-sm justify-center lg:justify-end items-center flex flex-row space-x-1 overflow-hidden">
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
            <CallStatCard :showInfo="duration != 0">
                <template #icon>
                    <Phone class="h-6 w-6 group-hover:text-gray-50 dark:group-hover:text-gray-300 dark:text-gray-400" />
                </template>
                <template #versus v-if="duration != 0">
                    <TopRight v-if="analytics.totalCalls > analytics.prevTotalCalls"
                        class="h-6 w-6 mr-2 text-green-500 group-hover:text-gray-200" />
                    <BottomRight v-else class="h-6 w-6 mr-2 text-red-500 group-hover:text-gray-200" />
                    {{ calcDiffPercentage(analytics.prevTotalCalls, analytics.totalCalls) }}%
                </template>
                <template #value>
                    {{ analytics.totalCalls.toLocaleString() }}
                </template>
                <p>Total Calls</p>
                <template #info>
                    <div class="text-xs mb-2">
                        Total {{ durationLbl[duration] }} ago:<br>
                        <b>
                            {{ analytics.prevTotalCalls.toLocaleString() }}
                            call{{ analytics.prevTotalCalls != 1 ? 's' : '' }}
                        </b>
                    </div>
                    <div class="text-xs">
                        Total increase:<br>
                        <b>
                            {{ (analytics.totalCalls - analytics.prevTotalCalls).toLocaleString() }}
                            call{{ analytics.totalCalls - analytics.prevTotalCalls != 1 ? 's' : '' }}
                        </b>
                    </div>
                </template>
            </CallStatCard>
            <CallStatCard :showInfo="duration != 0">
                <template #icon>
                    <TrophyBase
                        class="h-6 w-6 dark:text-gray-400 dark:hover:text-gray-700 group-hover:text-gray-50 dark:group-hover:text-gray-400" />
                </template>
                <template #versus v-if="duration != 0">
                    <TopRight v-if="analytics.successfulCalls > analytics.prevSuccessfulCalls"
                        class="h-6 w-6 mr-2 text-green-500 group-hover:text-gray-200" />
                    <BottomRight v-else class="h-6 w-6 mr-2 text-red-500 group-hover:text-gray-200" />
                    {{ calcDiffPercentage(analytics.prevSuccessfulCalls, analytics.successfulCalls) }}%
                </template>
                <template #value>
                    <el-tooltip effect="dark" :content="analytics.successfulCalls.toLocaleString()" placement="right">
                        {{ calcPercentage(analytics.totalCalls, analytics.successfulCalls) }}%
                    </el-tooltip>
                </template>
                <p>Successful Calls</p>
                <template #info>
                    <div class="text-xs mb-2">
                        Total {{ durationLbl[duration] }} ago:<br>
                        <b>
                            {{ analytics.prevSuccessfulCalls.toLocaleString() }}
                            call{{ analytics.prevSuccessfulCalls != 1 ? 's' : '' }}
                        </b>
                    </div>
                    <div class="text-xs">
                        Total increase:<br>
                        <b>
                            {{ (analytics.successfulCalls - analytics.prevSuccessfulCalls).toLocaleString() }}
                            call{{ analytics.successfulCalls - analytics.prevSuccessfulCalls != 1 ? 's' : '' }}
                        </b>
                    </div>
                </template>
            </CallStatCard>
            <CallStatCard :showInfo="duration != 0">
                <template #icon>
                    <Clock
                        class="h-6 w-6 dark:text-gray-400 dark:hover:text-gray-700 group-hover:text-gray-50 dark:group-hover:text-gray-400" />
                </template>
                <template #versus v-if="duration != 0">
                    <TopRight v-if="analytics.followUpCalls > analytics.prevFollowUpCalls"
                        class="h-6 w-6 mr-2 text-green-500 group-hover:text-gray-200" />
                    <BottomRight v-else class="h-6 w-6 mr-2 text-red-500 group-hover:text-gray-200" />
                    {{ calcDiffPercentage(analytics.prevFollowUpCalls, analytics.followUpCalls) }}%
                </template>
                <template #value>
                    <el-tooltip effect="dark" :content="analytics.followUpCalls.toLocaleString()" placement="right">
                        {{ calcPercentage(analytics.totalCalls, analytics.followUpCalls) }}%
                    </el-tooltip>
                </template>
                <p>Follow Up Calls</p>
                <template #info>
                    <div class="text-xs mb-2">
                        Total {{ durationLbl[duration] }} ago:<br>
                        <b>
                            {{ analytics.prevFollowUpCalls.toLocaleString() }}
                            call{{ analytics.prevFollowUpCalls != 1 ? 's' : '' }}
                        </b>
                    </div>
                    <div class="text-xs">
                        Total increase:<br>
                        <b>
                            {{ (analytics.followUpCalls - analytics.prevFollowUpCalls).toLocaleString() }}
                            call{{ analytics.followUpCalls - analytics.prevFollowUpCalls != 1 ? 's' : '' }}
                        </b>
                    </div>
                </template>
            </CallStatCard>
            <CallStatCard :showInfo="duration != 0">
                <template #icon>
                    <More
                        class="h-6 w-6 dark:text-gray-400 dark:hover:text-gray-700 group-hover:text-gray-50 dark:group-hover:text-gray-400" />
                </template>
                <template #versus v-if="duration != 0">
                    <TopRight v-if="analytics.otherCategoryCalls > analytics.prevOtherCategoryCalls"
                        class="h-6 w-6 mr-2 text-green-500 group-hover:text-gray-200" />
                    <BottomRight v-else class="h-6 w-6 mr-2 text-red-500 group-hover:text-gray-200" />
                    {{ calcDiffPercentage(analytics.prevOtherCategoryCalls, analytics.otherCategoryCalls) }}%
                </template>
                <template #value>
                    <el-tooltip effect="dark" :content="analytics.otherCategoryCalls.toLocaleString()" placement="right">
                        {{ calcPercentage(analytics.totalCalls, analytics.otherCategoryCalls) }}%
                    </el-tooltip>
                </template>
                <p>Other Category Calls</p>
                <template #info>
                    <div class="text-xs mb-2">
                        Total {{ durationLbl[duration] }} ago:<br>
                        <b>
                            {{ analytics.prevOtherCategoryCalls.toLocaleString() }}
                            call{{ analytics.prevOtherCategoryCalls != 1 ? 's' : '' }}
                        </b>
                    </div>
                    <div class="text-xs">
                        Total increase:<br>
                        <b>
                            {{ (analytics.otherCategoryCalls - analytics.prevOtherCategoryCalls).toLocaleString() }}
                            call{{ analytics.otherCategoryCalls - analytics.prevOtherCategoryCalls != 1 ? 's' : '' }}
                        </b>
                    </div>
                </template>
            </CallStatCard>
        </div>
    </div>
</template>
