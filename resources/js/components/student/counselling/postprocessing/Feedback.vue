<template>
    <div class="h-100">
        <div class="d-flex flex-column h-100">
            <div class="note-header" v-if="!isTeacher">
                <div class="row" v-if="isFeedbackAvailable" >
                    <div class="col-auto d-flex align-items-center">
                        <input type="checkbox" class="form-check-input green" @change="noteCheckboxChanged" :checked="counselling.postprocessing_process?.teacher_feedback === 1"
                               :disabled="courseFinished"
                        />
                    </div>
                    <div class="col p-0" >
                        <b>Feedback von Trainer*in</b>
                    </div>
                </div>
            </div>
            <div class="h-100" v-show="!isFeedbackAvailable">
                <div class="row text-center align-items-center justify-content-center mt-4" >
                    <span v-if="!isFeedbackRequested" class="fw-bold mb-2">{{!isTeacher ? "Kein Feedback vorhanden." : "Kein Feedback angefragt."}}</span>
                    <div v-else v-if="!isTeacher">
                        <div class="fw-bold">Feedback ausstehend.</div>
                        <br>
                        <span>angefragt am {{ requestDate }}</span>
                    </div>
                </div>
                <submit-feedback v-if="isTeacher && feedbackRequestId !== null && teacher_allowed_to_edit" :counselling="counselling" :is-Teacher="isTeacher" :counselling-feedback="counsellingFeedback" :feedback-request-id="feedbackRequestId" :copy-content="copyContent" @feedback-submitted="onFeedbackSubmitted"></submit-feedback>
                <div class="text-center" v-if="!teacher_allowed_to_edit && isFeedbackRequested && isTeacher">In Bearbeitung seit {{accepted_at}} von {{name_trainer}}</div>
                <div v-if="!isFeedbackRequested && !isTeacher">
                    <div class="row text-center mt-3">
                        <div class="col">
                    <span
                        :title="availableFeedbackRequests === 0 || isFeedbackRequested ? 'Keine Feedback-Anfragen mehr möglich' : ''">
                        <button class="btn btn-primary" @click="requestFeedback"
                                :disabled="availableFeedbackRequests === 0 || isFeedbackRequested || courseFinished">Feedback anfragen</button>
                    </span>
                        </div>
                    </div>
                    <div class="row text-center mt-2">
                        <div class="col">
                            Noch <span class="fw-bold">{{ availableFeedbackRequests }}</span> von
                            {{ trainerFeedbackContingent }} Feedback-Anfragen verfügbar.
                        </div>
                    </div>
                </div>
            </div>
            <span class="mt-3" v-if="isFeedbackAvailable">Erhalten am {{received_at}} von {{name_trainer}} </span>
            <textarea v-show="isFeedbackAvailable" class="form-control mt-3 mb-3" style="flex: 1;" readonly
                      v-model="feedback_text"></textarea>
        </div>
    </div>


</template>

<script>
import {hideErrorAlert, showErrorAlert, showSuccessAlert} from "@/helpers/Alerts";

