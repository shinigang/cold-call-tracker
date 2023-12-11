<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';
import localizedFormat from 'dayjs/plugin/localizedFormat';

dayjs.extend(relativeTime);
dayjs.extend(localizedFormat);

defineProps(['calls']);

const downloadExport = () => {
    window.location.href = route('calls.export');
};
</script>

<template>
    <AppLayout title="Calls">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                List of Calls
            </h2>
        </template>

        <div class="py-4">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

                <div class="text-right mb-2">
                    <PrimaryButton type="button" :disabled="calls.data.length == 0" class="ms-2" @click="downloadExport">
                        Export
                    </PrimaryButton>
                </div>

                <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                            <tr>
                                <th scope="col" class="px-6 py-3">Caller / Consultant</th>
                                <th scope="col" class="px-6 py-3">Company</th>
                                <th scope="col" class="px-6 py-3">Contact No.</th>
                                <th scope="col" class="px-6 py-3">Date &amp; Time</th>
                                <th scope="col" class="px-6 py-3">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="calls.data.length" v-for="call in calls.data" :key="call.id"
                                class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                <td scope="row"
                                    class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                    {{ call.user.name }}
                                    <small v-if="call.status == 'Set Appointment Date'" class="block text-sm">
                                        Consultant: {{ call.consultant.name }}
                                    </small>
                                </td>
                                <td class="px-6 py-4">
                                    {{ call.company.name }}
                                </td>
                                <td class="px-6 py-4">
                                    {{ call.contact_number }}
                                </td>
                                <td class="px-6 py-4">
                                    <el-tooltip effect="dark" :content="dayjs(call.called_at).format('lll')"
                                        placement="top">
                                        {{ dayjs(call.called_at).fromNow() }}
                                    </el-tooltip>
                                </td>
                                <td class="px-6 py-4">
                                    {{ call.status }}
                                    <small v-if="call.status == 'Call again on Date'" class="block">
                                        {{ dayjs(call.follow_up_at).format('L LT') }}
                                    </small>
                                    <small v-if="call.status == 'Set Appointment Date'" class="block">
                                        {{ dayjs(call.appointment_at).format('L LT') }}
                                    </small>
                                </td>
                            </tr>
                            <tr v-else>
                                <td colspan="5">
                                    <el-empty description="No Calls Data" />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <Pagination :links="calls.links" />
            </div>
        </div>
    </AppLayout>
</template>
