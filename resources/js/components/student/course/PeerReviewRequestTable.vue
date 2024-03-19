<template>
    <div>
        <table class="table table-responsive" :class="{'smallView': smallView}" style="max-width: 100%;">
            <thead>
            <tr>
                <th class="d-lg-none" v-if="!smallView"></th>
                <th class="">Datum</th>
                <th class="d-lg-none d-xl-table-cell">Berater</th>
                <th class="d-none d-md-table-cell" :class="{ 'hidden': smallView }">Beratungsstelle</th>
                <th class="d-none d-md-table-cell" :class="{ 'hidden': smallView }">Persona</th>
                <th v-show="!smallView"></th>
                <th style="width: 1%;" ></th>
            </tr>
            </thead>
            <tbody v-for="(peerReviewRequest, index) in peerReviewRequests" :key="index">
            <peer-review-request :data="peerReviewRequest" :smallView="smallView" :courseFinished="courseFinished"></peer-review-request>
            </tbody>
            <tbody v-if="peerReviewRequests.length === 0">
            <tr>
                <td colspan="10" class="empty-msg">
                    <i>Keine Inhalte gefunden.</i>
                </td>
            </tr>
            </tbody>
        </table>
        <div class="open-card"><a class="link" :href="`/course/${courseId}/peer-reviews`" v-if="smallView && peerReviewRequests.length > 0">Zu den Anfragen</a></div>
    </div>
</template>

<script>
import { showErrorAlert} from '../../../helpers/Alerts';
import PeerReviewRequest from "@/components/student/course/PeerReviewRequest.vue";

export default {
    props: {
        smallView: {
            type: Boolean,
            default: false
        },
        courseId: {
            type: Number
        },
        courseEndDate: {
            type: String,
        },
    },
    data() {
        return {
            peerReviewRequests: [],
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
        axios.get(`/course/${this.courseId}/peer-review-requests`)
            .then(res => {
                if (this.smallView) {
                    this.peerReviewRequests = res.data.peerReviewRequests.slice(0, 2);
                } else {
                    this.peerReviewRequests = res.data.peerReviewRequests;
                }
            })
            .catch(err => {
                showErrorAlert(err);
            })
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
