<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Task;
use App\Models\Project;
use App\Models\Section;
use App\Models\UserProject;
use Validator;

class ProjectController extends Controller
{
    //add new project
    public function addProject(Request $request){
        $validator = Validator::make($request->all(),[
            'user_data'=>'required',
            'name'=> 'required',
            'start_time' =>'required|date',
            'due_time' =>'required|date'
        ]);

        if($validator->fails() || $request->start_time >= $request->due_time){
            return response()->json($validator->errors()->ToJson(),400);
        }


        //create project
        $project = Project::create([
            'name' => $request->name,
            'start_time' => $request->start_time,
            'due_time' => $request->due_time,
            'status_id' => 1,
        ]);
        //add user to project
        $user_project = UserProject::create([
            'role' => 1,
            'user_id' =>$request->user_data->id,
            'project_id' =>$project->id,
        ]);
        return response()->json([
            'message' => 'Project added successfully',
            'user_project' => $user_project,
            'project' => $project
        ],201);
    }

    //edit project
    public function editProject(Request $request){
        $validator = Validator::make($request->all(),[
            'user_data'=>'required',
            'project_id' =>'required',
        ]);
        if($validator->fails()){
            return response()->json($validator->errors()->ToJson(),400);
        }
        $project = Project::where('id',$request->project_id)->first();
        if(! $project){
            return response()->json([
                'status' => "Failed",
                'message' => 'Project not found',
            ]);
        }
        if($request->name != null){
            $project->name = $request->name;
        }
        if($request->start_time != null){
            $project->start_time = $request->start_time;
        }
        if($request->due_time != null){
            $project->due_time = $request->due_time;
        }
        if($request->status_id != null){
            $project->status_id = $request->status_id;
            //if project status is done, change all sections status to done
            if($request->status_id == 3){
                $sections = $project->sections()->get();
                foreach($sections as $section){
                    $section->status_id = 3;
                    $section->save();
                    //change all tasks status to done
                    $tasks = $section->tasks()->get();
                    foreach($tasks as $task){
                        $task->status_id = 3;
                        $task->save();
                    }
                }
            }

        }
        if($project->start_time >= $project->due_time){
            return response()->json([
                'status' => "Fail",
            ]);
        }
        $project->save();
        return response()->json([
            'status' => "Success",
            'message' => 'Project updated',
            'project' => $project
        ]);

    }

    public function getProject(Request $request){

        $user = User::where('id',$request->user_data->id)->first();
        if($request->project_id != null){
            $project = Project::where('id',$request->project_id)->first();
            $project = [
                'id' => $project->id,
                'name' => $project->name,
                'start_time' => $project->start_time,
                'due_time' => $project->due_time,
                'status' => $project->status()->first(),
            ];
            //get project leader that have user_project role 1
            $user_project = UserProject::where('project_id',$request->project_id)->where('role',1)->first();
            $user = User::where('id',$user_project->user_id)->first();
            $project['leader'] = $user->name;
            //get project members
            $user_projects = UserProject::where('project_id',$request->project_id)->get();
            $members = [];

            foreach($user_projects as $user_project){
                $user = User::where('id',$user_project->user_id)->first();
                $members[] = $user;
            }
            $project['members'] = $members;
            //number of members
            $project['members_count'] = count($members);

            //get project sections
            $sections = Section::where('project_id',$request->project_id)->get();
            $project['sections'] = $sections;
            $project['sections_count'] = count($sections);
            //get project tasks
            $tasks = Task::where('project_id',$request->project_id)->get();
            $project['tasks_count'] = count($tasks);
            $project['tasks'] = $tasks;
            return response()->json([
                'status' => "Success",
                'message' => 'Project found',
                'project' => $project,
            ],201);
        }
        $user_projects= $user->userProjects()->get();
        $projects =[];

        foreach($user_projects as $user_project){
            $project = $user_project->project()->first();
            $status = $project->status()->first();
            $project->status = $status;
            $projects[] = $project;
        }
        //get project leader that have user_project role 1
        foreach($projects as $project){
            $user_project = UserProject::where('project_id',$project->id)->where('role',1)->first();
            $user = User::where('id',$user_project->user_id)->first();
            $project->leader = $user;
        }

        return response()->json([
            'status' => "Success",
            'message' => 'Projects found',
            'projects' => $projects,
        ],201);
    }

