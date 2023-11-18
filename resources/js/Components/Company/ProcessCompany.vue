<script setup>
import { ref } from 'vue';
// import { useForm } from '@inertiajs/vue3';
import CompanyCalls from '@/Components/Company/CompanyCalls.vue';
import ActionLogs from '@/Components/Company/ActionLogs.vue';
import { ArrowRight } from '@element-plus/icons-vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const props = defineProps({
    company: Object,
});

const companyName = ref(props.company.name);

const emit = defineEmits(['nextCompany']);

const onNextCompany = () => {
    emit('nextCompany');
};

const getStatusBgColor = (status) => {
    const callStatus = page.props.callStatuses.find(cStatus => cStatus.status == status);
    if (callStatus) {
        return callStatus.group.color;
    }
    return '#var(--el-fill-color-blank)';
};
</script>

<template>
    <div class="flex items-center border-b border-gray-200 dark:border-gray-700">
        <div class="grow">
            <div class="relative z-0 w-full mt-2 mb-0 group">
                <input :value="company.name" type="text" name="floating_name" id="floating_name"
                    class="text-3xl block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer"
                    placeholder=" " required />
                <label for="floating_name"
                    class="peer-focus:font-xs absolute text-3xl text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-7 scale-[.5] top-3 -z-10 origin-[0] peer-focus:start-0 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto peer-focus:text-indigo-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-[.5] peer-focus:-translate-y-7">Company
                    Name</label>
            </div>
        </div>
        <div class="px-3">
            <el-button round @click="onNextCompany" class="relative">
                Next
                <ArrowRight class="w-5 h-5 -me-[8px]" />
            </el-button>
        </div>
    </div>
    <div class="md:grid md:grid-cols-3 md:gap-2 h-full">
        <div class="mt-5 md:mt-0 md:col-span-2 p-3">
            <el-tag effect="dark" round
                :style="`background-color: ${getStatusBgColor(company.call_status)}; border-color: ${getStatusBgColor(company.call_status)}`">
                <i>Call Status</i>
                <el-divider direction="vertical" class="-top-[1px] !mx-1" />
                {{ company.call_status }}
            </el-tag>


            <ActionLogs :logs="company.actionLogs" />
        </div>
        <div class="md:col-span-1 border-0 md:border-l border-gray-200 dark:border-gray-700 px-3">
            <div class="flex justify-between flex-col">
                <p
                    class="flex justify-start items-center border-b border-gray-200 dark:border-gray-700 dark:bg-gray-700 py-2 ps-3 -mx-[9px]">
                    <el-tag size="small" effect="dark" round class="me-2 font-semibold !bg-indigo-500 !border-indigo-500">{{
                        (company.calls.length ?? 0).toLocaleString()
                    }}</el-tag>
                    <span class="uppercase text-sm font-semibold text-gray-700 dark:text-gray-50">Call Logs</span>
                </p>
                <CompanyCalls :calls="company.calls" />
            </div>
        </div>
    </div>
</template>
