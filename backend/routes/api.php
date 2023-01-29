<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\GoogleController;


Route::group([ 'prefix' => 'auth' ], function () {
    Route::post('register',[AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::group(['middleware' => 'CORS'], function () {
        Route::group(['middleware' => ['web']], function () {
            Route::get('google', [AuthController::class, 'redirectToGoogle']);
        });
    });

});


Route::group([ 'prefix' => 'user' ], function () {
    Route::group([ 'middleware' => 'userJWT' ], function () {
        Route::get('tasks/{section_id}',[UserController::class, 'getTasksBySectionId']);
        Route::get('users/{project_id}',[UserController::class, 'getUsersOfProject']);
        Route::get('getUsers/{project_id}',[UserController::class, 'getUsersExceptProject']);
        Route::post('deleteuser',[UserController::class, 'removeUserFromProject']);
        Route::post('adduser',[UserController::class, 'addUserToProject']);
    });
});

Route::group([ 'middleware' => 'userJWT' ], function () {
    Route::get('project/{project_id?}',[ProjectController::class, 'getProject']);
    Route::get('memberProjects',[ProjectController::class, 'getMemberProjects']);
    Route::get('managerProjects',[ProjectController::class, 'getManagerProjects']);
    Route::get('projectManger/{project_id}',[ProjectController::class, 'getProjectManager']);
    Route::get('projectMembers/{project_id}',[ProjectController::class, 'getProjectMembers']);
    Route::post('project',[ProjectController::class, 'addProject']);
    Route::put('project',[ProjectController::class, 'editProject']);
    Route::delete('project/{project_id}',[ProjectController::class, 'deleteProject']);
});

Route::group([ 'middleware' => 'userJWT' ], function () {
    Route::get('section/{section_id?}',[SectionController::class, 'getSection']);
    Route::get('sections/{project_id}',[SectionController::class, 'getSections']);
    Route::post('section',[SectionController::class, 'addSection']);
    Route::put('section',[SectionController::class, 'editSection']);
    Route::delete('section/{section_id}',[SectionController::class, 'deleteSection']);
});

route::group([ 'middleware' => 'userJWT' ], function () {
    Route::get('task/{task_id?}',[TaskController::class, 'getTask']);
    Route::get('tasks/{section_id}',[TaskController::class, 'getTasksBySectionId']);
    Route::post('task',[TaskController::class, 'addTask']);
    Route::post('tasks/reorder',[TaskController::class, 'sortAndUpdate']);
    Route::put('task',[TaskController::class, 'editTask']);
    Route::delete('task/{task_id}',[TaskController::class, 'deleteTask']);
});







