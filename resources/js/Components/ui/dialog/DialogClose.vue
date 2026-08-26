<script setup>
import { DialogClose, useForwardProps } from 'radix-vue';

const props = defineProps({
    asChild: { type: Boolean, default: true },
});

const forwarded = useForwardProps(props);
</script>

<template>
    <DialogClose v-bind="forwarded">
        <slot />
    </DialogClose>
</template>