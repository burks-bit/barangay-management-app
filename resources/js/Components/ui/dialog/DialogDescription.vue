<script setup>
import { DialogDescription, useForwardProps } from 'radix-vue';
import { cn } from '@/lib/utils';

const props = defineProps({
    class: { type: [String, Array], default: '' },
});

const forwarded = useForwardProps(props);
</script>

<template>
    <DialogDescription
        v-bind="forwarded"
        :class="cn('text-sm text-muted-foreground', props.class)"
    >
        <slot />
    </DialogDescription>
</template>