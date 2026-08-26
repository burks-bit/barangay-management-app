<script setup>
import { cn } from '@/lib/utils';

const props = defineProps({
    class: { type: [String, Array], default: '' },
    modelValue: { type: [String, Number], default: '' },
});

const emit = defineEmits(['update:modelValue']);
</script>

<template>
    <textarea
        :value="modelValue"
        @input="emit('update:modelValue', $event.target.value)"
        :class="cn('flex min-h-[60px] w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50', props.class)"
    />
</template>