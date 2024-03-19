<template>
    <div class="custom-select">
        <span v-for="(option, index) in options" :key="index">
            <input type="radio" :id="'option'+prefix+index" :name="prefix" v-model="selected" :value="option.id">
            <label :for="'option'+prefix+index">{{ option.name }}</label>
        </span>
    </div>
</template>
<script>
export default {
    props: ['options', 'prefix'],
    data() {
        return {
            selected: null,
        }
    },
    watch: {
        selected(newVal) {
            this.$emit('update:modelValue', newVal);
        },
        options: {
            handler() {
                this.selected = null;
            },
            deep: true
        },
    }

}
</script>
<style lang="scss" scoped>
@import '../../../../../css/variables.scss';
 .custom-select input[type="radio"] {
    display: none;
  }

  .custom-select label {
    display: block;
    border: 1px solid $background;
    padding: 5px;
    cursor: pointer;

    &:hover {
        border-color: $grey;
    }
  }

  .custom-select input[type="radio"]:checked + label {
    color: $primary;
    border-color: $primary;
  }
</style>