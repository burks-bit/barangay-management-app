<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';

defineProps({ puroks: Array });

const form = useForm({
    event_code: '',
    name: '',
    type: 'typhoon',
    description: '',
    started_at: new Date().toISOString().slice(0, 16),
    ended_at: '',
    severity: 'moderate',
    status: 'reported',
    affected_households: 0,
    affected_residents: 0,
    purok_ids: [],
    notes: '',
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
    form.post('/calamities');
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Create Calamity" />
        <div class="max-w-4xl mx-auto space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-foreground">Create Calamity</h1>
                    <p class="text-sm text-muted-foreground">Record a new calamity event</p>
                </div>
                <Link href="/calamities" class="text-sm text-muted-foreground hover:text-foreground">&larr; Back</Link>
            </div>

            <form @submit.prevent="submit" class="bg-card rounded-xl border p-6 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-muted-foreground mb-1">Event Code *</label>
                        <Input v-model="form.event_code" type="text" placeholder="CAL-2026-000003" required />
                        <p v-if="form.errors.event_code" class="mt-1 text-xs text-destructive">{{ form.errors.event_code }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-muted-foreground mb-1">Name *</label>
                        <Input v-model="form.name" type="text" placeholder="e.g., Typhoon Rolly" required />
                        <p v-if="form.errors.name" class="mt-1 text-xs text-destructive">{{ form.errors.name }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-muted-foreground mb-1">Type *</label>
                        <Select v-model="form.type">
                            <option v-for="t in types" :key="t" :value="t">{{ t.replace('_', ' ') }}</option>
                        </Select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-muted-foreground mb-1">Start Date *</label>
                        <Input v-model="form.started_at" type="datetime-local" required />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-muted-foreground mb-1">End Date</label>
                        <Input v-model="form.ended_at" type="datetime-local" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-muted-foreground mb-1">Severity</label>
                        <Select v-model="form.severity">
                            <option v-for="s in severities" :key="s" :value="s">{{ s }}</option>
                        </Select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-muted-foreground mb-1">Status</label>
                        <Select v-model="form.status">
                            <option v-for="s in statuses" :key="s" :value="s">{{ s.replace('_', ' ') }}</option>
                        </Select>
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
                        {{ form.processing ? 'Saving...' : 'Create Calamity' }}
                    </Button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>