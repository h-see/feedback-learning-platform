<template>
    <div class="row item m-0 py-4 px-2" :class="{'colored-bg': this.data.author === 'vikl'}">
        <div class="col">
            <div class="row">
                <div class="row align-items-baseline">
                    <div v-if="data.author === 'vikl'" class="col-auto pe-0 robot align-self-center">
                        <fa-icon :icon="['fas', 'robot']"></fa-icon>
                    </div>
                    <div class="col-auto pe-0">
                        <span class="fw-bold fs-5">{{ author }}</span>
                    </div>
                    <div class="col-auto pe-0">
                        <span class="text-grey-dark">{{ dateTime }}</span>
                    </div>
                </div>
                <div class="row mt-1">
                    <div class="col-12">{{ data.content }}</div>
                </div>

            </div>
        </div>

        <div class="col-auto d-flex align-items-center dropdown" v-if="showCopyButton">
            <button v-if="!(courseFinished) && data.author !== 'vikl'"
                    class="btn-note" @click="copyContent" :disabled="courseFinished">
                <span data-bs-toggle="tooltip"
                      title="Nachricht zum Feedbacktext hinzufügen">
                    <fa-icon :icon="['far', 'share-from-square']"
                             class="rating empty" :class="{'disabled': courseFinished }"></fa-icon>
                </span>
            </button>
        </div>

        <div class="col-auto d-flex align-items-center dropdown" v-if="userIsCounsellingCreator && !isTeacher">
            <button v-if="!(courseFinished && ratingValue === undefined) && data.author === 'vikl'"
                    class="btn-note" @click="saveThumbsUp" :class="{ 'active': ratingValue === '+' }" :disabled="courseFinished">
                <span data-bs-toggle="tooltip"
                      title="Die Nachricht hat mir gut gefallen/Hat gut gepasst.">
                    <fa-icon v-show="ratingValue === undefined || ratingValue === ''  || ratingValue === '-'" :icon="['far', 'thumbs-up']"
                             class="rating up empty" :class="{'disabled': courseFinished }"></fa-icon>
                    <fa-icon v-show="ratingValue !== undefined && ratingValue === '+'" :icon="['fas', 'thumbs-up']"
                             class="rating up" :class="{'disabled': courseFinished }"></fa-icon>
                </span>
            </button>
            <button v-if="!(courseFinished && ratingValue === undefined) && data.author === 'vikl'"
                    class="btn-note" @click="saveThumbsDown" :class="{ 'active': ratingValue === '-' }" :disabled="courseFinished">
                <span data-bs-toggle="tooltip"
                      title="Die Nachricht hat mir nicht gut gefallen/Hat nicht gut gepasst.">
                    <fa-icon v-show="ratingValue === undefined || ratingValue === '' || ratingValue === '+'" :icon="['far', 'thumbs-down']"
                             class="rating down empty" :class="{'disabled': courseFinished }"></fa-icon>
                    <fa-icon v-show="ratingValue !== undefined && ratingValue === '-'" :icon="['fas', 'thumbs-down']"
                             class="rating down" :class="{'disabled': courseFinished }"></fa-icon>
                </span>
            </button>
            <button v-if="!(courseFinished && explanation === undefined) && data.author === 'vikl'"
                    class="btn-note dropdown-toggle"
                    data-bs-toggle="dropdown" aria-expanded="false" data-bs-auto-close="outside"
                    ref="dropdownBtnExplanation">
                <span data-bs-toggle="tooltip" title="Optionale Begründung für Bewertung der Nachricht." >
                    <fa-icon v-show="explanation === undefined || explanation === ''" :icon="['far', 'note-sticky']"
                             class="note empty"></fa-icon>
                    <fa-icon v-show="explanation !== undefined && explanation !== ''" :icon="['fas', 'note-sticky']"
                             class="note"></fa-icon>
                </span>
            </button>
            <div class="dropdown-menu container-content note-container" ref="dropdownMenuExplanation">
                <div class="row">
                    <div class="col"><b>Begründung</b></div>
                    <div class="col-auto btn-close-note" @click="closeExplanation">
                        <fa-icon :icon="['fas', 'close']"></fa-icon>
                    </div>
                </div>
                <textarea placeholder="Text..." rows="3" class="form-control mb-1" v-model="explanationNew"
                          :readonly="courseFinished"></textarea>
                <div class="row justify-content-end">
                    <div class="col-auto">
                        <button v-if="!courseFinished" class="btn btn-primary btn-sm px-3" @click="saveExplanation"
                                :disabled="explanationNew?.trim() === explanation?.trim()">
                            <fa-icon :icon="['fas', 'save']"></fa-icon>
                        </button>
                    </div>
                </div>
            </div>
            <div v-if="data.author === 'vikl'" class="d-flex justify-content-center">
                <div class="vr"></div>
            </div>
            <button v-if="!(courseFinished && note === undefined)" class="btn-note dropdown-toggle"
                    data-bs-toggle="dropdown" aria-expanded="false" data-bs-auto-close="outside"
                    ref="dropdownBtnNote">
                <span data-bs-toggle="tooltip"
                      title="Persönliche Notizen sind privat und können von keiner anderen Person gelesen werden.">
                    <fa-icon v-show="note === undefined || note === ''" :icon="['far', 'message']"
                             class="note empty"></fa-icon>
                    <fa-icon v-show="note !== undefined && note !== ''" :icon="['fas', 'message']"
                             class="note"></fa-icon>
                </span>
            </button>
            <div class="dropdown-menu container-content note-container" ref="dropdownMenuNote">
                <div class="row">
                    <div class="col"><b>Persönliche Notiz</b></div>
                    <div class="col-auto btn-close-note" @click="closeNote">
                        <fa-icon :icon="['fas', 'close']"></fa-icon>
                    </div>
                </div>
                <textarea placeholder="Text..." rows="3" class="form-control mb-1" v-model="noteNew"
                          :readonly="courseFinished"></textarea>
                <div class="row justify-content-end">
                    <div class="col-auto">
                        <button v-if="!courseFinished" class="btn btn-primary btn-sm px-3" @click="saveNote"
                                :disabled="noteNew?.trim() === note?.trim()">
                            <fa-icon :icon="['fas', 'save']"></fa-icon>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import {showSuccessAlert, showErrorAlert} from '../../../helpers/Alerts';

