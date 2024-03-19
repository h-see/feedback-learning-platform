<template>
    <div>
        <div class="row mb-3" v-for="(input, key) in inputs" :key="key">
            <div class="col-sm-12">
                <label :title="input.labelTitle">{{ input.label }}</label>
                <input :id="key" type="number" class="form-control number-input" min="1" v-model="inputs[key].value" :class="{ 'is-invalid': v$.inputs[key].$error }" :disabled="disabled">
                <div v-if="v$.inputs[key].$error" class="invalid-feedback">
                    {{ v$.inputs[key].$errors[0].$message }}
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { useVuelidate } from '@vuelidate/core';
import { required, helpers, minValue } from '@vuelidate/validators';

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
        trainer_feedback_contingent: {
            default: 1,
        },
        peer_review_start_token: {
            default: 1,
        }
    },
    data() {
        return {
            inputs: {
                trainer_feedback_contingent: {
                    label: "Maximale Anzahl Feedbackanfragen an Trainer:in",
                    labelTitle: "",
                    value: this.trainer_feedback_contingent,
                },
                peer_review_start_token: {
                    label: "Anzahl Start Tokens für Peer Review",
                    labelTitle: "Studierende benötigen Tokens um Peer-Reviews anzufragen. 1 Starttoken ist das Minimum.",
                    value: this.peer_review_start_token,
                }
            },
            v$: useVuelidate(),
        };
    },
    validations() {
        const validations = {};
        for (const key in this.inputs) {
            validations[key] = { required, }; // minValue: helpers.withMessage('Wert muss mindestens 1 sein', (value) => value >= 1 )
        }
        return { inputs: validations };
    },
    watch: {
        inputs: {
            handler(newValue) {
                this.$emit('update:feedbackSetting', newValue)
            },
            deep: true,
        }
    }
};
</script>

<style scoped lang="scss">
.number-input {
    width: 10%;
}
</style>
