<script setup>
import { ref, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { debounce } from 'lodash';

const page = usePage();

import { useSelectOptions } from '@/Composables/useSelectOptions';

const props = defineProps({
    filters: Object,
    countries: Object
});

const emit = defineEmits(['searchCompany']);

const states = ref([]);
const cities = ref([]);

const searchKeyword = ref(props.filters.query);
const callStatus = ref(props.filters.status);
const selectedCountry = ref(props.filters.country ? props.countries.find(cntry => cntry.name == props.filters.country).iso2 : null);
const selectedState = ref(props.filters.state);
const selectedCity = ref(props.filters.city);

const callStatuses = ref(page.props.callStatuses);

const countryStates = ref([]);
const { selectOptions: countryOptions } = useSelectOptions(props.countries, 'iso2', 'name');
const onSelectCountry = async () => {
    selectedState.value = null;
    selectedCity.value = null;

    if (selectedCountry.value) {
        const response = await axios.get(`api/states?filters[country_code]=${selectedCountry.value}&fields=cities`);
        countryStates.value = response.data.data;
        states.value = response.data.data.map(state => ({ value: state.name, label: state.name }));
        let stateCities = [];
        response.data.data.forEach(state => {
            stateCities = [...stateCities, ...state.cities];
        });
        cities.value = stateCities.map(city => ({ value: city.name, label: city.name }));
    }
    else {
        states.value = [];
        cities.value = [];
    }

    searchCompanies();
};
const onSelectState = () => {
    selectedCity.value = null;
    if (selectedState.value) {
        const stateData = countryStates.value.filter(cState => cState.name == selectedState.value)[0];
        cities.value = stateData.cities.map(city => ({ value: city.name, label: city.name }));
    }
    else {
        let stateCities = [];
        countryStates.value.forEach(state => {
            stateCities = [...stateCities, ...state.cities];
        });
        cities.value = stateCities.map(city => ({ value: city.name, label: city.name }));
    }

    searchCompanies();
};
const searchCompanies = debounce(() => {
    emit('searchCompany', searchKeyword.value, callStatus.value, selectedCountry.value, selectedState.value, selectedCity.value);
}, 500);

onMounted(() => {
    // if (searchKeyword.value) {
    //     emit('searchCompany', searchKeyword.value, callStatus.value, selectedCountry.value, selectedState.value, selectedCity.value);
    // }
    if (selectedCountry.value != null) {
        onSelectCountry();
    }
});

</script>

<template>
    <div
        class="focus-within:border-indigo-400 lg:h-12 mx-3 mb-5 searh-field flex flex-col lg:flex-row items-center border-0 lg:border-b transition-all js-search border-gray-300 dark:border-gray-700">
        <input v-model="searchKeyword" type="text" placeholder="Type here to search companies..." @input="searchCompanies"
            class="placeholder:italic placeholder:text-slate-400 grow w-full lg:w-auto h-12 lg:h-auto bg-transparent text-black dark:text-gray-300 border-b border-gray-300 dark:border-gray-700 lg:border-0 mb-2 lg:mb-0 text-medium border-0 pb-0 focus:outline-none focus:ring-transparent focus:border-purple-100 ltr:pr-10 rtl:pl-10">
        <el-select v-model="callStatus" filterable clearable placeholder="Select Call Status" class="w-full lg:w-[165px]"
            @change="searchCompanies">
            <el-option v-for="callStatus in callStatuses" :key="callStatus.status" :label="callStatus.status"
                :value="callStatus.status" />
        </el-select>
        <el-select-v2 v-model="selectedCountry" filterable :options="countryOptions" @change="onSelectCountry"
            placeholder="Select Country" clearable class="mt-2 lg:mt-0 lg:ms-2 w-full lg:w-[165px]" />
        <el-select-v2 v-model="selectedState" filterable :options="states" @change="onSelectState"
            :disabled="states.length == 0" placeholder="Select State" clearable
            class="mt-2 lg:mt-0 lg:ms-2 w-full lg:w-[165px]" />
        <el-select-v2 v-model="selectedCity" filterable :options="cities" @change="searchCompanies"
            :disabled="cities.length == 0" placeholder="Select City" clearable
            class="mt-2 lg:mt-0 lg:ms-2 w-full lg:w-[165px]" />
    </div>
</template>