export default {
    props: ['data', 'persona', 'pseudonym', 'courseFinished', 'userIsCounsellingCreator', 'isTeacher', 'showCopyButton'],
    data() {
        return {
            note: this.data?.additions?.note ? this.data?.additions?.note : '',
            noteNew: this.note ? this.note : '',
            explanation: this.data?.additions?.rating?.explanation ? this.data?.additions?.rating?.explanation : '',
            explanationNew: this.explanation ? this.explanation : '',
            ratingValue: this.data?.additions?.rating?.value ? this.data?.additions?.rating?.value : '',
        };
    },
    computed: {
        author() {
            return this.data.author === 'vikl' ? this.persona : this.pseudonym;
        },
        dateTime() {
            const date = new Date(this.data.created_at);
            return date instanceof Date && !isNaN(date) ? date.toLocaleString('de-DE') : '';
        }
    },
    mounted() {
        this.$refs.dropdownBtnNote?.addEventListener('show.bs.dropdown', event => {
            this.noteNew = this.note;
        });
        this.$refs.dropdownBtnExplanation?.addEventListener('show.bs.dropdown', event => {
            this.explanationNew = this.explanation;
        });
    },

    beforeUnmount() {
        this.$refs.dropdownBtnNote?.removeEventListener('show.bs.dropdown', event => {
            this.noteNew = this.note;
        });
        this.$refs.dropdownBtnExplanation?.removeEventListener('show.bs.dropdown', event => {
            this.explanationNew = this.explanation;
        });
    },
    methods: {
        saveNote() {
            this.saveAddition('note', this.noteNew);
        },

        saveExplanation() {
            this.saveAddition('explanation', this.explanationNew);
        },

        saveThumbsUp() {
            this.toggleRating('+');
        },

        saveThumbsDown() {
            this.toggleRating('-');
        },

        toggleRating(rating) {
            // check if current rating is the same
            const isCurrentRating = this.ratingValue === rating;
            this.ratingValue = isCurrentRating ? '' : rating;
            this.saveAddition('ratingValue', this.ratingValue);
        },

        saveAddition(type, text) {
            axios.put(`/counselling/addition/${this.data.counselling_id}/${this.data.message_number}`, {
                type: type,
                text: text,
            })
                .then(res => {
                    if (type === 'note') {
                        this.note = res.data.counsellingMessage.additions.note || '';
                        this.closeNote();
                        showSuccessAlert('Anmerkung gespeichert');
                    } else if (type === 'explanation') {
                        this.explanation = res.data.counsellingMessage.additions.rating.explanation || '';
                        this.closeExplanation();
                        showSuccessAlert('Begründung gespeichert');
                    }
                    else if (type === 'ratingValue') {
                        this.ratingValue = res.data.counsellingMessage.additions.rating.value || '';
                        showSuccessAlert(`Bewertung gespeichert`);
                    }
                })
                .catch(err => {
                    showErrorAlert(err);
                })
        },

        closeNote() {
            this.$refs.dropdownBtnNote.ariaExpanded = false;
            this.$refs.dropdownBtnNote.classList.remove('show');
            this.$refs.dropdownMenuNote.classList.remove('show');
        },

        closeExplanation() {
            this.$refs.dropdownBtnExplanation.ariaExpanded = false;
            this.$refs.dropdownBtnExplanation.classList.remove('show');
            this.$refs.dropdownMenuExplanation.classList.remove('show');
        },

        copyContent(){
            this.$emit('content-copied', this.data.content);
        },
    },
}

</script>
<style lang="scss" scoped>
@import '../../../../css/general.scss';

.colored-bg {
    background-color: $background-light;
}

.note {
    width: 1.5em;
    height: 1.5em;
    color: $primary;

    &:hover {
        color: $primary-dark;
        cursor: pointer;
    }

    &.empty {
        color: $grey-dark;

        &:hover {
            color: $primary;
        }
    }
}

.rating {
    width: 1.5em;
    height: 1.5em;

    &.down {
        color: $red;
    }

    &.up {
        color: $green;
    }

    &.empty {
        color: $grey-dark;
    }

    &:not(.disabled) {
        &.down:hover {
            color: $red-dark;
            cursor: pointer;
        }
        &.up:hover {
            color: $green-dark;
            cursor: pointer;
        }
    }
}

.robot svg {
    width: 20px;
    height: 20px;
}

.btn-note {
    border: none;
    background-color: transparent;

    &::after {
        display: none;
    }

    &.show svg {
        color: $primary;
    }
}

.note-container {
    min-width: 200px;
    border: none;
    border-radius: 0;

    textarea {
        resize: none;
        width: 100%;
        padding: 3px;

        &:focus {
            box-shadow: none !important;
            outline: none;
        }
    }

    svg {
        width: 16px;
        height: 16px;
    }
}

.btn-close-note {
    color: $grey-dark;

    &:hover {
        cursor: pointer;
        color: $primary-dark;
    }
}
</style>
