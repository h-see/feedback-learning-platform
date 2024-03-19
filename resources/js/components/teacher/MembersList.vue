<template>
    <div>
        <table class="table table-responsive mb-0">
            <thead>
                <tr class="d-none d-sm-table-row">
                    <th class="d-lg-none" style="width: 2%;"></th>
                    <th>Name</th>
                    <th class="d-none d-sm-table-cell">E-Mail</th>
                    <th class="d-none d-lg-table-cell">Pseudonym</th>
                    <th class="d-none d-md-table-cell">Rechte</th>
                    <th></th>
                </tr>
            </thead>
            <tbody v-for="user in users">
                <tr class="main-row">
                    <td class="d-lg-none pt-3 pb-3" style="width: 1%;">
                        <a class="btn-toggle" data-bs-toggle="collapse" :href="'#userCollapse' + user.id"
                            role="button" aria-expanded="false"
                            :aria-controls="'userCollapse' + user.id">
                            <fa-icon :icon="['fas', 'angle-right']" class="icon-closed"></fa-icon>
                            <fa-icon :icon="['fas', 'angle-down']" class="icon-opened d-none"></fa-icon>
                        </a>
                    </td>
                    <td>
                        {{ user.name }}
                    </td>
                    <td class="d-none d-sm-table-cell">{{ user.email }}</td>
                    <td class="d-none d-lg-table-cell">{{ pseudonym(user.id) }}</td>
                    <td class="d-none d-md-table-cell">
                        <select v-model="user.role_id" @change="changeRole(user)" class="form-select" :disabled="(user.role_id === teacher_role_id && single_teacher) || disabled">
                            <option v-for="option in possible_roles" :value="option.id">
                                {{ option.text }}
                            </option>
                        </select>
                    </td>
                    <td v-show="user.enabled">
                        <button class="btn btn-danger btn-sm btn--full-width" data-bs-toggle="modal" :data-bs-target="'#confirmDeleteMember-'+user.id"  data-bs-target="#confirmDeleteMember" :disabled="user.role_id === teacher_role_id && single_teacher">
                            <span class="d-none d-lg-inline">Entfernen</span>
                            <fa-icon class="d-lg-none" :icon="['fas', 'trash']"></fa-icon>
                        </button>
                        <div class="modal fade" :id="'confirmDeleteMember-'+user.id" tabindex="-1" :aria-labelledby="'confirmDeleteMember-'+user.id"
                             aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="exampleModalLabel">Aufgabe löschen bestätigen</h5>
                                    </div>
                                    <div class="modal-body">
                                        <b>Achtung:</b><br/> Möchtest du diesen Kursteilnehmer wirklich löschen?
                                        <div class="col-auto">
                                            <button type="button" class="btn btn-secondary me-3" data-bs-dismiss="modal">Abbrechen
                                            </button>
                                            <button type="button" class="btn btn-danger" @click="deleteUser(user.id)"
                                                    data-bs-dismiss="modal">Löschen
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td v-show="!user.enabled">
                        <button class="btn btn-success btn-sm btn--full-width text-white" data-bs-toggle="modal" :data-bs-target="'#confirmEnableMember-'+user.id" :disabled="user.role_id === teacher_role_id && single_teacher">
                            <span class="d-none d-lg-inline">Akzeptieren</span>
                            <fa-icon class="d-lg-none" :icon="['fas', 'check']"></fa-icon>
                        </button>
                        <div class="modal fade" :id="'confirmEnableMember-'+user.id" tabindex="-1" :aria-labelledby="'confirmEnableMember-'+user.id"
                             aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="exampleModalLabel">Akzeptieren Bestätigen</h5>
                                    </div>
                                    <div class="modal-body">
                                        <b>Achtung:</b><br/> Möchtest du dieses Kursmitglied als Trainer*in wirklich bestätigen?
                                        <br> Mit dieser Rolle hat das Mitglied Zugriff auf alle Feedbackanfragen und erledigte Pflichtaufgaben.
                                        <div class="col-auto">
                                            <button type="button" class="btn btn-secondary me-3" data-bs-dismiss="modal">Abbrechen
                                            </button>
                                            <button type="button" class="btn btn-success text-white" @click="enableMember(user.id)"
                                                    data-bs-dismiss="modal">Akzeptieren
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                <!-- collapsable details-->
                <tr class="collapse d-lg-none" :id="'userCollapse' + user.id">
                    <td></td>
                    <td colspan="10" class="pb-4">
                        <span>
                            <div class="d-sm-none mb-2"><b>E-Mail: </b>{{ user.email }}</div>
                            <div><b>Pseudonym:</b> {{ pseudonym(user.id) }}</div>
                            <div class="d-md-none mt-2">
                                <b>Rechte: </b>
                                <select v-model="user.role_id" @change="changeRole(user)" class="form-select" :disabled="(user.role_id === teacher_role_id && single_teacher) || disabled">
                                    <option v-for="option in possible_roles" :value="option.id">
                                        {{ option.text }}
                                    </option>
                                </select>
                            </div>
                        </span>
                    </td>
                </tr>
            </tbody>
            <tbody>
            <tr v-if="showAddUserRow">
                <td></td>
                <td><input v-model="newUser.email" class="form-control" placeholder="E-Mail" :class="{ 'is-invalid': v$.newUser.email.$error }">
                    <div v-if="v$.newUser.email.$error" class="invalid-feedback">
                        {{ v$.newUser.email.$errors[0].$message }}
                    </div>
                </td>

                <td></td>
                <td></td>
                <td>
                    <button class="btn btn-primary btn-sm btn--full-width" @click="addMember">
                        <span>Hinzufügen</span>
                    </button>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
