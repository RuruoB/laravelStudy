<?php

namespace App\Http\Controllers;

use App\Handlers\ImageUploadHandler;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Requests\UserRequest;
use Illuminate\Support\Facades\Auth;

class UsersController extends Controller
{
	public function __construct()
	{
		$this->middleware('auth', ['except' => ['show']]);
	}

	public function show(User $user)
	{
		return view('users.show', compact('user'));
	}

	public function edit(User $user)
	{
		return view('users.edit', compact('user'));
	}

	public function update(UserRequest $request, ImageUploadHandler $uploadHandler, User $user)
	{
		try {
			$this->authorize('update', $user);
		} catch (AuthorizationException $e) {
			report($e);
			return redirect()->route('users.show', Auth::id())->with('danger', '违规操作！');
		}
		$data = $request->all();

		if ($request->avatar) {
			$result = $uploadHandler->save($request->avatar, 'avatars', $user->id, 362);
			if ($result) {
				$data['avatar'] = $result['path'];
			}
		}

		$user->update($data);
		return redirect()->route('users.show', $user->id)->with('success', '个人资料更新成功！');
	}
}