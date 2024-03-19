<template>
    <div>
        <div class="form-group row align-items-center">
            <div class="col-md-3 col-sm-12">
                <label>Fälligkeitsdatum:</label>
            </div>
            <div class="col-auto">
                <input type="date" v-model="localSetup.due_date" class="form-control" :class="{ 'is-invalid': v$.localSetup.due_date.$error }" :disabled="disabled"/>
                <div v-if="v$.localSetup.due_date.$error" class="invalid-feedback">
                    {{ v$.localSetup.due_date.$errors[0].$message }}
                </div>
            </div>
        </div>
        <div class="form-group row align-items-center">
            <div class="col-md-3 col-sm-12">
                <label>Beratungsstelle(n):</label>
            </div>
            <div class="col-sm-9">
                <multiselect v-model="localSetup.settings.counselling_fields"
                    :options="all_fields"
                    label="name"
                    selectLabel=""
                    deselectLabel=""
                    placeholder="Beratungsstellen aus-/abwählen"
                    :multiple="true"
                    track-by="id"
                    :class="{ 'is-invalid': v$.localSetup.settings.counselling_fields.$error }"
                    :disabled="disabled">
                </multiselect>
                <div v-if="v$.localSetup.settings.counselling_fields.$error" class="invalid-feedback">
                    {{ v$.localSetup.settings.counselling_fields.$errors[0].$message }}
                </div>
            </div>
        </div>
        <div class="form-group row align-items-center">
            <div class="col-md-3 col-sm-12">
                <label>Persona(e):</label>
            </div>
            <div class="col-sm-9">
                <multiselect v-model="localSetup.settings.personae"
                    :options="availablePersonae"
                    label="name"
                    selectLabel=""
                    deselectLabel=""
                    placeholder="Personae aus-/abwählen"
                    :multiple="true"
                    track-by="id"
                    :class="{ 'is-invalid': v$.localSetup.settings.personae.$error }"
                    :disabled="disabled">
                </multiselect>
                <div v-if="v$.localSetup.settings.personae.$error" class="invalid-feedback">
                    {{ v$.localSetup.settings.personae.$errors[0].$message }}
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import multiselect from '@suadelabs/vue3-multiselect';
import { useVuelidate } from '@vuelidate/core';
import { helpers, required, between } from '@vuelidate/validators';

export default {
    components: { multiselect },
    props: {
        setup: {
            type: Object,
        },
        all_personae: {
            type: Array,
        },
        all_fields: {
            type: Array,
        },

        course_duration: {
            type: Object,
        },

        disabled: {
            type: Boolean,
            default: false,
        },
    },

    data() {
        return {
            localSetup: JSON.parse(JSON.stringify(this.setup)),
            local_course_duration: JSON.parse(JSON.stringify(this.course_duration)), // local course duration fixes the bug of  Maximum recursive updates exceeded.
            v$: useVuelidate(),
        }
    },

    validations() {
        return {
            localSetup: {
                due_date: { 
                    required: helpers.withMessage('Pflichtfeld', required),
                     betweenCourse:
                        helpers.withMessage('Das Fälligkeitsdatum muss innerhalb des Kurszeitraumes liegen', 
                        (val) => {
                            if(val != null) {
                                return (new Date(val) <= new Date(this.local_course_duration.end)) && (new Date(val) > new Date(this.local_course_duration.start));
                            }
                            else {
                                return true;
                            }
                            
                        }),
                },
                settings: {
                    counselling_fields: { required: helpers.withMessage('Pflichtfeld', required), },
                    personae: { required: helpers.withMessage('Pflichtfeld', required), },
                }
            }
        };
    },

    computed: {
        availablePersonae() {
            return this.all_personae.filter((persona) => this.localSetup.settings.counselling_fields.some((field) => field.id === persona.field));
        },
    },

    watch: {
        localSetup: {
            handler(newSetup) {
                this.$emit('update:setup', newSetup)
            }, 
            deep: true
        },
        availablePersonae: {
            // check if selected personae are still available after editing fields
            handler(newAvailablePersonae) {
                this.setup.settings.personae = this.setup.settings.personae.filter(persona =>
                    newAvailablePersonae.some(availablePersona => availablePersona.id === persona.id)
                );
            },
            deep: true
        },
    }
}
</script>
<style lang="">
    
</style>