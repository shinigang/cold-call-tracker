<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';

import DialogModal from '@/Components/DialogModal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { Download } from '@element-plus/icons-vue';

const emit = defineEmits(['companiesImported', 'modalClosed']);

const props = defineProps({
    show: Boolean
});

const fileInput = ref(null);

const form = useForm({
    file: null
});

const closeModal = () => {
    form.clearErrors();
    form.reset();
    emit('modalClosed');
};

const selectedFile = () => {
    const file = fileInput.value.files[0];
    if (!file) return;
    form.file = file;
};

const importCompanies = () => {
    form.post(route('companies.import'), {
        errorBag: 'importCompanies',
        onSuccess: (resp) => {
            closeModal();
            if (resp.props.flash.message) {
                ElMessage({
                    type: 'success',
                    message: resp.props.flash.message,
                });
            }
            emit('companiesImported');
        },
    });
};
</script>

<template>
    <DialogModal :show="show" @close="closeModal">
        <template #title>
            Import Companies via CSV
        </template>
        <template #content>
            <InputLabel for="companiesCsv" value="CSV File Upload" />
            <input id="companiesCsv" ref="fileInput" @change="selectedFile" type="file" accept=".csv"
                class="file:mr-4 file:py-2 file:px-4
            file:rounded-full file:border-0
            file:text-sm file:font-semibold
            file:bg-violet-50 file:text-violet-700
            hover:file:bg-violet-100
            block mt-1 w-full text-sm text-gray-900 rounded-lg cursor-pointer dark:text-gray-400 focus:outline-none dark:placeholder-gray-400" aria-describedby="file_input_help" />
            <InputError :message="form.errors.file" class="mt-2" />
            <div id="file_input_help" class="mt-1 text-sm text-gray-500 dark:text-gray-300 flex items-center mb-2">
                <el-icon class="me-1">
                    <Download />
                </el-icon>
                <el-link type="info" :href="'/import/import_companies_template.csv'">
                    <span class="text-xs">Download CSV Template</span>
                </el-link>
            </div>
        </template>
        <template #footer>
            <form @submit.prevent="importCompanies">
                <SecondaryButton @click="closeModal">
                    Cancel
                </SecondaryButton>

                <PrimaryButton type="submit" class="ms-3" :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing">
                    Import
                </PrimaryButton>
            </form>
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