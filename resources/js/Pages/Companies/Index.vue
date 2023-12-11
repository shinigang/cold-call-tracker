<script setup>
import { ref } from 'vue';
import { Link, router, usePage, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import ImportCompaniesModal from '@/Components/Company/ImportCompaniesModal.vue';
import { ElMessage, ElMessageBox } from 'element-plus';
import { Search } from '@element-plus/icons-vue';
import { debounce } from 'lodash';

defineProps(['companies', 'search']);

const modalVisible = ref(false);
const onCompaniesImported = () => {
    router.reload({ preserveState: false, preserveScroll: false, forceFormData: true });
};
const onModalClosed = () => {
    modalVisible.value = false;
};

const page = usePage();

const form = useForm({
    query: page.props.search.query,
    call_status: page.props.search.call_status
});

const callStatuses = ref(page.props.callStatuses.reverse());

const getStatusColor = (status) => {
    const callStatus = page.props.callStatuses.find(cStatus => cStatus.status == status);
    if (callStatus) {
        return callStatus.group.color;
    }
    return '#var(--el-fill-color-blank)';
};

const addCompany = () => {
    router.visit(route('dashboard'), { data: { new: true } });
};

const downloadExport = () => {
    window.location.href = route('companies.export');
};

const search = debounce(() => {
    form.get(route('companies.index'), {
        preserveState: true,
        preserveScroll: true
    });
}, 500);

const destroy = (company) => {
    ElMessageBox.confirm(
        `Are you sure you want to delete ${company.name}?`,
        'Warning',
        {
            confirmButtonText: 'Yes',
            cancelButtonText: 'Cancel',
            type: 'warning',
        }
    )
        .then(() => {
            const destroyData = {
                company: company.id
            };
            router.delete(route('companies.destroy', destroyData));
            ElMessage({
                type: 'success',
                message: `${company.name} removed`,
            });
        })
        .catch(() => {
            // console.log('remove canceled');
            // ElMessage({
            //     type: 'info',
            //     message: 'Remove canceled',
            // });
        });
};
</script>


<template>
    <AppLayout title="Companies">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                List of Companies
            </h2>
        </template>

        <ImportCompaniesModal :show="modalVisible" @companies-imported="onCompaniesImported"
            @modal-closed="onModalClosed" />

        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

                <div class="flex flex-col lg:flex-row gap-2 lg:gap-10 justify-center items-center mb-2">
                    <div class="grow">
                        <el-input v-model="form.query" placeholder="Search companies..." @input="search" clearable
                            class="focus:outline-0 focus:shadow-none">
                            <template #prepend>
                                <el-select v-model="form.call_status" placeholder="Select" class="border-0" @change="search"
                                    :default-first-option="true">
                                    <el-option label="All Call Status" value="All" />
                                    <el-option v-for="callStatus in callStatuses" :key="callStatus.status"
                                        :label="callStatus.status" :value="callStatus.status" />
                                </el-select>
                            </template>
                            <template #append>
                                <el-button :icon="Search" />
                            </template>
                        </el-input>
                    </div>
                    <div class="text-right">
                        <PrimaryButton type="button" @click="addCompany">Add</PrimaryButton>
                        <PrimaryButton type="button" @click="modalVisible = true" class="ms-2">Import</PrimaryButton>
                        <PrimaryButton type="button" :disabled="companies.data.length == 0" class="ms-2"
                            @click="downloadExport">
                            Export
                        </PrimaryButton>
                    </div>
                </div>

                <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                            <tr>
                                <th scope="col" class="px-6 py-3">Name / Contact Person</th>
                                <th scope="col" class="px-6 py-3">Email / Contact Number</th>
                                <th scope="col" class="px-6 py-3">Address</th>
                                <th scope="col" class="px-6 py-3 text-center">Call Status</th>
                                <th scope="col" class="px-6 py-3 text-center">Employees</th>
                                <th scope="col" class="px-6 py-3">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="companies.data.length" v-for="company in companies.data" :key="company.id"
                                class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                <td scope="row" class="px-6 py-4 text-gray-900 dark:text-gray-200">
                                    <div class="font-medium truncate">
                                        {{ company.name }}
                                    </div>
                                    <div v-if="company.contact_persons.length" class="text-xs truncate">
                                        {{ company.contact_persons[0].first_name }}
                                        {{ company.contact_persons[0].last_name }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-xs truncate">
                                        {{ company.email }}
                                    </div>
                                    <div v-if="company.contact_numbers.length" class="text-xs truncate">
                                        {{ company.contact_numbers[0].label }}:
                                        {{ company.contact_numbers[0].number }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-medium truncate">
                                        {{ company.address_street }}
                                    </div>
                                    <div class="text-xs truncate">
                                        {{ company.address_city }}
                                        {{ company.address_state }}
                                        {{ company.address_country }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="text-xs" :style="`color: ${getStatusColor(company.call_status)};}`">
                                        {{ company.call_status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    {{ (company.total_employees ?? 0).toLocaleString() }}
                                </td>
                                <td class="flex items-center px-6 py-4 space-x-3">
                                    <Link :href="`${route('dashboard')}?company=${company.uuid}`"
                                        class="font-medium text-blue-600 dark:text-blue-500 hover:underline">Edit</Link>
                                    <span @click="destroy(company)"
                                        class="cursor-pointer font-medium text-red-600 dark:text-red-500 hover:underline">Remove</span>
                                </td>
                            </tr>

                            <tr v-else>
                                <td colspan="6">
                                    <el-empty description="No Companies Data" />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <Pagination :links="companies.links" />
            </div>
        </div>
    </AppLayout>
</template>
