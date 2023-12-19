<script setup>
import { ref, watch } from 'vue';
import { usePage, useForm } from '@inertiajs/vue3';
import { Delete, Select, Warning } from '@element-plus/icons-vue';
import { debounce } from 'lodash';

import FloatingLabel from '@/Components/FloatingLabel.vue';
import FloatingInput from '@/Components/FloatingInput.vue';
import InputError from '@/Components/InputError.vue';

const page = usePage();
const props = defineProps(['mode', 'companyId', 'numbers', 'errors']);

const emit = defineEmits(['numbersUpdated']);
const formMode = ref(props.mode ?? 'add');
const form = useForm({
    contact_numbers: props.numbers ?? []
});

const addNumber = () => {
    form.contact_numbers.push({
        id: '',
        label: '',
        number: '',
        verified: false
    });
};

const removeNumber = async (index) => {
    const number = form.contact_numbers[index];
    form.contact_numbers.splice(index, 1);
    if (formMode.value == 'add') {
        emit('numbersUpdated', form.contact_numbers);
    }
    else if (formMode.value == 'edit' && number.id) {
        const removeData = {
            contact_number: number.id,
            source: 'dashboard',
            _token: page.props.csrf_token,
        };
        const response = await axios.delete(route('contact-numbers.destroy', removeData));
        emit('numbersUpdated', response.data);
    }
};

const onInputChange = debounce(() => {
    if (formMode.value == 'add') {
        emit('numbersUpdated', form.contact_numbers);
    }
}, 500);

const onSave = async (index) => {
    let method = 'post';
    const number = form.contact_numbers[index];
    const saveData = {
        source: 'dashboard',
        company_id: props.companyId,
        _token: page.props.csrf_token,
        ...number
    };
    delete saveData.id;
    let saveRoute = 'contact-numbers.store';
    if (number.id != '') {
        method = 'put';
        saveData.contact_number = number.id;
        saveRoute = 'contact-numbers.update';
    }

    try {
        const response = await axios[method](route(saveRoute, saveData));
        number.id = response.data.number.id;
        emit('numbersUpdated', response.data.company);
    } catch (e) {
        form.errors.contact_numbers = e.response.data.errors.contact_numbers;
    }
};

if (props.errors) {
    form.errors = {
        ...form.errors,
        ...props.errors
    };
}

watch(
    () => props.numbers,
    (numbers) => {

        form.contact_numbers = [];
        setTimeout(() => {
            form.clearErrors();
            form.contact_numbers = numbers;
        }, 1);
    }
);
</script>

<template>
    <div class="company-contact-numbers">
        <div v-if="form.contact_numbers.length" v-for="(number, index) in form.contact_numbers" :key="index">
            <div class="flex justify-items-stretch space-x-2 space-y-4 items-center">
                <button @click="removeNumber(index)"
                    class="bg-white dark:bg-gray-600 py-1 px-1 flex-shrink-0 text-sm leading-none font-medium text-red-600 rounded-md">
                    <Delete class="h-4 w-4 text-red-600 dark:text-gray-400" />
                </button>
                <div class="flex-1">
                    <div class="relative z-0 w-full group">
                        <select v-model="number.label" :id="`label${index + 1}`" :name="`label${index + 1}`" placeholder=" "
                            @change="(e) => onInputChange()"
                            class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer">
                            <option selected value="">Select a Label</option>
                            <option>Telephone</option>
                            <option>Mobile</option>
                            <option>Fax</option>
                            <option>Home</option>
                            <option>Work</option>
                        </select>
                        <FloatingLabel :for="`label${index + 1}`" value="Label" />
                    </div>
                </div>
                <div class="flex-1">
                    <div class="relative z-0 w-full group">
                        <FloatingInput type="text" v-model="number.number" :id="`number${index + 1}`"
                            :name="`number${index + 1}`" @change="(e) => onInputChange()" />
                        <FloatingLabel :for="`number${index + 1}`" value="Number" />
                    </div>
                </div>
                <div class="flex-1">
                    <div class="w-full group">
                        <el-switch v-model="number.verified" active-text="Verified" inactive-text="Not Verified"
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
            <InputError :message="form.errors[`contact_numbers.${index}.label`]" class="mt-2" />
            <InputError :message="form.errors[`contact_numbers.${index}.number`]" class="mt-2" />
        </div>
        <el-empty v-else description="No Contact Numbers" class="!p-0 mb-2">
            <template #image>
                <p align="center" class="m-0">
                    <Warning class="text-center !w-12 !h-12" />
                </p>
            </template>
        </el-empty>
        <button @click="addNumber"
            class="w-full text-sm py-1 mt-2 font-bold rounded-md text-center border-dashed border-2 border-gray-300 dark:border-gray-700 hover:border-gray-500 dark:hover:border-gray-600 text-gray-600 dark:text-gray-500 dark:hover:text-gray-300">+
            Add Contact Number</button>
    </div>
</template>

<style>
.company-contact-numbers .el-empty__description {
    margin-top: 4px;
}
</style>