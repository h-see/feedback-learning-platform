<template>
  <div>
    <div class="row mb-3" v-for="field in fields" :key="field.id">
      <div class="col-sm-12">
        <label :for="field.id">{{ field.label }}</label>
        <input :id="field.id" :type="field.type" class="form-control"
               v-model.lazy="formData[field.model]"
               :class="{ 'is-invalid': v$[field.model]?.$error }"
               :disabled="disabled">
        <div v-if="v$[field.model]?.$error" class="invalid-feedback">
          {{ v$[field.model].$errors[0]?.$message }}
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { useVuelidate } from '@vuelidate/core';
import { required, helpers } from '@vuelidate/validators';

export default {
  props: {
    id: {
      type: String,
      required: true,
    },
    disabled: {
      type: Boolean,
      default: false,
    },
    name: {
      type: String,
      default: '',
    },
    enrollment_key: {
      type: String,
      default: '',
    },
    start_date: {
      type: String,
      default: '',
    },
    end_date: {
      type: String,
      default: '',
    },
  },
  data() {
    return {
      formData: {
        name: this.name,
        enrollment_key: this.enrollment_key,
        start_date: this.start_date,
        end_date: this.end_date,
      },
      v$: useVuelidate(),
    };
  },
  computed: {
    fields() {
      return [
        { id: 'name', label: 'Kursname', type: 'text', model: 'name' },
        { id: 'enrollment-key', label: 'Einschreibeschlüssel', type: 'text', model: 'enrollment_key' },
        { id: 'start-date', label: 'Startdatum', type: 'date', model: 'start_date' },
        { id: 'end-date', label: 'Enddatum', type: 'date', model: 'end_date' },
      ];
    }
  },
  validations() {
    return {
      name: { required: helpers.withMessage('Pflichtfeld', required) },
      enrollment_key: { required: helpers.withMessage('Pflichtfeld', required) },
      start_date: { required: helpers.withMessage('Pflichtfeld', required) },
      end_date: { 
        required: helpers.withMessage('Pflichtfeld', required),
        minValue: helpers.withMessage('Das Enddatum muss nach dem Startdatum liegen', (val) => new Date(val) > new Date(this.formData.start_date)),
      },
    };
  },
  watch: {
    formData: {
      handler(newValue) {
        this.$emit('update:baseSetting', newValue);
      },
      deep: true,
    },
  },
}
</script>
