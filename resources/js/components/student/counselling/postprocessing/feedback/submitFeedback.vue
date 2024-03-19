<template>
    <div class="h-100" v-show="!showSuccess" >
        <div class="d-flex flex-column h-100" v-if="isPeer || (isTeacher && this.feedbackRequestId !== undefined)">
            <div class="note-header">
                <div class="row">
                    <div class="col-6 col-xl-4 fw-bold">
                        Beratungsstelle:
                    </div>
                     <div class="col">
                            {{counselling.counselling_field}}
                    </div>
                </div>
                <div class="row">
                    <div class="col-6 col-xl-4  fw-bold">
                        Persona:
                    </div>
                     <div class="col">
                            {{counselling.persona}}
                    </div>
                </div>
            </div>
            <textarea class="form-control mt-3 h-100" style="flex: 1;" v-if="!teacherFeedbackSubmitted" :placeholder="isPeer ? 'Peer Review...' : 'Feedback...'" :class="{ 'is-invalid': v$.inputs.text.$error }" :readonly="courseFinished" v-model="inputs.text" @input="autoSave"></textarea>
            <textarea class="form-control mt-3 h-100" style="flex: 1;" v-else readonly v-model="inputs.text"></textarea>
            <div v-if="v$.inputs.text.$error" class="invalid-feedback">
                {{ v$.inputs.text.$errors[0].$message }}
            </div>
            <div class="word-count mt-2 me-2 text-end" v-if="!teacherFeedbackSubmitted">Anzahl Wörter: {{ wordCount }}</div>
            <div class="row text-center mt-5" v-if="!courseFinished && !teacherFeedbackSubmitted">
                <div class="col text-end">
                    <button class="btn btn-secondary btn-100" @click="save_and_return(inputs.text)" :title="isPeer? 'Peer Review später weiter schreiben (Bisheriger Text wird gespeichert)' : 'Feedback später weiter schreiben (Bisheriger Text wird gespeichert)'">Speichern</button>
                </div>
                <div class="col " :title="wordCount < 50 && !isTeacher ? 'Peer Review zu kurz. Minimum = 50 Wörter.' : ''">
                    <button class="btn btn-primary btn-100" data-bs-toggle="modal" data-bs-target="#confirmSubmitModal" :disabled="wordCount < 50 && !isTeacher ">Senden</button>
                </div>
                <div class="col text-start">
                    <button class="btn btn-danger btn-100" data-bs-toggle="modal" data-bs-target="#confirmCancelModal" title="Peer Review Abbrechen und wieder für andere Mitstudierende freigeben.">Verwerfen</button>
                </div>

            </div>
        </div>
        <div class="d-flex flex-column h-100 text-center text-danger" v-else>
            Kein Feedback angefragt.
            <div class="row text-center mt-5">
                <div class="col">
                    <a class="btn btn-primary" :href="`/course/${counselling.course}`">Zum Kurs</a>
                </div>
            </div>
        </div>
    </div>

    <div class="h-100" v-show="showSuccess && !isTeacher">
        <div class="d-flex flex-column h-100">
            <div class="row text-center align-items-center justify-content-center">
                <h4 class="mt-4 mb-4">Peer Review abgeschickt!</h4>
                  <div class="col-auto align-items-center text-success">
                    <span class="">
                        <fa-icon class="coin" :icon="['fas', 'coins']" size="3x"/>
                        <span class="ms-2 fw-bold coin-counter">+1 PeerReview Token</span>
                    </span>
                </div>
            </div>
            <div class="row text-center mt-5">
                <div class="col">
                    <a class="btn btn-primary" :href="`/course/${counselling.course}`">Zum Kurs</a>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL CONFIRM CANCEL -->
    <div class="modal fade" id="confirmCancelModal" tabindex="-1" aria-labelledby="confirmCancelModal" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Verwerfen bestätigen</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div v-if="isPeer"><b>Achtung:</b><br/>Beim Freigeben wird das eventuell bisherig geschriebene Peer Review gelöscht und die Beratung wieder für Mitstudierende freigegeben.</div>
                    <div v-else><b>Achtung:</b><br/>Beim Freigeben wird das eventuell bisherig geschriebene Feedback gelöscht und die Beratung wieder auf angefragt gesetzt.</div>
                    <div class="row justify-content-end mt-3">
                        <div class="col-auto">
                            <button type="button" class="btn btn-secondary me-3" data-bs-dismiss="modal">Nein, zurück</button>
                            <button type="button" class="btn btn-danger" @click="cancel_feedback()">Ja, verwerfen</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL CONFIRM SUBMIT -->
    <div class="modal fade" id="confirmSubmitModal" tabindex="-1" aria-labelledby="confirmSubmitModal" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Senden bestätigen</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div v-if="isPeer"><b>Achtung:</b><br/>Das Peer Review kann nach dem Absenden nicht mehr bearbeitet oder eingesehen werden.</div>
                    <div v-else><b>Achtung:</b><br/>Das Feedback kann nach dem Absenden nicht mehr bearbeitet, sondern nur noch eingesehen werden.</div>
                    <div class="row justify-content-end mt-3">
                        <div class="col-auto">
                            <button type="button" class="btn btn-secondary me-3" data-bs-dismiss="modal">Nein, zurück</button>
                            <button type="button" class="btn btn-primary" @click="submit_feedback()" data-bs-dismiss="modal">Ja, senden</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import {showErrorAlert, showSuccessAlert} from "@/helpers/Alerts";