export default {
    props: ['counselling', 'counselling_id', 'courseFinished', 'counsellingFeedbacks', 'counsellingFeedback', 'trainerFeedbackContingent', 'isTeacher', 'feedbackRequestId', 'copyContent', 'userId'],
    data() {
        return {
            coinCount: 1,
            availableFeedbackRequests: 0,
            availableFeedbacksData: {},
            isFeedbackRequested: false,
            isFeedbackPending: false,
            isFeedbackAvailable: false,
            courseID: '',
            requestDate: '',
            feedback_text: this.availableFeedbacksData?.feedback_text || '',
            source: this.isTeacher ? "teacher" : "peer",
            received_at: new Date(this.counsellingFeedbacks?.received_at).toLocaleDateString('de') || '',
            accepted_at: new Date(this.counsellingFeedbacks?.accepted_at).toLocaleDateString('de') || '',
            name_trainer: this.counsellingFeedbacks?.name_trainer || '',
            teacher_allowed_to_edit: this.counsellingFeedbacks?.user_id === this.userId,
        };
    },

    mounted() {
        this.availableFeedbackRequests = this.counselling.course_member?.properties
            ? (this.trainerFeedbackContingent - JSON.parse(this.counselling.course_member.properties).feedback_requests_made)
            : null;
    },

    methods: {
        requestFeedback() {
            axios.post('/counselling/' + this.counselling_id + "/request-feedback", {
                counselling_id: this.counselling_id,
                source: 'teacher',
            })
                .then(res => {
                    showSuccessAlert(res.data.message);
                    this.isFeedbackRequested = true;
                    this.requestDate = new Date(res.data.requestDate).toLocaleDateString("de");
                })
                .catch(err => {
                    showErrorAlert(err);
                })
        },

        noteCheckboxChanged($event) {
            let data;
            data = $event.target.checked ? 1 : 0;
            this.saveProcess(data);
        },

        saveProcess(data){
            axios.put('/counselling/' + this.counselling_id, {
                type: 'teacher_feedback',
                data: data
            })
                .then(res => {
                    this.$emit('counsellingChanged', res.data.counselling);
                    showSuccessAlert(res.data.message);
                })
                .catch(err => {
                    showErrorAlert(err);
                })
        },

        updateFeedbacksData() {
            const feedbacksArray = Object.values(this.counsellingFeedbacks);
            const feedbackRequest = feedbacksArray.filter(feedback => feedback.feedback_source_id === 1);
            const openFeedbacks = feedbackRequest.filter(feedback => feedback.status_feedback_id === 1 || feedback.status_feedback_id === 2);
            const availableFeedbacks = feedbackRequest.filter(feedback => feedback.status_feedback_id === 3 || feedback.status_feedback_id === 4);

            this.availableFeedbacksData = availableFeedbacks;
            this.isFeedbackRequested = feedbackRequest.length !== 0;
            this.isFeedbackPending = openFeedbacks.length !== 0;
            this.isFeedbackAvailable = availableFeedbacks.length !== 0;

            if(this.isFeedbackPending){
                this.teacher_allowed_to_edit = openFeedbacks[0].user_id === this.userId;
                this.accepted_at = new Date(openFeedbacks[0].accepted_at).toLocaleDateString('de')
                this.name_trainer = openFeedbacks[0].name_trainer;
            }

            if(this.isFeedbackRequested) {
                this.requestDate = new Date(feedbackRequest[0].created_at).toLocaleDateString("de");
            }

            if(this.isFeedbackAvailable){
                this.feedback_text = this.availableFeedbacksData[0].feedback_text;
                this.received_at = new Date(this.availableFeedbacksData[0].received_at).toLocaleDateString('de')
                this.name_trainer = this.availableFeedbacksData[0].name_trainer;
                if(this.availableFeedbacksData[0].status_feedback_id === 3 ){
                    this.updateFeedbackStatus();
                }
            }
        },

        updateFeedbackStatus() {
            if(!this.isTeacher){
                axios.post('/counselling/' + this.counselling_id + '/feedback/' + this.feedbackRequestId + '/update-newAvailable', {
                    feedback_request_id: this.availableFeedbacksData[0]?.id
                })
            }
        },

        onFeedbackSubmitted() {
            this.$emit('feedback-submitted');
        }
    },

    watch: {
        counselling: {
            handler(counselling) {
                this.availableFeedbackRequests = this.counselling.course_member?.properties
                    ? (this.trainerFeedbackContingent - JSON.parse(this.counselling.course_member.properties).feedback_requests_made)
                    : null;
                this.courseID = counselling.course;
            },
            deep: true
        },
        counsellingFeedbacks: {
            handler(counsellingFeedbacks) {
                // update variables when changed
                this.updateFeedbacksData();
            },
            deep: true,
        },
    },
}
</script>

<style scoped lang="scss">
textarea {
    resize: none !important;
}
</style>