    //get projects that user is manager of
    public function getManagerProjects(Request $request){
        $user = User::where('id',$request->user_data->id)->first();
        $user_projects= $user->userProjects()->get();
        $projects =[];
        foreach($user_projects as $user_project){
            if($user_project->role == 1){
                $project = $user_project->project()->first();
                $status = $project->status()->first();
                $project->status = $status;
                $projects[] = $project;
            }
        }
        //get project leader that have user_project role 1
        foreach($projects as $project){
            $user_project = UserProject::where('project_id',$project->id)->where('role',1)->first();
            $user = User::where('id',$user_project->user_id)->first();
            $project->leader = $user;
        }
        //calculate project progress
        foreach($projects as $project){
            $sections = $project->sections()->get();
            $sections_count = count($sections);
            $done_sections_count = 0;
            foreach($sections as $section){
                if($section->status_id == 3){
                    $done_sections_count++;
                }
            }
            if($sections_count != 0){
                $project->progress = ($done_sections_count/$sections_count)*100;
            }
            else{
                $project->progress = 0;
            }
        }
        return response()->json([
            'status' => "Success",
            'message' => 'Projects found',
            'projects' => $projects,
        ],201);
    }

    //get projects that user is member of
    public function getMemberProjects(Request $request){
        $user = User::where('id',$request->user_data->id)->first();
        $user_projects= $user->userProjects()->get();
        $projects =[];
        foreach($user_projects as $user_project){
            if($user_project->role == 2){
                $project = $user_project->project()->first();
                $status = $project->status()->first();
                $project->status = $status;
                $projects[] = $project;
            }
        }
        //get project leader that have user_project role 1
        foreach($projects as $project){
            $user_project = UserProject::where('project_id',$project->id)->where('role',1)->first();
            $user = User::where('id',$user_project->user_id)->first();
            $project->leader = $user;
        }
        return response()->json([
            'status' => "Success",
            'message' => 'Projects found',
            'projects' => $projects,
        ],201);
    }
    //get manager of project
    public function getProjectManager(Request $request){
        //find project
        $project = Project::where('id',$request->project_id)->first();
        //get the leader
        $user_project = UserProject::where('project_id',$project->id)->where('role',1)->first();
        $user = User::where('id',$user_project->user_id)->first();
        //make array of id name and image
        $user = [
            'id' => $user->id,
            'name' => $user->name,
            'image' => $user->image,
        ];
        return response()->json([
            'status' => "Success",
            'message' => 'Project manager found',
            'user' => $user,
        ],201);
    }
    //get project members
    public function getProjectMembers(Request $request){
        //find project
        $project = Project::where('id',$request->project_id)->first();
        //get the members
        $user_projects = UserProject::where('project_id',$project->id)->where('role',2)->get();
        $users = [];
        foreach($user_projects as $user_project){
            $user = User::where('id',$user_project->user_id)->first();
            $user = [
                'id' => $user->id,
                'name' => $user->name,
                'image' => $user->image,
            ];
            $users[] = $user;
        }
        return response()->json([
            'status' => "Success",
            'message' => 'Project members found',
            'users' => $users,
        ],201);
    }

    public function deleteProject(Request $request){
        $validator = Validator::make($request->all(),[
            'user_data'=>'required',
        ]);
        if($validator->fails()){
            return response()->json($validator->errors()->ToJson(),400);
        }
        if(! $request->project_id){
            return response()->json([
                'status' => "Failed",
                'message' => 'Project id not found',
            ]);
        }
        $project = Project::where('id',$request->project_id)->first();
        if(! $project){
            return response()->json([
                'status' => "Failed",
                'message' => 'Project not found',
            ]);
        }
        //delete all sections and tasks
        $sections = $project->sections()->get();
        foreach($sections as $section){
            $tasks = $section->tasks()->get();
            foreach($tasks as $task){
                $task->delete();
            }
            $section->delete();
        }
        $project->delete();
        return response()->json([
            'status' => "Success",
            'message' => 'Project deleted',
        ],201);

    }
    //remove user from project
    public function removeUserFromProject(Request $request){
        $validator = Validator::make($request->all(),[
            'user_data'=>'required',
            'project_id' =>'required',
            'user_id' =>'required',
        ]);
        if($validator->fails()){
            return response()->json($validator->errors()->ToJson(),400);
        }
        $user_project = UserProject::where('user_id',$request->user_id)->where('project_id',$request->project_id)->first();
        if(! $user_project){
            return response()->json([
                'status' => "Failed",
                'message' => 'User not found in project',
            ]);
        }
        $user_project->delete();
        return response()->json([
            'status' => "Success",
            'message' => 'User removed from project',
        ],201);
    }


}
