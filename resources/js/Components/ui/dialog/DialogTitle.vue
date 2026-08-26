<script setup>
import { DialogTitle, useForwardProps } from 'radix-vue';
import { cn } from '@/lib/utils';

const props = defineProps({
    class: { type: [String, Array], default: '' },
});

const forwarded = useForwardProps(props);
</script>

<template>
    <DialogTitle
        v-bind="forwarded"
        :class="cn('text-lg font-semibold leading-none tracking-tight', props.class)"
    >
        <slot />
    </DialogTitle>
</template>