<template>
    <div>
        <div class="chat_inbox row" ref="chatInbox">
            <!-- Sidebar -->
            <div class="chat_sidebar col-xl-2 col-md-3" :class="{'d-none d-md-block': !hints_accepted}" ref="chatSidebar">
                <span v-if="hints_accepted">
                    <div v-if="sidebar_visible" class="d-flex flex-column p-1 h-100">
                        <!-- Counselling infos -->
                        <div class="row mb-md-auto mb-2 mt-2">
                            <div class="col-6 col-md-12">
                                <div class="row align-items-center">
                                    <div class="col-12"><b>Beratungsstelle:</b></div>
                                    <div class="col">{{ counselling.counselling_field }}</div>
                                </div>
                            </div>
                            <div class="col-6 col-md-12">
                                <div class="row align-items-center mt-md-2">
                                    <div class="col-12"><b>Persona:</b></div>
                                    <div class="col">{{ counselling.persona }}</div>
                                </div>
                            </div>
                        </div>
                        <!-- Button area -->
                        <div class="row">
                            <div class="col-md-12 col-6">
                                <button class="btn btn-secondary btn--full-width m-0 mb-2" @click="cancel">
                                    <span class="d-block d-xl-none">Abbrechen</span>
                                    <span class="d-none d-xl-block">Chat abbrechen</span>
                                </button>
                            </div>
                            <div class="col-md-12 col-6" v-if="!courseFinished">
                                <button class="btn btn-primary btn--full-width m-0 mb-2" @click="finish">
                                    <span class="d-block d-xl-none">Beenden</span>
                                    <span class="d-none d-xl-block">Chat beenden</span>
                                </button>
                            </div>
                        </div>
                    </div>
                    <!-- Toggle Button for sidebar for smaller screens -->
                    <div id="collapse" class="d-md-none" @click="sidebar_visible=!sidebar_visible">
                        <fa-icon v-show="sidebar_visible" :icon="['fas', 'angle-up']"></fa-icon>
                        <fa-icon v-show="!sidebar_visible" :icon="['fas', 'angle-down']"></fa-icon>
                    </div>
                </span>
            </div>
            <!-- chat area -->
            <div class="chat_messages col-xl-10 col-md-9 p-0" ref="chatMessagesContainer">
                <div class="d-flex flex-column h-100">
                    <div v-if="!hints_accepted" class="text-center">
                        <hints></hints>
                        <button class="btn btn-primary mt-3 mb-2" @click="start_chat()">Chat starten</button>
                    </div>
                    <div v-else ref="chatMessages" class="h-100 overflow-auto">
                        <messages :counselling="counselling" :messages="counselling.counselling_messages" :pseudonym="pseudonym"
                        :courseFinished="courseFinished" :user-is-counselling-creator="true"></messages>
                        <div v-if="message_loading.status">
                            <message v-if="message_loading.viklMessage" :user-is-counselling-creator="true" :data="message_loading.viklMessage" :persona="counselling.persona"></message>
                        </div>
                    </div>
                    <!-- text input -->
                    <div v-if="hints_accepted && !courseFinished" class="row input_area" ref="chatInput">
                        <div class="col">
                            <textarea class="w-100 form-control" :disabled="!hints_accepted" placeholder="Nachricht eingeben"
                                v-model="new_message" @keydown.enter.prevent="keyEnter($event)"></textarea>
                        </div>
                        <div class="col-auto d-flex align-items-center">
                            <button class="btn btn-primary circle" :disabled="!hints_accepted || new_message === '' || message_loading.status" @click="save_and_generate_message">
                                <fa-icon :icon="['fas', 'paper-plane']"></fa-icon>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import { showSuccessAlert , showErrorAlert} from '../../../helpers/Alerts';
import { getShortPseudonym } from '../../../helpers/Pseudonym';

