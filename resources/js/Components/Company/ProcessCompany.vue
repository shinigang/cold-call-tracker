<script setup>
import { ref, watch } from 'vue';
import { usePage, useForm } from '@inertiajs/vue3';
import CompanyDetails from '@/Components/Company/CompanyDetails.vue';
import ContactPersons from '@/Components/Company/ContactPersons.vue';
import ContactNumbers from '@/Components/Company/ContactNumbers.vue';
import CompanyCalls from '@/Components/Company/CompanyCalls.vue';
import ActionLogs from '@/Components/Company/ActionLogs.vue';
import AssignmentLogs from '@/Components/Company/AssignmentLogs.vue';
import DiscussionLogs from '@/Components/Company/DiscussionLogs.vue';
import LinkButton from '@/Components/LinkButton.vue';
import InputError from '@/Components/InputError.vue';
import { ArrowRight, Delete } from '@element-plus/icons-vue';
import { debounce } from 'lodash';

import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';
import localizedFormat from 'dayjs/plugin/localizedFormat';

dayjs.extend(relativeTime);
dayjs.extend(localizedFormat);

const page = usePage();
const props = defineProps({
    company: Object,
    hasNextCompany: Boolean
});

const emit = defineEmits(['companyUpdated', 'nextCompany', 'unselectCompany']);

const onNextCompany = () => {
    emit('nextCompany');
};

const onUnselectCompany = () => {
    emit('unselectCompany')
};

const onCompanyUpdated = (newCompanyData) => {
    emit('companyUpdated', newCompanyData);
};

const form = useForm({
    company: {
        id: props.company.id,
        name: props.company.name,
        description: props.company.description ?? '',
        industry: props.company.industry ?? '',
        total_employees: props.company.total_employees ?? '',
        email: props.company.email ?? '',
        website: props.company.website ?? '',
        linkedin: props.company.linkedin ?? '',
        address_street: props.company.address_street ?? '',
        address_city: props.company.address_city ?? '',
        address_state: props.company.address_state ?? '',
        address_country: props.company.address_country ?? '',
        address_zipcode: props.company.address_zipcode ?? '',
        contact_persons: props.company.contact_persons ?? [],
        contact_numbers: props.company.contact_numbers ?? [],
        assigned_caller: props.company.assigned_caller,
        assigned_consultant: props.company.assigned_consultant,
        follow_up_date: props.company.follow_up_date ?? '',
        appointment_date: props.company.appointment_date ?? '',
        call_status: props.company.call_status,
        calls: props.company.calls ?? [],
        action_logs: props.company.action_logs ?? [],
        assignments: props.company.assignments ?? []
    }
});

const activeTab = ref('activities');
const activeCollapse = ref(['details', 'contactPersons', 'contactNumbers', 'comments']);

const getStatusBgColor = (status) => {
    const callStatus = page.props.callStatuses.find(cStatus => cStatus.status == status);
    if (callStatus) {
        return callStatus.group.color;
    }
    return '#var(--el-fill-color-blank)';
};

const updateCompany = debounce(async (company, $event, field) => {
    const newValue = $event.target.value;
    const origValue = company[field] ?? '';
    if (newValue != origValue) {
        form.clearErrors();

        const updateData = {
            company: company.id,
            _token: page.props.csrf_token,
        };
        updateData[field] = newValue ?? '';
        updateData.fieldName = field;
        try {
            const response = await axios.put(route('companies.update', updateData));
            emit('companyUpdated', response.data);
        } catch (e) {
            form.errors[field] = e.response.data.errors[field][0];
        }
    }
}, 500);

const onCallAdded = (newCallResponse) => {
    form.company.calls = newCallResponse.company.calls;
    form.company.action_logs = newCallResponse.company.action_logs;
    form.company.assignments = newCallResponse.company.assignments;
    form.company.call_status = newCallResponse.call.status;
    form.company.assigned_caller = newCallResponse.call.user;
    if (newCallResponse.call.consultant) {
        form.company.assigned_consultant = newCallResponse.call.consultant;
    }

    emit('companyUpdated', newCallResponse.company);
};

watch(
    () => props.company,
    (company) => {

        form.company = {
            id: '',
            name: '',
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
            address_zipcode: '',
            contact_persons: [],
            contact_numbers: [],
            assigned_caller: null,
            assigned_consultant: null,
            follow_up_date: '',
            appointment_date: '',
            call_status: '',
            calls: [],
            action_logs: [],
            assignments: []
        };
        setTimeout(() => {
            form.clearErrors();
            form.company = company;
        }, 1);
    }
);
</script>

