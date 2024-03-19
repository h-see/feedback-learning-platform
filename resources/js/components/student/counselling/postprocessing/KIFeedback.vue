<template>
    <div class="h-100 pb-2">
        <div class="d-flex flex-column h-100 mb-4">
            <div class="note-header" v-if="!isTeacher">
                <div class="row" v-if="kiFeedbackAvailable">
                    <div class="col-auto d-flex align-items-center">
                        <input type="checkbox" class="form-check-input green" @change="noteCheckboxChanged" :checked="counselling.postprocessing_process?.ki_feedback === 1"
                               :disabled="courseFinished || !kiFeedbackAvailable"
                        />
                    </div>
                    <div class="col p-0">
                        <b>KI Feedback</b>
                    </div>
                </div>
                <div class="row justify-content-center mt-5" v-if="!kiFeedbackAvailable">
                    <p class="text-center text-danger" v-if="userMessageCount < 10">Beratung zu kurz um Feedback zu generieren. <br> Mindestens 10 Nachrichten vom User erforderlich.</p>
                    <div class="col-auto" v-if="!kiFeedbackGenerating && !this.kiFeedbackAvailable" ref="request-ki-feedback" :title="userMessageCount < 10 ? 'Zur Feedbackgenerierung sind mindestens 10 Nachrichten vom User erforderlich.' : ''">
                        <button class="btn btn-primary" :disabled="userMessageCount < 10" @click="generateFeedback">KI Feedback generieren</button>
                    </div>
                    <div class="col-auto" ref="spinner" v-if="kiFeedbackGenerating">
                        <strong>Feedback wird generiert... </strong>
                        <div class="spinner-border ms-auto"></div>
                    </div>
                </div>
            </div>
            <div v-else-if="isTeacher && !kiFeedbackAvailable" class="text-center">Studierender hat kein KI Feedback angefordert.</div>
            <div class="d-block toggle-bar mt-4 mx-3" ref="toggleBar" v-if="kiFeedbackAvailable">
                <div class="row justify-content-center shadow-sm">
                    <div class="col text-center p-3 " :class="{'active': active_tab === 'mentor', 'selected': active_tab === 'mentor'}" @click="active_tab='mentor'">
                        <span>KI Mentor*in</span>
                    </div>
                    <div class="col text-center p-3" :class="{'active': active_tab === 'client', 'selected': active_tab === 'client'}"  @click="active_tab='client'">
                        <span>{{ persona }}</span>
                        <span class="ms-2 pointer" data-bs-toggle="modal" data-bs-target="#questionnaireInfos"><fa-icon :icon="['fas', 'circle-info']"></fa-icon></span>
                    </div>
                </div>
            </div>

            <div class="h-100" v-show="kiFeedbackAvailable">
                <div class="d-flex flex-column h-100">
                    <div class="mt-4" v-if="!isTeacher">
                        <span :title="kiMentorFeedbackCount === 3 ? 'Feedback wurde bereits drei mal generiert' : ''">
                            <button class="btn btn-secondary" v-if="active_tab==='mentor'" data-bs-toggle="modal" data-bs-target="#confirmRegenerate" @click="clickedButton = 'mentor'" :disabled="kiMentorFeedbackCount === 3">Mentor*innen Feedback erneut generieren</button>
                        </span>
                        <span :title="kiClientFeedbackCount === 3 ? 'Feedback wurde bereits drei mal generiert' : ''">
                             <button class="btn btn-secondary" v-if="active_tab==='client'" data-bs-toggle="modal" data-bs-target="#confirmRegenerate" @click="clickedButton = 'client'" :disabled="kiClientFeedbackCount === 3">Klient*innen Feedback erneut generieren</button>
                        </span>
                    </div>
                    <div class="row justify-content-center mt-5" v-if="activeTabSpinner === 'mentor' && kiMentorFeedbackRegenerating">
                        <div class="col-auto" ref="spinner" >
                            <strong>Feedback wird neu generiert... </strong>
                            <div class="spinner-border ms-auto"></div>
                        </div>
                    </div>
                    <textarea class="form-control mt-4" style="flex: 1;" readonly v-show="active_tab === 'mentor' && !kiMentorFeedbackRegenerating" v-model="latestKiMentorFeedbackText"></textarea>

                    <div class="row justify-content-center mt-5" v-if="activeTabSpinner === 'client' && kiClientFeedbackRegenerating">
                        <div class="col-auto" ref="spinner" >
                            <strong>Feedback wird neu generiert... </strong>
                            <div class="spinner-border ms-auto"></div>
                        </div>
                    </div>
                    <table class="table table-bordered mt-4 small" v-show="active_tab === 'client' && !kiClientFeedbackRegenerating" ref="questionnaire">
                        <thead>
                        <tr>
                            <th scope="col" style="width: 30px;"></th>
                            <th scope="col" style="width: 30px;">1</th>
                            <th scope="col" style="width: 30px;">2</th>
                            <th scope="col" style="width: 30px;">3</th>
                            <th scope="col" style="width: 30px;">4</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <th scope="row"></th>
                            <td ref="sehr" class="p-0 text-center small">sehr</td>
                            <td ></td>
                            <td></td>
                            <td ref="wenig" class="p-0 text-center small">wenig</td>
                        </tr>
                        <tr id="zufrieden">
                            <th scope="row" class="fw-normal" title="Mit der Beratung bin ich grundsätzlich zufrieden.">zufrieden</th>
                            <td class="class-1" ref="zufrieden-1"></td>
                            <td class="class-2" ref="zufrieden-2"></td>
                            <td class="class-3" ref="zufrieden-3"></td>
                            <td class="class-4" ref="zufrieden-4"></td>
                        </tr>
                        <tr id="verstanden">
                            <th scope="row"  class="fw-normal" title="Der Berater / die Beraterin hat mein Anliegen verstanden.">Anliegen verstanden</th>
                            <td class="class-1" ref="verstanden-1"></td>
                            <td class="class-2" ref="verstanden-2"></td>
                            <td class="class-3" ref="verstanden-3"></td>
                            <td class="class-4" ref="verstanden-4"></td>
                        </tr>
                        <tr id="ernst_genommen">
                            <th scope="row" class="fw-normal"  title="Der Berater / die Beraterin nahm meine Frage ernst.">ernst genommen</th>
                            <td class="class-1" ref="ernst_genommen-1"></td>
                            <td class="class-2" ref="ernst_genommen-2"></td>
                            <td class-="class-3" ref="ernst_genommen-3"></td>
                            <td class="class-4" ref="ernst_genommen-4"></td>
                        </tr>
                        <tr id="hilfreich">
                            <th scope="row" class="fw-normal"  title="Die Beratung war hilfreich zur Klärung meines Anliegens.">hilfreich</th>
                            <td class="class-1" ref="hilfreich-1"></td>
                            <td class="class-2" ref="hilfreich-2"></td>
                            <td class="class-3" ref="hilfreich-3"></td>
                            <td class="class-4" ref="hilfreich-4"></td>
                        </tr>
                        <tr id="neue_perspektive">
                            <th scope="row"  class="fw-normal"  title="Die Antwort hat mir eine neue Perspektive ermöglicht.">neue Perspektiven</th>
                            <td class="class-1" ref="neue_perspektive-1"></td>
                            <td class="class-2" ref="neue_perspektive-2"></td>
                            <td class="class-3" ref="neue_perspektive-3"></td>
                            <td class="class-4" ref="neue_perspektive-4"></td>
                        </tr>
                        <tr id="umsetzung_in_praxis">
                            <th scope="row"  class="fw-normal"  title="Erkenntnisse konnte ich in meine Praxis umsetzen.">Umsetzung in Praxis</th>
                            <td class="class-1" ref="umsetzung_in_praxis-1"></td>
                            <td class="class-2" ref="umsetzung_in_praxis-2"></td>
                            <td class="class-3" ref="umsetzung_in_praxis-3"></td>
                            <td class="class-4" ref="umsetzung_in_praxis-4"></td>
                        </tr>
                        <tr id="richtige_worte" >
                            <th scope="row" class="fw-normal"  title="Der Berater / die Beraterin hat die richtigen Worte gewählt, den richtigen Tonfall
                                getroffen."> Richtige Worte/Tonfall
                            </th>
                            <td class="class-1" ref="richtige_worte-1"></td>
                            <td class="class-2" ref="richtige_worte-2"></td>
                            <td class="class-3" ref="richtige_worte-3"></td>
                            <td class="class-4" ref="richtige_worte-4"></td>
                        </tr>
                        <tr>
                            <th scope="row"></th>
                            <td class="p-0 text-center small">ja</td>
                            <td></td>
                            <td></td>
                            <td class="p-0 text-center small">nein</td>
                        </tr>
                        <tr id="wieder_melden">
                            <th scope="row" class="fw-normal"  title="Bei diesem Berater / dieser Beraterin würde ich mich wieder melden.">Wieder melden</th>
                            <td class="class-1" ref="wieder_melden-1"></td>
                            <td class="class-2" ref="wieder_melden-2"></td>
                            <td class="class-3" ref="wieder_melden-3"></td>
                            <td class="class-4" ref="wieder_melden-4"></td>
                        </tr>
                        <tr id="freies_textfeedback">
                            <th scope="row" class="fw-normal" >Weitere Rückmeldungen:</th>
                            <td colspan="4" class="feedback-text" ref="freies_textfeedback"></td>
                        </tr>
                        <tr id="erklaerung_bewertung">
                            <th scope="row" class="fw-normal" >Erklärung Bewertung:</th>
                            <td colspan="4" class="feedback-text" ref="erklaerung_bewertung"></td>
                        </tr>
                        </tbody>
                    </table>
                </div>


                <div class="modal modal-lg fade" id="questionnaireInfos" tabindex="-1"
                     aria-labelledby="questionnaireInfos" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="exampleModalLabel">Informationen zum Fragebogen</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div>
                                    <ul>
                                        <li>Das KI-Modell wurde aufgefordert mögliches Feedback aus Sicht von {{ persona }} zu erstellen.</li>
                                        <li>Hierbei sollte es sich an einen Fragebogen aus dem integrativen Qualitätssicherungsmodell (IQSM) nach Eidenbenz und Lang orientieren.</li>
                                        <li>Alle Felder des Fragebogens sind direkt übernommen worden und die KI sollte jeweils eine Wertung zwischen 1: sehr und 4: wenig geben.</li>
                                        <li>Das Feld "Erklärung Bewertung" wurde zusätzlich hinzugefügt. Hier sollte die KI erklären warum es sich jeweils für welche Wertung entschieden hat.</li>
                                        <li>Zur besseren Lesbarkeit wurden die Aussagen gekürzt. Die vollständige Aussage ist beim Hovern über den Begriff zu sehen.</li>
                                    </ul>
                                </div>
                            </div>
                            <br>
                            <div class="row justify-content-end mb-3 me-3">
                                <div class="col-auto">
                                    <button type="button" class="btn btn-primary me-3" data-bs-dismiss="modal">Okay
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="confirmRegenerate" tabindex="-1"
                     aria-labelledby="confirmRegenerate" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="exampleModalLabel">{{ clickedButton === 'mentor' ? 'KI Mentor*innen ' : 'KI Klient*innen ' }}Feedback erneut generieren</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                Du kannst dieses Feedback insgesamt drei Mal generieren. <br>
                                Bereits generiert: {{ clickedButton === 'mentor' ? kiMentorFeedbackCount : kiClientFeedbackCount }} Mal. <br>
                                Bitte beachte, dass beim erneuten Generieren nur das neueste Feedback angezeigt wird. <br>
                                Möchtest du wirklich fortfahren und das Feedback neu generieren?
                            </div>
                            <br>
                            <div class="row justify-content-end m-3">
                                <div class="col-auto">
                                    <button type="button" class="btn btn-secondary me-3" data-bs-dismiss="modal">Abbrechen</button>
                                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal" @click="regenerateFeedback(clickedButton)">Ja</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>

