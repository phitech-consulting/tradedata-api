<?php

namespace App\Http\Controllers;

use App\Classes\TdaSelf;
use App\Models\SettingModel;
use Illuminate\Http\Request;

class SettingsController extends Controller
{

    /**
     * Return some basic (non security sensitive) settings and version data.
     * Route: GET /api/settings/about
     * @return mixed
     */
    public function about() {
        return response()->json(TdaSelf::describe(), 200);
    }


    /**
     * Display a listing of the resource.
     * Route: GET /api/settings
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $settings = SettingModel::all();
        return response()->json($settings, 200);
    }


    /**
     * Store a newly created resource in storage.
     * Route: POST /api/settings
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'key' => 'required|unique:settings',
                'value' => 'nullable',
                'description' => 'nullable',
                'active' => 'boolean',
            ]);

            $setting = SettingModel::create($validatedData);

            return response()->json($setting, 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => $e->validator->errors()->first()], 422);
        }
    }


    /**
     * Display the specified resource.
     * Route: GET /api/settings/{id}
     * @param  \App\Models\SettingModel  $setting
     * @return \Illuminate\Http\Response
     */
    public function show(SettingModel $setting)
    {
        if (!$setting) {
            return response()->json(['message' => 'Setting not found'], 404);
        }

        return response()->json($setting, 200);
    }


    /**
     * Update the specified resource in storage.
     * Route: PUT /api/settings/{id}
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\SettingModel  $setting
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, SettingModel $setting)
    {
        $validatedData = $request->validate([
            'value' => 'nullable',
            'description' => 'nullable',
            'active' => 'boolean',
        ]);

        $setting->update($validatedData);

        return response()->json($setting, 200);
    }


    /**
     * Remove the specified resource from storage.
     * Route: DELETE /api/settings/{id}
     * @param  \App\Models\SettingModel  $setting
     * @return \Illuminate\Http\Response
     */
    public function destroy(SettingModel $setting)
    {
        $setting->delete();

        return response()->json(null, 204);
    }
}
