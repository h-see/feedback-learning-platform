<template>
    <tr>
        <td class="d-lg-none pt-3 pb-3" style="width: 1%;" v-if="!smallView">
            <a class="btn-toggle" data-bs-toggle="collapse" :href="'#taskCollapse' + data.id" role="button"
                aria-expanded="false" :aria-controls="'taskCollapse' + data.id">
                <fa-icon :icon="['fas', 'angle-right']" class="icon-closed"></fa-icon>
                <fa-icon :icon="['fas', 'angle-down']" class="icon-opened d-none"></fa-icon>
            </a>
        </td>
        <td v-if="!isTeacher">
            <div class="d-flex align-items-center"
                title="Bezieht sich auf die 3 Nachbereitungsschritte: Notizen, KI Feedback, Bewertung des Virtuellen Klienten">
                <span class="text-danger d-flex align-items-center gap-2" v-if="status > 2">
                    <fa-icon :icon="['fas', 'circle-xmark']" style="width: 16px; height: 16px;"></fa-icon>
                    <span>{{ status + '/3' }}</span>
                </span>
                <span class="text-warning d-flex align-items-center gap-2" v-if="status === 2 || status === 1">
                    <fa-icon :icon="['fas', 'circle-xmark']" style="width: 16px; height: 16px;"></fa-icon>
                    <span>{{ status + '/3' }}</span>
                </span>
                <span class="text-success" v-if="status === 0">
                    <fa-icon :icon="['fas', 'circle-check']" style="width: 16px; height: 16px"></fa-icon>
                </span>
                <div>
                    <span class="d-none d-sm-block" style="margin-left: 5px;">
                        {{ status > 0 ? 'offen' : ' beendet' }}
                    </span>
                </div>

            </div>
        </td>
        <td class="d-none d-sm-table-cell" v-if="!isTeacher">{{ started_at }}</td>
        <td v-if="isTeacher">{{ feedback_request_date }}</td>
        <td class="d-none d-md-table-cell" :class="{ 'hidden': smallView && !isTeacher }">{{ data.counselling_field }}
        </td>
        <td class="d-none d-md-table-cell" :class="{ 'hidden': smallView && !isTeacher }">{{ data.persona }}</td>
        <td v-if="!isTeacher">{{ data.title }}</td>
        <feedback-status :data="data" :courseFinished="courseFinished" :smallView="smallView" :collapsed="false"
            v-if="!isTeacher"></feedback-status>
        <td v-if="isTeacher">
            <feedback-status-teacher v-if="isTeacher" :data="data" :courseFinished="courseFinished"
                :smallView="smallView" :collapsed="false"></feedback-status-teacher>
        </td>
        <td v-show="!smallView && !isTeacher" class="cell-btn">
            <button v-if="!courseFinished" class="btn btn-secondary" @click="deleteCounselling">
                <span class="d-none d-xl-inline">Beratung löschen</span>
                <fa-icon class="d-xl-none" :icon="['fas', 'trash']"></fa-icon>
            </button>
        </td>
        <td class="cell-btn" v-show="!isTeacher">
            <a class="btn btn-primary btn-160" :href="`/counselling/${data.id}`">
                <span class="d-none d-xl-inline">Öffnen</span>
                <fa-icon class="d-xl-none" :icon="['fas', 'arrow-right']"></fa-icon>
            </a>
        </td>
        <td class="cell-btn" v-show="isTeacher">
            <a class="btn btn-primary btn-160" :href="`/counselling/${data.id}`">
                <span class="d-none d-xl-inline">{{ feedback_in_progress && teacher_allowed_to_edit ? 'Fortsetzen' :
            'Öffnen' }}</span>
                <fa-icon class="d-xl-none"
                    :icon="feedback_in_progress && teacher_allowed_to_edit ? ['fas', 'arrow-rotate-right'] : ['fas', 'arrow-right']"></fa-icon>
            </a>
        </td>
    </tr>
    <!-- collapsable details-->
    <tr class="collapse d-lg-none" :id="'taskCollapse' + data.id" v-if="!smallView">
        <td></td>
        <td colspan="10" class="pb-4">
            <span>
                <div class="d-sm-none mb-2"><b>Erstellt: </b>{{ started_at }}</div>
                <div class="d-md-none mb-2"><b>Beratungsstelle: </b>{{ data.counselling_field }}</div>
                <div class="d-md-none mb-2"><b>Persona: </b>{{ data.persona }}</div>
                <feedback-status :data="data" :courseFinished="courseFinished" :smallView="smallView" :collapsed="true"
                    v-if="!isTeacher"></feedback-status>
                <feedback-status-teacher v-if="isTeacher" :data="data" :courseFinished="courseFinished"
                    :smallView="smallView" :collapsed="true"></feedback-status-teacher>
            </span>
        </td>
    </tr>
</template>
<script>
import { showSuccessAlert, showErrorAlert } from '../../../helpers/Alerts';

export default {
    emits: ['exerciseDeleted'],
    props: {
        data: {
            type: Object,
        },
        smallView: {
            type: Boolean,
            default: false
        },
        courseFinished: {
            type: Boolean,
        },
        isTeacher: {
            type: Boolean,
            default: false
        },
        mandatory: {
            type: Boolean,
            default: false
        },
        userId: {

        }
    },

    data() {
        return {
            feedbackRequestDate: '',
            teacherAllowedToEdit: false,
        };
    },

    computed: {
        started_at() {
            const date = new Date(this.data.start_date);
            return date.toLocaleDateString('de');
        },
        status() {
            const options = [this.data.postprocessing_process?.note, this.data.postprocessing_process?.ki_feedback, this.data.postprocessing_process?.vikl_rating];
            const i = options.filter(option => option != null && (option === 1 || option.done)).length;
            return 3 - i;
        },

        feedback_request_date() {
            const date = new Date(this.data.counselling_feedbacks[0].created_at);
            return date.toLocaleDateString('de');
        },

        feedback_in_progress() {
            if (this.data.counselling_feedbacks[0]?.status_feedback_id === 2) {
                return true;
            }
            return false;
        },

        teacher_allowed_to_edit() {
            if (this.data.counselling_feedbacks[0]?.status_feedback_id === 2 && this.data.counselling_feedbacks[0]?.feedback_source_id === 1) {
                if (this.data.counselling_feedbacks[0]?.user_id === this.userId) {
                    return true;
                }
            }
            return false;
        }
    },
    methods: {
        deleteCounselling() {
            axios.delete('/counselling/' + this.data.id)
                .then(res => {
                    this.$emit('exerciseDeleted', this.data.id);
                    showSuccessAlert(res.data.message);
                })
                .catch(err => {
                    showErrorAlert(err);
                })
        },
    },
}
</script>
<style lang="scss" scoped>
@import '../../../../css/general.scss';

.circle {
    width: 15px;
    height: 15px;
    background-color: $red;
    display: inline-block;
    margin-right: 8px;

    &.done {
        background-color: $green;
    }
}

@include media-breakpoint-down(xl) {
    .btn-160 {
        width: auto;
    }
}
</style>
