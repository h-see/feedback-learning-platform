<template>
    <div>
        <personas :groups="groups" :selectedFields="enabledFields" :selectedPersonae="enabledPersonae"
            @personaChanged="togglePersona"
            @fieldChanged="toggleField">
        </personas>
    </div>
</template>
<script>
import { showSuccessAlert , showErrorAlert} from '../../helpers/Alerts';

export default {
    data() {
        return {
            groups: [],
        }
    },
    computed: {
        enabledPersonasCount: function () {
            return (fieldId) => {
                const field = this.groups.find((field) => field.id === fieldId);
                if (!field || !field.enabled) return 0;

                return field.personas.reduce(
                    (count, persona) => (persona.enabled ? count + 1 : count),
                    0
                );
            };
        },
        enabledFields() {
            return this.groups.filter((field) => field.enabled).map(field => field.id);
        },
        enabledPersonae() {
            return this.groups
                .map(field => {
                    return field.personas
                        .filter(persona => persona.enabled)
                        .map(persona => persona.id);
                })
                .flat();
        }
    },
    mounted() {
        axios.get('/personas')
            .then((res) => {
                this.groups = res.data;
            })
            .catch((err) => {
                showErrorAlert(err);
            });
    },
    methods: {
        togglePersona(persona) {
            axios.put(`/personas/${persona.id}`, {
                enabled: !persona.enabled
            })
            .then((res) => {
                this.groups.map((counsellingField) => {
                    const index = counsellingField.personas.findIndex((p) => p.id === persona.id);
                    if (index !== -1) {
                        counsellingField.personas[index] = res.data.persona;
                    }
                })
                showSuccessAlert(res.data.message);
            })
            .catch((err) => {
                showErrorAlert(err);
            })
        },

        toggleField(counsellingField) {
            axios.put(`/counselling-fields/${counsellingField.id}`, {
                enabled: !counsellingField.enabled
            })
            .then((res) => {
                const index = this.groups.findIndex(cf => cf.id === counsellingField.id);
                if (index !== -1) {
                    const personas = this.groups[index].personas;
                    this.groups[index] = res.data.counsellingField;
                    this.groups[index].personas = personas;
                }
                showSuccessAlert(res.data.message);
            })
            .catch((err) => {
                showErrorAlert(err);
            })
        },
    },
}
</script>
<style></style>