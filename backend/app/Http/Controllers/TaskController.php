<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use App\Models\Task;
use App\Models\Section;
use App\Models\Project;
use App\Models\User;
use App\Models\Status;
use Illuminate\Support\Str;


class TaskController extends Controller
{
    //get task by id
    public function getTask(Request $request){
        $validator = Validator::make($request->all(),[
            'user_data'=>'required',
            'task_id' =>'required',
        ]);
        if($validator->fails()){
            return response()->json($validator->errors()->ToJson(),400);
        }
        $task = Task::where('id',$request->task_id)->first();
        if(! $task){
            return response()->json([
                'status' => "Failed",
                'message' => 'Task not found',
            ],400);
        }
        return response()->json([
            'status' => "Success",
            'message' => 'Task found',
            'task' => $task
        ],201);
    }

    //get all tasks of a section
    public function getTasksBySectionId(Request $request){
        $section = Section::where('id',$request->section_id)->first();
        if(! $section){
            return response()->json([
                'status' => "Failed",
                'message' => 'Section not found',
            ],400);
        }
        $tasks = $section->tasks()->get();

        $todo = [];
        $in_progress = [];
        $completed = [];
        foreach($tasks as $task){
            if($task->status_id == 1){
                array_push($todo,$task);
            } elseif($task->status_id == 2){
                array_push($in_progress,$task);
            } elseif($task->status_id == 3){
                array_push($completed,$task);
            }
        }
        $tasks_sorted = [
            'todo' => $todo,
            'in_progress' => $in_progress,
            'completed' => $completed
        ];
        return response()->json([
            'status' => "Success",
            'message' => 'Tasks found',
            'tasks' => $tasks_sorted
        ],201);
    }

    //add new task
    public function addTask(Request $request){
        $validator = Validator::make($request->all(),[
            'user_data'=>'required',
            'description'=> 'required',
            'start_time' =>'required|date',
            'due_time' =>'required|date',
            'priority' =>'required',
            'section_id' =>'required'

        ]);
        if($validator->fails()){
            return response()->json($validator->errors()->ToJson(),400);
        }

        $section = Section::where('id',$request->section_id)->first();
        $project= $section->project()->get()->first();

        $task = Task::create([
            'description'=> $request->description,
            'start_time' =>$request->start_time,
            'due_time' => $request->end_time,
            'priority' => $request->priority,
            'project_id' => $project->id,
            'section_id' => $request->section_id,
            'status_id' => 1
        ]);

        return response()->json([
            'message' => 'Section added successfully',
            'task' => $task
        ],201);
    }

    //edit task api function
    public function editTask(Request $request){
        $validator = Validator::make($request->all(),[
            'user_data'=>'required',
            'task_id' =>'required',
        ]);
        if($validator->fails()){
            return response()->json($validator->errors()->ToJson(),400);
        }
        $task = Task::where('id',$request->task_id)->first();
        if(! $task){
            return response()->json([
                'status' => "Failed",
                'message' => 'Task not found',
            ],400);
        }
        if($request->description != null){
            $task->description = $request->description;
        }
        if($request->start_time != null){
            $task->start_time = $request->start_time;
        }
        if($request->due_time != null){
            $task->due_time = $request->due_time;
        }
        if($request->priority != null){
            $task->priority = $request->priority;
        }
        if($request->status_id != null){
            $task->status_id = $request->status_id;
        }
        if($task->start_time >= $task->due_time){
            return response()->json([
                'status' => "date fail",
            ]);
        }
        $task->save();
        //if all tasks are completed then change the status of section to completed
        //if status_id is 3 then task is completed
        if($request->status_id == 3){
            $section = Section::where('id',$task->section_id)->first();
            $tasks = $section->tasks()->get();
            $completed_tasks = $section->tasks()->where('status_id',3)->get();
            if(count($tasks) == count($completed_tasks)){
                $section->status_id = 3;
                $section->save();
                //if all sections are completed then change the status of project to completed
                $project = Project::where('id',$section->project_id)->first();
                $sections = $project->sections()->get();
                $completed_sections = $project->sections()->where('status_id',3)->get();
                if(count($sections) == count($completed_sections)){
                    $project->status_id = 3;
                    $project->save();
                }
            }
        } elseif($request->status_id == 2){
            $section = Section::where('id',$task->section_id)->first();
            if($section->status_id != 2){
                $section->status_id = 2;
                $section->save();
                $project = Project::where('id',$section->project_id)->first();
                if($project->status_id != 2){
                    $project->status_id = 2;
                    $project->save();
                }
            }
        }
        return response()->json([
            'status' => "Success",
            'message' => 'Task updated',
            'task' => $task
        ],201);
    }

