<script setup>
import { usePage } from '@inertiajs/vue3';

import AppLayout from '@/Layouts/AppLayout.vue';
import CallerDashboard from '@/Pages/Dashboard/Partials/Caller.vue';
import ConsultantDashboard from '@/Pages/Dashboard/Partials/Consultant.vue';
import { ref } from 'vue';

const page = usePage();
const props = defineProps({
    analytics: Object,
    company: Object,
    companies: Object,
    filters: Object,
    countries: Object,
    newCompany: String,
});
const role = page.props.authUserCurrentTeam.role.key;
const isAdmin = role == 'admin' || role == 'owner';
const activeDashboard = ref(role == 'consultant' ? 'consultant' : 'caller');

const changeDashboard = (dashboardName) => {
    activeDashboard.value = dashboardName;
};
</script>

<template>
    <AppLayout title="Dashboard">
        <template #header>
            <div v-if="isAdmin">
                <el-select v-model="activeDashboard" class="dashboard-select" placeholder="Select Dashboard" size="large">
                    <el-option value="caller" label="Caller Dashboard" />
                    <el-option value="consultant" label="Consultant Dashboard" />
                </el-select>
            </div>
            <div v-else>
                <h2 v-if="role == 'consultant'"
                    class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Consultant Dashboard
                </h2>
                <h2 v-else class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Caller Dashboard
                </h2>
            </div>
        </template>

        <ConsultantDashboard v-if="activeDashboard == 'consultant'" :analytics="analytics" />
        <CallerDashboard v-else-if="activeDashboard == 'caller'" :analytics="analytics" :company="company"
            :companies="companies" :filters="filters" :countries="countries" :newCompany="newCompany" />
        <el-empty v-else description="No dashboard to show" />
    </AppLayout>
</template>

<style>
.dashboard-select .select-trigger .el-input__inner {
    font-weight: bold;
}
</style>
