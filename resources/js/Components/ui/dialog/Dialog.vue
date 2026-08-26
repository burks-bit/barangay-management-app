<script setup>
import { DialogRoot, useForwardPropsEmits } from 'radix-vue';

const props = defineProps({
    open: { type: Boolean, default: false },
    defaultOpen: { type: Boolean, default: false },
    modal: { type: Boolean, default: true },
});

const emits = defineEmits(['update:open']);

const forwarded = useForwardPropsEmits(props, emits);
</script>

<template>
    <DialogRoot v-bind="forwarded">
        <slot />
    </DialogRoot>
</template>