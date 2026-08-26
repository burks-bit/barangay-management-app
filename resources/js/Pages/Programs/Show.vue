<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { Card, CardContent } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';

const props = defineProps({
    program: Object,
    enrollments: Array,
    residents: Array,
});

// --- Resident search -----------------------------------------------------
const residentSearch = ref('');
const showResults = ref(false);

const fullName = (r) => {
    if (!r) return '';
    let name = [r.first_name, r.middle_name, r.last_name].filter(Boolean).join(' ');
    return r.suffix ? `${name} ${r.suffix}` : name;
};

const filteredResidents = computed(() => {
    const q = residentSearch.value.trim().toLowerCase();
    let list = props.residents;
    if (q) {
        list = list.filter((r) =>
            fullName(r).toLowerCase().includes(q) ||
            (r.resident_id || '').toLowerCase().includes(q)
        );
    }
    return list.slice(0, 8);
});

const selectedResidentId = ref('');
const selectResident = (resident) => {
    selectedResidentId.value = resident.id;
    residentSearch.value = '';
    showResults.value = false;
};

const enrollForm = useForm({ member_profile_id: '', notes: '' });

const submitEnroll = () => {
    enrollForm.member_profile_id = selectedResidentId.value;
    enrollForm.post(`/programs/${props.program.id}/enrollments`, {
        onSuccess: () => {
            selectedResidentId.value = '';
            enrollForm.reset();
        },
    });
};

// --- Enrollment updates --------------------------------------------------
const editingEnrollmentId = ref(null);
const enrollmentForm = useForm({ status: 'enrolled', notes: '' });

const editEnrollment = (enrollment) => {
    editingEnrollmentId.value = enrollment.id;
    enrollmentForm.status = enrollment.status;
    enrollmentForm.notes = enrollment.notes || '';
};

const submitEnrollmentUpdate = () => {
    enrollmentForm.put(`/programs/${props.program.id}/enrollments/${editingEnrollmentId.value}`, {
        onSuccess: () => {
            editingEnrollmentId.value = null;
            enrollmentForm.reset();
        },
    });
};

const removeEnrollment = (enrollment) => {
    if (confirm(`Remove ${fullName(enrollment.resident)} from this program?`)) {
        router.delete(`/programs/${props.program.id}/enrollments/${enrollment.id}`, { preserveScroll: true });
    }
};

const statusClass = (status) => ({
    enrolled: 'bg-green-100 text-green-700 border-green-200',
    completed: 'bg-blue-100 text-blue-700 border-blue-200',
    dropped: 'bg-red-100 text-red-700 border-red-200',
}[status] || 'bg-gray-100 text-gray-700 border-gray-200');

const programStatusClass = (status) => ({
    planning: 'bg-gray-100 text-gray-700 border-gray-200',
    active: 'bg-green-100 text-green-700 border-green-200',
    on_hold: 'bg-amber-100 text-amber-700 border-amber-200',
    completed: 'bg-blue-100 text-blue-700 border-blue-200',
}[status] || 'bg-gray-100 text-gray-700 border-gray-200');

