<template>
    <div>
        <message v-for="(message, index) in messages" :key="index" :data="message" :persona="counselling.persona" :pseudonym="pseudonym" :courseFinished="courseFinished" :userIsCounsellingCreator="userIsCounsellingCreator" :is-teacher="isTeacher" :show-copy-button="showCopyButton"  @content-copied="onContentCopied"></message>
    </div>
</template>
<script>
export default {
    props: ['counselling', 'messages', 'pseudonym', 'courseFinished', 'userIsCounsellingCreator', 'isTeacher', 'showCopyButton'],

    methods: {
        onContentCopied(content) {
            this.$emit("content-copied", content);
        },
    },
}
</script>
<style lang="">

</style>
