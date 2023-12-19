<script setup>
import { useForm } from '@inertiajs/vue3';
import ActionMessage from '@/Components/ActionMessage.vue';
import FormSection from '@/Components/FormSection.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    user: Object,
});
const daysOfWeek = props.user.availability?.days_of_week;
const form = useForm({
    _method: 'PUT',
    days_of_week: Object.keys(daysOfWeek).filter(k => daysOfWeek[k]) ?? ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'],
    shift_start: props.user.availability?.shift_start ?? '08:00',
    shift_end: props.user.availability?.shift_end ?? '17:00',
    meeting_duration: props.user.availability?.meeting_duration,
});

const updateAvailability = () => {
    form.post(route('user-availability.update'), {
        errorBag: 'updateAvailability',
        preserveScroll: true,
    });
};
</script>

<template>
    <FormSection @submitted="updateAvailability">
        <template #title>
            Meeting Availability
        </template>

        <template #description>
            Update your work schedule for answering meeting calls.
        </template>

        <template #form>
            <!-- Days of Week -->
            <div class="col-span-6 sm:col-span-4">
                <InputLabel for="name" value="Work Days" />
                <el-checkbox-group v-model="form.days_of_week">
                    <el-checkbox label="Sunday" />
                    <el-checkbox label="Monday" />
                    <el-checkbox label="Tuesday" />
                    <el-checkbox label="Wednesday" />
                    <el-checkbox label="Thursday" />
                    <el-checkbox label="Friday" />
                    <el-checkbox label="Saturday" />
                </el-checkbox-group>
                <InputError :message="form.errors.days_of_week" class="mt-2" />
            </div>

            <!-- Shift Start -->
            <div class="col-span-6 sm:col-span-4">
                <InputLabel for="shiftStart" value="Shift Start" />
                <TextInput id="shiftStart" v-model="form.shift_start" type="time" class="mt-1 block w-full"
                    autocomplete="off" />
                <InputError :message="form.errors.shift_start" class="mt-2" />
            </div>

            <!-- Shift End -->
            <div class="col-span-6 sm:col-span-4">
                <InputLabel for="shiftEnd" value="Shift End" />
                <TextInput id="shiftEnd" v-model="form.shift_end" type="time" class="mt-1 block w-full"
                    autocomplete="off" />
                <InputError :message="form.errors.shift_end" class="mt-2" />
            </div>

            <!-- Meeting Duration -->
            <div class="col-span-6 sm:col-span-4">
                <InputLabel for="meetingDuration" value="Meeting Duration" />
                <select name="meeting_duration" id="meetingDuration" v-model="form.meeting_duration"
                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 dark:focus:border-blue-600 focus:ring-blue-500 dark:focus:ring-blue-600 rounded-md shadow-sm">
                    <option value="" selected>
                        -- Select duration --
                    </option>
                    <option value="15">15</option>
                    <option value="30">30</option>
                    <option value="45">45</option>
                    <option value="60">60</option>
                </select>
                <InputError :message="form.errors.meeting_duration" class="mt-2" />
            </div>
        </template>

        <template #actions>
            <ActionMessage :on="form.recentlySuccessful" class="me-3">
                Saved.
            </ActionMessage>

            <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                Save
            </PrimaryButton>
        </template>
    </FormSection>
</template>
