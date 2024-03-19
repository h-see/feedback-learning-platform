<template>
    <div class="h-100">
        <div class="d-flex flex-column h-100">
            <span class="note-header">
                <div class="row">
                    <div class="col-auto d-flex align-items-center">
                        <input type="checkbox" class="form-check-input green" @change="noteCheckboxChanged"
                            :checked="note?.done === 1" :disabled="courseFinished"/>
                    </div>
                    <div class="col p-0">
                        <b>Notizen zur Selbstreflexion</b>
                    </div>
                </div>
                <div>In den Notizen können Gedanken, Eindrücke und Erkenntnisse zum Beratungsverlauf festgehalten werden.
                    Sie können als Anhaltspunkt für die Selbstreflexion genutzt werden, die am Ende des Kurses stattfindet.
                </div>
            </span>

            <textarea class="form-control mt-3" style="flex: 1;" placeholder="Notiz hinzufügen..." v-model="text" :readonly="courseFinished"></textarea>

            <div class="row text-center mt-5" v-if="!courseFinished">
                <div class="col">
                    <button class="btn btn-primary" @click="saveBtn">Speichern</button>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import { showSuccessAlert , showErrorAlert} from '../../../../helpers/Alerts';

export default {
    props: ['note', 'counselling', 'counselling_id', 'courseFinished'],
    data() {
        return {
            text: this.note?.text,
        };
    },

    methods: {
        noteCheckboxChanged($event) {
            let noteData = {};
            noteData.done = $event.target.checked ? 1 : 0;
            noteData.text = this.note === null ? '' : this.note.text;
            this.saveNote(noteData);
        },

        saveBtn() {
            let noteData = {};
            noteData.done = this.note?.done ? 1 : 0;
            noteData.text = this.text;
            this.saveNote(noteData);
        },

        saveNote(data) {
            axios.put('/counselling/' + this.counselling_id, {
                type: 'note',
                note: data
            })
            .then(res => {
                this.$emit('counsellingChanged', res.data.counselling);
                showSuccessAlert(res.data.message);
            })
            .catch(err => {
                showErrorAlert(err);
            })
        },
    },
    watch: {
        note: {
            handler(note) {
                this.text = note?.text;
            },
            deep: true
        },
    },

}
</script>
<style lang="scss" scoped>
textarea {
    resize: none;
}
</style>
