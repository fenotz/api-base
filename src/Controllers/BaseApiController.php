<?php

namespace Fenox\ApiBase\Controllers;

use Fenox\ApiBase\Helpers\ResponseHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class BaseApiController extends Controller
{
    protected $model;
    protected string $sortBy = 'id'; // Por defecto, si no se define en el controlador hijo
    protected int $paginate = 15; // Valor por defecto para la paginación
    protected $storeRequest;
    protected $updateRequest;


    /**
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        if ($this->paginate > 0) {
            $results = $this->model::orderBy($this->sortBy)
                ->paginate($this->paginate);
        } else {
            $results = $this->model::orderBy($this->sortBy)
                ->get();  // Obtener todos los registros sin paginación
        }
        return ResponseHelper::success($results, 'List retrieved successfully');

    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validated = app($this->storeRequest)->validated();
        $record = $this->model::create($validated);

        return ResponseHelper::success($record, 'Record created successfully', 201);
    }


    /**
     * @param $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        $record = $this->model::findOrFail($id);

        return ResponseHelper::success($record, 'Record retrieved successfully');
    }

    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function update(Request $request, $id): JsonResponse
    {
        $validated = app($this->updateRequest)->validated();
        $record = $this->model::findOrFail($id);
        $record->update($validated);

        return ResponseHelper::success($record, 'Record updated successfully');
    }

    /**
     * @param $id
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        $record = $this->model::findOrFail($id);
        $record->delete();

        return ResponseHelper::success([], 'Record deleted successfully');
    }
}

