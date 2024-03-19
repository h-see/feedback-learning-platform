<template>
    <div class="h-100">
        <div class="d-flex flex-column h-100">
            <div class="note-header">
                <div class="row align-items-center" v-if="availablePeerReviews !== 0">
                    <div class="col-auto d-flex align-items-center"
                         :title="availablePeerReviews < totalPeerReviews ? 'Kann nicht abgeschlossen werden, da noch PeerReviews offen sind.' : ''">
                        <input type="checkbox" class="form-check-input green" @change="noteCheckboxChanged"
                               :checked="counselling.postprocessing_process?.peer_review === 1"
                               :disabled="courseFinished || availablePeerReviews < totalPeerReviews "
                        />
                    </div>
                    <div class="col-auto p-0 d-flex flex-column flex-lg-row align-items-lg-center">
                        <b>Peer Review&nbsp;</b>
                        <b class="d-flex align-items-center">({{ availablePeerReviews }}/{{ totalPeerReviews }} vorhanden) </b>
                    </div>
                    <div class="col-auto ms-auto">
                        <button class="btn btn-secondary" v-if="currentIndex!==-1" @click="currentIndex=-1">
                            <span class="d-none d-xl-inline">Weitere Peer Reviews anfragen</span>
                            <fa-icon class="d-xl-none" :icon="['fas', 'plus']"></fa-icon>
                       </button>
                        <button class="btn btn-secondary" v-else @click="currentIndex=0">
                            <span class="d-none d-xl-inline">Zurück zu Peer Reviews</span>
                            <fa-icon class="d-xl-none" :icon="['fas', 'arrow-right']"></fa-icon>
                        </button>
                    </div>
                </div>
            </div>
            <div v-if="currentIndex === -1 || availablePeerReviews === 0">
                <div class="row text-center align-items-center justify-content-center mt-4">
                    <span v-if="totalPeerReviews === 0" class="fw-bold mb-2">Kein PeerReview vorhanden.</span>
                    <div v-else class="fw-bold mb-2">
                    <span v-if="availablePeerReviews === 0">{{ pendingPeerReviews }}/{{ totalPeerReviews }} PeerReview ausstehend </span>
                    <span v-else>Weitere Peer Reviews anfragen </span>
                </div>
                    <div class="col-auto my-2">
                        <div class="input-group number-spinner border rounded-3 " style="max-width: 100px;">
                        <span class="input-group-btn">
                            <button
                                class="btn btn-default m-0"
                                :class="{ 'text-muted': coinCount === 1, 'border-0': coinCount === 1 }"
                                @click="decrement"
                                :disabled="coinCount===1">
                                <span><fa-icon :icon="['fas', 'minus']" size="xs"/></span>
                            </button>
                        </span>
                            <input type="text" class="form-control text-center p-0 m-0" v-model="coinCount" min="1" @keyup="setMin" @input="validateInput"  readonly>
                            <span class="input-group-btn">
                            <button
                                class="btn btn-default m-0"
                                :class="{ 'text-muted': coinCount + 1 > availableTokens, 'border-0': coinCount + 1 > availableTokens }"
                                data-dir="up"
                                @click="increment"
                                :disabled="coinCount+1 > availableTokens">
                                <span><fa-icon :icon="['fas', 'plus']" size="xs"/></span>
                            </button>
                        </span>
                        </div>
                    </div>
                    <div class="col-auto align-items-center">
                    <span class="">
                        <fa-icon class="coin" :icon="['fas', 'coins']" size="3x"/>
                        <span class="ms-1 fw-bold coin-counter">-{{ coinCount }}</span>
                    </span>
                    </div>
                </div>
                <div class="row text-center mt-3">
                    <div class="col">
                    <span :title="coinCount > availableTokens ? 'Nicht genügend Tokens verfügbar' : ''">
                        <button class="btn btn-primary" @click="requestPeerReview"
                                :disabled="coinCount > availableTokens || courseFinished || coinCount < 1">Peer Review anfragen</button>
                    </span>
                    </div>
                </div>
                <div class="row text-center mt-2">
                    <div class="col">
                        Aktuell <span class="fw-bold">{{ availableTokens }}</span> PeerReview-Token verfügbar.
                    </div>
                </div>
                <div class="row text-center">
                    <div class="col-7 m-auto mt-2">
                        Durch Abgeben eines PeerReviews können weitere PeerReview-Token verdient werden.
                    </div>
                </div>
                <div class="row text-center mt-1">
                    <div class="col">
                        <div class="open-card open-exercises"><a class="link"
                                                                 :href="`/course/${courseID}/peer-reviews`">Zu den
                            Anfragen <span><fa-icon :icon="['fas', 'arrow-right-long']"/></span></a></div>
                    </div>
                </div>
            </div>
            <show-peer-review v-else
                                :available-peer-feedbacks-data="availablePeerFeedbacksData"
                                :index="currentIndex"
                                @updateFeedbackStatus="handleFeedbackStatusUpdate">
            </show-peer-review>
            <!-- Pagination -->
            <div class="row text-center mt-3 mt-auto" v-if="availablePeerReviews > 0 && currentIndex !== -1 && totalPeerReviews !== 1">
                <div class="col">
                    <button class="btn btn-outline-secondary" @click="previousFeedback" :hidden="currentIndex <= 0"
                            :class="{ 'text-muted': currentIndex === 0}">
                        <fa-icon :icon="['fas', 'chevron-left']"/>
                    </button>
                    <span v-if="currentIndex === -1" class="mx-2">
                        <fa-icon :icon="['fas', 'house']" size="lg"/>
                    </span>
                    <span v-else class="ms-2 me-2">{{ currentIndex+1 }} / {{ totalPeerReviews }}</span>
                    <span :title="currentIndex === availablePeerReviews-1 ? 'Weitere Peer Reviews ausstehend' : ''">
                        <button class="btn btn-outline-secondary" v-show="currentIndex < totalPeerReviews-1" @click="nextFeedback"
                                :disabled="currentIndex === availablePeerReviews-1 || availablePeerReviews === 0"
                                :class="{ 'text-muted': currentIndex === availablePeerReviews-1 || availablePeerReviews === 0}">
                            <fa-icon :icon="['fas', 'chevron-right']"/>
                        </button>
                    </span>
                </div>
            </div>
        </div>
    </div>


