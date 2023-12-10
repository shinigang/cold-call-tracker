<script setup>
import { computed, nextTick, onMounted, ref } from 'vue';

import AppLayout from '@/Layouts/AppLayout.vue';
import ActiveCallers from '@/Components/Analytics/ActiveCallers.vue';
import CallStats from '@/Components/Analytics/CallStats.vue';
import CompanyFilters from '@/Components/Company/CompanyFilters.vue';
import CompanyItem from '@/Components/Company/CompanyItem.vue';
import NewCompany from '@/Components/Company/NewCompany.vue';
import ProcessCompany from '@/Components/Company/ProcessCompany.vue';

import { Pointer } from '@element-plus/icons-vue';
import axios from 'axios';

const props = defineProps({
    analytics: Object,
    company: Object,
    companies: Object,
    filters: Object,
    countries: Object,
    new: String,
});

const totalCompanies = ref(props.companies.total);
const companyItems = ref(props.companies.data);
const nextPageUrl = ref(props.companies.next_page_url);
const activeCompany = ref(props.company);
const hasNextCompany = ref(false);
const activeCallersData = ref(props.analytics.activeCallers);
const callStatsData = ref(props.analytics.calls);

const callStatsDuration = ref(30);
const nextCompany = ref(null);

const addMode = ref(props.new == 'true');

const companiesLoading = ref(false);
const companiesLoadedAll = computed(() => companyItems.value.length >= totalCompanies.value);
const companiesScrollDisabled = computed(() => companiesLoading.value || companiesLoadedAll.value);

const selectCompany = (company) => {
    const index = companyItems.value.findIndex(item => item.uuid == company.uuid);
    activeCompany.value = company;
    addMode.value = false;

    const nextCompanyIndex = index + 1;
    if (companyItems.value[nextCompanyIndex]) {
        hasNextCompany.value = true;
        nextCompany.value = {
            index: nextCompanyIndex,
            company: companyItems.value[nextCompanyIndex]
        };
    }
    else {
        hasNextCompany.value = false;
    }
    const newURL = new URL(window.location);
    newURL.searchParams.set('company', company.uuid);
    newURL.searchParams.delete('new');
    window.history.pushState({}, '', newURL);
};

const selectNextCompany = () => {
    activeCompany.value = nextCompany.value.company;
    const nextCompanyIndex = nextCompany.value.index + 1;

    if (companyItems.value[nextCompanyIndex]) {
        nextCompany.value = {
            index: nextCompanyIndex,
            company: companyItems.value[nextCompanyIndex]
        };
    }

    nextTick(() => {
        const elementToScroll = document.querySelector('.is-active');
        if (elementToScroll) {
            elementToScroll.scrollIntoView({ behavior: 'smooth' });
        }
    });
};

const unselectCompany = () => {
    activeCompany.value = null;
    hasNextCompany.value = false;

    const newURL = new URL(window.location);
    newURL.searchParams.delete('company');
    window.history.pushState({}, '', newURL);
}

const loadCompanies = async () => {
    companiesLoading.value = true;

    const response = await axios.get(nextPageUrl.value);
    nextPageUrl.value = response.data.next_page_url;
    companyItems.value = [...companyItems.value, ...response.data.data];

    companiesLoading.value = false;
};

const searchCompany = async (keyword, callStatus, country, state, city) => {
    let countryName = null;
    if (country) {
        const countryData = props.countries.find(propCountry => propCountry.iso2 == country);
        countryName = countryData.name;
    }
    const response = await axios.get(route('dashboard.searchCompany'), {
        params: {
            filter: 1,
            query: keyword,
            call_status: callStatus,
            filtered_country: countryName,
            filtered_state: state,
            filtered_city: city,
        }
    });
    companyItems.value = response.data.data;
    nextPageUrl.value = response.data.next_page_url;
    totalCompanies.value = response.data.total;
};

const isActiveCompany = (company) => {
    if (activeCompany.value) {
        return activeCompany.value.uuid == company.uuid;
    }
    return false;
};

const onAddCompanyEntry = () => {
    addMode.value = true;
    activeCompany.value = null;
    hasNextCompany.value = false;

    const newURL = new URL(window.location);
    newURL.searchParams.delete('company');
    newURL.searchParams.set('new', 'true');
    window.history.pushState({}, '', newURL);
};

const onCancelAddCompany = () => {
    addMode.value = false;

    const newURL = new URL(window.location);
    newURL.searchParams.delete('new');
    window.history.pushState({}, '', newURL);
}

const onCompanyAdded = (companyData) => {
    companyItems.value = [companyData, ...companyItems.value];
    selectCompany(companyData);
};

const onCompanyUpdated = (updatedData) => {
    const companyToUpdateIndex = companyItems.value.findIndex(companyItem => companyItem.id == updatedData.id);
    companyItems.value[companyToUpdateIndex] = updatedData;
    activeCompany.value = updatedData;
};

