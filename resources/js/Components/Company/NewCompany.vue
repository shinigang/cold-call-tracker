<script setup>
import { ref, onMounted } from 'vue';
import { usePage, useForm } from '@inertiajs/vue3';

import CompanyDetails from '@/Components/Company/CompanyDetails.vue';
import ContactPersons from '@/Components/Company/ContactPersons.vue';
import ContactNumbers from '@/Components/Company/ContactNumbers.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import LinkButton from '@/Components/LinkButton.vue';
import InputError from '@/Components/InputError.vue';

const page = usePage();

const emit = defineEmits(['companyAdded', 'cancelAdd']);

const activeCollapse = ref(['details', 'contactPersons', 'contactNumbers']);

const form = useForm({
    name: '',
    details: {
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
        address_zipcode: null,
    },
    contact_persons: [],
    contact_numbers: [],
    source: 'dashboard'
});

const getStatusBgColor = (status) => {
    const callStatus = page.props.callStatuses.find(cStatus => cStatus.status == status);
    if (callStatus) {
        return callStatus.group.color;
    }
    return '#var(--el-fill-color-blank)';
};

const onDetailsUpdated = (detailsData) => {
    form.details = detailsData;
};

const onPersonsUpdated = (personsData) => {
    form.contact_persons = personsData ?? [];
};

const onNumbersUpdated = (numbersData) => {
    form.contact_numbers = numbersData ?? [];
};

const onCancelAdd = () => {
    emit('cancelAdd');
};

const onSaveCompany = async () => {
    form.clearErrors();
    try {
        const storeData = {
            ...form.data(),
            ...form.details
        };
        delete storeData.id;
        delete storeData.details;

        const response = await axios.post(route('companies.store', {
            ...storeData,
            _token: page.props.csrf_token
        }));
        form.reset();
        emit('companyAdded', response.data);
    } catch (e) {
        const errors = [];
        for (const key in e.response.data.errors) {
            errors[key] = e.response.data.errors[key][0];
        }
        form.setError(errors);
    }
};

const focusNameInput = () => {
    document.getElementById('companyName').focus();
};

onMounted(focusNameInput);
</script>

<template>
    <div class="company-details">
        <div class="flex items-center border-b border-gray-200 dark:border-gray-700">
            <div class="grow">
                <div class="relative z-0 w-full mt-2 mb-0 group">
                    <input v-model="form.name" type="text" name="floating_name" id="companyName"
                        class="text-3xl block py-2.5 px-0 w-full text-gray-900 bg-transparent border-0 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer"
                        placeholder=" " required />
                    <label for="companyName"
                        class="peer-focus:font-xs absolute text-3xl text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-7 scale-[.5] top-3 -z-10 origin-[0] peer-focus:start-0 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto peer-focus:text-indigo-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-[.5] peer-focus:-translate-y-7">
                        Company Name *
                    </label>
                    <InputError :message="form.errors.name" class="mt-2" />
                </div>
            </div>
            <div class="px-3">
                <LinkButton @click="onCancelAdd">Cancel</LinkButton>
                <el-divider direction="vertical" class="inline-block" />
                <PrimaryButton @click="onSaveCompany" class="ms-1">
                    Save
                </PrimaryButton>
            </div>
        </div>
        <div class="h-[565px] overflow-y-auto">
            <div class="md:grid md:grid-cols-2 md:gap-2 h-full">
                <div class="mt-5 md:mt-0 md:col-span-2 p-3">
                    <el-tag effect="dark" round
                        :style="`background-color: ${getStatusBgColor('Unprocessed')}; border-color: ${getStatusBgColor('Unprocessed')}`">
                        <i>Call Status</i>
                        <el-divider direction="vertical" class="-top-[1px] !mx-1" />
                        Unprocessed
                    </el-tag>

                    <el-collapse class="mt-4" v-model="activeCollapse">
                        <el-collapse-item title="Company Details" name="details">
                            <CompanyDetails mode="add" :company="form.details" :errors="form.errors"
                                @details-updated="onDetailsUpdated" />
                        </el-collapse-item>
                        <el-collapse-item title="Contact Persons" name="contactPersons">
                            <ContactPersons mode="add" :persons="form.contact_persons" :errors="form.errors"
                                @persons-updated="onPersonsUpdated" />
                        </el-collapse-item>
                        <el-collapse-item title="Contact Numbers" name="contactNumbers">
                            <ContactNumbers mode="add" :numbers="form.contact_numbers" :errors="form.errors"
                                @numbers-updated="onNumbersUpdated" />
                        </el-collapse-item>
                    </el-collapse>
                </div>

            </div>
        </div>
    </div>
</template>
