import { ref, computed, reactive } from "vue";

export function useForm(initialValues = {}, onSubmit) {
    const values = reactive({ ...initialValues });
    const errors = reactive({});
    const touched = reactive({});
    const isSubmitting = ref(false);
    const isDirty = ref(false);

    // Validation rules
    const rules = reactive({});

    const setFieldValue = (field, value) => {
        values[field] = value;
        isDirty.value = true;
        touched[field] = true;
    };

    const setFieldError = (field, error) => {
        errors[field] = error;
    };

    const setFieldTouched = (field, isTouched = true) => {
        touched[field] = isTouched;
    };

    const validateField = (field) => {
        if (!rules[field]) return true;

        const fieldRules = Array.isArray(rules[field])
            ? rules[field]
            : [rules[field]];

        for (const rule of fieldRules) {
            const error = rule(values[field], values);
            if (error) {
                errors[field] = error;
                return false;
            }
        }

        delete errors[field];
        return true;
    };

    const validate = () => {
        Object.keys(rules).forEach((field) => {
            validateField(field);
        });
        return Object.keys(errors).length === 0;
    };

    const handleSubmit = async (e) => {
        if (e) e.preventDefault();

        if (!validate()) {
            return;
        }

        isSubmitting.value = true;
        try {
            await onSubmit?.(values);
        } finally {
            isSubmitting.value = false;
        }
    };

    const reset = () => {
        Object.assign(values, initialValues);
        Object.keys(errors).forEach((key) => delete errors[key]);
        Object.keys(touched).forEach((key) => delete touched[key]);
        isDirty.value = false;
    };

    const getFieldProps = (field) => ({
        modelValue: values[field],
        "onUpdate:modelValue": (val) => setFieldValue(field, val),
        error: errors[field],
        touched: touched[field],
    });

    return {
        values,
        errors,
        touched,
        isSubmitting,
        isDirty,
        rules,
        setFieldValue,
        setFieldError,
        setFieldTouched,
        validateField,
        validate,
        handleSubmit,
        reset,
        getFieldProps,
    };
}

// Validation rules
export const validators = {
    required: (value) => (value ? null : "Field ini wajib diisi"),
    email: (value) =>
        /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value || "")
            ? null
            : "Email tidak valid",
    minLength: (length) => (value) =>
        (value?.length || 0) >= length ? null : `Minimal ${length} karakter`,
    maxLength: (length) => (value) =>
        (value?.length || 0) <= length ? null : `Maksimal ${length} karakter`,
    pattern: (pattern, message) => (value) =>
        pattern.test(value || "") ? null : message || "Format tidak valid",
    numeric: (value) =>
        /^\d+$/.test(value || "") ? null : "Hanya angka yang diizinkan",
    currency: (value) =>
        /^\d+(\.\d{1,2})?$/.test(value || "")
            ? null
            : "Format currency tidak valid",
    minValue: (min) => (value) =>
        (parseFloat(value) || 0) >= min ? null : `Minimal ${min}`,
    maxValue: (max) => (value) =>
        (parseFloat(value) || 0) <= max ? null : `Maksimal ${max}`,
    match: (fieldValue, message) => (value) =>
        value === fieldValue ? null : message || "Nilai tidak sesuai",
    custom: (fn, message) => (value) =>
        fn(value) ? null : message || "Validasi gagal",
};
