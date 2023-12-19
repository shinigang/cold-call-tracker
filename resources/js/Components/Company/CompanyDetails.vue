<script setup>
import { ref, onMounted, watch, nextTick } from 'vue';
import { usePage, useForm } from '@inertiajs/vue3';
import { debounce } from 'lodash';

import FloatingLabel from '@/Components/FloatingLabel.vue';
import FloatingInput from '@/Components/FloatingInput.vue';
import InputError from '@/Components/InputError.vue';

const page = usePage();
const props = defineProps(['mode', 'company', 'errors']);

const emit = defineEmits(['detailsUpdated']);
const formMode = ref(props.mode ?? 'add');
const form = useForm({
    company: {
        id: props.company.id ?? '',
        description: props.company.description ?? '',
        industry: props.company.industry ?? '',
        total_employees: props.company.total_employees ?? '',
        email: props.company.email ?? '',
        website: props.company.website ?? '',
        linkedin: props.company.linkedin ?? '',
        address_street: props.company.address_street ?? '',
        address_city: props.company.address_city,
        address_state: props.company.address_state,
        address_country: props.company.address_country,
        address_zipcode: props.company.address_zipcode ?? ''
    }
});

const countryStates = ref([]);
const states = ref([]);
const cities = ref([]);

const onSelectCountry = async () => {
    if (form.company.address_country) {
        const countryIso2 = page.props.countries.find(pCountry => pCountry.name == form.company.address_country);
        const response = await axios.get(`api/states?filters[country_code]=${countryIso2.iso2}&fields=cities`);
        countryStates.value = response.data.data;
        states.value = response.data.data.map(state => ({ value: state.name, label: state.name }));
        let stateCities = [];
        response.data.data.forEach(state => {
            stateCities = [...stateCities, ...state.cities];
        });
        if (stateCities.length > 0) {
            cities.value = stateCities.map(city => ({ value: city.name, label: city.name }));
        }
    }
    else {
        states.value = [];
        cities.value = [];
    }
};
const onSelectState = () => {
    if (form.company.address_state) {
        const stateData = countryStates.value.filter(cState => cState.name == form.company.address_state)[0];
        if (stateData && stateData.cities.length > 0) {
            cities.value = stateData.cities.map(city => ({ value: city.name, label: city.name }));
        }
    }
    else {
        let stateCities = [];
        countryStates.value.forEach(state => {
            stateCities = [...stateCities, ...state.cities];
        });
        if (stateCities.length > 0) {
            cities.value = stateCities.map(city => ({ value: city.name, label: city.name }));
        }
    }
};

const getStatusBgColor = (status) => {
    const callStatus = page.props.callStatuses.find(cStatus => cStatus.status == status);
    if (callStatus) {
        return callStatus.group.color;
    }
    return '#var(--el-fill-color-blank)';
};

const updateCompany = debounce(async (company, $event, field) => {
    if (formMode.value == 'add') {
        form.company[field] = $event.target.value;
        emit('detailsUpdated', form.company);
    }
    else if (formMode.value == 'edit') {
        const newValue = $event.target.value;
        const origValue = company[field] ?? '';
        if (newValue != origValue) {
            form.clearErrors();

            const updateData = {
                fieldName: field
            };
            updateData[field] = newValue ?? '';
            try {
                const { data } = await axios.put(route('companies.update', company.id), updateData);
                emit('detailsUpdated', data);
            } catch (e) {
                form.errors[field] = e.response.data.errors[field][0];
            }
        }
    }
}, 500);

const updateCompanyModel = debounce(async (company, modelValue, field) => {
    if (formMode.value == 'add') {
        emit('detailsUpdated', form.company);
    }
    else if (formMode.value == 'edit') {
        form.clearErrors();

        const updateData = {
            company: company.id,
            _token: page.props.csrf_token,
        };
        updateData[field] = modelValue;
        updateData.fieldName = field;
        try {
            const { data } = await axios.put(route('companies.update', updateData));
            emit('detailsUpdated', data);
        } catch (e) {
            form.errors[field] = e.response.data.errors[field][0];
        }
    }
}, 500);

if (props.errors) {
    form.errors = {
        ...form.errors,
        ...props.errors
    };
}

onMounted(() => {
    if (formMode.value == 'edit') {
        if (props.company.address_country) {
            onSelectCountry();
            nextTick();
            if (props.company.address_state) {
                setTimeout(() => {
                    form.company.address_state = props.company.address_state;
                    onSelectState();
                }, 1000);
                if (props.company.address_city) {
                    setTimeout(() => {
                        form.company.address_city = props.company.address_city;
                    }, 2000);
                }
            }
        }
    }
});

