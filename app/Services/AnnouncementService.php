<?php

namespace App\Services;

use App\Models\Announcement;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

class AnnouncementService extends Service
{
    public const TYPES = [
        'calamity_warning',
        'evacuation_notice',
        'barangay_announcement',
        'community_event',
        'service_interruption',
        'emergency_instruction',
        'general',
    ];

    public function __construct(private AuditLogService $auditLog)
    {
    }

    /**
     * Management view: all announcements regardless of status.
     */
    public function list(): Collection
    {
        return Announcement::with('creator:id,name')
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Create an announcement as a draft, or publish it immediately
     * when the "publish" flag is sent.
     */
    public function create(Request $request, User $creator): Announcement
    {
        $validated = $this->validate($request);
        $publish = $request->boolean('publish');

        $announcement = $this->transaction(function () use ($validated, $creator, $publish) {
            $announcement = Announcement::create([
                ...$validated,
                'created_by' => $creator->id,
                'status' => $publish ? 'published' : 'draft',
                'published_at' => $publish ? now() : null,
            ]);

            $this->auditLog->log(
                $publish ? 'published announcement' : 'created announcement draft',
                'announcements',
                'Announcement',
                $announcement->id,
                null,
                $announcement->toArray()
            );

            return $announcement;
        }, 'Failed to create announcement.');

        return $announcement;
    }

    public function update(Request $request, Announcement $announcement): void
    {
        $validated = $this->validate($request);
        $oldValues = $announcement->only(array_keys($validated));

        $this->transaction(function () use ($validated, $announcement, $oldValues) {
            $announcement->update($validated);

            $this->auditLog->log(
                'updated announcement',
                'announcements',
                'Announcement',
                $announcement->id,
                $oldValues,
                $announcement->fresh()->toArray()
            );
        }, 'Failed to update announcement.');
    }

    /**
     * Publish a draft (or re-publish an archived) announcement.
     */
    public function publish(Announcement $announcement): void
    {
        $oldValues = $announcement->only(['status', 'published_at', 'archived_at']);

        $this->transaction(function () use ($announcement, $oldValues) {
            $announcement->update([
                'status' => 'published',
                'published_at' => $announcement->published_at ?? now(),
                'archived_at' => null,
            ]);

            $this->auditLog->log(
                'published announcement',
                'announcements',
                'Announcement',
                $announcement->id,
                $oldValues,
                $announcement->fresh()->toArray()
            );
        }, 'Failed to publish announcement.');
    }

    /**
     * Archive an announcement so members no longer see it.
     */
    public function archive(Announcement $announcement): void
    {
        $oldValues = $announcement->only(['status', 'archived_at']);

        $this->transaction(function () use ($announcement, $oldValues) {
            $announcement->update([
                'status' => 'archived',
                'archived_at' => now(),
            ]);

            $this->auditLog->log(
                'archived announcement',
                'announcements',
                'Announcement',
                $announcement->id,
                $oldValues,
                $announcement->fresh()->toArray()
            );
        }, 'Failed to archive announcement.');
    }

    public function delete(Announcement $announcement): void
    {
        $this->transaction(function () use ($announcement) {
            $oldValues = $announcement->toArray();
            $announcement->delete();

            $this->auditLog->log(
                'deleted announcement',
                'announcements',
                'Announcement',
                $announcement->id,
                $oldValues,
                null
            );
        }, 'Failed to delete announcement.');
    }

    private function validate(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'content' => ['required', 'string'],
            'type' => ['required', Rule::in(self::TYPES)],
            'priority' => ['required', Rule::in(['normal', 'important', 'emergency'])],
        ]);
    }
}