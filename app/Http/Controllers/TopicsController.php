<?php

namespace App\Http\Controllers;

use App\Handlers\ImageUploadHandler;
use App\Models\Category;
use App\Models\Topic;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Auth;
use App\Http\Requests\TopicRequest;

class TopicsController extends Controller
{
	public function __construct()
	{
		$this->middleware('auth', ['except' => ['index', 'show']]);
	}

	public function index(Request $request, Topic $topic)
	{
		$topics = $topic->withOrder($request->order)->paginate(20);
		return view('topics.index', compact('topics'));
	}

	public function show(Topic $topic)
	{
		return view('topics.show', compact('topic'));
	}

	public function create(Topic $topic)
	{
		$categories = Category::all();
		return view('topics.create_and_edit', compact('topic', 'categories'));
	}

	public function store(TopicRequest $request, Topic $topic)
	{
		$topic->fill($request->all());
		$topic->user_id = Auth::id();
		$topic->save();

		return redirect()->route('topics.show', $topic->id)->with('success', '成功创建主题!');
	}

	public function edit(Topic $topic)
	{
		try {
			$this->authorize('update', $topic);
		} catch (AuthorizationException $e) {
			report($e);
			return redirect()->route('topics.show', $topic->id)->with('danger', '敏感操作!');
		};
		$categories = Category::all();
		return view('topics.create_and_edit', compact('topic', 'categories'));
	}

	public function update(TopicRequest $request, Topic $topic)
	{
		try {
			$this->authorize('update', $topic);
		} catch (AuthorizationException $e) {
			report($e);
			return redirect()->route('topics.show', $topic->id)->with('danger', '敏感操作!');
		};
		$topic->update($request->all());

		return redirect()->route('topics.show', $topic->id)->with('success', '更新成功!');
	}

	public function destroy(Topic $topic)
	{
		try {
			$this->authorize('destroy', $topic);
		} catch (AuthorizationException $e) {
			report($e);
			return redirect()->route('topics.show', $topic->id)->with('danger', '敏感操作!');
		}
		try {
			$topic->delete();
		} catch (\Exception $e) {
			report($e);
			return redirect()->route('topics.show', $topic->id)->with('danger', '删除失败!');
		}

		return redirect()->route('topics.index')->with('success', '删除成功!.');
	}

	public function uploadImage(Request $request, ImageUploadHandler $uploader)
	{
		// 初始化返回数据，默认是失败的
		$data = [
			'success'   => false,
			'msg'       => '上传失败!',
			'file_path' => '',
		];
		// 判断是否有上传文件，并赋值给 $file
		if ($file = $request->upload_file) {
			// 保存图片到本地
			$result = $uploader->save($request->upload_file, 'topics', \Auth::id(), 1024);
			// 图片保存成功的话
			if ($result) {
				$data['file_path'] = $result['path'];
				$data['msg']       = "上传成功!";
				$data['success']   = true;
			}
		}
		return $data;
	}

}