const formatDate = (date) => date ? new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }) : '—';
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Program Details" />

        <div class="space-y-4">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <Link href="/programs" class="text-sm text-muted-foreground hover:text-foreground">&larr; Back to Programs</Link>
                    <h1 class="text-xl font-bold text-foreground mt-1">{{ program.name }}</h1>
                    <p class="text-sm text-muted-foreground">{{ program.description || 'No description provided.' }}</p>
                </div>
                <Badge :class="programStatusClass(program.status)" class="capitalize">
                    {{ program.status.replace('_', ' ') }}
                </Badge>
            </div>

            <!-- Program info -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <Card><CardContent class="p-4"><p class="text-xs text-muted-foreground">Code</p><p class="text-sm font-mono font-medium text-foreground mt-1">{{ program.code }}</p></CardContent></Card>
                <Card><CardContent class="p-4"><p class="text-xs text-muted-foreground">Category / Funding</p><p class="text-sm font-medium text-foreground mt-1">{{ program.category || '—' }}<span v-if="program.funding_agency" class="text-muted-foreground"> · {{ program.funding_agency }}</span></p></CardContent></Card>
                <Card><CardContent class="p-4"><p class="text-xs text-muted-foreground">Budget</p><p class="text-sm font-medium text-foreground mt-1">{{ program.budget ? `₱${Number(program.budget).toLocaleString()}` : '—' }}</p></CardContent></Card>
                <Card><CardContent class="p-4"><p class="text-xs text-muted-foreground">Beneficiaries</p><p class="text-sm font-medium text-foreground mt-1">{{ program.active_enrollments_count }} active / {{ program.total_enrollments_count }} total</p></CardContent></Card>
            </div>

            <!-- Enroll beneficiary -->
            <form @submit.prevent="submitEnroll" class="bg-card rounded-xl border p-6 space-y-4">
                <h2 class="text-sm font-semibold text-foreground">Enroll Beneficiary</h2>
                <template v-if="program.status === 'active'">
                    <template v-if="!selectedResidentId">
                        <Input
                            v-model="residentSearch"
                            type="text"
                            placeholder="Search resident by name or ID..."
                            autocomplete="off"
                            @focus="showResults = true"
                            @blur="showResults = false"
                        />
                        <div
                            v-if="showResults"
                            class="mt-1 bg-card border rounded-lg shadow-lg max-h-60 overflow-y-auto z-10 relative"
                        >
                            <button
                                v-for="resident in filteredResidents"
                                :key="resident.id"
                                type="button"
                                class="w-full text-left px-4 py-2.5 hover:bg-muted border-b last:border-0"
                                @mousedown.prevent="selectResident(resident)"
                            >
                                <span class="block text-sm font-medium text-foreground">{{ fullName(resident) }}</span>
                                <span class="block text-xs text-muted-foreground">ID: {{ resident.resident_id || '—' }}</span>
                            </button>
                            <p v-if="!filteredResidents.length" class="px-4 py-3 text-sm text-muted-foreground">No matching residents found.</p>
                        </div>
                    </template>
                    <div v-else class="flex items-center justify-between rounded-lg bg-primary/5 border px-4 py-2.5">
                        <p class="text-sm font-medium text-foreground">{{ fullName(props.residents.find((r) => r.id === Number(selectedResidentId))) }}</p>
                        <button type="button" @click="selectedResidentId = ''" class="text-xs font-medium text-destructive hover:text-destructive/80">Change</button>
                    </div>
                    <p v-if="enrollForm.errors.member_profile_id" class="text-xs text-destructive">{{ enrollForm.errors.member_profile_id }}</p>

                    <div>
                        <label class="block text-sm font-medium text-muted-foreground">Notes</label>
                        <Textarea v-model="enrollForm.notes" rows="2" placeholder="Optional enrollment notes..." class="mt-1" />
                    </div>

                    <div class="flex justify-end">
                        <Button type="submit" :disabled="!selectedResidentId || enrollForm.processing">
                            {{ enrollForm.processing ? 'Enrolling...' : 'Enroll Resident' }}
                        </Button>
                    </div>
                </template>
                <p v-else class="text-sm text-muted-foreground">Only active programs can accept new beneficiaries.</p>
            </form>

            <!-- Beneficiaries table -->
            <Card class="overflow-hidden">
                <div class="px-6 py-4 border-b">
                    <h2 class="text-sm font-semibold text-foreground">Beneficiaries</h2>
                </div>
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Resident</TableHead>
                            <TableHead>Purok</TableHead>
                            <TableHead>Enrolled</TableHead>
                            <TableHead>Status</TableHead>
                            <TableHead>Notes</TableHead>
                            <TableHead class="text-right">Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="enrollment in props.enrollments" :key="enrollment.id" class="hover:bg-muted/50">
                            <TableCell class="font-medium text-foreground">{{ fullName(enrollment.resident) }}</TableCell>
                            <TableCell class="text-muted-foreground">{{ enrollment.resident?.purok?.name || '—' }}</TableCell>
                            <TableCell class="text-muted-foreground whitespace-nowrap">{{ formatDate(enrollment.enrolled_at) }}</TableCell>
                            <TableCell>
                                <Badge :class="statusClass(enrollment.status)" class="capitalize">{{ enrollment.status }}</Badge>
                            </TableCell>
                            <TableCell class="text-muted-foreground max-w-xs truncate">{{ enrollment.notes || '—' }}</TableCell>
                            <TableCell class="text-right space-x-2 whitespace-nowrap">
                                <Button variant="ghost" size="sm" @click="editEnrollment(enrollment)">Edit</Button>
                                <Button variant="ghost" size="sm" class="text-destructive" @click="removeEnrollment(enrollment)">Remove</Button>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="!props.enrollments.length">
                            <TableCell colspan="6" class="py-10 text-center text-muted-foreground">No beneficiaries enrolled yet.</TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </Card>

            <!-- Edit enrollment modal-ish inline form -->
            <div v-if="editingEnrollmentId" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 p-4" @click.self="editingEnrollmentId = null">
                <form @submit.prevent="submitEnrollmentUpdate" class="bg-card rounded-xl shadow-lg p-6 w-full max-w-md space-y-4">
                    <h2 class="text-sm font-semibold text-foreground">Update Enrollment</h2>
                    <div>
                        <label class="block text-sm font-medium text-muted-foreground">Status *</label>
                        <Select v-model="enrollmentForm.status" required class="mt-1">
                            <option value="enrolled">Enrolled</option>
                            <option value="completed">Completed</option>
                            <option value="dropped">Dropped</option>
                        </Select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-muted-foreground">Notes</label>
                        <Textarea v-model="enrollmentForm.notes" rows="3" class="mt-1" />
                    </div>
                    <div class="flex justify-end gap-3">
                        <Button type="button" variant="outline" @click="editingEnrollmentId = null">Cancel</Button>
                        <Button type="submit" :disabled="enrollmentForm.processing">
                            {{ enrollmentForm.processing ? 'Saving...' : 'Save Changes' }}
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>