<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';

const props = defineProps({
    calamity: Object,
    puroks: Array,
});

const form = useForm({
    event_code: props.calamity.event_code,
    name: props.calamity.name,
    type: props.calamity.type,
    description: props.calamity.description || '',
    started_at: props.calamity.started_at?.slice(0, 16) || '',
    ended_at: props.calamity.ended_at?.slice(0, 16) || '',
    severity: props.calamity.severity,
    status: props.calamity.status,
    affected_households: props.calamity.affected_households ?? 0,
    affected_residents: props.calamity.affected_residents ?? 0,
    purok_ids: props.calamity.puroks?.map(p => p.id) || [],
    notes: props.calamity.notes || '',
});

const types = ['typhoon', 'flood', 'earthquake', 'fire', 'landslide', 'storm_surge', 'other'];
const statuses = ['reported', 'active', 'under_response', 'contained', 'resolved', 'archived'];
const severities = ['low', 'moderate', 'high', 'severe', 'critical'];

const togglePurok = (id) => {
    const index = form.purok_ids.indexOf(id);
    if (index === -1) form.purok_ids.push(id);
    else form.purok_ids.splice(index, 1);
};

const submit = () => {
    form.put(`/calamities/${props.calamity.id}`);
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Edit Calamity" />
        <div class="max-w-4xl mx-auto space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-foreground">Edit Calamity</h1>
                    <p class="text-sm text-muted-foreground">Update calamity event</p>
                </div>
                <Link href="/calamities" class="text-sm text-muted-foreground hover:text-foreground">&larr; Back</Link>
            </div>

            <form @submit.prevent="submit" class="bg-card rounded-xl border p-6 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-muted-foreground mb-1">Event Code *</label>
                        <Input v-model="form.event_code" type="text" required />
                        <p v-if="form.errors.event_code" class="mt-1 text-xs text-destructive">{{ form.errors.event_code }}</p>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-muted-foreground mb-1">Name *</label>
                        <Input v-model="form.name" type="text" required />
                        <p v-if="form.errors.name" class="mt-1 text-xs text-destructive">{{ form.errors.name }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-muted-foreground mb-1">Type</label>
                        <Select v-model="form.type">
                            <option v-for="t in types" :key="t" :value="t">{{ t.replace('_', ' ') }}</option>
                        </Select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-muted-foreground mb-1">Status</label>
                        <Select v-model="form.status">
                            <option v-for="s in statuses" :key="s" :value="s">{{ s.replace('_', ' ') }}</option>
                        </Select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-muted-foreground mb-1">Severity</label>
                        <Select v-model="form.severity">
                            <option v-for="s in severities" :key="s" :value="s">{{ s }}</option>
                        </Select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-muted-foreground mb-1">Started At</label>
                        <Input v-model="form.started_at" type="datetime-local" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-muted-foreground mb-1">Ended At</label>
                        <Input v-model="form.ended_at" type="datetime-local" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-muted-foreground mb-1">Affected Households</label>
                        <Input v-model="form.affected_households" type="number" min="0" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-muted-foreground mb-1">Affected Residents</label>
                        <Input v-model="form.affected_residents" type="number" min="0" />
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-muted-foreground mb-1">Description</label>
                        <Textarea v-model="form.description" rows="3" />
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-muted-foreground mb-2">Affected Puroks</label>
                        <div class="flex flex-wrap gap-2">
                            <label v-for="purok in puroks" :key="purok.id" class="inline-flex items-center px-3 py-2 rounded-lg border cursor-pointer" :class="form.purok_ids.includes(purok.id) ? 'bg-primary/5 border-primary/30' : 'border-input'">
                                <input type="checkbox" :checked="form.purok_ids.includes(purok.id)" @change="togglePurok(purok.id)" class="rounded border-input" />
                                <span class="ml-2 text-sm text-foreground">{{ purok.name }}</span>
                            </label>
                        </div>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-muted-foreground mb-1">Notes</label>
                        <Textarea v-model="form.notes" rows="2" />
                    </div>
                </div>

                <div class="flex items-center justify-end space-x-3 pt-4 border-t">
                    <Button variant="outline" as-child>
                        <Link href="/calamities">Cancel</Link>
                    </Button>
                    <Button type="submit" :disabled="form.processing">
                        {{ form.processing ? 'Saving...' : 'Update Calamity' }}
                    </Button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>