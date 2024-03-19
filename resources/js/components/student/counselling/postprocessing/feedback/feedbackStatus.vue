<template>
    <!-- teacher not collapsed -->
    <td v-if="data.feedbackCounts && !collapsed" class="d-none d-lg-table-cell"
        :class="{'text-success': isStatusNewAvailable(data.feedbackCounts.teacher), 'text-danger': isTeacherStatusPending(data.feedbackCounts.teacher)}">
            <span v-if="data.feedbackCounts && isStatusNewAvailable(data.feedbackCounts.teacher)">
                <fa-icon :icon="['fas', 'exclamation']"/> eingetroffen
            </span>
        <span v-else>
            {{ getTeacherFeedbackStatus(data.feedbackCounts.teacher) }}
        </span>
    </td>

    <!-- teacher collapsed -->
    <div class="d-lg-none mb-2" v-if="data.feedbackCounts && collapsed">
        <b>Feedback: </b>
        <span
            :class="{'text-success': isStatusNewAvailable(data.feedbackCounts.teacher), 'text-danger': isTeacherStatusPending(data.feedbackCounts.teacher)}">
        <span class="text-success" v-if="data.feedbackCounts && isStatusNewAvailable(data.feedbackCounts.teacher)">
                <fa-icon :icon="['fas', 'exclamation']"/> eingetroffen
            </span>
        <span v-else>
            {{ getTeacherFeedbackStatus(data.feedbackCounts.teacher) }}
        </span>
        </span>
    </div>

    <!-- Peer not collapsed  -->
    <td v-if="data.feedbackCounts && !collapsed" class="d-none d-lg-table-cell"
        :class="{'text-success': isStatusNewAvailable(data.feedbackCounts.peer), 'text-danger': isPeerStatusNotAllAvailable(data.feedbackCounts.peer) && !isStatusNewAvailable(data.feedbackCounts.peer)}">
            <span v-if="data.feedbackCounts && isStatusNewAvailable(data.feedbackCounts.peer)">
                <fa-icon :icon="['fas', 'exclamation']"/> eingetroffen
            </span>
        <span v-else>
                {{ getPeerFeedbackStatus(data.feedbackCounts.peer) }}
            </span>
    </td>

    <!-- Peer not collapsed  -->
    <div class="d-lg-none mb-2" v-if="data.feedbackCounts && collapsed">
        <b>PeerReview: </b>
        <span :class="{'text-success': isStatusNewAvailable(data.feedbackCounts.peer), 'text-danger': isPeerStatusNotAllAvailable(data.feedbackCounts.peer) && !isStatusNewAvailable(data.feedbackCounts.peer)}">
            <span v-if="data.feedbackCounts && isStatusNewAvailable(data.feedbackCounts.peer)">
                    <fa-icon :icon="['fas', 'exclamation']"/> eingetroffen
            </span>
            <span v-else>
                    {{ getPeerFeedbackStatus(data.feedbackCounts.peer) }}
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
        }
    },

    methods: {
        getTeacherFeedbackStatus(teacherFeedback) {
            if (!teacherFeedback) return '';

            // map status to display text
            const statusDisplayMap = {
                'requested': 'ausstehend',
                'in progress': 'in Bearbeitung',
                'new available': 'eingetroffen',
                'available': 'vorhanden',
            };

            // find first status with count > 0
            const currentStatus = Object.keys(teacherFeedback.status).find(
                status => teacherFeedback.status[status] > 0
            );

            return statusDisplayMap[currentStatus] || '';
        },

        getPeerFeedbackStatus(peerFeedback) {
            if (!peerFeedback) return '';

            const totalFeedbacks = peerFeedback.total;
            const availableFeedbacks = peerFeedback.status['available'] || 0;

            // if at least one feedback is new available --> always show eingetroffen
            if (peerFeedback.status['new available'] > 0) {
                return 'eingetroffen';
            }

            // Else: show count/total vorhanden
            return `${availableFeedbacks}/${totalFeedbacks} vorhanden`;
        },

        // methods for conditional styling
        isTeacherStatusPending(teacherFeedback) {
            if (!teacherFeedback) return '';
            // check if status is requested --> red
            return teacherFeedback && teacherFeedback.status['requested'] > 0;
        },

        isPeerStatusNotAllAvailable(peerFeedback) {
            if (!peerFeedback) return '';
            // check if not all peer requests are available --> red
            const total = peerFeedback.total;
            const available = peerFeedback.status['available'] || 0;
            const newAvailable = peerFeedback.status['new available'] || 0;

            return available < total && newAvailable === 0;
        },

        isStatusNewAvailable(feedback) {
            if (!feedback) return '';
            // check if status is new available --> green
            return feedback && feedback.status['new available'] > 0;
        },
    },
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
