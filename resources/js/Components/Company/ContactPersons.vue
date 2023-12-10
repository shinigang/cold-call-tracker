<script setup>
import { ref, watch } from 'vue';
import { usePage, useForm } from '@inertiajs/vue3';
import { Delete, Select } from '@element-plus/icons-vue';
import { debounce } from 'lodash';

import FloatingLabel from '@/Components/FloatingLabel.vue';
import FloatingInput from '@/Components/FloatingInput.vue';
import InputError from '@/Components/InputError.vue';

const page = usePage();
const props = defineProps(['mode', 'companyId', 'persons', 'errors']);

const emit = defineEmits(['personsUpdated']);
const formMode = ref(props.mode ?? 'add');
const form = useForm({
    contact_persons: props.persons ?? []
});

const addPerson = () => {
    form.contact_persons.push({
        id: '',
        prefix: '',
        first_name: '',
        middle_name: '',
        last_name: '',
        suffix: '',
        position: '',
        verified: false
    });
};

const removePerson = async (index) => {
    const person = form.contact_persons[index];
    form.contact_persons.splice(index, 1);
    if (formMode.value == 'add') {
        emit('personsUpdated', form.contact_persons);
    }
    else if (formMode.value == 'edit' && person.id) {
        const removeData = {
            contact_person: person.id,
            source: 'dashboard',
            _token: page.props.csrf_token,
        };
        const response = await axios.delete(route('contact-persons.destroy', removeData));
        emit('personsUpdated', response.data);
    }
};

const onInputChange = debounce(() => {
    if (formMode.value == 'add') {
        emit('personsUpdated', form.contact_persons);
    }
}, 500);

const onSave = async (index) => {
    let method = 'post';
    const person = form.contact_persons[index];
    const saveData = {
        source: 'dashboard',
        company_id: props.companyId,
        _token: page.props.csrf_token,
        ...person
    };
    delete saveData.id;
    delete saveData.saving;
    let saveRoute = 'contact-persons.store';
    if (person.id != '') {
        method = 'put';
        saveData.contact_person = person.id;
        saveRoute = 'contact-persons.update';
    }

    try {
        const response = await axios[method](route(saveRoute, saveData));
        person.id = response.data.person.id;
        emit('personsUpdated', response.data.company);
    } catch (e) {
        form.errors.contact_persons = e.response.data.errors.contact_persons;
    }
};

if (props.errors) {
    form.errors = {
        ...form.errors,
        ...props.errors
    };
}

watch(
    () => props.persons,
    (persons) => {

        form.contact_persons = [];
        setTimeout(() => {
            form.clearErrors();
            form.contact_persons = persons;
        }, 1);
    }
);
</script>

<template>
    <div class="company-contact-persons">
        <div v-if="form.contact_persons.length" v-for="(person, index) in form.contact_persons" :key="index">
            <div class="flex space-x-2 space-y-4 items-center">
                <button @click="removePerson(index)"
                    class="bg-white dark:bg-gray-600 py-1 px-1 flex-shrink-0 text-sm leading-none font-medium text-red-600 rounded-md">
                    <Delete class="h-4 w-4 text-red-600 dark:text-gray-400" />
                </button>
                <div class="flex-1">
                    <div class="relative z-0 w-full group">
                        <FloatingInput type="text" v-model="person.first_name" :id="`personFirstname${index + 1}`"
                            :name="`personFirstname${index + 1}`" @change="(e) => onInputChange()" />
                        <FloatingLabel :for="`personFirstname${index + 1}`" value="First Name" />
                    </div>
                </div>
                <div class="flex-1">
                    <div class="relative z-0 w-full group">
                        <FloatingInput type="text" v-model="person.last_name" :id="`personLastname${index + 1}`"
                            :name="`personLastname${index + 1}`" @change="(e) => onInputChange()" />
                        <FloatingLabel :for="`personLastname${index + 1}`" value="Last Name" />
                    </div>
                </div>
                <div class="flex-1">
                    <div class="relative z-0 w-full group">
                        <FloatingInput type="text" v-model="person.position" :id="`personPosition${index + 1}`"
                            :name="`personPosition${index + 1}`" @change="(e) => onInputChange()" />
                        <FloatingLabel :for="`personPosition${index + 1}`" value="Position" />
                    </div>
                </div>
                <div class="flex-1">
                    <div class="w-full group">
                        <el-switch v-model="person.verified" active-text="Verified" inactive-text="Not Verified"
                            inline-prompt @change="(e) => onInputChange()" />
                    </div>
                </div>
                <div v-if="formMode == 'edit'">
                    <el-button size="small" type="primary" round @click="onSave(index)">
                        <el-icon class="me-1">
                            <Select />
                        </el-icon>
                        Save
                    </el-button>
                </div>
            </div>
            <InputError :message="form.errors[`contact_persons.${index}.first_name`]" class="mt-2" />
            <InputError :message="form.errors[`contact_persons.${index}.last_name`]" class="mt-2" />
            <InputError :message="form.errors[`contact_persons.${index}.position`]" class="mt-2" />
        </div>
        <el-empty v-else description="No Contact Persons" class="!p-0 mb-2" />
        <button @click="addPerson"
            class="w-full text-sm py-1 mt-2 font-bold rounded-md text-center border-dashed border-2 border-gray-300 dark:border-gray-700 hover:border-gray-500 dark:hover:border-gray-600 text-gray-600 dark:text-gray-500 dark:hover:text-gray-300">+
            Add Contact Person
        </button>
    </div>
</template>