<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { Download, Eye } from 'lucide-vue-next';
import { Ckeditor } from '@ckeditor/ckeditor5-vue';
import {
    Alignment,
    Bold,
    ClassicEditor,
    Essentials,
    Font,
    Italic,
    Link as EditorLink,
    List,
    Paragraph,
} from 'ckeditor5';
import 'ckeditor5/ckeditor5.css';
import { computed, ref } from 'vue';

const props = defineProps({
    serviceRequest: Object,
    backUrl: { type: String, default: '/requests' },
    staff: Array,
    captains: Array,
});

console.log('props.captains', props.captains);

const page = usePage();
const permissions = computed(() => page.props.auth?.permissions || []);
const roles = computed(() => page.props.auth?.roles || []);
const canProcess = computed(() => permissions.value.includes('process requests'));
const canApprove = computed(() => permissions.value.includes('approve requests'));
const isModeratorOrAdmin = computed(() => roles.value.includes('admin') || roles.value.includes('moderator'));

// --- Release / edit-access rules ---------------------------------------
// Released docs can always be viewed/downloaded, by anyone with access to
// this page (member/requester, moderator, admin).
const isReleased = computed(() => props.serviceRequest.status === 'released');

// Moderator/admin can always edit the encoded document, released or not.
// Regular staff (canProcess) can only edit before release.
const canEditDocument = computed(
    () => isModeratorOrAdmin.value || (canProcess.value && !isReleased.value)
);

// Moderator/admin can always re-select a captain and (re-)release, as long
// as there's document content to release. Regular approvers only get this
// before the first release.
const canReleaseDocument = computed(
    () =>
        !!props.serviceRequest.document_content &&
        (isModeratorOrAdmin.value || (canApprove.value && !isReleased.value))
);

// Update-status panel follows the same role override so mod/admin aren't
// locked out of it once a request is released.
const canUpdateStatus = computed(
    () =>
        props.serviceRequest.status !== 'cancelled' &&
        (isModeratorOrAdmin.value || (canProcess.value && !isReleased.value))
);
// -------------------------------------------------------------------------

const showProcessForm = ref(false);
const processForm = useForm({
    status: '',
    remarks: '',
});
const encodeForm = useForm({
    document_content: props.serviceRequest.document_content || '',
    encoded_by: props.serviceRequest.encoded_by || '',
});
const releaseForm = useForm({ approved_by: '' });
const editor = ClassicEditor;
const editorConfig = {
    licenseKey: 'GPL',
    plugins: [Essentials, Paragraph, Bold, Italic, Font, EditorLink, List, Alignment],
    toolbar: [
        'undo', 'redo', '|', 'bold', 'italic', '|',
        'fontSize', 'fontColor', 'fontBackgroundColor', '|',
        'bulletedList', 'numberedList', '|', 'alignment', 'link',
    ],
};

const submitProcess = () => {
    processForm.post(`/requests/${props.serviceRequest.id}/process`, {
        onSuccess: () => {
            showProcessForm.value = false;
            processForm.reset();
        },
    });
};

const submitEncoding = () => encodeForm.post(`/requests/${props.serviceRequest.id}/encode`);
const submitRelease = () => releaseForm.post(`/requests/${props.serviceRequest.id}/release`);

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleString('en-US', {
        year: 'numeric', month: 'short', day: 'numeric',
        hour: 'numeric', minute: '2-digit',
    });
};

// The resident this request is for. For walk-in requests encoded by staff,
// the resident may not have a user account, so fall back to `resident`.
const residentProfile = computed(
    () => props.serviceRequest.requester?.member_profile || props.serviceRequest.resident
);
const requesterName = computed(() => {
    if (residentProfile.value) {
        const p = residentProfile.value;
        let name = [p.first_name, p.middle_name, p.last_name].filter(Boolean).join(' ');
        return p.suffix ? `${name} ${p.suffix}` : name;
    }
    return props.serviceRequest.requester?.name || '-';
});

