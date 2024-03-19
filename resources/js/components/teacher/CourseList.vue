<template>
    <div>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th style="width: 50%;">Kursname</th>
                    <th style="width: 15%;">Startdatum</th>
                    <th style="width: 15%;" class="d-none d-sm-table-cell">Enddatum</th>
                    <th></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="course in courses" :key="course.id">
                    <td>{{ course.name }}</td>
                    <td>{{ new Date(course.start_date).toLocaleDateString("de") }}</td>
                    <td class="d-none d-sm-table-cell">{{ new Date(course.end_date).toLocaleDateString("de") }}</td>
                    <td class="cell-btn">
                        <a :href="`/course/${course.id}/edit`" class="btn btn-secondary">
                            <span class="d-none d-md-inline">Bearbeiten</span>
                            <fa-icon class="d-md-none" :icon="['fas', 'arrow-right']"></fa-icon>
                        </a>
                    </td>
                    <td class="cell-btn">
                        <a  class="btn btn-primary" @click="open_course(course.id)">
                            <span class="d-none d-md-inline">Öffnen</span>
                            <fa-icon class="d-md-none" :icon="['fas', 'arrow-right']"></fa-icon>
                        </a>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
<script>
import { showSuccessAlert , showErrorAlert} from '../../helpers/Alerts';

export default {
    data() {
        return {
            courses: [],
        };
    },
    mounted() {
        axios.get('courses/created')
            .then((response) => {
                this.courses = response.data.courses;
            })
            .catch((err) => {
                showErrorAlert(err);
            })
    },

    methods:{
        open_course(course_id){
            window.location.href = `/course/${course_id}`;
        }
    }
}
</script>
<style scoped>

</style>
