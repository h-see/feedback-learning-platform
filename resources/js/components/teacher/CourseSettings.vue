<template>
    <div>
        <div class="row mb-3">
            <div class="col-xl-6">
                <div class="settings-group">
                    <h5>Kursinformationen</h5>
                    <div class="sub-controls">
                        <base-settings v-if="isDataLoaded" :id="`base-setting-` + { courseId }" :disabled="courseFinished"
                            :name="baseSettings.name" :enrollment_key="baseSettings.enrollment_key"
                            :start_date="baseSettings.start_date" :end_date="baseSettings.end_date" @update:baseSetting="updateBaseSetting"></base-settings>
                    </div>
                </div>
                <div class="mt-3 settings-group" v-if="!(courseFinished && setups.length == 0)">
                    <h5>Aufgaben</h5>
                    <div class="sub-controls">
                        <div v-for="(setup, index) in setups" :key="index" class="mb-2">
                            <div class="row align-items-center">
                                <div class="col">
                                    <span class="fw-bold">{{ index + 1 }}. Aufgabe: </span>
                                </div>
                                <div class="col-auto" v-if="!courseFinished">
                                    <button class="btn btn-danger btn-sm" data-bs-toggle="modal"
                                        data-bs-target="#confirmDeleteTask">
                                        <fa-icon :icon="['fas', 'fa-trash-can']" />
                                    </button>
                                    <!-- <button class="btn btn-danger btn-sm" @click="deleteCounsellingSetup(index)">
                                        Löschen
                                    </button> -->
                                </div>
                            </div>
                            <counselling-setups-settings v-if="isDataLoaded" :setup="setup" :index="index" :all_personae="all_personae"
                                :all_fields="all_fields" :disabled="courseFinished"
                                :course_duration="{ 'start': baseSettings.start_date, 'end': baseSettings.end_date }" @update:setup="updateSetup(index, $event)">
                            </counselling-setups-settings>
                        </div>
                        <button class="btn btn-secondary" @click="addCounsellingSetup" v-if="!courseFinished">Aufgabe hinzufügen</button>
                    </div>
                </div>
                <div class="settings-group">
                    <h5>Feedback Einstellungen</h5>
                    <div class="sub-controls">
                        <feedback-settings v-if="isDataLoaded" :id="`feedback-setting-` + { courseId }" :disabled="courseFinished"
                            :trainer_feedback_contingent="feedbackSettings.trainer_feedback_contingent"
                            :peer_review_start_token="feedbackSettings.peer_review_start_token" @update:feedbackSetting="updateFeedbackSetting"></feedback-settings>
                    </div>
                </div>
            </div>
            <div class="col-xl-6 mt-3 mt-md-0">
                <div class="settings-group">
                    <h5>Personae für Übungen</h5>
                    <div class="sub-controls" :class="{ 'is-invalid': v$.exercise.settings.personae.$error }">
                        <div class="invalid-feedback d-block mb-3" v-if="v$.exercise.settings.personae.$error">
                            Mindestens eine Persona auswählen
                        </div>
                        <personas :groups="groups" :selectedFields="exercise.settings.counselling_fields"
                            :selectedPersonae="exercise.settings.personae" @personaChanged="togglePersona"
                            @fieldChanged="toggleField" :disabled="courseFinished">
                        </personas>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mb-3" v-if="courseId">
            <div class="col settings-group mt-0">
                <h5>Kursteilnehmer*innen</h5>
                <div class="sub-controls p-0">
                    <members-list :id="courseId" :disabled="courseFinished" :showAddUserRow="showAddUserRow"
                        @hideAddUserRow="hideAddUserRow"></members-list>
                </div>
            </div>
            <div class="row text-end">
                <div class="col-md-12">
                    <button v-if="!courseFinished" class="btn btn-secondary" @click="toggleAddUserRow">Trainer*in
                        manuell hinzufügen</button>
                </div>
            </div>
        </div>
        <div class="row mb-3" v-if="courseId">
            <div class="col settings-group mt-0">
                <h5>Kurs löschen</h5>
                <div class="sub-controls">
                    <div><b>Achtung:</b><br />Durch Löschen des Kurses werden alle zugehörigen Beratungen gelöscht. Die
                        Kursmitglieder haben keinen Zugriff mehr auf die Chatverläufe.</div>
                    <button class="btn btn-danger mt-1" data-bs-toggle="modal" data-bs-target="#confirmDeleteModal">
                        Kurs löschen
                    </button>
                </div>
            </div>
        </div>
        <div class="row text-center">
            <div class="col-md-12">
                <a class="btn" :class="[courseFinished ? 'btn-primary' : 'btn-secondary']" href="/">{{ courseFinished ?
                            'Zurück' : 'Abbrechen' }}</a>
                <button v-if="!courseFinished" class="btn btn-primary" @click="save">{{ courseId ? 'Änderungen speichern'
                    : 'Kurs anlegen' }}</button>
            </div>
        </div>
    </div>
    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModal"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Löschen bestätigen</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div><b>Achtung:</b><br />Durch Löschen des Kurses werden alle zugehörigen Beratungen gelöscht. Die
                        Kursmitglieder haben keinen Zugriff mehr auf die Chatverläufe.</div>
                    <div class="row my-3">
                        <div class="mb-2">Zum Bestätigen bitte Kursname und Einschreibeschlüssel eingeben:</div>
                        <div class="col-sm-6">
                            <label>Kursname</label>
                            <input id="confirmName" type="text" class="form-control" v-model="confirm.name"
                                :class="{ 'is-invalid': confirm.wrong }">
                        </div>
                        <div class="col-sm-6">
                            <label>Einschreibeschlüssel</label>
                            <input id="confirmKey" type="text" class="form-control" v-model="confirm.enrollment_key"
                                :class="{ 'is-invalid': confirm.wrong }">
                        </div>
                    </div>
                    <div class="row justify-content-end">
                        <div class="col-auto">
                            <button type="button" class="btn btn-secondary me-3"
                                data-bs-dismiss="modal">Abbrechen</button>
                            <button type="button" class="btn btn-danger" @click="delete_course()">Löschen</button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="confirmDeleteTask" tabindex="-1" aria-labelledby="confirmDeleteTask" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Aufgabe löschen bestätigen</h5>
                </div>
                <div class="modal-body">
                    <b>Achtung:</b><br /> Möchtest du die Aufgabe wirklich löschen? Die Aufgabe wird bei allen
                    Kursteilnehmern gelöscht, unabhängig davon, ob dieser die Aufgabe bereits durchgeführt hat.
                    <div class="col-auto">
                        <button type="button" class="btn btn-secondary me-3" data-bs-dismiss="modal">Abbrechen</button>
                        <button type="button" class="btn btn-danger" @click="deleteCounsellingSetup(index)"
                            data-bs-dismiss="modal">Löschen</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import { showSuccessAlert, showErrorAlert } from '../../helpers/Alerts';
