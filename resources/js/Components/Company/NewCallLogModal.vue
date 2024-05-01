<script setup>
import { ref, watch, computed } from 'vue';
import { usePage, useForm } from '@inertiajs/vue3';

import DialogModal from '@/Components/DialogModal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

import axios from 'axios';
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';

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
const consultantMeetings = ref([]);
const consultantTimeslots = ref([]);
const consultantAvailability = ref([]);

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

let contact_number = '';
if (props.company.contact_numbers.length > 0) {
    contact_number = props.company.contact_numbers[0].number;
}

const form = useForm({
    company_id: props.company.id,
    contact_number,
    meeting_email: '',
    status: '',
    called_at: dayjs().toString(),
    follow_up_at: '',
    follow_up_time: '',
    appointment_at: '',
    appointment_time: '',
    consultant_id: '',
    source: 'dashboard'
});

const closeModal = () => {
    form.clearErrors();
    form.reset();
    emit('modalClosed');
};

const saveCallLog = async () => {
    form.clearErrors();
    try {
        const response = await axios.post(route('calls.store', {
            ...form.data()
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

const selectedConsultant = async (consultantId) => {
    if (consultantId) {
        form.appointment_at = '';
        const response = await axios.post(route('user-events.list', { user_id: consultantId }), {
            upcoming: true
        });

        consultantMeetings.value = response.data.meetings;
        consultantTimeslots.value = response.data.timeslots;
        consultantAvailability.value = response.data.availability;
    }
};

const disabledDates = (time) => {
    const date = new Date();
    // const previousDate = date.setDate(date.getDate() - 1);
    const todayDate = date.setDate(date.getDate());
    let disabledRules = null;
    let daysOfWeek = page.props.auth.user.availability.days_of_week;
    let timeslots = page.props.auth.user.timeslots;
    let upcomingMeetings = page.props.auth.user.upcoming_meetings;

    if (form.consultant_id) {
        daysOfWeek = consultantAvailability.value.days_of_week;
        timeslots = consultantTimeslots.value;
        upcomingMeetings = consultantMeetings.value;

        if (daysOfWeek == undefined || timeslots == undefined || upcomingMeetings == undefined) {
            return false;
        }
    }

    const nonWorkDays = Object.keys(daysOfWeek).filter(k => !daysOfWeek[k]);
    const weekdays = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
    nonWorkDays.forEach((day) => {
        if (disabledRules == null) {
            disabledRules = time.getDay() == weekdays.indexOf(day);
        } else {
            disabledRules = disabledRules || time.getDay() == weekdays.indexOf(day);
        }
    });
    if (upcomingMeetings.length) {
        const minDate = new Date(Math.min(...upcomingMeetings.map(meeting => new Date(meeting.start))));
        const maxDate = new Date(Math.max(...upcomingMeetings.map(meeting => new Date(meeting.start))));
        let currDate = new Date(minDate);
        while (currDate <= maxDate) {
            let currTimeslots = timeslots;
            const currMeetings = upcomingMeetings.filter(meeting => dayjs(currDate).isSame(dayjs(meeting.start), 'day'));
            currMeetings.forEach((meeting) => {
                const meetingStart = dayjs(meeting.start);
                currTimeslots = currTimeslots.filter(timeslot => timeslot.start != meetingStart.format('HH:mm'));
            });
            if (currTimeslots.length == 0) {
                disabledRules = disabledRules || dayjs(time).format('MM-DD-YYYY') == dayjs(currDate).format('MM-DD-YYYY');
            }

            const newDate = currDate.setDate(currDate.getDate() + 1);
            currDate = new Date(newDate);
        }
    }

    if (disabledRules == null) {
        disabledRules = true;
    }

    return time.getTime() < todayDate || (disabledRules);
};

const timeslots = computed(() => {
    let userTimeslots = page.props.auth.user.timeslots;
    let upcomingMeetings = page.props.auth.user.upcoming_meetings;
    let selectedDate = null;
    if (form.consultant_id) {
        userTimeslots = consultantTimeslots.value;
        upcomingMeetings = consultantMeetings.value;
    }
    if (form.status == 'Call again on Date' && form.follow_up_at) {
        selectedDate = form.follow_up_at;
    }
    if (form.status == 'Set Appointment Date' && form.appointment_at) {
        selectedDate = form.appointment_at;
    }

    if (upcomingMeetings && selectedDate) {
        const currMeetings = upcomingMeetings.filter(meeting => dayjs(selectedDate).isSame(dayjs(meeting.start), 'day'));
        currMeetings.forEach((meeting) => {
            const meetingStart = dayjs(meeting.start);
            userTimeslots = userTimeslots.filter(timeslot => timeslot.start != meetingStart.format('HH:mm'));
        });
    }
    return userTimeslots;
});

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
                    <InputLabel for="contactNumber" value="Contact Number *" />
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
                    <InputLabel for="calledAt" value="Call date and time *" />
                    <el-date-picker id="calledAt" v-model="form.called_at" type="datetime"
                        placeholder="Select call date and time" class="w-full" />
                    <InputError :message="form.errors.called_at" class="mt-2" />
                </div>

                <div>
                    <InputLabel for="callStatus" value="Status *" />
                    <el-select id="callStatus" v-model="form.status" filterable clearable placeholder="Select Call Status"
                        class="w-full" aria-autocomplete="off">
                        <el-option v-for="status in callStatuses" :key="status.id" :label="status.status"
                            :value="status.status" />
                    </el-select>
                    <InputError :message="form.errors.status" class="mt-2" />
                </div>

                <div v-if="form.status == 'Call again on Date'">
                    <InputLabel for="followUpAt" value="Follow up date *" />
                    <el-date-picker id="followUpAt" v-model="form.follow_up_at" type="date" value-format="YYYY-MM-DD"
                        @change="form.follow_up_time = null" placeholder="Select follow up date"
                        :disabled-date="disabledDates" class="!w-full" />
                    <InputError :message="form.errors.follow_up_at" class="mt-2" />
                </div>

                <div v-if="form.status == 'Set Appointment Date'">
                    <InputLabel for="consultant" value="Consultant *" />
                    <el-select id="consultant" v-model="form.consultant_id" filterable clearable
                        @change="selectedConsultant" placeholder="Select Consultant" class="!w-full"
                        aria-autocomplete="off">
                        <el-option v-for="consultant in consultants" :key="consultant.id" :label="consultant.name"
                            :value="consultant.id" />
                    </el-select>
                    <InputError :message="form.errors.consultant_id" class="mt-2" />
                </div>

                <div v-if="form.status == 'Set Appointment Date'">
                    <InputLabel for="appointmentAt" value="Appointment date *" />
                    <el-date-picker id="appointmentAt" v-model="form.appointment_at" type="date" value-format="YYYY-MM-DD"
                        @change="form.appointment_time = null" placeholder="Select appointment"
                        :disabled-date="disabledDates" class="!w-full" />
                    <InputError :message="form.errors.appointment_at" class="mt-2" />
                </div>

                <div v-if="form.status == 'Set Appointment Date' || form.status == 'Call again on Date'">
                    <InputLabel for="googleMeetEmail" value="Google meet email" />
                    <el-input id="googleMeetEmail" v-model="form.meeting_email" clearable
                        placeholder="Add email for google meet" class="w-full" aria-autocomplete="off" />
                    <InputError :message="form.errors.meeting_email" class="mt-2" />
                </div>

                <div v-if="form.status == 'Call again on Date'">
                    <InputLabel for="followUpTime" value="Follow up time *" />
                    <el-select id="followUpTime" v-model="form.follow_up_time" filterable clearable
                        placeholder="Select Timeslot" class="!w-full" aria-autocomplete="off">
                        <el-option v-for="(timeslot, idx) in timeslots" :key="`followUpTime${idx}`"
                            :label="`${timeslot.start} - ${timeslot.end}`" :value="timeslot.start" />
                    </el-select>
                    <InputError :message="form.errors.follow_up_time" class="mt-2" />
                </div>

                <div v-if="form.status == 'Set Appointment Date'">
                    <InputLabel for="appointmentAt" value="Appointment Time *" />
                    <el-select id="appointmentTime" v-model="form.appointment_time" filterable clearable
                        placeholder="Select Timeslot" class="!w-full" aria-autocomplete="off">
                        <el-option v-for="(timeslot, idx) in timeslots" :key="`followUpTime${idx}`"
                            :label="`${timeslot.start} - ${timeslot.end}`" :value="timeslot.start" />
                    </el-select>
                    <InputError :message="form.errors.appointment_time" class="mt-2" />
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