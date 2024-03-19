<template>
    <tr>
        <td class="d-lg-none pt-3 pb-3" style="width: 1%;" v-if="!smallView">
            <a class="btn-toggle" data-bs-toggle="collapse" :href="'#peerReviewCollapse' + data.id"
               role="button" aria-expanded="false"
               :aria-controls="'peerReviewCollapse' + data.id">
                <fa-icon :icon="['fas', 'angle-right']" class="icon-closed"></fa-icon>
                <fa-icon :icon="['fas', 'angle-down']" class="icon-opened d-none"></fa-icon>
            </a>
        </td>
        <td class="" v-if="feedbackRequestDate">{{feedbackRequestDate}}</td>
        <td class="d-lg-none d-xl-table-cell" v-if="pseudonym">{{pseudonym}}</td>
        <td class="d-none d-md-table-cell" v-if="counselling_field" :class="{ 'hidden': smallView }">{{ counselling_field }}</td>
        <td class="d-none d-md-table-cell" v-if="persona" :class="{ 'hidden': smallView }">{{ persona }}</td>
        <td class="cell-btn">
            <button class="btn btn-primary" @click="openCounselling" :disabled="courseFinished" v-show="status === 'requested'">
                <span class="d-none d-xxl-inline" :class="{ 'd-sm-inline': !smallView }">Annehmen</span>
                <fa-icon class="d-xxl-none" :class="{ 'd-sm-none': !smallView }" :icon="['fas', 'check']" title="Annehmen"></fa-icon>
            </button>
            <button class="btn btn-secondary" @click="openCounselling" :disabled="courseFinished" v-show="status === 'in progress'">
                <span class="d-none d-xxl-inline" :class="{ 'd-sm-inline': !smallView }">Fortsetzen</span>
                <fa-icon class="d-xxl-none" :class="{ 'd-sm-none': !smallView }" :icon="['fas', 'arrow-rotate-right']" title="Fortsetzen"></fa-icon>
            </button>
        </td>
    </tr>

    <!-- collapsable details-->
    <tr class="collapse d-lg-none" :id="'peerReviewCollapse' + data.id" v-if="!smallView">
        <td></td>
        <td colspan="10" class="pb-4">
            <span>
                <div class="d-md-none mb-2"><b>Beratungsstelle: </b>{{ counselling_field }}</div>
                <div class="d-sm-none mb-2"><b>Persona: </b>{{ persona }}</div>
            </span>
        </td>
    </tr>
</template>


<script>
import { showSuccessAlert , showErrorAlert} from '../../../helpers/Alerts';
import {getShortPseudonym} from "@/helpers/Pseudonym";

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
        }
    },
    computed: {
        persona() {
            if (this.data !== undefined) {
                return this.data.persona_name;
            } else {
                return '';
            }
        },
        counselling_field() {
            if (this.data !== undefined) {
                return this.data.counselling_field;
            } else {
                return false;
            }
        },

        feedbackRequestDate(){
            if (this.data !== undefined) {
                const date = new Date(this.data.created_at);
                return date.toLocaleDateString('de');
            } else {
                return false;
            }
        },

        status(){
            if (this.data !== undefined) {
                return this.data.status_feedback_id === 1 ? 'requested' : 'in progress';
            } else {
                return false;
            }
        },

        pseudonym(){
            if (this.data !== undefined) {
                return this.data.pseudo_first_name + " " + (this.data.pseudo_last_name).slice(0,1) + ".";
            } else {
                return false;
            }
        },
    },

    methods: {
        openCounselling() {
            axios.get(`/counselling/${this.data.counselling_id}/feedback/${this.data.id}/lock-peer`)
                .then(res =>{
                        window.location.replace('/counselling/' + this.data.counselling_id + '/feedback/' + this.data.id);
            })


        },
    },
}
</script>
<style scoped lang="scss">

</style>
