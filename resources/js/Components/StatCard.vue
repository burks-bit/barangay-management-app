<script setup>
import { Card, CardContent } from '@/components/ui/card';
import { Users, Home, ClipboardList, AlertTriangle, BarChart3, CheckCircle2, Package, Clock } from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps({
    title: {
        type: String,
        required: true,
    },
    value: {
        type: [Number, String],
        required: true,
    },
    icon: {
        type: String,
        default: 'chart',
    },
    color: {
        type: String,
        default: 'blue',
    },
    href: {
        type: String,
        default: null,
    },
});

const colors = {
    blue: 'bg-blue-500',
    green: 'bg-green-500',
    yellow: 'bg-yellow-500',
    red: 'bg-red-500',
    purple: 'bg-purple-500',
    indigo: 'bg-indigo-500',
    orange: 'bg-orange-500',
    teal: 'bg-teal-500',
};

const iconMap = {
    users: Users,
    home: Home,
    clipboard: ClipboardList,
    alert: AlertTriangle,
    chart: BarChart3,
    check: CheckCircle2,
    box: Package,
    clock: Clock,
};

const iconComponent = computed(() => iconMap[props.icon] || BarChart3);
</script>

<template>
    <component
        :is="href ? 'a' : 'div'"
        :href="href"
        class="block"
    >
        <Card class="overflow-hidden transition-shadow hover:shadow-md">
            <CardContent class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center w-11 h-11 rounded-lg" :class="colors[color]">
                            <component :is="iconComponent" class="w-6 h-6 text-white" aria-hidden="true" />
                        </div>
                    </div>
                    <div class="ml-4 flex-1 min-w-0">
                        <p class="text-sm font-medium text-muted-foreground truncate">{{ title }}</p>
                        <p class="text-2xl font-bold text-foreground">{{ value }}</p>
                    </div>
                </div>
            </CardContent>
        </Card>
    </component>
</template>