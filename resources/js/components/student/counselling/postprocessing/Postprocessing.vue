<template>
    <div class="row justify-content-center" ref="siteContent">
        <div class="col-lg-6 col-xxl-7">
            <!-- Left Card: Chat -->
            <div class="card mb-0 d-lg-flex" v-show="active_card === 'chat'">
                <div class="card-header">
                    <fa-icon :icon="['fas', 'comment']"></fa-icon>
                        Chat
                    </div>
                <div class="card-body p-0" ref="chatCard">
                    <messages :counselling="counselling" :messages="counselling.counselling_messages" :pseudonym="pseudonym" :courseFinished="courseFinished" :userIsCounsellingCreator="userIsCounsellingCreator" :is-teacher="isTeacher" :show-copy-button="showCopyButton" @content-copied="onContentCopied" ></messages>
                </div>
            </div>
        </div>
        <!-- Right Card: Postprocessing -->
        <div class="col-lg-6 col-xxl-5">
            <div class="card card-right mb-0 d-lg-flex" v-show="active_card === 'postprocessing'">
                <div class="card-header p-0" ref="postprocessingHeader">
                    <div class="nav nav-tabs d-flex">
                        <div class="nav-item" v-if="!isPeer">
                            <button class="nav-link" :class="{'selected': active_tab === 'dashboard'}" @click="active_tab='dashboard'">
                                <fa-icon :icon="['fas', 'house']"></fa-icon>
                            </button>
                        </div>
                        <div class="nav-item" v-if="!isTeacher && !isPeer">
                            <button class="nav-link" :class="{'selected': active_tab === 'note'}" @click="active_tab='note'">Notiz</button>
                        </div>
                        <div class="nav-item feedback" v-if="!isPeer">
                            <button class="nav-link" :class="{'selected': active_tab === 'feedback-ki'}"  @click="active_tab='feedback-ki'">KI Feedback</button>
                        </div>
                        <div class="nav-item feedback" v-if="!isPeer">
                            <button class="nav-link" :class="{'selected': active_tab === 'feedback-teacher'}"  @click="active_tab='feedback-teacher'">Feedback Trainer*in</button>
                        </div>
                        <div class="nav-item feedback" v-if="!isTeacher">
                            <button class="nav-link" :class="{'selected': active_tab === 'feedback-peer'}"  @click="active_tab='feedback-peer'">Peer Review</button>
                        </div>

                        <div class="nav-item dropdown">
                            <button class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button"
                                aria-expanded="false">Feedback</button>
                            <ul class="dropdown-menu">
                                <li><button class="dropdown-item" @click="active_tab='feedback-ki'">KI Feedback</button></li>
                                <li><button class="dropdown-item" @click="active_tab='feedback-teacher'">Feedback Trainer*in</button></li>
                                <li><button class="dropdown-item" @click="active_tab='feedback-peer'" v-if="!isTeacher">Peer Review</button></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="card-body mb-3" ref="postprocessingCard">
                    <dashboard v-show="active_tab === 'dashboard'"
                        :counselling="counselling"
                        :counselling_feedbacks = "counsellingFeedbacks"
                        :courseFinished="courseFinished"
                        :is-teacher="isTeacher"
                        @changeTab="active_tab=$event;"
                        @counsellingChanged="counselling=$event;"
                    ></dashboard>
                    <note v-show="active_tab === 'note'"
                          v-if="counselling.postprocessing_process && counselling.postprocessing_process.note"
                        :note="counselling.postprocessing_process.note"
                        :courseFinished="courseFinished"
                        :counselling_id="counselling.id"
                        @counsellingChanged="counselling=$event;"
                    ></note>
                    <k-i-feedback v-show="active_tab === 'feedback-ki'"
                              :course-member="counselling.course_member_id"
                              :is-teacher="isTeacher"
                              :feedback-request-id="feedbackRequestId"
                              :counselling="counselling"
                              :counselling-feedbacks = "counsellingFeedbacks"
                              :persona = "counselling.persona"
                              :courseFinished="courseFinished"
                              :counselling_id="counselling.id"
                              @counsellingChanged="counselling=$event;"
                    ></k-i-feedback>
                    <feedback v-show="active_tab === 'feedback-teacher'"
                         :course-member="counselling.course_member_id"
                         :trainer-feedback-contingent="trainerFeedbackContingent"
                         :is-teacher="isTeacher"
                          :user-id="userId"
                         :copy-content="copyContent"
                         :feedback-request-id="feedbackRequestId"
                         :counselling="counselling"
                         :counselling-feedback = "counsellingFeedback"
                         :counselling-feedbacks = "counsellingFeedbacks"
                         :courseFinished="courseFinished"
                         :counselling_id="counselling.id"
                         @counsellingChanged="counselling=$event;"
                         @feedback-submitted="onFeedbackSubmitted"
                    ></feedback>
                    <peer-review v-show="active_tab === 'feedback-peer'"
                                 v-if="!isTeacher"
                          :course-member="counselling.course_member_id"
                          :counselling="counselling"
                          :counselling-feedbacks = "counsellingFeedbacks"
                          :courseFinished="courseFinished"
                          :counselling-id="counselling.id"
                          @counsellingChanged="counselling=$event;"
                    ></peer-review>
                </div>
            </div>
        </div>
    </div>
    <!-- Toggle bar at bottom for small screens -->
    <div class="d-block d-lg-none toggle-bar" ref="toggleBar">
        <div class="row justify-content-center">
            <div class="col text-center p-3" :class="{'active': active_card === 'chat'}" @click="active_card = 'chat'">
                <span>Chat</span>
            </div>
            <div class="col text-center p-3" :class="{'active': active_card === 'postprocessing'}" @click="active_card = 'postprocessing'">
                <span>Nachbereitung</span>
            </div>
        </div>
    </div>

