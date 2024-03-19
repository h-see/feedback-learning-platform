<template>
    <div>
        <div class="row text-center mt-2">
            <div class="col">
                <span v-show="counselling_field === null">
                    Auswahl der Beratungsstelle, bei welcher die Beratung durchgeführt wird:
                </span>
                <span v-show="counselling_field !== null">
                    Auswahl der Persona, die der Virtuelle Klient simuliert:
                </span>
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-md-3">
                <div class="container-content">
                    <b>Beratungsstelle</b>
                    <wizard-selection :options="settings.counselling_fields" v-model="counselling_field"
                        :prefix="'field'">
                    </wizard-selection>
                </div>
            </div>
            <div class="col-md-3" v-show="counselling_field != null">
                <div class="container-content">
                    <b>Ratsuchende*r</b>
                    <wizard-selection :options="available_personae" v-model="persona"
                        :prefix="'persona'">
                    </wizard-selection>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col text-center">
                <button class="btn btn-primary" @click="createCounselling"
                    :disabled="counselling_field === null || persona === null">
                    Weiter
                </button>
            </div>
        </div>

    </div>
</template>
<script>
import { showErrorAlert} from '../../../../helpers/Alerts';

export default {
    props: ['settings', 'setupId'],
    data() {
        return {
            counselling_field: null,
            persona: null
        };
    },
    computed: {
        available_personae() {
            return this.settings.personae.filter((persona) => persona.counselling_field_id === this.counselling_field);
        },
    },
    methods: {
        createCounselling() {
            axios.post('/counselling/' + this.settings.setup_id, {
                persona: [this.persona]
            })
            .then((res) => {
                window.location.replace('/counselling/' + res.data.id);
            })
            .catch((err) => {
                showErrorAlert(err);
            })
        },
    },

}
</script>
<style lang="scss" scoped>
.row {
    margin-bottom: 2rem;
}
.container-content {
    height: 100%;
}
</style>