<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Services\AnnouncementService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AnnouncementController extends Controller
{
    public function __construct(private AnnouncementService $announcements)
    {
        // Access is restricted to admin/moderator via `role:` middleware (see routes/web.php).
    }

    /**
     * Management view: all announcements regardless of status.
     */
    public function index(): Response
    {
        return Inertia::render('Announcements/Manage', [
            'announcements' => $this->announcements->list(),
        ]);
    }

    /**
     * Create an announcement as a draft, or publish it immediately
     * when the "publish" flag is sent.
     */
    public function store(Request $request)
    {
        $publish = $request->boolean('publish');

        $this->announcements->create($request, $request->user());

        return back()->with(
            'success',
            $publish ? 'Announcement published successfully.' : 'Draft saved successfully.'
        );
    }

    public function update(Request $request, Announcement $announcement)
    {
        $this->announcements->update($request, $announcement);

        return back()->with('success', 'Announcement updated successfully.');
    }

    /**
     * Publish a draft (or re-publish an archived) announcement.
     */
    public function publish(Announcement $announcement)
    {
        $this->announcements->publish($announcement);

        return back()->with('success', 'Announcement published successfully.');
    }

    /**
     * Archive an announcement so members no longer see it.
     */
    public function archive(Announcement $announcement)
    {
        $this->announcements->archive($announcement);

        return back()->with('success', 'Announcement archived successfully.');
    }

    public function destroy(Announcement $announcement)
    {
        $this->announcements->delete($announcement);

        return redirect()->route('announcements.manage')->with('success', 'Announcement deleted successfully.');
    }
}