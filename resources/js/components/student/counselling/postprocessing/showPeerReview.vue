<template>
    <span class="mt-3">Erhalten am {{received_at}} von {{peer_reviewer_pseudonym}} </span>
    <textarea class="form-control mb-3" style="flex: 1;" readonly v-model="feedback_text"></textarea>
</template>


<script>
import {showErrorAlert} from "@/helpers/Alerts";

export default {
    props: ['availablePeerFeedbacksData', 'index'],
    emits: ['updateFeedbackStatus'],
    data() {
        return {
            feedback_text: this.availablePeerFeedbacksData[this.index]?.feedback_text || '',
            status_feedback_id: this.availablePeerFeedbacksData[this.index]?.status_feedback_id || '',
            received_at: new Date(this.availablePeerFeedbacksData[this.index]?.received_at).toLocaleDateString('de') || '',
            peer_reviewer_pseudonym: this.availablePeerFeedbacksData[this.index]?.peer_reviewer_pseudonym || '',
        };
    },

    created() {
        this.status_feedback_id = this.availablePeerFeedbacksData[this.index]?.status_feedback_id || '';
        if(this.availablePeerFeedbacksData !== undefined && this.status_feedback_id === 3){
            this.updateFeedbackStatus();
        }
    },

    watch: {
        availablePeerFeedbacksData: {
            handler(availablePeerFeedbacksData) {
                this.feedback_text = availablePeerFeedbacksData[this.index]?.feedback_text || '';
                this.status_feedback_id = availablePeerFeedbacksData[this.index]?.status_feedback_id || '';
                this.received_at = new Date(this.availablePeerFeedbacksData[this.index]?.received_at).toLocaleDateString('de') || '';
                this.peer_reviewer_pseudonym = this.availablePeerFeedbacksData[this.index]?.peer_reviewer_pseudonym || '';
            },
            deep: true
        },
        index: function(newIndex) {
            this.feedback_text = this.availablePeerFeedbacksData[newIndex]?.feedback_text || '';
            this.status_feedback_id = this.availablePeerFeedbacksData[newIndex]?.status_feedback_id || '';
            this.received_at = new Date(this.availablePeerFeedbacksData[newIndex]?.received_at).toLocaleDateString('de') || '';
            this.peer_reviewer_pseudonym = this.availablePeerFeedbacksData[newIndex]?.peer_reviewer_pseudonym || '';

            // check if current status is new available
            if (this.availablePeerFeedbacksData[newIndex]?.status_feedback_id === 3) {
                this.updateFeedbackStatus();
            }
        }
    },

    methods: {
            updateFeedbackStatus() {
                axios.post('/counselling/' + this.availablePeerFeedbacksData[this.index].counselling_id  + '/feedback/' + this.availablePeerFeedbacksData[this.index].id + '/update-newAvailable', {
                    feedback_request_id: this.availablePeerFeedbacksData[this.index]?.id
                })
                    .then((res) => {
                        // inform parent and update status in prop
                        this.$emit('updateFeedbackStatus', this.index);
                    })
                    .catch((err) => {

                    })
            },
    }
}
</script>

<style scoped lang="scss">
textarea {
    resize: none !important;
}
</style>
