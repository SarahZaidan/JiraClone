<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Task;
use App\Models\Project;
use App\Models\Section;
use App\Models\UserProject;
use App\Models\Assignment;
use Validator;
use Socialite;
class UserController extends Controller
{

   public function removeUserFromProject(Request $request){
    //validate request
        $validator = Validator::make($request->all(), [
            'user_data' => 'required',
            'user_id' => 'required',
            'project_id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => "Failed",
                'message' => 'Validation Error',
                'errors' => $validator->errors(),
            ],400);
        }
        $user_project = UserProject::where('user_id',$request->user_id)->where('project_id',$request->project_id)->first();
        if(!$user_project){
            return response()->json([
                'status' => "Failed",
                'message' => 'User not found in project',
                'user_project' => $user_project,
            ],400);
        }
        $user_project->delete();
        return response()->json([
            'status' => "Success",
            'message' => 'User removed from project',
        ],201);
   }

    //get users of a project
    public function getUsersOfProject(Request $request){
         $project = Project::where('id',$request->project_id)->first();
         if(! $project){
              return response()->json([
                'status' => "Failed",
                'message' => 'Project not found',
              ],400);
         }
         //get users of project from user_project table
        $user_projects = UserProject::where('project_id',$request->project_id)->get();
        $users = [];
        foreach($user_projects as $user_project){
            $user = User::where('id',$user_project->user_id)->first();
            array_push($users,[
                'id' => $user->id,
                'name' => $user->name,
                'role' => $user_project->role,
                'image' => $user->image,
            ]);
        }
         return response()->json([
              'status' => "Success",
              'message' => 'Users of project',
              'users' => $users,
         ],201);
    }
    //get all users except users of a project
    public function getUsersExceptProject(Request $request){
        $project = Project::where('id',$request->project_id)->first();
        if(! $project){
             return response()->json([
               'status' => "Failed",
               'message' => 'Project not found',
             ],400);
        }
        //get users of project from user_project table
         $user_projects = UserProject::where('project_id',$request->project_id)->get();
            $users = [];
            foreach($user_projects as $user_project){
                $user = User::where('id',$user_project->user_id)->first();
                array_push($users,[
                    'id' => $user->id,
                    'name' => $user->name,
                    'role' => $user_project->role,
                    'image' => $user->image,
                ]);
            }
        //get all users
        $all_users = User::all();
        $users_except_project = [];
        foreach($all_users as $user){
            $flag = 0;
            foreach($users as $user_project){
                if($user->id == $user_project['id']){
                    $flag = 1;
                }
            }
            if($flag == 0){
                array_push($users_except_project,[
                    'id' => $user->id,
                    'name' => $user->name,
                    'image' => $user->image,
                ]);
            }
        }
        return response()->json([
            'status' => "Success",
            'message' => 'Users of project',
            'users' => $users_except_project,
         ],201);
    }
    //add user to project
    public function addUserToProject(Request $request){
        //validate request
        $validator = Validator::make($request->all(), [
            'user_data' => 'required',
            'user_id' => 'required',
            'project_id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => "Failed",
                'message' => 'Validation Error',
                'errors' => $validator->errors(),
            ],400);
        }
        $user_project = UserProject::where('user_id',$request->user_id)->where('project_id',$request->project_id)->first();
        if($user_project){
            return response()->json([
                'status' => "Failed",
                'message' => 'User already in project',
                'user_project' => $user_project,
            ],400);
        }
        $user_project = new UserProject();
        $user_project->user_id = $request->user_id;
        $user_project->project_id = $request->project_id;
        $user_project->role = 2;
        $user_project->save();
        return response()->json([
            'status' => "Success",
            'message' => 'User added to project',
        ],201);
    }

}