</template>
<script>
import { showSuccessAlert , showErrorAlert} from '../../../../helpers/Alerts';
import { getShortPseudonym } from '../../../../helpers/Pseudonym';

export default {
    props: ['id', 'courseEndDate', 'trainerFeedbackContingent', 'isTeacher', 'isPeer', 'feedbackRequestId', 'counsellingFeedback', 'userIsCounsellingCreator', 'userId'],
    data() {
        return {
            counselling: {},
            pseudonym: '',
            active_tab: 'dashboard',
            active_card: 'postprocessing',
            counsellingFeedbacks: {},
            courseID: '',
            copyContent: '',
            showCopyButton: this.counsellingFeedback !== undefined && this.counsellingFeedback.user_id === this.userId && (this.counsellingFeedback.status_feedback_id === 1 || this.counsellingFeedback.status_feedback_id === 2),
        };
    },

    computed: {
        courseFinished() {
            const end = new Date(this.courseEndDate).setHours(0,0,0,0);
            const today = new Date().setHours(0,0,0,0);
            return today > end;
        },
    },

    mounted() {
            axios.get(`/counselling/${this.id}/data`)
                .then(res => {
                    this.counselling = res.data.counselling;
                    this.courseID = res.data.counselling.course;
                    this.counsellingFeedbacks = this.counselling.counselling_feedbacks;
                    if(!this.isTeacher){
                        axios.get(`/course/${this.counselling.course}/pseudonym`)
                            .then((res) => {
                                this.pseudonym = getShortPseudonym(res.data.pseudo_first_name, res.data.pseudo_last_name);
                            })
                            .catch((err) => {
                                showErrorAlert(err);
                            });
                    }
                    else{
                        axios.get(`/counselling/${this.counselling.id}/pseudonym`)
                            .then((res) => {
                                this.pseudonym = getShortPseudonym(res.data.pseudo_first_name, res.data.pseudo_last_name);
                            })
                            .catch((err) => {
                                showErrorAlert(err);
                            });
                    }
                })
                .catch(err => {
                    showErrorAlert(err);
                })

        this.setCardHeight();
        window.addEventListener('resize', this.setCardHeight);
    },
    beforeDestroy() {
        window.removeEventListener('resize', this.setCardHeight);
    },
    methods: {
        setCardHeight() {
            const navBar = document.getElementsByClassName('navbar')[0];
            let height = document.documentElement.clientHeight - this.$refs.postprocessingHeader.clientHeight - 50;
            if (window.innerWidth < 992) {
                height = height - this.$refs.toggleBar.clientHeight;
                this.$refs.siteContent.style.marginBottom = this.$refs.toggleBar.clientHeight + 'px';
            } else {
                height =  height - navBar.clientHeight;
            }
            this.$refs.chatCard.style.height = height + 'px';
            this.$refs.postprocessingCard.style.height = height + 'px';
        },

        onFeedbackSubmitted() {
            this.showCopyButton = false;
        },

        onContentCopied(content) {
            this.copyContent = content;
        }

    },

    watch: {
        counselling: {
            handler(counselling) {
                this.counselling = counselling;
                this.counsellingFeedbacks = counselling.counselling_feedbacks;
            },
            deep: true
        },

        copyContent: {
            handler(newCopyContent) {
                this.copyContent = newCopyContent;
            }
        }
    }
}
</script>
<style lang="scss" scoped>
@import '../../../../../css/general.scss';

.card-right {
    .card-header {
    background-color: white;
    border-bottom: 1px solid $grey-dark;
    margin-bottom: 1px;

        svg {
            margin-right: 0;
        }
    }

    .card-body {
        padding: 30px 40px;
    }
}

.card-body {
    overflow: auto;
}

.nav-tabs {
    border-bottom: 1px solid transparent;
    gap: 3px;
}

.nav-tabs .nav-link {
    color: $text;
    background-color: white;
    border: none;
    padding-top: 15px;
    padding-bottom: 15px;

    &.selected {
        color: white;
        background-color: $grey-dark;
        border: none;
    }

    &:hover:not(.selected) {
        background-color: $grey-light;
        color: white;
    }

    &:disabled {
        color: $grey;
    }
}

.nav-item.dropdown {
    display: none;
}

.dropdown-toggle.show {
    background-color: $grey-light !important;
    color: white !important;
}

.dropdown-menu.show {
    transform: translate(0px, 56px) !important;
}


.dropdown-item:hover{
    background-color: $grey-light;
    color: white;
}

@media (min-width: map-get($grid-breakpoints, lg)) and (max-width: map-get($grid-breakpoints, xl)), (max-width: map-get($grid-breakpoints, md)) {
    .nav-item.feedback {
        display: none;
    }
    .nav-item.dropdown {
        display: block;
    }
}

@include media-breakpoint-between(xl, xxl) {
    .nav-link {
        padding: 15px 10px;
    }
}

.toggle-bar {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    background-color: $white;
    border-top: 1px solid $border-color;

    .row .col {

        &:hover {
            background-color: $grey;
            cursor: pointer;
        }
        &.active {
            background-color: $grey-dark;
            color: white;
        }
    }

}

</style>
