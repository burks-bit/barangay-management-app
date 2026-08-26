<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Plus, Eye, Pencil, Trash2 } from 'lucide-vue-next';

defineProps({ profiles: Array });

const destroy = (profile) => {
    if (confirm('Delete this barangay profile?')) {
        router.delete(`/barangay/${profile.id}`);
    }
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Barangay Profile" />
        <div class="space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h1 class="text-xl font-bold text-foreground">Barangay Profiles</h1>
                    <p class="text-sm text-muted-foreground">Manage the information of your barangay</p>
                </div>
                <Button as-child>
                    <Link href="/barangay/create"><Plus class="h-4 w-4" /> New Profile</Link>
                </Button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <Card v-for="profile in profiles" :key="profile.id">
                    <CardContent class="p-6">
                        <h2 class="font-semibold text-lg text-foreground">{{ profile.name }}</h2>
                        <p class="text-sm text-muted-foreground mt-1">{{ profile.address || 'No address' }}</p>
                        <p class="text-xs text-muted-foreground mt-2">{{ profile.officials_count }} officials</p>
                        <div class="mt-4 flex gap-2">
                            <Button variant="ghost" size="sm" as-child>
                                <Link :href="`/barangay/${profile.id}`"><Eye class="h-4 w-4" /> View</Link>
                            </Button>
                            <Button variant="ghost" size="sm" as-child>
                                <Link :href="`/barangay/${profile.id}/edit`"><Pencil class="h-4 w-4" /> Edit</Link>
                            </Button>
                            <Button variant="ghost" size="sm" class="text-destructive" @click="destroy(profile)">
                                <Trash2 class="h-4 w-4" /> Delete
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>
            <p v-if="!profiles.length" class="bg-card rounded-xl border p-12 text-center text-sm text-muted-foreground">No barangay profiles found. Create one to get started.</p>
        </div>
    </AuthenticatedLayout>
</template>