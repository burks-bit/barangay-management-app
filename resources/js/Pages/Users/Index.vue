<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { Card, CardContent } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Select } from '@/components/ui/select';
import { Badge } from '@/components/ui/badge';

const props = defineProps({
    users: Array,
    roles: Array,
});

const updateRole = (user, role) => {
    useForm({ role }).put(`/users/${user.id}/role`, { preserveScroll: true });
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Users and Roles" />
        <div class="space-y-4">
            <div>
                <h1 class="text-xl font-bold text-foreground">Users and Roles</h1>
                <p class="text-sm text-muted-foreground">Manage account access for barangay users.</p>
            </div>
            <Card class="overflow-hidden">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Name</TableHead>
                            <TableHead>Email</TableHead>
                            <TableHead>Role</TableHead>
                            <TableHead>Status</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="user in props.users" :key="user.id">
                            <TableCell class="font-medium text-foreground">{{ user.name }}</TableCell>
                            <TableCell class="text-muted-foreground">{{ user.email }}</TableCell>
                            <TableCell>
                                <Select :model-value="user.roles?.[0]?.name" class="w-full max-w-40" @update:model-value="(v) => updateRole(user, v)">
                                    <option v-for="role in props.roles" :key="role" :value="role">{{ role }}</option>
                                </Select>
                            </TableCell>
                            <TableCell>
                                <Badge :class="user.is_active ? 'bg-green-100 text-green-700 border-green-200' : 'bg-gray-100 text-gray-500 border-gray-200'">
                                    {{ user.is_active ? 'Active' : 'Inactive' }}
                                </Badge>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>