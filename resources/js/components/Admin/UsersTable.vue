<template>
    <div class="card">
        <div class="card-header">
            <div class="row justify-content-between">
                <div class="col">Nutzer*innen</div>
            </div>
        </div>
        <div class="card-body p-0">
            <table class="table table-responsive">
                <thead>
                    <tr>
                        <th class="d-md-none" style="width: 2%;"></th>
                        <th class="ps-0 ps-md-4">Id</th>
                        <th>Name</th>
                        <th class="d-none d-md-table-cell">E-Mail</th>
                        <th class="d-none d-sm-table-cell">Rechte</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody v-for="user in users">
                    <tr class="main-row">
                        <td class="d-md-none pt-3 pb-3" style="width: 1%;">
                            <a class="btn-toggle" data-bs-toggle="collapse" :href="'#userCollapse' + user.id"
                                role="button" aria-expanded="false"
                                :aria-controls="'userCollapse' + user.id">
                                <fa-icon :icon="['fas', 'angle-right']" class="icon-closed"></fa-icon>
                                <fa-icon :icon="['fas', 'angle-down']" class="icon-opened d-none"></fa-icon>
                            </a>
                        </td>
                        <td class="ps-0 ps-md-4">{{ user.id }}</td>
                        <td>{{ user.name }}</td>
                        <td class="d-none d-md-table-cell">{{ user.email }}</td>
                        <td class="d-none d-sm-table-cell">
                            <select v-model="user.main_role_id"
                                class="form-select"
                                :disabled="user.roleTitle === 'Administrator' && single_admin"
                                @change="changeRole(user)">
                                <option v-for="option in possible_roles" :key="option.id"
                                    :value="option.id">
                                    {{option.text}}
                                </option>
                            </select>
                        </td>
                        <td>
                            <button class="btn btn-danger btn--full-width"
                                @click="deleteUser(user.id)" :disabled="user.roleTitle === 'Administrator' && single_admin">
                                <span class="d-none d-lg-inline">Löschen</span>
                                <fa-icon class="d-lg-none" :icon="['fas', 'trash']"></fa-icon>
                            </button>
                        </td>
                    </tr>
                    <!-- collapsable details for smaller view -->
                    <tr class="collapse d-md-none" :id="'userCollapse' + user.id">
                        <td></td>
                        <td colspan="10" class="pb-4">
                            <span>
                                <div class="d-md-none mb-2"><b>E-Mail: </b>{{ user.email }}</div>
                                <div class="d-sm-none mt-2">
                                    <b>Rechte: </b>
                                    <select v-model="user.main_role_id"
                                        class="form-select"
                                        :disabled="user.roleTitle === 'Administrator' && single_admin"
                                        @change="changeRole(user)">
                                        <option v-for="option in possible_roles" :key="option.id"
                                            :value="option.id">
                                            {{option.text}}
                                        </option>
                                    </select>
                                </div>
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
<script>
import { showSuccessAlert , showErrorAlert} from '../../helpers/Alerts';

export default {
    data() {
        return {
            users: [],
            possible_roles: [],
        };
    },
    computed: {
        single_admin() {
            const admins = this.users.filter((user) => user.roleTitle === 'Administrator');
            return admins.length === 1;
        },
    },
    mounted() {
        axios.get('/admin/roles')
            .then((res) => {
                this.possible_roles = res.data;
                axios.get('/users')
                    .then(res => {
                        this.users = res.data;
                    })
                    .catch(err => {
                        showErrorAlert(err);
                    })
            })
            .catch((err) => {
                showErrorAlert(err);
            })
    },
    methods: {
        changeRole(user) {
            axios.put(`/users/${user.id}/role`, { role_id: user.main_role_id })
                .then((res) => {
                    const index = this.users.findIndex((u) => u.id === user.id);
                    this.users[index] = res.data.user;
                    showSuccessAlert(res.data.message);
                })
                .catch((err) => {
                    showErrorAlert(err);
                });
        },

        deleteUser(id) {
            axios.delete('/users/' + id)
                .then((res) => {
                    const index = this.users.findIndex(user => user.id === id);
                    if (index !== -1) {
                        this.users.splice(index, 1);
                    }
                    showSuccessAlert(res.data.message);
                })
                .catch((err) => {
                    showErrorAlert(err);
                });
        },
    }
}
</script>
<style scoped lang="scss">
    td {
        vertical-align: middle;
    }
</style>