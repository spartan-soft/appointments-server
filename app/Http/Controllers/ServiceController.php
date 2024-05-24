<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\User;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    /**
     * get Services
     */
    public function index()
    {
        $services = auth()->user()->services;
        return response()->json($services);
    }

    /**
     * Create Service
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $service = Service::create($request->all());
        auth()->user()->services()->attach($service->id);

        return response()->json([
            'status' => true,
            'message' => 'Service created successfully',
            'data' => $service,
        ]);
    }

    /**
     * Get Services for ID and all
     */

    public function show($id)
    {
        $service = Service::findOrFail($id);

        return response()->json($service);
    }

    /**
     *
     */

    public function updateUserServices(Request $request, $userId)
    {
        // Validar los datos recibidos
        $request->validate([
            'service_ids' => 'required|array',
            'service_ids.*' => 'exists:services,id',
        ]);

        // Buscar al usuario por su ID
        $user = User::findOrFail($userId);

        // Actualizar los servicios asociados al usuario
        $user->services()->sync($request->service_ids);

        return response()->json([
            'status' => true,
            'message' => 'User services updated successfully',
            'services' => $user->services,
        ]);
    }

    /**
     * Update Service
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $service = Service::findOrFail($id);
        $service->update($request->all());

        return response()->json([
            'status' => true,
            'message' => 'Service updated successfully',
            'data' => $service,
        ]);
    }

    /**
     * Associate Service to User
     */
    public function associateUserToService(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'service_id' => 'required|exists:services,id',
        ]);

        $user = User::findOrFail($request->user_id);
        $service = Service::findOrFail($request->service_id);

        $user->services()->attach($service->id);

        return response()->json([
            'status' => true,
            'message' => 'User associated with service successfully',
        ]);
    }

    /**
     * get user services
     */
    public function getUserServices($userId)
    {
        // Buscar al usuario por su ID
        $user = User::findOrFail($userId);

        // Obtener los servicios asociados al usuario
        $services = $user->services;

        return response()->json([
            'status' => true,
            'services' => $services,
        ]);
    }

    /**
     * get service users
     */
    public function getServiceUsers($serviceId)
    {
        // Buscar el servicio por su ID
        $service = Service::findOrFail($serviceId);

        // Obtener los usuarios asociados al servicio
        $users = $service->users;

        return response()->json([
            'status' => true,
            'users' => $users,
        ]);
    }



    /**
     * Delete Service
     */
    public function destroy($id)
    {
        $service = Service::findOrFail($id);
        auth()->user()->services()->detach($id);
        $service->delete();

        return response()->json([
            'status' => true,
            'message' => 'Service deleted successfully',
        ]);
    }
}
