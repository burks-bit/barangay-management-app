<script setup>
import { Primitive, useForwardProps } from 'radix-vue';
import { computed } from 'vue';
import { cn } from '@/lib/utils';
import { buttonVariants } from '.';

const props = defineProps({
    variant: { type: String, default: 'default' },
    size: { type: String, default: 'default' },
    asChild: { type: Boolean, default: false },
    as: { type: [String, Object], default: 'button' },
    class: { type: [String, Array], default: '' },
});

const delegatedProps = computed(() => {
    const { class: _, ...delegated } = props;
    return delegated;
});

const forwardedProps = useForwardProps(delegatedProps);
</script>

<template>
    <Primitive
        :as="as"
        :as-child="asChild"
        :class="cn(buttonVariants({ variant, size }), props.class)"
        v-bind="forwardedProps"
    >
        <slot />
    </Primitive>
</template>