const statusOptions = [
    { value: 'for_verification', label: 'For Verification' },
    { value: 'approved', label: 'Approved' },
    { value: 'processing', label: 'Processing' },
    { value: 'ready_for_release', label: 'Ready for Release' },
    { value: 'released', label: 'Released' },
    { value: 'rejected', label: 'Rejected' },
    { value: 'cancelled', label: 'Cancelled' },
];
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Request Details" />

        <div class="max-w-6xl mx-auto">
            <div class="flex items-center justify-between mb-4">
                <Link :href="props.backUrl" class="text-sm text-gray-500 hover:text-gray-700">&larr; Back to Requests</Link>
                <StatusBadge :status="serviceRequest.status" />
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-[30%_70%] gap-6">
                <!-- Left column: Request details -->
                <div class="space-y-4">
                    <!-- Request details -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="px-6 py-5 border-b border-gray-100">
                            <p class="text-xs font-mono text-gray-500">{{ serviceRequest.tracking_number }}</p>
                            <h1 class="text-lg font-bold text-gray-900 mt-1">{{ serviceRequest.request_type?.name }}</h1>
                            <p v-if="serviceRequest.request_type?.fee > 0" class="text-sm text-gray-500 mt-1">Fee: ₱{{ serviceRequest.request_type.fee }}</p>
                        </div>

                        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                            <dl class="space-y-3 text-sm">
                                <div>
                                    <dt class="text-gray-500">Requester</dt>
                                    <dd class="font-medium text-gray-900">
                                        {{ requesterName }}
                                        <span
                                            v-if="serviceRequest.source === 'walk_in'"
                                            class="ml-1 inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold uppercase tracking-wide bg-amber-100 text-amber-700"
                                        >
                                            Walk-in
                                        </span>
                                    </dd>
                                    <dd v-if="serviceRequest.source === 'walk_in' && serviceRequest.creator" class="text-xs text-gray-500 mt-0.5">
                                        Encoded by {{ serviceRequest.creator.name }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-gray-500">Purok</dt>
                                    <dd class="font-medium text-gray-900">{{ residentProfile?.purok?.name || '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-gray-500">Purpose</dt>
                                    <dd class="font-medium text-gray-900">{{ serviceRequest.purpose }}</dd>
                                </div>
                            </dl>
                            <dl class="space-y-3 text-sm">
                                <div>
                                    <dt class="text-gray-500">Submitted</dt>
                                    <dd class="font-medium text-gray-900">{{ formatDate(serviceRequest.submitted_at) }}</dd>
                                </div>
                                <div>
                                    <dt class="text-gray-500">Assigned Staff</dt>
                                    <dd class="font-medium text-gray-900">{{ serviceRequest.assigned_staff?.name || 'Unassigned' }}</dd>
                                </div>
                                <div v-if="serviceRequest.released_at">
                                    <dt class="text-gray-500">Released</dt>
                                    <dd class="font-medium text-green-700">{{ formatDate(serviceRequest.released_at) }}</dd>
                                </div>
                            </dl>
                        </div>

                        <div v-if="serviceRequest.description" class="px-6 pb-6">
                            <h3 class="text-sm font-semibold text-gray-900 mb-2">Additional Details</h3>
                            <p class="text-sm text-gray-600 bg-gray-50 rounded-lg p-4">{{ serviceRequest.description }}</p>
                        </div>
                    </div>

                    <!-- Status history timeline -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <h3 class="text-sm font-semibold text-gray-900 mb-4">Status History</h3>
                        <ol v-if="serviceRequest.status_histories?.length" class="relative border-l border-gray-200 ml-3 space-y-6">
                            <li v-for="(history, index) in serviceRequest.status_histories" :key="history.id" class="ml-6">
                                <span
                                    class="absolute flex items-center justify-center w-6 h-6 rounded-full -left-3 ring-4 ring-white"
                                    :class="index === 0 ? 'bg-blue-600' : 'bg-gray-300'"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-white" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900 capitalize">{{ (history.to_status || '').replace(/_/g, ' ') }}</p>
                                        <p v-if="history.remarks" class="text-xs text-gray-500 mt-0.5">{{ history.remarks }}</p>
                                        <p class="text-xs text-gray-400 mt-1">by {{ history.user?.name || 'System' }}</p>
                                    </div>
                                    <span class="text-xs text-gray-400 whitespace-nowrap">{{ formatDate(history.created_at) }}</span>
                                </div>
                            </li>
                        </ol>
                        <p v-else class="text-sm text-gray-400">No status history yet.</p>
                    </div>
                </div>

                <!-- Right column: Encoding requested document -->
                <div class="space-y-4">
                    <!-- View/Download: visible to member, moderator, and admin once released -->
                    <div v-if="isReleased" class="flex justify-end">
                        <div class="flex items-center gap-2">
                            <a
                                :href="`/requests/${serviceRequest.id}/download?inline=1`"
                                target="_blank"
                                rel="noopener"
                                title="Open PDF"
                                aria-label="Open PDF"
                                class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-blue-600 text-white hover:bg-blue-700"
                            >
                                <Eye class="h-5 w-5" aria-hidden="true" />
                            </a>
                            <a
                                :href="`/requests/${serviceRequest.id}/download`"
                                title="Download PDF"
                                aria-label="Download PDF"
                                class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-gray-700 text-white hover:bg-gray-800"
                            >
                                <Download class="h-5 w-5" aria-hidden="true" />
                            </a>
                        </div>
                    </div>

                    <!-- Encode form: mod/admin can always edit, even after release -->
                    <div v-if="canEditDocument" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-semibold text-gray-900">Encode Requested Document</h3>
                            <span v-if="isReleased" class="text-xs font-medium text-amber-600 bg-amber-50 px-2 py-1 rounded-full">
                                Editing released document
                            </span>
                        </div>
                        <form @submit.prevent="submitEncoding" class="mt-4 space-y-4">
                            <div><label class="block text-sm font-medium text-gray-700">Encoded by *</label><select v-model="encodeForm.encoded_by" required class="w-full"><option value="">Select staff...</option><option v-for="person in staff" :key="person.id" :value="person.id">{{ person.name }}</option></select></div>
                            <Ckeditor v-model="encodeForm.document_content" :editor="editor" :config="editorConfig" class="document-editor" />
                            <p v-if="encodeForm.errors.document_content" class="text-xs text-red-600">{{ encodeForm.errors.document_content }}</p>
                            <button type="submit" :disabled="encodeForm.processing" class="mt-4 bg-blue-600 text-white hover:bg-blue-700">{{ encodeForm.processing ? 'Saving...' : 'Save Encoded Document' }}</button>
                        </form>
                    </div>

                    <!-- Release form: captain selection + release button, always visible to mod/admin -->
                    <div v-if="canReleaseDocument" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <h3 class="text-sm font-semibold text-gray-900">
                            {{ isReleased ? 'Update Release' : 'Release Document' }}
                        </h3>
                        <form @submit.prevent="submitRelease" class="mt-4 flex flex-col sm:flex-row gap-3 sm:items-end">
                            <div class="flex-1"><label class="block text-sm font-medium text-gray-700">Captain *</label><select v-model="releaseForm.approved_by" required class="w-full"><option value="">Select captain...</option><option v-for="captain in captains" :key="captain.id" :value="captain.id">{{ captain.first_name }} {{ captain.last_name }}</option></select></div>
                            <div class="flex items-center gap-2">
                                <a :href="`/requests/${serviceRequest.id}/preview?preview=1&inline=1`" target="_blank" rel="noopener" title="Preview release PDF" aria-label="Preview release PDF" class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-blue-600 text-white hover:bg-blue-700"><Eye class="h-5 w-5" aria-hidden="true" /></a>
                                <button type="submit" :disabled="releaseForm.processing" class="bg-green-600 text-white hover:bg-green-700">{{ releaseForm.processing ? 'Releasing...' : (isReleased ? 'Update Release' : 'Release Document') }}</button>
                            </div>
                        </form>
                    </div>

                    <!-- Process actions (staff / mod / admin) -->
                    <div
                        v-if="canUpdateStatus"
                        class="bg-white rounded-xl shadow-sm border border-gray-100 p-6"
                    >
                        <button
                            @click="showProcessForm = !showProcessForm"
                            class="w-full flex items-center justify-between text-left"
                        >
                            <span class="text-sm font-semibold text-gray-900">Update Request Status</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-400 transition-transform" :class="{ 'rotate-180': showProcessForm }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <form v-if="showProcessForm" @submit.prevent="submitProcess" class="mt-4 space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">New Status *</label>
                                    <select v-model="processForm.status" required
                                        class="mt-1 px-3 py-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                        <option value="">Select status...</option>
                                        <option v-for="opt in statusOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Remarks</label>
                                    <input v-model="processForm.remarks" type="text"
                                        placeholder="Optional processing notes..."
                                        class="mt-1 px-3 py-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm" />
                                </div>
                            </div>
                            <div class="flex justify-end">
                                <button type="submit" :disabled="processForm.processing"
                                    class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 disabled:opacity-50">
                                    {{ processForm.processing ? 'Updating...' : 'Update Status' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
:deep(.document-editor .ck-editor__editable_inline) {
    min-height: 420px;
}
</style>