</template>


<script>
import {showSuccessAlert, showErrorAlert} from '../../../../helpers/Alerts';

export default {
    props: ['counselling', 'counsellingId', 'courseFinished', 'counsellingFeedbacks'],
    data() {
        return {
            coinCount: 1,
            availableTokens: '',
            availablePeerFeedbacksData: {},
            totalPeerReviews: 0,
            pendingPeerReviews: 0,
            availablePeerReviews: 0,
            courseID: '',
            currentIndex: 0,
        };
    },

    methods: {
        increment() {
            this.coinCount++;
        },
        decrement() {
            if (this.coinCount > 1) {
                this.coinCount--;
            }
        },

        setMin() {
            if(this.coinCount < 1){
                this.coinCount = 1;
            }
        },

        validateInput() {
            // remove all inputs that are not numeric
            this.coinCount = this.coinCount.replace(/[^0-9]/g, '');
            // reset to 1
            this.setMin();
        },

        requestPeerReview() {
            axios.post('/counselling/' + this.counsellingId + "/request-feedback", {
                counselling_id: this.counsellingId,
                coins: this.coinCount,
                source: 'peer',
            })
                .then(res => {
                    showSuccessAlert(res.data.message);
                    this.availableTokens = res.data.availableTokens;
                    this.totalPeerReviews = res.data.totalPeerReviews;
                    this.pendingPeerReviews = res.data.pendingPeerReviews;

                    if(this.counselling.postprocessing_process?.peer_review === 1){
                        this.saveProcess(0);
                    }
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
            axios.put('/counselling/' + this.counsellingId, {
                type: 'peer_review',
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
            const allPeerFeedbacks = feedbacksArray.filter(feedback => feedback.feedback_source_id === 2);
            const openPeerFeedbacks = allPeerFeedbacks.filter(feedback => feedback.status_feedback_id === 1 || feedback.status_feedback_id === 2);
            const availablePeerFeedbacks = allPeerFeedbacks.filter(feedback => feedback.status_feedback_id === 3 || feedback.status_feedback_id === 4);

            this.availablePeerFeedbacksData = availablePeerFeedbacks;
            this.totalPeerReviews = allPeerFeedbacks.length;
            this.pendingPeerReviews = openPeerFeedbacks.length;
            this.availablePeerReviews = availablePeerFeedbacks.length;
        },

        previousFeedback() {
            if (this.currentIndex >= 0) {
                this.currentIndex--;
            }
        },

        nextFeedback() {
            if (this.currentIndex !== this.availablePeerReviews && this.availablePeerReviews > 0) {
                this.currentIndex++;
            }
        },

        handleFeedbackStatusUpdate(index) {
            this.availablePeerFeedbacksData[index].status_feedback_id = 4;
        }

    },

    mounted() {
        // get feedback Data om Load
        this.updateFeedbacksData();
        this.availableTokens = this.counselling.course_member?.properties
            ? JSON.parse(this.counselling.course_member.properties).peer_review_token
            : null;

        // Set currentIndex to the first available peer review if any
        if (this.availablePeerReviews > 0) {
            this.currentIndex = 0;
        }

    },

    watch: {
        counselling: {
            handler(counselling) {
                this.availableTokens = counselling.course_member.properties
                    ? JSON.parse(counselling.course_member.properties).peer_review_token
                    : null;

                this.courseID = counselling.course;
            },
            deep: true
        },
        counsellingFeedbacks: {
            handler(counselling_feedbacks) {
                // update variables when changed
                this.updateFeedbacksData();
            },
            deep: true,
        },
    },
}

</script>


<style lang="scss" scoped>
@import '../../../../../css/general.scss';

.input-group-btn input {
    border: none;
}

.coin, .coin-counter {
    color: $red;
}

button:disabled {

}

</style>