import { useVuelidate } from '@vuelidate/core';
import { integer, required } from '@vuelidate/validators';

export default {
    props: ['courseId'],
    data() {
        return {
            course: {},
            isDataLoaded: false,
            baseSettings: {},
            feedbackSettings: {},
            trainer_feedback_contingent: 0,
            peer_review_start_token: 0,
            groups: [],
            all_fields: [],
            all_personae: [],
            exercise: {
                settings: {
                    counselling_fields: [],
                    personae: []
                },
                id: null,
            },
            setups: [],
            v$: useVuelidate(),
            confirm: {
                name: '',
                enrollment_key: '',
                wrong: false,
            },
            showAddUserRow: false,
        }
    },

    computed: {
        courseFinished() {
            const end = new Date(this.course?.end_date).setHours(0, 0, 0, 0);
            const today = new Date().setHours(0, 0, 0, 0);
            return today > end;
        },
    },

    validations() {
        return {
            exercise: {
                settings: {
                    personae: {
                        required,
                        minimumOne: (value) => {
                            // Check if at least one persona has been selected that belongs to a selected field
                            return value.some((personaId) => {

                                const personaGroupId = this.groups.find((group) =>
                                    group.personas.some((p) => p.id === personaId)
                                ).id;

                                return this.exercise.settings.counselling_fields.includes(personaGroupId);
                            });
                        },
                    }
                }

            },
        }
    },

    async mounted() {
        try {
            await this.loadPersonas();
            if (this.courseId) {
                await this.loadCourseData();
            } else {
                this.setDefaultExerciseSettings();
            }
        } catch (err) {
            showErrorAlert(err);
        }

        this.setupModalListener();
    },

    unmounted() {
        const confirmDeleteModal = document.getElementById('confirmDeleteModal');
        confirmDeleteModal.removeEventListener('hide.bs.modal', event => {
            this.confirm = {
                name: '',
                enrollment_key: '',
                wrong: false
            };
        });
    },

    methods: {
        updateBaseSetting(newBaseSetting) {
            this.baseSettings.name = newBaseSetting.name;
            this.baseSettings.enrollment_key = newBaseSetting.enrollment_key;
            this.baseSettings.start_date = newBaseSetting.start_date;
            this.baseSettings.end_date = newBaseSetting.end_date;
        },
        updateFeedbackSetting(newFeedbackSetting){
            this.feedbackSettings.peer_review_start_token = newFeedbackSetting.peer_review_start_token.value
            this.feedbackSettings.trainer_feedback_contingent = newFeedbackSetting.trainer_feedback_contingent.value
        },
        updateSetup(index, newSetup){
            this.setups[index] = newSetup
        },
        setupModalListener() {
            const confirmDeleteModal = document.getElementById('confirmDeleteModal');
            confirmDeleteModal.addEventListener('hide.bs.modal', () => {
                this.confirm = {
                    name: '',
                    enrollment_key: '',
                    wrong: false
                };
            });
        },
        async loadPersonas() {
            const response = await axios.get('/personas');
            this.groups = response.data;
            this.groups.forEach(group => {
                this.all_fields.push({ name: group.name, id: group.id });
                group.personas.forEach(persona => {
                    this.all_personae.push({ name: persona.name, id: persona.id, field: group.id });
                });
            });
        },

        async loadCourseData() {
            try {
                const response = await axios.get(`/course/${this.courseId}/infos`);
                this.setData(response.data);
                this.isDataLoaded = true;
            } catch (err) {
                showErrorAlert(err);
                throw err; // Re-throw to be caught by the outer try-catch
            }
        },

        setDefaultExerciseSettings() {
            this.exercise.settings.counselling_fields = this.all_fields.map(field => field.id);
            this.exercise.settings.personae = this.all_personae.map(persona => persona.id);
        },
        toggleAddUserRow() {
            this.showAddUserRow = !this.showAddUserRow;
        },

        hideAddUserRow() {
            this.showAddUserRow = false;
        },

        setData(courseData) {
            this.course = courseData;
            const counselling_setups = courseData.counselling_setups;
            // set data for exercise-setup
            const exerciseSetup = counselling_setups.filter((setup) => !setup.mandatory)[0];
            this.exercise.settings = exerciseSetup.settings;
            this.exercise.id = exerciseSetup.id;

            // set data for tasks-setups
            this.setups = [];
            let setupsClone = JSON.parse(JSON.stringify(counselling_setups)); // Perform operations on setupsClone instead of this.setups since there is somehow a recursive call and the page hangs up

            const tasks = setupsClone.filter((setup) => setup.mandatory);
            tasks.forEach((task) => {
                this.setups.push({
                    id: task.id,
                    mandatory: true,
                    due_date: task.due_date.split(" ")[0],
                    settings: {
                        counselling_fields: this.all_fields.filter((field) => task.settings.counselling_fields.includes(field.id)),
                        personae: this.all_personae.filter((persona) => task.settings.personae.includes(persona.id)),
                    }
                });
            });
            // set data for baseSettings
            this.baseSettings.name = courseData.name;
            this.baseSettings.enrollment_key = courseData.enrollment_key;
            this.baseSettings.start_date = courseData.start_date;
            this.baseSettings.end_date = courseData.end_date;

            // set data for feedbackSettings
            const settings = JSON.parse(courseData.settings);
            this.feedbackSettings.trainer_feedback_contingent = settings.trainer_feedback_contingent;
            this.feedbackSettings.peer_review_start_token = settings.peer_review_start_token;
        },
        toggleField(field) {
            if (this.exercise.settings.counselling_fields.includes(field.id)) {
                const index = this.exercise.settings.counselling_fields.indexOf(field.id);
                this.exercise.settings.counselling_fields.splice(index, 1);
            } else {
                this.exercise.settings.counselling_fields.push(field.id);
            }
        },
        togglePersona(persona) {
            if (this.exercise.settings.personae.includes(persona.id)) {
                const index = this.exercise.settings.personae.indexOf(persona.id);
                this.exercise.settings.personae.splice(index, 1);
            } else {
                this.exercise.settings.personae.push(persona.id);
            }
        },
        addCounsellingSetup() {
            const newSetup = {
                mandatory: true,
                due_date: null,
                settings: {
                    counselling_fields: [], // Use empty arrays instead of referencing existing ones
                    personae: []
                },
                id: null,
            };
            this.setups.push(JSON.parse(JSON.stringify(newSetup)));
        },

        deleteCounsellingSetup(index) {
            this.setups.splice(index, 1);
        },
        async save() {
            const inputCorrect = await this.v$.$validate();
            if (!inputCorrect) return;

            let course = {
                name: this.baseSettings.name,
                enrollment_key: this.baseSettings.enrollment_key,
                start_date: this.baseSettings.start_date,
                end_date: this.baseSettings.end_date,
                counselling_setups: [this.exercise],
                trainer_feedback_contingent: this.feedbackSettings.trainer_feedback_contingent,
                peer_review_start_token: this.feedbackSettings.peer_review_start_token
            };
            if (this.setups.length > 0) {
                const setups_data = [];
                this.setups.forEach(setup => {
                    setups_data.push({
                        'settings': {
                            'counselling_fields': setup.settings.counselling_fields.map(cf => cf.id),
                            'personae': setup.settings.personae.map(p => p.id),
                        },
                        'mandatory': setup.mandatory,
                        'due_date': setup.due_date,
                        'id': setup.id
                    })
                });
                course.counselling_setups = course.counselling_setups.concat(setups_data);
            }
            if (this.course?.id) {
                course.id = this.course.id;
            }
            axios.post('/course/store', course)
                .then(res => {
                    if (!this.courseId) {
                        window.location.href = `/course/${res.data.id}`;
                        return;
                    }
                    this.setData(res.data.course);
                    showSuccessAlert('Änderungen gespeichert');
                })
                .catch(err => {
                    showErrorAlert(err);
                })
        },
        delete_course() {
            if (this.confirm.name !== this.course.name || this.confirm.enrollment_key !== this.course.enrollment_key) {
                this.confirm.wrong = true;
                return;
            }
            axios.delete('/course/' + this.course.id)
                .then((res) => {
                    window.location.href = '/';
                })
                .catch((err) => {
                    showErrorAlert(err);
                })
        },
    },
}
</script>
<style lang="scss" scoped>
.settings-group {
    margin: 20px;
}
</style>
<style src="@suadelabs/vue3-multiselect/dist/vue3-multiselect.css"></style>
