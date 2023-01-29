<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Task;
use App\Models\Project;
use App\Models\Section;
use App\Models\UserProject;
use Validator;

class SectionController extends Controller
{

    //get section by id
    public function getSection(Request $request){
        if($request->section_id == null){
            return response()->json([
                'status' => "Failed",
                'message' => 'Section id is required',
            ],400);
        }
        $section = Section::where('id',$request->section_id)->first();
        if(! $section){
            return response()->json([
                'status' => "Failed",
                'message' => 'Section not found',
            ],400);
        }
        return response()->json([
            'status' => "Success",
            'message' => 'Section found',
            'section' => $section
        ],200);
    }

    //get all sections in a project
    public function getSections(Request $request){
        if($request->project_id == null){
            return response()->json([
                'status' => "Failed",
                'message' => 'Project id is required',
            ],400);
        }
        $sections = Section::where('project_id',$request->project_id)->get();
        if(! $sections){
            return response()->json([
                'status' => "Failed",
                'message' => 'Sections not found',
            ],400);
        }
        return response()->json([
            'status' => "Success",
            'message' => 'Sections found',
            'sections' => $sections
        ],200);
    }

    //add new section
    public function addSection(Request $request){
        $validator = Validator::make($request->all(),[
            'user_data'=>'required',
            'name'=> 'required',
            'project_id' =>'required'
        ]);
        if($validator->fails()){
            return response()->json($validator->errors()->ToJson(),400);
        }

        $section = Section::create([
            'name' => $request->name,
            'project_id' => $request->project_id,
            'status_id' => 1
        ]);

        return response()->json([
            'message' => 'Section added successfully',
            'section' => $section
        ],201);
    }


    //edit section api function
    public function editSection(Request $request){
        $validator = Validator::make($request->all(),[
            'user_data'=>'required',
            'section_id' =>'required',
        ]);
        if($validator->fails()){
            return response()->json($validator->errors()->ToJson(),400);
        }
        $section = Section::where('id',$request->section_id)->first();
        if(! $section){
            return response()->json([
                'status' => "Failed",
                'message' => 'Section not found',
            ],400);
        }
        if($request->name != null){
            $section->name = $request->name;
        }
        if($request->status_id != null){
            $section->status_id = $request->status_id;
        }
        $section->save();

        //if status_id is changed to 3 then update all tasks in this section to 3
        if($request->status_id == 3){
            $tasks = $section->tasks()->get();
            foreach($tasks as $task){
                $task->status_id = 3;
                $task->save();
            }
            //check all sections in this project if all are 3 then update project status to 3
            $project = $section->project()->first();
            $sections = $project->sections()->get();
            $all_completed = true;
            foreach($sections as $section){
                if($section->status_id != 3){
                    $all_completed = false;
                    break;
                }
            }
            if($all_completed){
                $project->status_id = 3;
                $project->save();
            }
        }

        return response()->json([
            'status' => "Success",
            'message' => 'Section updated',
            'section' => $section
        ],201);
    }

    //delete section api function
    public function deleteSection(Request $request){
        //if section id is not provided
        if($request->section_id == null){
            return response()->json([
                'status' => "Failed",
                'message' => 'Section id is required',
            ],400);
        }
        $section = Section::where('id',$request->section_id)->first();
        if(! $section){
            return response()->json([
                'status' => "Failed",
                'message' => 'Section not found',
            ],400);
        }
        $section->delete();
        return response()->json([
            'status' => "Success",
            'message' => 'Section deleted',
        ],201);
    }
}
