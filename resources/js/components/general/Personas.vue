<template>
    <div>
        <div v-for="(item, index) in groups" :key="item.id" class="mb-5">
            <div class="counselling-field d-flex justify-content-between left-15 mb-3 row">
                <span class="d-flex align-items-center col p-0">
                    <input type="checkbox" :checked="fieldSelected(item.id)" class="form-check-input"
                        @change="toggleField(item)" :id="'field-' + item.id"
                        :disabled="item.personas.length === 0 || disabled" data-toggle="tooltip"
                        title="Beratungsfeld aktivieren/deaktivieren">
                    <label :for="'field-' + item.id" class="mb-0" style="margin-left: 5px;">{{ item.name }}</label>
                </span>
                <span class="col-sm-auto col-12 align-self-end ps-0">Aktivierte Personae: {{
            selectedPersonasCount(item.id) }} / {{ item.personas.length }}</span>
            </div>

            <i v-if="item.personas.length === 0" class="left-15">Keine Personae zu diesem Beratungsfeld vorhanden.</i>
            <div v-for="(persona, pIndex) in item.personas" :key="persona.id">
                <div class="accordion" :id="'accordionPersona-' + item.id + '-' + persona.id">
                    <div class="accordion-item container-content">
                        <div class="accordion-header d-flex align-items-center">
                            <input type="checkbox" :checked="personaSelected(persona.id)" class="form-check-input"
                                :id="'persona-checkbox-' + item.id + '-' + persona.id" @change="togglePersona(persona)"
                                :disabled="!selectedFields?.includes(item.id) || disabled" data-toggle="tooltip"
                                title="Persona aktivieren/deaktivieren">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                :data-bs-target="'#collapsePersona-' + item.id + '-' + persona.id" aria-expanded="false"
                                data-toggle="tooltip" title="Weitere Informationen zur Persona">
                                <label :for="'persona-checkbox-' + item.id + '-' + persona.id" class="mb-0">{{
            persona.name }}</label>
                            </button>
                        </div>
                        <div :id="'collapsePersona-' + item.id + '-' + persona.id" class="accordion-collapse collapse">
                            <div v-for="(property, key) in persona.properties" :key="key" class="mb-3">
                                <div class="fw-bold">{{ key }}</div>
                                <ul v-if="typeof property === 'object'">
                                    <div v-for="(prop, k) in property" :key="k">
                                        <li v-if="typeof k === 'number'">{{ prop }}</li>
                                        <li v-else>{{ k }}: {{ prop }}</li>
                                    </div>
                                </ul>
                                <span v-else>{{ property }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</template>

<script>

export default {
    props: {
        groups: {
            type: Array,
        },
        selectedFields: {
            type: Array,
        },
        selectedPersonae: {
            type: Array,
        },
        disabled: {
            type: Boolean,
            default: false,
        }
    },
    computed: {
        selectedPersonasCount: function () {
            return (fieldId) => {
                const field = this.groups.find((field) => field.id === fieldId);
                if (!field || !this.fieldSelected(field.id)) return 0;

                return field.personas.reduce(
                    (count, persona) => (this.personaSelected(persona.id) ? count + 1 : count),
                    0
                );
            };
        },

        personaSelected: function () {
            return (personaId) => {
                return this.selectedPersonae?.includes(personaId);
            }
        },

        fieldSelected: function () {
            return (fieldId) => {
                return this.selectedFields?.includes(fieldId);
            }
        }
    },
    methods: {
        togglePersona(persona) {
            this.$emit('personaChanged', persona);
        },

        toggleField(counsellingField) {
            this.$emit('fieldChanged', counsellingField);
        },
    },
}
</script>
<style lang="scss" scoped>
input[type="checkbox"] {
    margin-top: 0;
    margin-right: 5px;
}

.collapsing {
    transition: none;
}

.accordion-item {
    border: none;
}

.left-15 {
    margin-left: 15px;
}

.accordion-button {
    padding: 5px;
    box-shadow: none;

    &:not(.collapsed) {
        background-color: transparent;
    }
}

.accordion-collapse.show {
    border: none;
}
</style>