<template>
    <div class="company-details">
        <div class="flex items-center border-b border-gray-200 dark:border-gray-700">
            <div class="grow">
                <div class="relative z-0 w-full mt-2 mb-0 group">
                    <input :value="form.company.name" type="text" name="floating_name" id="floating_name"
                        @blur="(e) => updateCompany(company, e, 'name')"
                        class="text-3xl block py-2.5 px-0 w-full text-gray-900 bg-transparent border-0 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer"
                        placeholder=" " required />
                    <label for="floating_name"
                        class="peer-focus:font-xs absolute text-3xl text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-7 scale-[.5] top-3 -z-10 origin-[0] peer-focus:start-0 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto peer-focus:text-indigo-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-[.5] peer-focus:-translate-y-7">
                        Company Name*
                    </label>
                    <InputError :message="form.errors.name" class="mt-2" />
                </div>
            </div>
            <div class="px-3">
                <LinkButton @click="onUnselectCompany">Close</LinkButton>
                <el-divider v-if="hasNextCompany" direction="vertical" class="inline-block" />
                <el-button v-if="hasNextCompany" round @click="onNextCompany" class="relative">
                    <span class="inline-block me-[1px]">Next</span>
                    <ArrowRight class="w-5 h-5 -me-[6px]" />
                </el-button>
            </div>
        </div>
        <div class="h-[565px] overflow-y-auto">
            <div class="md:grid md:grid-cols-3 md:gap-2 h-full">
                <div class="mt-5 md:mt-0 md:col-span-2 p-3">
                    <el-tag effect="dark" round class="me-1 my-[2px]"
                        :style="`background-color: ${getStatusBgColor(form.company.call_status)}; border-color: ${getStatusBgColor(form.company.call_status)}`">
                        <i>Call Status</i>
                        <el-divider direction="vertical" class="-top-[1px] !mx-1" />
                        {{ form.company.call_status }}
                    </el-tag>

                    <el-tag v-if="form.company.call_status == 'Set Appointment Date' && form.company.appointment_date != ''"
                        effect="light" round class="my-[2px]">
                        <i>Appointment on</i>
                        <el-divider direction="vertical" class="-top-[1px] !mx-1" />
                        {{ dayjs(form.company.appointment_date).format('lll') }}
                    </el-tag>

                    <el-tag v-if="form.company.call_status == 'Call again on Date' && form.company.follow_up_date != ''"
                        effect="light" round class="my-[2px]">
                        <i>Follow up on</i>
                        <el-divider direction="vertical" class="-top-[1px] !mx-1" />
                        {{ dayjs(form.company.follow_up_date).format('lll') }}
                    </el-tag>

                    <el-collapse class="mt-4" v-model="activeCollapse">
                        <el-collapse-item title="Company Details" name="details">
                            <CompanyDetails :company="form.company" mode="edit" @details-updated="onCompanyUpdated" />
                        </el-collapse-item>
                        <el-collapse-item title="Contact Persons" name="contactPersons">
                            <ContactPersons mode="edit" :companyId="company.id" :persons="form.company.contact_persons"
                                @persons-updated="onCompanyUpdated" />
                        </el-collapse-item>
                        <el-collapse-item title="Contact Numbers" name="contactNumbers">
                            <ContactNumbers mode="edit" :companyId="company.id" :numbers="form.company.contact_numbers"
                                @numbers-updated="onCompanyUpdated" />
                        </el-collapse-item>
                        <el-collapse-item title="Comments" name="comments">
                            <DiscussionLogs :comments="company.comments" class="!p-0" />
                        </el-collapse-item>
                    </el-collapse>
                </div>
                <div class="md:col-span-1 px-3">

                    <div v-if="form.company.assigned_caller" class="flex items-center gap-4 mt-3">
                        <img class="w-10 h-10 rounded-full" :src="form.company.assigned_caller.profile_photo_url" alt="">
                        <div class="font-medium dark:text-white">
                            <div>{{ form.company.assigned_caller.name }}</div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Last Caller</div>
                        </div>
                    </div>

                    <div v-if="form.company.assigned_consultant" class="flex items-center gap-4 mt-2">
                        <img class="w-10 h-10 rounded-full" :src="form.company.assigned_consultant.profile_photo_url"
                            alt="">
                        <div class="font-medium dark:text-white">
                            <div>{{ form.company.assigned_consultant.name }}</div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Web Consultant</div>
                        </div>
                    </div>

                    <div class="flex justify-between flex-col">
                        <p
                            class="flex justify-start items-center border border-gray-200 dark:border-gray-700 dark:bg-gray-700 py-2 ps-3 -mx-[9px] mt-3">
                            <el-tag size="small" effect="dark" round
                                class="me-2 font-semibold !bg-indigo-500 !border-indigo-500">{{
                                    (form.company.calls.length ?? 0).toLocaleString()
                                }}</el-tag>
                            <span class="uppercase text-sm font-semibold text-gray-700 dark:text-gray-50">Call Logs</span>
                        </p>
                        <CompanyCalls :company="company" :calls="company.calls" @call-added="onCallAdded" />
                    </div>

                    <el-divider border-style="dashed" />

                    <el-tabs v-model="activeTab" :stretch="true">
                        <el-tab-pane label="Activities" name="activities">
                            <ActionLogs :actionLogs="form.company.action_logs" />
                        </el-tab-pane>
                        <el-tab-pane label="Assignments" name="assignments">
                            <AssignmentLogs :assignments="form.company.assignments" />
                        </el-tab-pane>
                    </el-tabs>
                </div>
            </div>
        </div>
    </div>
</template>
