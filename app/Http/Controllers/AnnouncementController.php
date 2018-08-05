<?php

namespace App\Http\Controllers;

use Validator;
use App\Announcement;
use App\AnnouncementView;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use GenTux\Jwt\GetsJwtToken;

class AnnouncementController extends BaseController 
{
    use GetsJwtToken;
    /**
     * The request instance.
     *
     * @var \Illuminate\Http\Request
     */
    private $request;

    /**
     * Create a new controller instance.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    public function __construct(Request $request) {
        $this->request = $request;
    }

    public function index() {
        $payload = $this->jwtPayload();
        
        $announcements = Announcement::get()->sortByDesc('created_at')->values();

        if(isset($payload['context']['permission']) && $payload['context']['permission'] === 'member') {
            $member_id = $payload['context']['id'];

            $announcements->each(function($announcement) use($member_id) {
                $view = $announcement->views->where('member_id', $member_id)->first();
                unset($announcement->views);
                $announcement->view = $view;
            });
        }

        return response()->json($announcements);
    }
    
    public function create(Request $request) {
        $this->validate($request, [
            'content' => 'required',
        ]);

        $announcement = Announcement::create($request->all());

        return response()->json($announcement, 201);
    }

    public function update(Request $request, $id) {
        $announcement = Announcement::find($id);
        if($announcement) {
            $this->validate($request, [
                'content' => 'required',
            ]);
            
            $announcement->content = $request->input('content');
            $announcement->save();

            return response()->json($announcement);
        }
        else {
            return response(['error' => 'Not found announcement for ID '. $id], 404);
        }
    }

    public function read(Request $request, $id) {
        try {
            $payload = $this->jwtPayload();

            $announcementView = new AnnouncementView;
            $announcementView->member_id = $payload['context']['id'];
            $announcementView->announcement_id = $id;
            $announcementView->read_date = date('Y-m-d H:i:s');
            $announcementView->save();

            return response()->json($announcementView, 201);
        } catch(\Exception $e) {
            return response(['error' => 'Unable to save.'], 404);
        }
    }

    public function mutiread(Request $request) {
        $payload = $this->jwtPayload();

        $this->validate($request, [
            'ids' => 'required',
        ]);
        
        $ids = explode(',', $request->input('ids'));
        foreach($ids as $id) {
            try {
                $announcementView = new AnnouncementView;
                $announcementView->member_id = $payload['context']['id'];
                $announcementView->announcement_id = $id;
                $announcementView->read_date = date('Y-m-d H:i:s');
                $announcementView->save();
            } catch(\Exception $e) {
                // print($e->getMessage());
            }
        }
        
        return response('Done');
    }

    public function delete($id) {
        $announcement = Announcement::find($id);
        if($announcement) {
            $announcement->delete();
            return response('Deleted Successfully');
        }
        else {
            return response(['error' => 'Not found announcement for ID '. $id], 404);
        }
    }
}