    //delete task api function
    public function deleteTask(Request $request){
        $task = Task::where('id',$request->task_id)->first();
        if(! $task){
            return response()->json([
                'status' => "Failed",
                'message' => 'Task not found',
            ],400);
        }
        $task->delete();
        return response()->json([
            'status' => "Success",
            'message' => 'Task deleted',
        ],201);
    }

    //assign task to user
    public function assignTask(Request $request){
        $validator = Validator::make($request->all(),[
            'user_data'=>'required',
            'task_id' =>'required',
            'user_id' =>'required',
        ]);
        if($validator->fails()){
            return response()->json($validator->errors()->ToJson(),400);
        }
        $task = Task::where('id',$request->task_id)->first();
        if(! $task){
            return response()->json([
                'status' => "Failed",
                'message' => 'Task not found',
            ],400);
        }
        $user = User::where('id',$request->user_id)->first();
        if(! $user){
            return response()->json([
                'status' => "Failed",
                'message' => 'User not found',
            ],400);
        }
        $assignment = Assignment::create([
            'task_id' => $request->task_id,
            'user_id' => $request->user_id,
        ]);
        return response()->json([
            'status' => "Success",
            'message' => 'Task assigned',
            'assignment' => $assignment
        ],201);
    }

    //unasign user to task
    public function unassignTask(Request $request){
        $validator = Validator::make($request->all(),[
            'user_data'=>'required',
            'task_id' =>'required',
            'user_id' =>'required',
        ]);
        if($validator->fails()){
            return response()->json($validator->errors()->ToJson(),400);
        }
        $task = Task::where('id',$request->task_id)->first();
        if(! $task){
            return response()->json([
                'status' => "Failed",
                'message' => 'Task not found',
            ],400);
        }
        $user = User::where('id',$request->user_id)->first();
        if(! $user){
            return response()->json([
                'status' => "Failed",
                'message' => 'User not found',
            ],400);
        }
        $assignment = Assignment::where('task_id',$request->task_id)->where('user_id',$request->user_id)->first();
        if(! $assignment){
            return response()->json([
                'status' => "Failed",
                'message' => 'Assignment not found',
            ],400);
        }
        $assignment->delete();
        return response()->json([
            'status' => "Success",
            'message' => 'Task unassigned',
        ],201);
    }

    public function sortAndUpdate(Request $request){
        $validator = Validator::make($request->all(),[
            'user_data'=>'required',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->ToJson(),400);
        }
        if(Str::length($request->todo) > 0){
            $todo = explode(',',$request->todo);
        } else {
            $todo = [];
        }
        if(Str::length($request->in_progress) > 0){
            $in_progress = explode(',',$request->in_progress);
        } else {
            $in_progress = [];
        }
        if(Str::length($request->done) > 0){
            $done = explode(',',$request->done);
        } else {
            $done = [];
        }

        $i=0;
        foreach($todo as $task_id){
            $task_id = (int)$task_id;
            $task = Task::where('id',$task_id)->first();
            $task->status_id = 1;
            $task->index = $i;
            $task->save();
            $i++;
        }
        $i=0;
        foreach($in_progress as $task_id){
            $task_id = (int)$task_id;
            $task = Task::where('id',$task_id)->first();
            $task->status_id = 2;
            $task->index = $i;
            $task->save();
            $i++;
        }
        $i=0;
        foreach($done as $task_id){
            $task_id = (int)$task_id;
            $task = Task::where('id',$task_id)->first();
            $task->status_id = 3;
            $task->index = $i;
            $task->save();
            $i++;
        }

        return response()->json([
            'status' => "Success",
            'message' => 'Tasks updated',
        ],201);

    }
}
