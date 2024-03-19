<template>
    <div class="row justify-content-center" ref="siteContent">
        <div class="col-lg-7 col-xxl-8">
            <!-- Left Card: Chat -->
            <div class="card mb-0 d-lg-flex" v-show="active_card === 'chat'">
                <div class="card-header">
                    <fa-icon :icon="['fas', 'comment']"></fa-icon>
                    Chat
                </div>
                <div class="card-body p-0" ref="chatCard">
                    <messages :counselling="counselling" :messages="counselling.counselling_messages"
                              :pseudonym="pseudonym" :courseFinished="courseFinished"
                              :userIsCounsellingCreator=false @content-copied="onContentCopied" :show-copy-button="showCopyButton"></messages>
                </div>
            </div>
        </div>
        <!-- Right Card: Postprocessing -->
        <div class="col-lg-5 col-xxl-4">
            <div class="card card-right mb-0 d-lg-flex" v-show="active_card === 'feedback'">
                <div class="card-header" ref="postprocessingHeader">
                    <span v-if="isPeer">
                         <fa-icon :icon="['fas', 'user-friends']"></fa-icon>
                    Peer Review <span class="ms-2 pointer" data-bs-toggle="modal" data-bs-target="#peerInfos"><fa-icon :icon="['fas', 'circle-info']"></fa-icon></span>
                    </span>
                    <span v-else>
                        <fa-icon :icon="['fas', 'comment']"></fa-icon>
                    Feedback Trainer*in
                    </span>
                </div>
                <div class="card-body" ref="postprocessingCard">
                    <submit-feedback :counselling="counselling" :is-peer="isPeer" :feedback-request-id="feedbackRequestId" :counselling-feedback="counsellingFeedback" :copy-content="copyContent" @feedback-submitted="onFeedbackSubmitted"></submit-feedback>
                </div>
            </div>
        </div>
    </div>

    <div class="d-block d-lg-none toggle-bar" ref="toggleBar">
        <div class="row justify-content-center">
            <div class="col text-center p-3" :class="{'active': active_card === 'chat'}" @click="active_card = 'chat'">
                <span>Chat</span>
            </div>
            <div class="col text-center p-3" :class="{'active': active_card === 'feedback'}"
                 @click="active_card = 'feedback'">
                <span v-if="isPeer">Peer Review</span>
                <span v-else>Feedback</span>
            </div>
        </div>
    </div>

    <div class="modal modal-lg fade" id="peerInfos" tabindex="-1" aria-labelledby="peerInfos" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Informationen zum Peer Review</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div><b>Technische Informationen:</b><br>
                        <ul>
                            <li> Für jedes abgeschickte Peer Review erhältst du einen Token.</li>
                            <li> Mit diesen Tokens kannst du dir wiederrum selbst Peer Reviews anfordern. </li>
                            <li> Wenn du das Peer Review doch nicht abgeben willst, klicke unten auf "Abbrechen" (bitte <u>nicht</u> einfach das Browserfenster schließen)</li>
                            <li> Beim Abbrechen wird das eventuell bisherig geschriebene Peer Review gelöscht und die Beratung wieder für Mitstudierende freigegeben. </li>
                            <li> Über den Kopieren Button neben den Nachrichten, kannst du diese ins Textfeld kopieren um bei Bedarf direkt Bezug darauf zu nehmen.</li>
                        </ul>
                    </div>
                    <br>
                    <div><b>Inhaltliche Informationen:</b><br>
                        <ul>
                            <li> Dein Feedback soll beschreibend statt bewertend, konkret und verhaltensbezogen, einladend und erbeten sein </li>
                            <li> Bitte formuliere das Feedback in Ich-Botschaften </li>
                            <li> Gib konkrete Verbesserungsvorschläge wenn notwendig </li>
                            <li> Eine Textlänge zwischen 120 und 180 Wörtern ist optimal. 50 Wörter sind das Minimum um das Peer Review abgeben zu können. </li>
                        </ul>
                    </div>


                    <div class="row justify-content-end mt-3">
                        <div class="col-auto">
                            <button type="button" class="btn btn-primary me-3" data-bs-dismiss="modal">Okay</button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</template>

<script>
import {getFullPseudonym, getShortPseudonym} from "@/helpers/Pseudonym";
import {showErrorAlert} from "@/helpers/Alerts";

export default {
    inheritAttrs:false,
    props: ['id', 'courseEndDate', 'trainerFeedbackContingent', 'isTeacher', 'isPeer', 'feedbackRequestId', 'counsellingFeedback'],
    data() {
        return {
            counselling: {},
            pseudonym: '',
            active_tab: 'dashboard',
            active_card: 'postprocessing',
            courseID: '',
            copyContent: '',
            showCopyButton: this.counsellingFeedback !== undefined && this.isPeer && (this.counsellingFeedback.status_feedback_id === 1 || this.counsellingFeedback.status_feedback_id === 2),
        };
    },

    computed: {
        courseFinished() {
            const end = new Date(this.courseEndDate).setHours(0, 0, 0, 0);
            const today = new Date().setHours(0, 0, 0, 0);
            return today > end;
        },
    },

    mounted() {
        axios.get(`/counselling/${this.id}/data/feedback/${this.feedbackRequestId}`)
            .then(res => {
                this.counselling = res.data.counselling;
                this.courseID = res.data.counselling.course;

                axios.get(`/counselling/${this.id}/pseudonym`)
                    .then((res) => {
                        this.pseudonym = getShortPseudonym(res.data.pseudo_first_name, res.data.pseudo_last_name);
                    })
                    .catch((err) => {
                        showErrorAlert(err);
                    });
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
                height = height - navBar.clientHeight;
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

    watch:{
        copyContent: {
            handler(newCopyContent) {
                this.copyContent = newCopyContent;
            }
        }
    }
}
</script>
<style scoped lang="scss">
@import '../../../../../../css/general';

.card-right {
    .card-header {
        background-color: $grey-dark;
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
