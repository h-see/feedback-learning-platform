<template>
    <span v-if="mandatory && data.counselling[0] && data.counselling[0].feedbackCounts  && !collapsed" class="d-none d-lg-table-cell"
          :class="{'text-danger': isTeacherStatusPending(data.counselling[0].feedbackCounts.teacher)}">
        {{ getTeacherFeedbackStatus(data.counselling[0].feedbackCounts.teacher) }}
    </span>
    <span v-if="!mandatory && data.feedbackCounts && !collapsed" :class="{'text-danger': isTeacherStatusPending(data.feedbackCounts.teacher)}">
        {{ getTeacherFeedbackStatus(data.feedbackCounts.teacher) }}
    </span>

    <!-- teacher collapsed -->
    <div class="d-lg-none mb-2" v-if="mandatory && collapsed && data.counselling[0] && data.counselling[0].feedbackCounts  ">
        <b>Feedback: </b>
        <span
            :class="{'text-danger': isTeacherStatusPending(data.counselling[0].feedbackCounts.teacher)}">
        <span>
            {{ getTeacherFeedbackStatus(data.counselling[0].feedbackCounts.teacher) }}
        </span>
        </span>
    </div>

    <div class="d-lg-none mb-2" v-if="!mandatory && collapsed && data.feedbackCounts">
        <b>Feedback: </b>
        <span
            :class="{'text-danger': isTeacherStatusPending(data.feedbackCounts.teacher)}">
        <span>
            {{ getTeacherFeedbackStatus(data.feedbackCounts.teacher) }}
        </span>
        </span>
    </div>
</template>

<script>
export default {
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
        collapsed: {
            type: Boolean,
        },
        mandatory: {
            type: Boolean,
            default: false
        },

    },

    methods: {
        getTeacherFeedbackStatus(teacherFeedback) {
            if (!teacherFeedback) return '';

            // map status to display text
            const statusDisplayMap = {
                'requested': 'angefragt',
                'in progress': 'in Bearbeitung',
                'new available': 'abgegeben',
                'available': 'abgegeben',
            };

            // find first status with count > 0
            const currentStatus = Object.keys(teacherFeedback.status).find(
                status => teacherFeedback.status[status] > 0
            );

            return statusDisplayMap[currentStatus] || '';
        },

        isTeacherStatusPending(teacherFeedback) {
            if (!teacherFeedback) return '';
            // check if status is requested --> red
            return teacherFeedback && teacherFeedback.status['requested'] > 0;
        },
    }
}
</script>



<style scoped lang="scss">
@import '../../../../../../css/general';

.text-danger {
    color: $red;
}

.text-success {
    color: $green;
}
</style>
