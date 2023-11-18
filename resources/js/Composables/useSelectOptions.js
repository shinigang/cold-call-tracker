import { computed } from "vue";

export const useSelectOptions = (options, valueField, labelField) => {
    const selectOptions = computed(() => {
        return options.map(option => ({ value: option[valueField], label: option[labelField] }));
    });
    return { selectOptions };
};