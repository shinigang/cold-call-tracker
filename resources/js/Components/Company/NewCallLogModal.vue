<script setup>
import { ref, watch } from 'vue';
import { usePage, useForm } from '@inertiajs/vue3';

import DialogModal from '@/Components/DialogModal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';
import axios from 'axios';

dayjs.extend(relativeTime);

const emit = defineEmits(['callAdded', 'modalClosed']);

const props = defineProps({
    show: Boolean,
    company: Object
});

const page = usePage();

const callStatuses = ref(page.props.callStatuses.filter(s => s.status != 'Unprocessed').reverse());
const consultants = ref(page.props.consultants);
const contactNumbers = ref(props.company.contact_numbers.map(contact => ({ value: contact.number, label: contact.label })) ?? []);

const querySearch = (queryString, cb) => {
    const results = queryString ? contactNumbers.value.filter(createFilter(queryString)) : contactNumbers.value;
    cb(results);
};
const createFilter = (queryString) => {
    return (contactNumber) => {
        return (
            contactNumber.value.toLowerCase().indexOf(queryString.toLowerCase()) >= 0
        );
    };
};

const form = useForm({
    company_id: props.company.id,
    contact_number: props.company.contact_numbers.length > 0 ? props.company.contact_numbers[0].number : '',
    status: '',
    called_at: dayjs().toString(),
    follow_up_at: '',
    appointment_at: '',
    consultant_id: '',
    meeting_email: '',
    source: 'dashboard'
});

const disabledPastDates = (time) => {
    const date = new Date();
    const previousDate = date.setDate(date.getDate() - 1);
    return time.getTime() < previousDate;
}

const shortcuts = [
    {
        text: 'Today',
        value: new Date(),
    },
    {
        text: 'Tomorrow',
        value: () => {
            const date = new Date()
            date.setTime(date.getTime() + 3600 * 1000 * 24)
            return date
        },
    },
    {
        text: 'Next week',
        value: () => {
            const date = new Date()
            date.setTime(date.getTime() + 3600 * 1000 * 24 * 7)
            return date
        },
    },
]

const closeModal = () => {
    form.clearErrors();
    form.reset();
    emit('modalClosed');
};

const saveCallLog = async () => {
    form.clearErrors();
    try {
        const response = await axios.post(route('calls.store', {
            ...form.data(),
            _token: page.props.csrf_token
        }));
        form.reset();
        closeModal();
        emit('callAdded', response.data);
    } catch (e) {
        const errors = [];
        for (const key in e.response.data.errors) {
            errors[key] = e.response.data.errors[key][0];
        }
        form.setError(errors);
    }
};

watch(
    () => props.company,
    (company) => {

        form.company_id = '';
        form.contact_number = '';
        form.called_at = dayjs().toString();
        contactNumbers.value = []
        setTimeout(() => {
            form.clearErrors();
            form.company_id = company.id;
            form.contact_number = company.contact_numbers.length > 0 ? company.contact_numbers[0].number : '';
            contactNumbers.value = company.contact_numbers.map(contact => ({ value: contact.number, label: contact.label })) ?? [];
        }, 1);
    }
);
</script>

<template>
    <DialogModal :show="show" @close="closeModal">
        <template #title>
            Add Call Log
            <p class="text-sm text-gray-500">Call {{ company.name }}</p>
        </template>

        <template #content>
            <div class="grid gap-4 md:grid-cols-2 md:gap-6">
                <div>
                    <InputLabel for="contactNumber" value="Contact Number" />
                    <el-autocomplete id="contactNumber" v-model="form.contact_number" :fetch-suggestions="querySearch"
                        clearable placeholder="Input Contact Number" class="w-full" aria-autocomplete="off"
                        autocomplete="off">
                        <template #default="{ item }">
                            <span class="text-sm font-bold">{{ item.label }}</span>:
                            <span class="text-sm">{{ item.value }}</span>
                        </template>
                    </el-autocomplete>
                    <InputError :message="form.errors.contact_number" class="mt-2" />
                </div>
                <div>
                    <InputLabel for="calledAt" value="Call date and time" />
                    <el-date-picker id="calledAt" v-model="form.called_at" type="datetime"
                        placeholder="Select call date and time" :shortcuts="shortcuts" class="w-full" />
                    <InputError :message="form.errors.called_at" class="mt-2" />
                </div>

                <div>
                    <InputLabel for="callStatus" value="Status" />
                    <el-select id="callStatus" v-model="form.status" filterable clearable placeholder="Select Call Status"
                        class="w-full" aria-autocomplete="off">
                        <el-option v-for="status in callStatuses" :key="status.id" :label="status.status"
                            :value="status.status" />
                    </el-select>
                    <InputError :message="form.errors.status" class="mt-2" />
                </div>
                <div v-if="form.status == 'Call again on Date'">
                    <InputLabel for="followUpAt" value="Follow up date and time" />
                    <el-date-picker id="followUpAt" v-model="form.follow_up_at" type="datetime"
                        placeholder="Select follow up date" :disabled-date="disabledPastDates" :shortcuts="shortcuts"
                        class="!w-full" />
                    <InputError :message="form.errors.follow_up_at" class="mt-2" />
                </div>
                <div v-if="form.status == 'Set Appointment Date'">
                    <InputLabel for="appointmentAt" value="Appointment date and time" />
                    <el-date-picker id="appointmentAt" v-model="form.appointment_at" type="datetime"
                        placeholder="Select appointment" :disabled-date="disabledPastDates" :shortcuts="shortcuts"
                        class="!w-full" />
                    <InputError :message="form.errors.appointment_at" class="mt-2" />
                </div>

                <div v-if="form.status == 'Set Appointment Date'">
                    <InputLabel for="consultant" value="Consultant" />
                    <el-select id="consultant" v-model="form.consultant_id" filterable clearable
                        placeholder="Select Consultant" class="!w-full" aria-autocomplete="off">
                        <el-option v-for="consultant in consultants" :key="consultant.id" :label="consultant.name"
                            :value="consultant.id" />
                    </el-select>
                    <InputError :message="form.errors.consultant_id" class="mt-2" />
                </div>

                <div v-if="form.status == 'Set Appointment Date' || form.status == 'Call again on Date'">
                    <InputLabel for="googleMeetEmail" value="Google meet email" />
                    <el-input id="googleMeetEmail" v-model="form.meeting_email" clearable
                        placeholder="Add email for google meet" class="w-full" aria-autocomplete="off" />
                    <InputError :message="form.errors.meeting_email" class="mt-2" />
                </div>
            </div>
        </template>

        <template #footer>
            <SecondaryButton @click="closeModal">
                Cancel
            </SecondaryButton>

            <PrimaryButton class="ms-3" :class="{ 'opacity-25': form.processing }" :disabled="form.processing"
                @click="saveCallLog">
                Save
            </PrimaryButton>
        </template>
    </DialogModal>
</template>

<style>
.el-input.w-full {
    width: 100% !important;
}

.el-input.w-full .el-input__inner {
    width: calc(100% - 30px) !important;
}
</style>