export default {
    props: ['id', 'courseEndDate'],
    data() {
        return {
            counselling: {},
            pseudonym: '',
            hints_accepted: false,
            save: false,
            sidebar_visible: true,
            new_message: '',
            message_loading: {
                status: false,
            },
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
        axios.get(`/counselling/${this.id}/data`)
            .then((res) => {
                this.counselling = res.data.counselling;
                this.hints_accepted = this.counselling.counselling_messages.length > 0;
                axios.get(`/course/${this.counselling.course}/pseudonym`)
                    .then((res) => {
                        this.pseudonym = getShortPseudonym(res.data.pseudo_first_name, res.data.pseudo_last_name);
                        if (this.hints_accepted) {
                            this.scrollToBottom();
                        }
                    })
                    .catch((err) => {
                        showErrorAlert(err);
                    });
            })
            .catch((err) => {
                showErrorAlert(err);
            });
        this.$nextTick(() => {
            this.setChatHeight();
        });
        window.addEventListener('resize', this.setChatHeight);
    },
    beforeDestroy() {
        window.removeEventListener('resize', this.setChatHeight);
    },
    methods: {
        keyEnter(event) {
            if (event.shiftKey) {
                // enter with shift -> new line
                return;
            } else if (this.new_message.trim() !== '') {
                // enter without shift -> send
                this.save_and_generate_message();
            }
        },

        save_and_generate_message() {
            this.message_loading.status = true;
            axios.post(`/counselling/${this.counselling.id}/message`, { message: this.new_message })
                .then(res => {
                    this.counselling.counselling_messages = this.counselling.counselling_messages.concat(res.data);
                    this.new_message = '';

                        setTimeout(() => {
                            this.message_loading.viklMessage = {
                                content: 'Schreibt...',
                                author: 'vikl',
                            };
                            this.$nextTick(() => {
                                this.scrollToBottom();
                            });
                        }, 1000);

                    this.generate_new_message();
                })
                .catch(err => {
                    showErrorAlert(err);
                    this.message_loading = { status: false}
                })
                .finally(() => {
                    this.scrollToBottom();
                });
        },

        start_chat() {
            this.hints_accepted = true;
            this.message_loading.viklMessage = {
                content: 'Schreibt...',
                author: 'vikl',
            };
            this.generate_new_message();
        },

        generate_new_message() {
            this.message_loading.status = true;

            axios.get(`/counselling/${this.counselling.id}/message`)
                .then(res => {
                    this.counselling.counselling_messages = this.counselling.counselling_messages.concat(res.data);
                })
                .catch(err => {
                    showErrorAlert(err);
                })
                .finally(() => {
                    this.message_loading = { status: false};
                    this.scrollToBottom();
                });

        },
        scrollToBottom() {
            this.$refs.chatMessages.scrollTop = this.$refs.chatMessages.scrollHeight;
        },
        cancel() {
            axios.delete('/counselling/' + this.counselling.id)
            .then(res => {
                window.location.href = '/course/' + this.counselling.course;
            })
            .catch(err => {
                showErrorAlert(err);
            })
        },
        finish() {
            axios.put(`/counselling/${this.counselling.id}/finish-chat`)
            .then(res => {
                window.location.href = '/counselling/' + this.counselling.id;
            })
            .catch(err => {
                showErrorAlert(err);
            })
        },
        setChatHeight() {
            if (window.innerWidth < 768) {
                this.$refs.chatMessagesContainer.style.height = this.$refs.chatInbox.clientHeight - this.$refs.chatSidebar.clientHeight + 'px';
            }
        }
    },
    watch: {
        sidebar_visible () {
            this.$nextTick(() => {
                this.setChatHeight();
            });
        },
        hints_accepted () {
            this.$nextTick(() => {
                this.setChatHeight();
            });
        },
    },
}
</script>
<style lang="scss" scoped>
@import '../../../../css/general.scss';

.chat_inbox {
    border: 1px solid $grey;
    overflow: hidden;
    margin: 25px;
}

.chat_sidebar {
    background-color: $background-light;
    border-right: 1px solid $grey;
}

.chat_messages {
    display: flex;
    flex-direction: column;
    height: 600px;
    overflow: auto;
}

.chat_history {
    flex: 1 1 auto;
}

.input_area {
    border-top: 1px solid $grey;
    position: sticky;
    background-color: $white;
    bottom: 0;
    margin: 0;

    > .col > textarea {
        height: 50px;
        border: none;
        resize: none;

        &:focus {
            outline: none;
            box-shadow: none !important;
        }
    }
}

.circle {
    width: 40px;
    height: 40px;
}

.chat_sidebar #collapse {
    position: absolute;
    background-color: $grey-dark;
    color: white;
    left: 50%;
    transform: translateX(-50%);
    width: fit-content;
    padding: 0 12px;
    border-radius: 0 0 5px 5px;
    &:hover {
        cursor: pointer;
    }
}

@include media-breakpoint-down(md) { // --> sidebar goes to top
    .chat_sidebar {
        border-right: none;
        border-bottom: 1px solid $grey;
        height: fit-content;
    }

    .chat_inbox {
        height: 80vh;
    }
}

</style>
