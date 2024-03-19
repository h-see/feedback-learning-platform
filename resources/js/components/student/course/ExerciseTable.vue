<template>
    <div>
        <table class="table table-responsive" :class="{'smallView': smallView}">
            <thead>
                <tr class="d-sm-table-row">
                    <th class="d-lg-none" v-if="!smallView"></th>
                    <th v-if="!isTeacher">
                        <span class="d-none d-sm-inline">Nachbereitung</span>
                    </th>
                    <th class="d-none d-sm-table-cell" v-if="!isTeacher">Erstellt</th>
                    <th v-if="isTeacher">Angefragt am</th>
                    <th class="d-none d-md-table-cell" :class="{ 'hidden': smallView && !isTeacher }">Beratungsstelle</th>
                    <th class="d-none d-md-table-cell" :class="{ 'hidden': smallView && !isTeacher }">Persona</th>
                    <th v-if="!isTeacher">Titel</th>
                    <th class="d-lg-table-cell">Feedback</th>
                    <th class="d-none d-lg-table-cell" v-if="!isTeacher">PeerReview</th>
                    <th v-show="!smallView"></th>
                    <th style="width: 1%;"></th>
                </tr>
            </thead>
            <tbody v-for="(exercise, index) in exercises" :key="index">
                <exercise :data="exercise" :user-id="userId" :smallView="smallView" :isTeacher="isTeacher" :courseFinished="courseFinished" :mandatory="false" @exerciseDeleted="exerciseDeleted"></exercise>
            </tbody>
            <tbody v-if="exercises.length === 0">
                <tr>
                    <td colspan="10" class="empty-msg">
                        <i>Keine Inhalte gefunden.</i>
                    </td>
                </tr>
            </tbody>
        </table>
        <div class="open-card" :class="!isTeacher ? 'open-exercises' : ''"><a class="link" :href="`/course/${courseId}/exercises`" v-if="smallView && exercises.length > 0">{{!isTeacher? 'Zu den Übungen' : 'Zu den Anfragen'}}</a></div>
        <div v-if="!courseFinished && !isTeacher" class="btn-new-exercise" :class="{'open-card position-relative': !smallView || exercises.length === 0,'mt-5': !smallView , 'mt-3': smallView && exercises.length === 0}">
            <a class="btn btn-primary btn-160" :href="`/counselling/create/${setupId}`">Neue Übung</a>
        </div>
    </div>
</template>
<script>
import {showErrorAlert} from '../../../helpers/Alerts';
import {is} from "immutable";

export default {
    props: {
        smallView: {
            type: Boolean,
            default: false
        },
        setupId: {
            type: Number
        },
        course:{
            type:Object
        },
        courseMembers:{
            type: Object
        },
        courseEndDate: {
            type: String,
        },
        isTeacher:{
            type: Boolean,
            default: false
        },
        userId:{

        }
    },
    data() {
        return {
            exercises: [],
            courseId: null,
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
        this.loadCounsellings();
    },
    methods: {
        loadCounsellings(){
            if(!this.isTeacher) {
                axios.get(`/counselling/setup/${this.setupId}`)
                    .then(res => {
                        if (res.data.length > 0) this.courseId = res.data[0].course;
                        if (this.smallView) {
                            this.exercises = res.data.slice(0, 2);
                        } else {
                            this.exercises = res.data;
                        }
                    })
                    .catch(err => {
                        showErrorAlert(err);
                    })
            }
            else {
                axios.get(`/counselling/setup/course/optional/${this.course.id}`)
                    .then(res => {
                        if (this.smallView) {
                            this.exercises = Object.values(res.data).slice(0, 2);

                            this.courseId = this.course.id;
                        } else {
                            this.exercises = Object.values(res.data);
                        }
                        // sort feedback requests by status ID
                        this.exercises = this.exercises.sort((a, b) => {
                            const statusA = a.counselling_feedbacks[0] ? a.counselling_feedbacks[0].status_feedback_id : null;
                            const statusB = b.counselling_feedbacks[0] ? b.counselling_feedbacks[0].status_feedback_id : null;

                            if (statusA === null && statusB === null) {
                                return 0;
                            } else if (statusA === null) {
                                return 1;
                            } else if (statusB === null) {
                                return -1;
                            }
                            // sort by status ascending
                            return statusA - statusB;
                        });
                    })
                    .catch(err => {
                        showErrorAlert(err);
                    })
            }
        },

        exerciseDeleted(id) {
            this.exercises = this.exercises.filter(exercise => exercise.id !== id);
        },
    },
}
</script>
<style lang="scss" scoped>
@import '../../../../css/general.scss';

    .btn-new-exercise {
        position: absolute;
        bottom: 0.5rem;
        right: 1rem;
    }

    @include media-breakpoint-down(sm) {
        .open-exercises {
            text-align: left !important;
            margin-left: 1rem;
        }
    }

    tbody {
        background-color: transparent !important;
        &:last-of-type {
            border-bottom: 1px solid $border-color !important;
        }
    }
</style>
