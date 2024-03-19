<template>
    <div>
        <table class="table" v-if="!error">
            <thead class="table-head">
                <tr>
                    <th colspan="2">Persona</th>
                    <th data-toggle="tooltip" data-placement="top" title="Anzahl an durchgeführten Beratungen">Durchgeführte<br/>Übungen</th>
                    <th data-toggle="tooltip" data-placement="top" title="Der Kursdurchschnitt gibt an, wie viele Beratungen ein*e Kursteilnehmer*in im Durchschnitt pro Persona durchgeführt hat.">
                        <span class="d-sm-none">Kursdurch-<br/>schnitt</span>
                        <span class="d-none d-sm-inline">Kursdurchschnitt</span>
                    </th>
                </tr>
            </thead>
            <tbody v-for="(group, index) in groupedData" :key="index">
                <tr>
                    <th colspan="2">{{ index }}</th>
                    <th>{{ group.count_user }}{{ group.count_user === 1 ? ' Chat' : ' Chats'}}</th>
                    <th>{{ groupAverage(index) }}{{ groupAverage(index) === 1 ? ' Chat' : ' Chats'}}<span class="d-none d-sm-inline">/Person</span></th>
                </tr>
                <tr v-for="(persona, index) in group.personae" :key="index">
                    <td style="width: 30px;"></td>
                    <td>{{ persona.name }}</td>
                    <td>{{ persona.countUser }} {{ persona.count_user === 1 ? ' Chat' : ' Chats'}}</td>
                    <td>{{ persona.averageUser }} {{ persona.averageUser === 1 ? ' Chat' : ' Chats'}}<span class="d-none d-sm-inline">/Person</span></td>
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
        };
    },
    computed: {
        groupedData() {
            const groupedData = {};

            this.statistics.forEach((entry) => {
                const { counsellingField, name, countUser, averageUser,  countAll} = entry;

                if (!groupedData[counsellingField]) {
                    groupedData[counsellingField] = {
                        personae: [],
                        count_user: 0,
                        count_all: 0,
                    };
                }

                groupedData[counsellingField].personae.push({ name, countUser, averageUser });
                groupedData[counsellingField].count_user += countUser;
                groupedData[counsellingField].count_all += countAll;
            });

            return groupedData;
        },
        groupAverage: function () {
            return (groupName) => {
                return Math.round(this.groupedData[groupName].count_all/this.countCourseMembers);
            };
        },
    },
    mounted() {
        axios.get(`/course/${this.courseId}/statistics`)
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