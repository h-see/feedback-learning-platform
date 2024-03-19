<template>
    <div class="container">
        <div class="row text-center mb-3 mt-3">
            <div class="col-12">
                <h3>Hinweise</h3>
            </div>
        </div>
        <div class="row text-center">
            <div class="col-xl-3 mb-3" v-for="(hint, index) in hints" :key="index">
                <div class="container-content h-100">
                    <fa-icon :icon="['fas', hint.icon]"></fa-icon>
                    <div class="fw-bold">{{ hint.heading }}</div>
                    <div :innerHTML="hint.text"></div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
export default {
    data() {
        return {
            hints: [
                {
                    icon: 'robot',
                    heading: 'Chatbot',
                    text:  'Die Antworten werden von einem Chatbot verfasst. Der Chatbot kann immer nur eine Nachricht auf einmal verarbeiten. <br/> <br/>Weitere Informationen zum Umgang mit einem Chatbot sind im Hilfecenter zu finden.'
                },
                {
                    icon: 'shield',
                    heading: 'Schutz persönlicher Daten',
                    text: 'Da die Chatverläufe für andere Personen sichtbar sind, ist es wichtig, keine persönlichen oder sensiblen Daten preiszugeben. <br/> <br/> Stattdessen sollte das zu Beginn festgelegte Pseudonym benutzt werden.'
                },
                {
                    icon: 'message',
                    heading: 'Anmerkungen im Chat',
                    text: 'Es besteht die Möglichkeit, Anmerkungen zu einzelnen Nachrichten hinzuzufügen. <br/> <br/> Dadurch können persönliche Gedanken zum Beratungsverlauf und Lernerfolg festgehalten werden.'
                },
                {
                    icon: 'thumbs-up',
                    heading: 'Bewertung der Nachrichten',
                    text: 'Durch die Bewertung der Chatbot-Nachrichten kann das Projektteam den Virtuellen Klienten kontinuierlich verbessern. <br/> <br/> Nach Abschließen des Chats kann die Bewertung der Nachrichten begründet werden.'
                }
            ]
        };
    },
    
}
</script>
<style lang="scss" scoped>

svg {
    width: 40px;
    height: auto;
    margin: 10px 0;
}
</style>