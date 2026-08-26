<script setup>
import { cn } from '@/lib/utils';

const props = defineProps({
    class: { type: [String, Array], default: '' },
    variant: {
        type: String,
        default: 'default',
        validator: (value) => ['default', 'destructive', 'success', 'warning'].includes(value),
    },
});

const variants = {
    default: 'bg-background text-foreground',
    destructive: 'border-destructive/50 text-destructive dark:border-destructive [&>svg]:text-destructive',
    success: 'border-green-500/50 text-green-700 [&>svg]:text-green-600',
    warning: 'border-yellow-500/50 text-yellow-700 [&>svg]:text-yellow-600',
};
</script>

<template>
    <div
        role="alert"
        :class="cn('relative w-full rounded-lg border px-4 py-3 text-sm [&>svg+div]:translate-y-[-3px] [&>svg]:absolute [&>svg]:left-4 [&>svg]:top-4 [&>svg]:text-foreground [&>svg~*]:pl-7', variants[props.variant], props.class)"
    >
        <slot />
    </div>
</template>