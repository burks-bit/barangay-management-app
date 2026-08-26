<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';

defineProps({
    categories: Array,
});

const form = useForm({
    category_id: '',
    subject: '',
    description: '',
    location: '',
    incident_datetime: '',
    priority: 'medium',
});

const submit = () => {
    form.post('/my-complaints');
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="File Complaint" />

        <div class="max-w-2xl mx-auto space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-foreground">File a Complaint</h1>
                    <p class="text-sm text-muted-foreground">Report an issue to the barangay</p>
                </div>
                <Link href="/my-complaints" class="text-sm text-muted-foreground hover:text-foreground">&larr; Back to My Complaints</Link>
            </div>

            <form @submit.prevent="submit" class="bg-card rounded-xl border p-6 space-y-5">
                <div>
                    <label class="block text-sm font-medium text-muted-foreground">Category *</label>
                    <Select v-model="form.category_id" required class="mt-1">
                        <option value="">Select a category...</option>
                        <option v-for="category in categories" :key="category.id" :value="category.id">{{ category.name }}</option>
                    </Select>
                    <p v-if="form.errors.category_id" class="mt-1 text-xs text-destructive">{{ form.errors.category_id }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-muted-foreground">Subject *</label>
                    <Input v-model="form.subject" type="text" required placeholder="Brief summary of the issue" class="mt-1" />
                    <p v-if="form.errors.subject" class="mt-1 text-xs text-destructive">{{ form.errors.subject }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-muted-foreground">Description *</label>
                    <Textarea v-model="form.description" rows="5" required placeholder="Describe what happened in detail..." class="mt-1" />
                    <p v-if="form.errors.description" class="mt-1 text-xs text-destructive">{{ form.errors.description }}</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-muted-foreground">Location *</label>
                        <Input v-model="form.location" type="text" required placeholder="e.g., Purok 3, near the basketball court" class="mt-1" />
                        <p v-if="form.errors.location" class="mt-1 text-xs text-destructive">{{ form.errors.location }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-muted-foreground">Date & Time of Incident *</label>
                        <Input v-model="form.incident_datetime" type="datetime-local" required class="mt-1" />
                        <p v-if="form.errors.incident_datetime" class="mt-1 text-xs text-destructive">{{ form.errors.incident_datetime }}</p>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-muted-foreground">Priority *</label>
                    <div class="mt-2 flex space-x-4">
                        <label v-for="level in ['low', 'medium', 'high', 'urgent']" :key="level" class="flex items-center">
                            <input v-model="form.priority" type="radio" :value="level"
                                class="h-4 w-4 text-primary border-input focus:ring-ring" />
                            <span class="ml-2 text-sm text-foreground capitalize">{{ level }}</span>
                        </label>
                    </div>
                </div>

                <div class="rounded-lg bg-yellow-50 border border-yellow-100 p-4">
                    <p class="text-xs text-yellow-700">
                        Please provide accurate information. False complaints may be subject to barangay review.
                        Your complaint will be reviewed by barangay staff.
                    </p>
                </div>

                <div class="flex items-center justify-end space-x-3 pt-2">
                    <Button variant="outline" as-child>
                        <Link href="/my-complaints">Cancel</Link>
                    </Button>
                    <Button type="submit" :disabled="form.processing">
                        {{ form.processing ? 'Submitting...' : 'Submit Complaint' }}
                    </Button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>