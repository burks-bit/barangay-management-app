<script setup>
import { DialogTrigger, useForwardProps } from 'radix-vue';

const props = defineProps({
    asChild: { type: Boolean, default: true },
});

const forwarded = useForwardProps(props);
</script>

<template>
    <DialogTrigger v-bind="forwarded">
        <slot />
    </DialogTrigger>
</template>