</template>
<script>
import { showSuccessAlert, showErrorAlert } from '../../helpers/Alerts';
import { getFullPseudonym } from '../../helpers/Pseudonym';
import { useVuelidate } from '@vuelidate/core';
import {required, requiredIf, email, helpers} from '@vuelidate/validators';

export default {
    props: {
        id: {
            type: Number,
        },
        disabled: {
            type: Boolean,
            default: false,
        },
        showAddUserRow: {
            type: Boolean,
            default: false,
        }
    },
    data() {
        return {
            users: [],
            possible_roles: [],
            newUser: {
                email: '',
            },
            v$: useVuelidate(),
        };
    },

    validations() {
    return {
        newUser: {
            email: {
                required: helpers.withMessage('Pflichtfeld', requiredIf(() => this.showAddUserRow)),
                email: helpers.withMessage('Keine gültige E-Mail-Adresse', email),
            }
        }
    };
},


    computed: {
        pseudonym: function () {
            return (userId) => {
                const user = this.users.find((user) => user.id === userId);
                return getFullPseudonym(user.pseudo_first_name, user.pseudo_last_name);
            };
        },
        single_teacher() {
            const teachers = this.users.filter((user) => user.role_id === this.teacher_role_id);
            return teachers.length === 1;
        },
        teacher_role_id() {
            return this.possible_roles.find((role) => role.title === 'editingteacher')?.id;
        }
    },
    mounted() {
        this.loadUsers();
        this.loadRoles();
    },
    methods: {
        loadUsers() {
            axios.get(`/course/${this.id}/members`)
                .then((res) => {
                    this.users = res.data;
                })
                .catch((err) => {
                    showErrorAlert(err);
                })
        },

        loadRoles() {
            axios.get('/course/roles')
                .then((res) => {
                    this.possible_roles = res.data;
                })
                .catch((err) => {
                    showErrorAlert(err);
                })
        },

        changeRole(user) {
            axios.put('/course/members/' + user.id, {
                role_id: user.role_id,
                type: 'changeRole'
            })
            .then(res => {
                showSuccessAlert(res.data.message);

            })
            .catch(err => {
                showErrorAlert(err);
                if (err?.response?.data?.current_role) {
                    const index = this.users.findIndex(u => u.id === user.id);
                    if (index !== -1) {
                        this.users[index].role_id = err.response.data.current_role;
                    }
                }
            })
        },

        deleteUser(userId) {
            axios.delete(`/course/members/${userId}`)
            .then(res => {
                const index = this.users.findIndex(user => user.id === userId);
                if (index !== -1) {
                    this.users.splice(index, 1);
                }
                showSuccessAlert(res.data.message);

            })
            .catch(err => {
                showErrorAlert(err);
            })
        },

        enableMember(userId) {

            axios.put(`/course/members/${userId}`, {
                type: 'acceptUser'
                })
                .then(res => {
                    const userIndex = this.users.findIndex(u => u.id === userId);
                    const updatedUser = { ...this.users[userIndex], enabled: true };
                    this.users.splice(userIndex, 1, updatedUser);
                    showSuccessAlert(res.data.message);
                })
                .catch(err => {
                    showErrorAlert(err);
                })
        },

        async addMember(){
            const inputCorrect = await this.v$.newUser.email.$validate();
    
            if (!this.v$.newUser.email.$valid) return;

            axios.post('/course/' + this.id + '/add-teacher/', { email: this.newUser.email,})
                .then((res) => {
                    this.$emit('hideAddUserRow');
                    this.newUser.email = '';
                    this.loadUsers();
                    showSuccessAlert(res.data.message);
                })
                .catch((err) => {
                    this.$emit('hideAddUserRow');
                    this.newUser.email = '';
                    showErrorAlert(err);
                })
        }
    }
}
</script>
<style scoped lang="scss">
@import '../../../css/general.scss';

    td {
        vertical-align: middle;
    }
</style>
