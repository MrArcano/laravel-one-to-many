<?php

namespace App\Http\Controllers\Admin;

use App\Functions\Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProjectRequest;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $projects = Project::orderBy("id","desc")->paginate(10);
        return view('admin.projects.index', compact('projects'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $title = 'Admin Project - Create';
        $method = 'POST';
        $project = null;
        $route = route('admin.project.store');
        return view('admin.projects.create_edit', compact('project','route','method','title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ProjectRequest $request)
    {
        $form_data = $request->all();

        $form_data['slug'] = Helper::generateSlug($form_data['name'] , Project::class);
        // nel form_data inserisco il nome dell'immagine
        $form_data['image_name'] = $request->file('image')->getClientOriginalName();
        // verifico se esiste l'immagine
        if(array_key_exists('image', $form_data)){
            $img_path = Storage::put('uploads', $form_data['image']);
            $form_data['image'] = $img_path;
        }

        $project = new Project();
        $project->fill($form_data);
        $project->save();
        return redirect()->route('admin.project.show', $project )->with('success','Creazione avvenuta con successo!');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Project $project)
    {
        return view('admin.projects.show', compact('project'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Project $project)
    {
        $title = 'Admin Project - Edit';
        $method = 'PUT';
        $route = route('admin.project.update', $project);
        return view('admin.projects.create_edit', compact('project','route','method','title'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(ProjectRequest $request, Project $project)
    {
        $form_data = $request->all();
        if($project->name === $form_data['name']){
            $form_data['slug'] = $project->slug;
        }else{
            $form_data['slug'] = Helper::generateSlug($form_data['name'] , Project::class);
        }

        // controllo che nei miei dati ricevuti dal form sia stata aggiunta un'immagine
        if(array_key_exists('image', $form_data)){
            if($project->image){
                // cancello la vecchia immmagine se esiste
                Storage::delete($project->image);
            }
            // inserisco la nuova immagine
            $form_data['image_name'] = $request->file('image')->getClientOriginalName();
            $img_path = Storage::put('uploads', $form_data['image']);
            $form_data['image'] = $img_path;
        }

        $project->update($form_data);
        return redirect()->route('admin.project.show', $project)->with('success','Modificato con successo!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Project $project)
    {
        if($project->image){
            // cancello la vecchia immmagine se esiste
            Storage::delete($project->image);
        }
        $project->delete();
        return redirect()->route('admin.project.index')->with('success','Progetto cancellato definitivamente!');
    }

    public function destroy_image(Project $project){
        if($project->image){
            // cancello la vecchia immmagine se esiste
            Storage::delete($project->image);
        }
        // resetto i dati nel db
        $form_data['image_name'] = null;
        $form_data['image'] = null;

        $project->update($form_data);

        return redirect()->route('admin.project.show', $project)->with('success','Immagine eliminata!');
    }
}
