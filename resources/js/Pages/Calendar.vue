<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';

import FullCalendar from '@fullcalendar/vue3';
import dayGridPlugin from '@fullcalendar/daygrid';
import listPlugin from '@fullcalendar/list';

const props = defineProps(['meetings']);

const handleDateClick = (info) => {
    info.jsEvent.preventDefault();
    if (info.event.url) {
        window.open(info.event.url);
    }
};

const calendarOptions = {
    plugins: [dayGridPlugin, listPlugin],
    initialView: 'dayGridMonth',
    weekends: true,
    events: props.meetings,
    headerToolbar: {
        left: 'prev,next',
        center: 'title',
        right: 'dayGridMonth,dayGridWeek,dayGridDay,listYear'
    },
    eventClick: handleDateClick
};
</script>

<template>
    <AppLayout title="Calendar">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Calendar of Events
            </h2>
        </template>

        <div class="py-4">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <FullCalendar :options='calendarOptions' />
            </div>
        </div>
    </AppLayout>
</template>