const updateAnalytics = async (statsType = 'all', interval) => {
    if (statsType == 'all' || statsType == 'calls') {
        callStatsDuration.value = interval;
    }
    const response = await axios.post(route('dashboard.analytics'), {
        stats_type: statsType,
        duration: interval
    });
    if (statsType == 'all' || statsType == 'calls') {
        callStatsData.value = response.data.calls;
    }
    if (statsType == 'all' || statsType == 'activeCallers') {
        activeCallersData.value = response.data.activeCallers;
    }
}

onMounted(() => {
    Echo.private(`user-activities`)
        .listen('UserMetadataUpdate', (e) => {
            updateAnalytics('activeCallers');
        });

    Echo.private(`call-updates`)
        .listenToAll((event, data) => {
            updateAnalytics('calls', callStatsDuration.value);
        });

    const elementToScroll = document.querySelector('.is-active');
    if (elementToScroll) {
        elementToScroll.scrollIntoView({ behavior: 'smooth' });
    }
});
</script>

<template>
    <AppLayout title="Dashboard">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Acquisition Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <CallStats :analytics="callStatsData" @update-analytics="updateAnalytics" />

                <CompanyFilters :filters="filters" :countries="countries" @search-company="searchCompany" />

                <div class="bg-white dark:bg-gray-800 shadow-xl mx-2 rounded-lg">
                    <div class="grid flex-none lg:flex">
                        <div class="min-w-full lg:min-w-[300px] lg:max-w-[300px] h-[640px]">
                            <div class="flex flex-col h-full border-r border-gray-100 dark:border-gray-700">
                                <div class="grow p-2">
                                    <p
                                        class="flex justify-start items-center border-b border-gray-200 dark:border-gray-700 dark:bg-gray-700 py-2 ps-3 -mx-[9px] -mt-[8px]">
                                        <el-tag size="small" effect="dark" round
                                            class="me-2 font-semibold !bg-indigo-500 !border-indigo-500">
                                            {{ (totalCompanies ?? 0).toLocaleString() }}
                                        </el-tag>
                                        <span
                                            class="uppercase text-sm font-semibold text-gray-700 dark:text-gray-50">Companies</span>
                                    </p>
                                    <div v-if="companyItems.length > 0" class="h-[calc(100%-60px)]">
                                        <div class="m-0 h-[310px] overflow-y-auto" v-infinite-scroll="loadCompanies"
                                            :infinite-scroll-disabled="companiesScrollDisabled">
                                            <ul class="divide-y divide-dashed divide-gray-200 dark:divide-gray-700">
                                                <CompanyItem v-for="(companyItem) in companyItems" :key="companyItem.id"
                                                    :class="`group/company ${isActiveCompany(companyItem) ? 'is-active' : ''}`"
                                                    :company="companyItem" @click="selectCompany(companyItem)" />
                                            </ul>
                                            <p v-if="companiesLoading" class="text-center">
                                                <el-tag size="large" type="info" effect="dark" round>
                                                    <div class="flex">
                                                        <svg class="animate-spin -ml-1 me-2 h-5 w-5 text-white inline-block"
                                                            xmlns="http://www.w3.org/2000/svg" fill="none"
                                                            viewBox="0 0 24 24">
                                                            <circle class="opacity-25" cx="12" cy="12" r="10"
                                                                stroke="currentColor" stroke-width="4"></circle>
                                                            <path class="opacity-75" fill="currentColor"
                                                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                                            </path>
                                                        </svg>
                                                        <span class="text-sm font-bold">Loading...</span>
                                                    </div>
                                                </el-tag>
                                            </p>
                                            <p v-if="companiesLoadedAll" class="text-xs">
                                                <el-divider>End</el-divider>
                                            </p>
                                        </div>
                                    </div>
                                    <el-empty v-else class="h-[calc(100%-60px)]" description="No company data available." />
                                    <button
                                        class="w-full text-sm py-1 font-bold rounded-md text-center border-dashed border-2 border-gray-300 dark:border-gray-700 hover:border-gray-500 dark:hover:border-gray-600 text-gray-600 dark:text-gray-500 dark:hover:text-gray-300"
                                        @click="onAddCompanyEntry">+ Add new entry</button>
                                </div>
                                <ActiveCallers class="flex-none p-2 h-[250px]" :callers="activeCallersData" />
                            </div>
                        </div>
                        <div class="grow w-full p-2 h-[640px]">
                            <NewCompany v-if="addMode" @company-added="onCompanyAdded" @cancel-add="onCancelAddCompany" />
                            <ProcessCompany v-else-if="activeCompany" :company="activeCompany"
                                :hasNextCompany="hasNextCompany" @next-company="selectNextCompany"
                                @company-updated="onCompanyUpdated" @unselect-company="unselectCompany" />
                            <div v-else class="pt-20 content-center text-center flex flex-col">
                                <Pointer class="my-5 h-[300px] text-gray-200 dark:text-gray-700" />
                                <span class="text-sm text-gray-400">Please select a company.</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