import {useVuelidate} from "@vuelidate/core";
import {helpers, required} from "@vuelidate/validators";

export default {
    emits: ['content-copied', 'feedback-submitted'],
    props: ['counselling', 'courseFinished', 'isTeacher', 'feedbackRequestId', 'isPeer', 'counsellingFeedback', 'copyContent'],
    data() {
        return {
            inputs: {
                text: this.counsellingFeedback?.feedback_text ? this.counsellingFeedback?.feedback_text : '',
            },
            v$: useVuelidate(),

            showSuccess: false,
            source : this.isPeer ? 'peer' : 'teacher',
            teacherFeedbackSubmitted: false,
            saveTimeout: null,
        };
    },

    validations() {
        return {
            inputs: {
                text: {
                    required: helpers.withMessage('Pflichtfeld', required),
                },
            },
        };
    },

    mounted() {
        if(this.counsellingFeedback?.status_feedback_id === 3 || this.counsellingFeedback?.status_feedback_id === 4){
            this.teacherFeedbackSubmitted = true;
        }

        this.$emit('content-copied', content => {
            this.inputs.text += content;
        });

    },

    computed: {
        wordCount() {
            // Count Feedback Text Words
            const words = this.inputs.text.split(/\s+/);
            return words.filter(word => word.length > 0).length;
        },
    },

    methods: {
        async submit_feedback() {
            const inputCorrect = await this.v$.$validate();
            if (!inputCorrect) return;

            axios.post('/counselling/' + this.counselling.id + '/feedback/' + this.feedbackRequestId + '/submit-feedback', { feedback_text: this.inputs.text, feedback_request_id: this.feedbackRequestId, course_id:  this.counselling.course, source: this.source})
                .then((res) => {
                    if(this.isPeer){
                        this.showSuccess = true;
                        this.$emit('feedback-submitted');
                    }
                    else{
                        this.teacherFeedbackSubmitted = true;
                        this.inputs.text = res.data.feedback_text;
                        this.$emit('feedback-submitted');
                    }

                    showSuccessAlert(res.data.message);
                })
                .catch((err) => {
                    showErrorAlert(err);
                })
        },

        cancel_feedback(){
            axios.post('/counselling/' + this.counselling.id + '/feedback/' + this.feedbackRequestId + '/cancel', {feedback_request_id: this.feedbackRequestId, course_id:  this.counselling.course})
                .then(() => {
                    window.location.href = '/course/' + this.counselling.course;
                })
                .catch((err) => {
                    showErrorAlert(err);
                })
        },

        autoSave(){
            clearTimeout(this.saveTimeout);
            this.saveTimeout = setTimeout(() => {
                // Rufen Sie die Auto-Save-Methode auf
                this.submit_auto_save(this.inputs.text)
            }, 2000);
        },

        submit_auto_save(text){
            axios.post('/counselling/' + this.counselling.id + '/feedback/' + this.feedbackRequestId + '/auto-save', {feedback_request_id: this.feedbackRequestId, course_id:  this.counselling.course, feedback_text: text})
        },

        save_and_return(text){
            axios.post('/counselling/' + this.counselling.id + '/feedback/' + this.feedbackRequestId + '/auto-save', {feedback_request_id: this.feedbackRequestId, course_id:  this.counselling.course, feedback_text: text})
                .then(() => {
                    window.location.href = '/course/' + this.counselling.course;
                })
        },
    },

    watch:{
        copyContent: {
            handler(copyContent){
                this.inputs.text += '"' + copyContent + '" => ';
            }
        }
    }
}
</script>
<style scoped lang="scss">
@import '../../../../../../css/general';
textarea {
    resize: none;
}

.text-success {
    color: $green;
}

.text-danger {
    color: $red;
}


</style>
