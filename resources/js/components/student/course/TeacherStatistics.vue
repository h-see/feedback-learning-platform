<template>
    <div>
        <table class="table table-sm" v-if="!error">
            <thead class="table-head">
            <tr>
                <th colspan="2">Persona</th>
                <th data-toggle="tooltip" data-placement="top" title="Anzahl an durchgeführten Pflichtaufgaben aller Studierender">Durchgeführte<br/> Pflichtaufgaben</th>
                <th data-toggle="tooltip" data-placement="top" title="Anzahl an zusätzlich durchgeführten Übungen">Durchgeführte<br/>Übungen</th>
            </tr>
            </thead>
            <tbody v-for="(group, index) in groupedData" :key="index">
            <tr>
                <th colspan="2" :title="group.count_all_tasks === 0 ? 'Keine Pflichtaufgabe für diese Persona hinterlegt.' : ''">{{ index }}</th>
                <th :title="group.count_all_tasks === 0 ? 'Keine Pflichtaufgabe für diese Persona hinterlegt.' : ''">{{ group.count_all_tasks === 0 ? '' : group.count_done_tasks + '/' + group.count_all_tasks + ' Chats'}}</th>
                <th>{{ group.count_exercises }}{{ group.count_exercises  === 1 ? ' Chat' : ' Chats'}}</th>
            </tr>
            <tr v-for="(persona, index) in group.personae" :key="index">
                <td style="width: 30px;"></td>
                <td :title="persona.countAllTasks === 0 ? 'Keine Pflichtaufgabe für diese Persona hinterlegt.' : ''">{{ persona.name }}</td>
                <td :title="persona.countAllTasks === 0 ? 'Keine Pflichtaufgabe für diese Persona hinterlegt.' : ''">{{persona.countAllTasks === 0 ? '' : persona.countDoneTasks + '/' + persona.countAllTasks + ' Chats'}} </td>
                <td>{{ persona.countExercises }} {{ persona.countExercises === 1 ? ' Chat' : ' Chats'}}</td>
            </tr>
            </tbody>
        </table>
        <div v-else class="empty-msg">{{ error }}</div>
    </div>
</template>
<script>
export default {
    props: ['courseId'],
    data() {
        return {
            statistics: [],
            countCourseMembers: 0,
            error: false,

            taskSumAll: 0,
            taskSumDone: 0,
            excerciseSum: 0,
        };
    },
    computed: {
        groupedData() {
            const groupedData = {};

            this.statistics.forEach((entry) => {
                const {counsellingField, name,countAllTasks, countDoneTasks, countExercises} = entry;

                this.taskSumAll += countAllTasks;
                this.taskSumDone += countDoneTasks;
                this.excerciseSum += countExercises;

                if (!groupedData[counsellingField]) {
                    groupedData[counsellingField] = {
                        personae: [],
                        count_all_tasks: 0,
                        count_done_tasks: 0,
                        count_exercises: 0,
                    };
                }

                groupedData[counsellingField].personae.push({name,countAllTasks, countDoneTasks, countExercises});
                groupedData[counsellingField].count_done_tasks += countDoneTasks;
                groupedData[counsellingField].count_all_tasks += countAllTasks;
                groupedData[counsellingField].count_exercises += countExercises;

            });

            groupedData['Gesamt'] = { 'count_all_tasks' : this.taskSumAll, 'count_done_tasks': this.taskSumDone, 'count_exercises': this.excerciseSum};
            return groupedData;
        },
     },
    mounted() {

        axios.get(`/course/${this.courseId}/teacher-statistics`)
            .then(res => {
                this.statistics = res.data.statistics;
                this.countCourseMembers = res.data.countCourseMembers;
            })
            .catch(err => {
                this.error = 'Fehler beim Laden der Statistik';
            })
    },
}
</script>
<style lang="scss" scoped>

.table-head {
    tr {
        vertical-align: middle;
    }
}


</style>
