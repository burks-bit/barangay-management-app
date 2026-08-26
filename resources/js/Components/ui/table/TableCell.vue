<script setup>
import { cn } from '@/lib/utils';

const props = defineProps({
    class: { type: [String, Array], default: '' },
});
</script>

<template>
    <td :class="cn('p-2 align-middle [&:has([role=checkbox])]:pr-0', props.class)">
        <slot />
    </td>
</template>