</template>


<script>
import {hideErrorAlert, showErrorAlert, showSuccessAlert} from "@/helpers/Alerts";

export default {
    props: ['counselling', 'counselling_id', 'courseFinished', 'counsellingFeedbacks', 'isTeacher', 'feedbackRequestId', 'persona'],

    data() {
        return {
            active_tab: 'mentor',
            active_card: 'mentor',
            latestKiMentorFeedbackText: '',
            latestKiClientFeedbackText: '',
            userMessageCount: this.counselling?.counselling_messages?.filter(message => message.author === 'user').length,
            kiFeedbackGenerating: false,
            kiFeedbackAvailable: false,
            kiMentorFeedbackCount: 0,
            kiClientFeedbackCount: 0,
            clickedButton: null,
            kiMentorFeedbackRegenerating: false,
            kiClientFeedbackRegenerating: false,
            activeTabSpinner: null,
        };
    },

    watch: {
        counsellingFeedbacks: {
            handler(counsellingFeedbacks) {
                this.kiFeedbackAvailable = counsellingFeedbacks.filter(feedback => feedback.feedback_source_id === 3).length !== 0;
                if(!this.kiFeedbackAvailable){
                    return;
                }
                this.updateKIFeedback(counsellingFeedbacks);
            },
            deep: true
        },

        counselling:{
            handler(counselling){
                this.userMessageCount = counselling.counselling_messages.filter(message => message.author === 'user').length;
            }
        }
    },

    methods: {

        generateFeedback(){
            this.kiFeedbackGenerating = true;
            axios.put(`/counselling/${this.counselling.id}/generate-ai-feedback`)
                .then(res => {
                    this.updateKIFeedback(res.data.counsellingFeedbacks);
                    this.kiFeedbackAvailable = true;
                    this.kiFeedbackGenerating = false;
                    this.fillQuestionnaire();
                })
                .catch(err => {
                    showErrorAlert(err);
                })
        },

        regenerateFeedback(type){
            this.activeTabSpinner = type;
            if(type === 'mentor'){
                this.kiMentorFeedbackRegenerating = true;
            }
            else{
                this.kiClientFeedbackRegenerating = true;
            }
            var feedbackTypeId = type === 'mentor' ? 1 : 5;

            axios.put(`/counselling/${this.counselling.id}/generate-ai-feedback`, {feedbackTypeId: feedbackTypeId})
                .then(res => {
                    this.kiFeedbackAvailable = true;
                    this.activeTabSpinner = null;
                    this.updateKIFeedback(res.data.counsellingFeedbacks);
                    if(type === 'mentor'){
                        this.kiMentorFeedbackRegenerating = false;
                    }
                    else{
                        this.kiClientFeedbackRegenerating = false;
                        this.fillQuestionnaire();
                    }
                })
                .catch(err => {
                    showErrorAlert(err);
                })
        },

        updateKIFeedback(counsellingFeedbacks){
            var kiMentorFeedbacks = counsellingFeedbacks?.filter(feedback => feedback.feedback_types_id === 1);
            var kiClientFeedbacks = counsellingFeedbacks?.filter(feedback => feedback.feedback_types_id === 5);

            kiMentorFeedbacks?.sort((a, b) => new Date(b.updated_at) - new Date(a.updated_at));
            kiClientFeedbacks?.sort((a, b) => new Date(b.updated_at) - new Date(a.updated_at));

            this.kiMentorFeedbackCount= kiMentorFeedbacks?.length;
            this.kiClientFeedbackCount= kiClientFeedbacks?.length;

            this.latestKiMentorFeedbackText = kiMentorFeedbacks[0]?.feedback_text;
            this.latestKiClientFeedbackText = kiClientFeedbacks[0]?.feedback_text;

            if(this.latestKiClientFeedbackText !== undefined){
                this.fillQuestionnaire();
            }
        },

        noteCheckboxChanged($event) {
            let data;
            data = $event.target.checked ? 1 : 0;
            this.saveProcess(data);
        },

        saveProcess(data){
            axios.put('/counselling/' + this.counselling_id, {
                type: 'ki_feedback',
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

        fillQuestionnaire() {
            var data = JSON.parse(this.latestKiClientFeedbackText);

            for (var key in data) {
                var value = data[key];

                var refName = '';
                if(key !== "freies_textfeedback" && key !== 'erklaerung_bewertung'){
                    // Construct the dynamic ref based on key and value
                    refName = key + '-' + value;
                }
                else{
                    refName = key;
                }

                // Use the ref to access the specific cell
                var cell = this.$refs[refName];

                if (cell) {
                    if(key !== "freies_textfeedback" && key !== 'erklaerung_bewertung'){
                        cell.classList.add('questionnaire-selected')
                    }

                    // Set the text content for specific rows
                    if (key === "freies_textfeedback" || key === 'erklaerung_bewertung') {
                        cell.textContent = value;
                    }
                }
            }
        }
    }

}
</script>

<style scoped lang="scss">
@import '../../../../../css/general.scss';
@import '../../../../../css/variables.scss';


.dropdown-toggle.show {
    background-color: $grey-light !important;
    color: white !important;
}



.toggle-bar {
    background-color: $white;
    //border: 1px solid $border-color;

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

textarea {
    resize: none;
}

.questionnaire-selected{
    background-color: $green !important;
    opacity: 0.5;
}

</style>
