<template>
    <div>
        <table class="table table-responsive" :class="{'smallView': smallView}">
            <thead>
                <tr>
                    <th class="d-lg-none" v-if="!smallView"></th>
                    <th style="width: 10%;" v-if="!feedbacksOnly">
                        <span class="d-none d-sm-inline">Status</span>
                    </th>
                    <th v-if="feedbacksOnly">Angefragt am</th>
                    <th v-if="!feedbacksOnly">Fällig am</th>
                    <th v-if="isTeacher">Student*in</th>
                    <th class="d-none d-md-table-cell" :class="{ 'hidden': smallView && !feedbacksOnly }">Beratungsstelle</th>
                    <th>Persona</th>
                    <th class="d-none d-sm-table-cell" v-if="!isTeacher">Titel</th>
                    <th class="d-none d-lg-table-cell">Feedback</th>
                    <th class="d-none d-lg-table-cell" v-if="!isTeacher">PeerReview</th>
                    <th v-show="!smallView"></th>
                    <th style="width: 1%;"></th>
                </tr>
            </thead>
            <tbody v-for="(task, index) in tasks" :key="index">
                <task :data="task" :user-id="userId" :smallView="smallView" :isTeacher="isTeacher" :feedbacks-only="feedbacksOnly" :courseFinished="courseFinished" :mandatory="true" @taskReseted="taskReseted"></task>
            </tbody>
            <tbody v-if="tasks.length === 0">
                <tr>
                    <td colspan="10" class="empty-msg">
                        <i>Keine Inhalte gefunden.</i>
                    </td>
                </tr>
            </tbody>
        </table>
        <div class="open-card"><a class="link" :href="!feedbacksOnly ? `/course/${courseId}/tasks` : `/course/${courseId}/tasks-with-feedback`" v-if="smallView && tasks.length > 0">{{!feedbacksOnly ? 'Zu den Aufgaben' : 'Zu den Anfragen'}}</a></div>
    </div>
</template>
<script>
import { showErrorAlert} from '../../../helpers/Alerts';

export default {
    props: {
        smallView: {
            type: Boolean,
            default: false
        },
        setups: {
            type: Object
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

        feedbacksOnly:{
            type: Boolean,
            default: false
        },
        userId:{

        }
    },
    data() {
        return {
            tasks: [],
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
        if(!this.isTeacher){
            if (this.smallView) {
                this.tasks = Object.values(this.setups).slice(0, 2);
            } else {
                this.tasks = Object.values(this.setups);
            }
            this.courseId = this.tasks[0].course_id;
        }

        this.loadCounsellings();
        if (this.feedbacksOnly) {
            this.filterTasksWithFeedbackRequests();
        }
    },

    methods: {
        taskReseted() {
            this.loadCounsellings();
        },

        filterTasksWithFeedbackRequests(){
            this.tasks = this.tasks.reduce((filteredTasks, task) => {
                if (task.counselling && task.counselling.length > 0) {
                    const filteredCounselling = task.counselling.flatMap(counselling =>
                        counselling.counselling_feedbacks && counselling.counselling_feedbacks.length > 0
                            ? counselling.counselling_feedbacks.filter(feedback =>
                                feedback.feedback_source_id === 1 &&
                                (feedback.status_feedback_id === 1 || feedback.status_feedback_id === 2)
                            ).map(filteredFeedback => ({ ...counselling, counselling_feedback: filteredFeedback }))
                            : []
                    );

                    if (filteredCounselling.length > 0) {
                        filteredTasks.push({ ...task, counselling: filteredCounselling });
                    }
                }
                return filteredTasks;
            }, []);
        },

        loadCounsellings() {
            if(!this.isTeacher){
                this.tasks.forEach((task) => {
                    axios.get(`/counselling/setup/${task.id}`)
                        .then(res => {
                            task.counselling = res.data;

                            // needed for feedbackStatus
                            if(task.counselling.length !== 0){
                                task.feedbackCounts = task.counselling[0].feedbackCounts;
                            }
                            else{
                                task.feedbackCounts = [];
                            }
                        })
                        .catch(err => {
                            showErrorAlert(err);
                        })
                })
            } else {
                axios.get(`/counselling/setup/course/mandatory/${this.course.id}`)
                    .then(res => {
                        if(res.data.length > 0){
                            if (this.smallView) {
                                this.tasks = Object.values(res.data).slice(0, 4);
                                this.courseId = this.tasks[0].course_id;
                            } else {
                                this.tasks = Object.values(res.data);
                            }

                            if (this.feedbacksOnly) {
                                this.filterTasksWithFeedbackRequests();
                            }
                        }
                    })
                    .catch(err => {
                        showErrorAlert(err);
                    })
            }
        }
    },
}
</script>
<style lang="scss" scoped>
@import '../../../../css/general.scss';

tbody {
    background-color: transparent !important;
    &:last-of-type {
        border-bottom: 1px solid $border-color !important;
    }
}
</style>
