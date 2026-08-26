<script setup>
import { cn } from '@/lib/utils';

const props = defineProps({
    class: { type: [String, Array], default: '' },
    variant: {
        type: String,
        default: 'default',
        validator: (value) => ['default', 'secondary', 'destructive', 'outline', 'success', 'warning'].includes(value),
    },
});

const variants = {
    default: 'bg-primary text-primary-foreground',
    secondary: 'bg-secondary text-secondary-foreground',
    destructive: 'bg-destructive text-destructive-foreground',
    outline: 'text-foreground border border-input',
    success: 'bg-green-100 text-green-800 border border-green-200',
    warning: 'bg-yellow-100 text-yellow-800 border border-yellow-200',
};
</script>

<template>
    <span
        :class="cn('inline-flex items-center rounded-md border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2', variants[props.variant], props.class)"
    >
        <slot />
    </span>
</template>