watch(
    () => props.company,
    (company) => {
        if (formMode.value == 'edit') {
            form.company = {
                id: '',
                description: '',
                industry: '',
                total_employees: '',
                email: '',
                website: '',
                linkedin: '',
                address_street: '',
                address_city: '',
                address_state: '',
                address_country: '',
                address_zipcode: ''
            };
            setTimeout(() => {
                form.clearErrors();
                form.company = company;
                if (company.address_country) {
                    onSelectCountry();
                    if (company.address_state) {
                        setTimeout(() => {
                            form.company.address_state = company.address_state;
                            onSelectState();
                        }, 1000);
                        if (company.address_city) {
                            setTimeout(() => {
                                form.company.address_city = company.address_city;
                            }, 2000);
                        }
                    }
                }
            }, 1);
        }
    }
);
watch(
    () => props.errors,
    (errors) => {
        form.errors = errors;
    }
);
</script>

<template>
    <div class="company-details">
        <div class="grid gap-4 md:grid-cols-2 md:gap-6 mt-4">
            <div class="relative z-0 w-full group">
                <FloatingInput type="email" :value="form.company.email" id="email" name="email"
                    @blur="(e) => updateCompany(company, e, 'email')" />
                <FloatingLabel for="email" value="Email *" />
                <InputError :message="form.errors.email" class="mt-2" />
            </div>
            <div class="relative z-0 w-full group">
                <FloatingInput type="text" :value="form.company.website" id="website" name="website"
                    @blur="(e) => updateCompany(company, e, 'website')" />
                <FloatingLabel for="website" value="Website" />
                <InputError :message="form.errors.website" class="mt-2" />
            </div>

            <div class="relative z-0 w-full group">
                <FloatingInput type="text" :value="form.company.industry" id="industry" name="industry"
                    @blur="(e) => updateCompany(company, e, 'industry')" />
                <FloatingLabel for="industry" value="Industry" />
                <InputError :message="form.errors.industry" class="mt-2" />
            </div>
            <div class="relative z-0 w-full group">
                <FloatingInput type="number" min="1" :value="form.company.total_employees" id="total_employees"
                    name="total_employees" @blur="(e) => updateCompany(company, e, 'total_employees')" />
                <FloatingLabel for="total_employees" value="Total Employees" />
                <InputError :message="form.errors.total_employees" class="mt-2" />
            </div>

            <div class="relative z-0 w-full group">
                <FloatingInput type="text" :value="form.company.linkedin" id="linkedin" name="linkedin"
                    @blur="(e) => updateCompany(company, e, 'linkedin')" />
                <FloatingLabel for="linkedin" value="Linkedin" />
                <InputError :message="form.errors.linkedin" class="mt-2" />
            </div>
            <div class="relative z-0 w-full group">
                <FloatingInput type="text" :value="form.company.address_street" id="address_street" name="address_street"
                    @blur="(e) => updateCompany(company, e, 'address_street')" />
                <FloatingLabel for="address_street" value="Street Address" />
                <InputError :message="form.errors.address_street" class="mt-2" />
            </div>

            <div class="relative z-0 w-full group">
                <select v-model="form.company.address_country" id="address_country" name="address_country" placeholder=" "
                    @change="(e) => { onSelectCountry(); updateCompanyModel(company, form.company.address_country, 'address_country'); }"
                    class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer">
                    <option selected value="">Select a Country</option>
                    <option v-for="country in page.props.countries" :key="country.id">
                        {{ country.name }}
                    </option>
                </select>
                <FloatingLabel for="address_country" value="Country" />
                <InputError :message="form.errors.address_country" class="mt-2" />
            </div>
            <div class="relative z-0 w-full group">
                <select v-model="form.company.address_state" id="address_state" name="address_state" placeholder=" "
                    @change="(e) => { onSelectState(); updateCompanyModel(company, form.company.address_state, 'address_state'); }"
                    class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer">
                    <option selected value="">Select a State</option>
                    <option v-if="states.length" v-for="state in states" :key="state.id">
                        {{ state.label }}
                    </option>
                </select>
                <FloatingLabel for="address_state" value="State" />
                <InputError :message="form.errors.address_state" class="mt-2" />
            </div>

            <div class="relative z-0 w-full group">
                <select v-model="form.company.address_city" id="address_city" name="address_city" placeholder=" "
                    @change="(e) => updateCompanyModel(company, form.company.address_city, 'address_city')"
                    class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer">
                    <option selected value="">Select a City</option>
                    <option v-if="cities.length" v-for="city in cities" :key="city.id">
                        {{ city.label }}
                    </option>
                </select>
                <FloatingLabel for="address_city" value="City" />
                <InputError :message="form.errors.address_city" class="mt-2" />
            </div>
            <div class="relative z-0 w-full group">
                <FloatingInput type="text" :value="form.company.address_zipcode" id="address_zipcode" name="address_zipcode"
                    @blur="(e) => updateCompany(company, e, 'address_zipcode')" />
                <FloatingLabel for="address_zipcode" value="Zip code" />
                <InputError :message="form.errors.address_zipcode" class="mt-2" />
            </div>
        </div>
        <div class="relative z-0 w-full my-5 group">
            <textarea :value="form.company.description" id="description" name="description" rows="3" placeholder=" "
                @blur="(e) => updateCompany(company, e, 'description')"
                class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer" />
            <FloatingLabel for="description" value="Remarks" />
            <InputError :message="form.errors.description" class="mt-2" />
        </div>
    </div>
</template>
