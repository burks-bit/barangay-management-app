<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';

defineProps({
    assistanceTypes: Array,
});

const form = useForm({
    assistance_type_id: '',
    reason: '',
    amount: '',
});

const submit = () => form.post('/my-assistance');
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Request Assistance" />

        <div class="max-w-2xl mx-auto space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-foreground">Request Assistance</h1>
                    <p class="text-sm text-muted-foreground mt-1">Submit a request for barangay assistance.</p>
                </div>
                <Link href="/my-assistance" class="text-sm text-muted-foreground hover:text-foreground">&larr; Back</Link>
            </div>

            <form @submit.prevent="submit" class="bg-card rounded-xl border p-6 space-y-5">
                <div>
                    <label class="block text-sm font-medium text-muted-foreground">Assistance type *</label>
                    <Select v-model="form.assistance_type_id" required class="mt-1">
                        <option value="">Select a type...</option>
                        <option v-for="type in assistanceTypes" :key="type.id" :value="type.id">{{ type.name }}</option>
                    </Select>
                    <p v-if="form.errors.assistance_type_id" class="mt-1 text-xs text-destructive">{{ form.errors.assistance_type_id }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-muted-foreground">Reason *</label>
                    <Textarea v-model="form.reason" required rows="6" placeholder="Explain why you need assistance..." class="mt-1" />
                    <p v-if="form.errors.reason" class="mt-1 text-xs text-destructive">{{ form.errors.reason }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-muted-foreground">Estimated amount (optional)</label>
                    <Input v-model="form.amount" type="number" min="0" step="0.01" class="mt-1" />
                    <p v-if="form.errors.amount" class="mt-1 text-xs text-destructive">{{ form.errors.amount }}</p>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <Button variant="outline" as-child>
                        <Link href="/my-assistance">Cancel</Link>
                    </Button>
                    <Button type="submit" :disabled="form.processing">
                        {{ form.processing ? 'Submitting...' : 'Submit Request' }}
                    </Button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>