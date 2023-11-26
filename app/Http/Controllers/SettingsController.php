<?php

namespace App\Http\Controllers;

use App\Classes\TdaSelf;
use App\Models\SettingModel;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    /**
     * Return some basic (non security sensitive) settings and version data.
     * Endpoint: GET /api/settings/about
     * @return mixed
     */
    public function about() {
        return response()->json(TdaSelf::describe(), 200);
    }


    /**
     * Add one setting as defined in API input.
     * Endpoint: POST /api/settings
     * @param Request $request
     * @return mixed
     */
    public function addSetting(Request $request)
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
     * Edit one existing setting by ID, defined in API input.
     * Endpoint: PUT /api/settings/{id}
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function editSetting(Request $request, $id)
    {
        $setting = SettingModel::findOrFail($id);

        $validatedData = $request->validate([
            'value' => 'nullable',
            'description' => 'nullable',
            'active' => 'boolean',
        ]);

        $setting->update($validatedData);

        return response()->json($setting, 200);
    }


    /**
     * Delete one setting by ID.
     * Endpoint: DELETE /api/settings/{id}
     * @param $id
     * @return mixed
     */
    public function deleteSetting($id)
    {
        $setting = SettingModel::findOrFail($id);
        $setting->delete();

        return response()->json(null, 204);
    }


    /**
     * Get one specific setting by ID.
     * Endpoint: GET /api/settings/{id}
     * @param $key
     * @return mixed
     */
    public function getSetting($id)
    {
        $setting = SettingModel::findOrFail($id);

        if (!$setting) {
            return response()->json(['message' => 'Setting not found'], 404);
        }

        return response()->json($setting, 200);
    }


    /**
     * Get all settings.
     * Endpoint: GET /api/settings
     * @return mixed
     */
    public function getAllSettings()
    {
        $settings = SettingModel::all();

        return response()->json($settings, 200);
    }
}
