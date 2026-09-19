<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Repositories\AuthRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    protected $repository;

    public function __construct(AuthRepository $repository)
    {
        $this->repository = $repository;
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        return $this->repository->register($request);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        return $this->repository->login($request);
    }

    public function user(Request $request): JsonResponse
    {
        return $this->repository->user($request);
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        return $this->repository->forgotPassword($request);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        return $this->repository->resetPassword($request);
    }

    public function logout(Request $request): JsonResponse
    {
        return $this->repository->logout($request);
    }
}
